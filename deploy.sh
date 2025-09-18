#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Default values
ENVIRONMENT="local"
BUILD_FRESH=false
WITH_DEV_TOOLS=false

# Function to show help
show_help() {
    echo "Usage: ./deploy.sh [options]"
    echo ""
    echo "Options:"
    echo "  --env ENVIRONMENT    Set environment (local/production) [default: local]"
    echo "  --fresh             Remove all images and rebuild from scratch"
    echo "  --dev               Include development tools (PHPMyAdmin, Redis Commander)"
    echo "  --help              Show this help message"
    echo ""
    echo "Examples:"
    echo "  ./deploy.sh                           - Deploy local environment"
    echo "  ./deploy.sh --env production          - Deploy production environment"
    echo "  ./deploy.sh --fresh --dev             - Fresh build with dev tools"
    echo "  ./deploy.sh --env production --fresh  - Fresh production deployment"
    echo ""
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --env)
            ENVIRONMENT="$2"
            shift 2
            ;;
        --fresh)
            BUILD_FRESH=true
            shift
            ;;
        --dev)
            WITH_DEV_TOOLS=true
            shift
            ;;
        --help)
            show_help
            exit 0
            ;;
        *)
            echo -e "${RED}Unknown option: $1${NC}"
            show_help
            exit 1
            ;;
    esac
done

echo "======================================================"
echo "EBBM Laravel Project Docker Deployment Script"
echo "======================================================"
echo ""

echo -e "${BLUE}Environment: ${ENVIRONMENT}${NC}"
echo -e "${BLUE}Fresh build: ${BUILD_FRESH}${NC}"
echo -e "${BLUE}Dev tools: ${WITH_DEV_TOOLS}${NC}"
echo ""

# Check if Docker is running
echo -e "${YELLOW}Checking Docker...${NC}"
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Error: Docker is not installed${NC}"
    echo "Please install Docker and ensure it's running"
    exit 1
fi

if ! docker info &> /dev/null; then
    echo -e "${RED}Error: Docker is not running${NC}"
    echo "Please start Docker daemon"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}Error: Docker Compose is not available${NC}"
    exit 1
fi

echo -e "${GREEN}Docker is ready!${NC}"
echo ""

# Environment-specific setup
if [ "$ENVIRONMENT" = "production" ]; then
    echo -e "${YELLOW}Setting up production environment...${NC}"
    
    # Check if .env.production exists
    if [ ! -f ".env.production" ]; then
        echo -e "${RED}Error: .env.production file not found${NC}"
        echo "Please copy .env.production template and configure it"
        exit 1
    fi
    
    # Create secrets directory
    mkdir -p secrets
    
    # Create secret files if they don't exist
    if [ ! -f "secrets/mysql_root_password.txt" ]; then
        echo "your_secure_root_password" > secrets/mysql_root_password.txt
        echo -e "${YELLOW}Created secrets/mysql_root_password.txt with default password${NC}"
        echo -e "${RED}Please update this file with a secure password!${NC}"
    fi
    
    if [ ! -f "secrets/mysql_password.txt" ]; then
        echo "your_secure_database_password" > secrets/mysql_password.txt
        echo -e "${YELLOW}Created secrets/mysql_password.txt with default password${NC}"
        echo -e "${RED}Please update this file with a secure password!${NC}"
    fi
    
    COMPOSE_FILE="docker-compose.prod.yml"
    PROJECT_NAME="ebbm-prod"
else
    echo -e "${YELLOW}Setting up ${ENVIRONMENT} environment...${NC}"
    COMPOSE_FILE="docker-compose.yml"
    PROJECT_NAME="ebbm-dev"
fi

# Stop existing containers
echo -e "${YELLOW}Stopping existing containers...${NC}"
docker-compose -f "$COMPOSE_FILE" -p "$PROJECT_NAME" down

# Remove old images if fresh build
if [ "$BUILD_FRESH" = true ]; then
    echo -e "${YELLOW}Removing old images...${NC}"
    docker-compose -f "$COMPOSE_FILE" -p "$PROJECT_NAME" down --rmi all --volumes --remove-orphans
    docker system prune -f
fi

# Build and start containers
echo -e "${YELLOW}Building and starting containers...${NC}"

if [ "$WITH_DEV_TOOLS" = true ]; then
    docker-compose -f "$COMPOSE_FILE" -p "$PROJECT_NAME" --profile dev up -d --build
else
    docker-compose -f "$COMPOSE_FILE" -p "$PROJECT_NAME" up -d --build
fi

if [ $? -ne 0 ]; then
    echo -e "${RED}Error: Failed to start containers${NC}"
    echo "Check the logs for more information:"
    echo "docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME logs"
    exit 1
fi

# Wait for containers to be ready
echo -e "${YELLOW}Waiting for containers to be ready...${NC}"
sleep 10

# Show container status
echo ""
echo -e "${GREEN}Container Status:${NC}"
docker-compose -f "$COMPOSE_FILE" -p "$PROJECT_NAME" ps

# Show access information
echo ""
echo -e "${GREEN}======================================================${NC}"
echo -e "${GREEN}Deployment completed successfully!${NC}"
echo -e "${GREEN}======================================================${NC}"
echo ""
echo -e "${BLUE}Application URL:${NC} http://localhost"
if [ "$WITH_DEV_TOOLS" = true ]; then
    echo -e "${BLUE}PHPMyAdmin:${NC} http://localhost:8080"
    echo -e "${BLUE}Redis Commander:${NC} http://localhost:8081"
fi
if [ "$ENVIRONMENT" = "local" ]; then
    echo -e "${BLUE}Vite Dev Server:${NC} http://localhost:5173"
fi
echo ""
echo -e "${YELLOW}Useful commands:${NC}"
echo "docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME logs    - View logs"
echo "docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME exec php bash - Access PHP container"
echo "docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME down   - Stop containers"
echo ""