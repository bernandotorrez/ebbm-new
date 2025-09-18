#!/bin/bash

# Laravel Docker Deployment Script
# Usage: ./deploy.sh [environment]
# Environment: development (default) | production

set -e

ENVIRONMENT=${1:-development}
PROJECT_NAME="ebbm-new"

echo "🚀 Starting Laravel Docker deployment for: $ENVIRONMENT"

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is running
check_docker() {
    if ! docker info >/dev/null 2>&1; then
        log_error "Docker is not running. Please start Docker and try again."
        exit 1
    fi
    log_success "Docker is running"
}

# Setup environment
setup_environment() {
    log_info "Setting up environment for: $ENVIRONMENT"
    
    if [ "$ENVIRONMENT" = "production" ]; then
        if [ ! -f .env.production ]; then
            log_error ".env.production file not found!"
            exit 1
        fi
        cp .env.production .env
        COMPOSE_FILE="docker-compose.prod.yml"
    else
        if [ ! -f .env ]; then
            if [ -f .env.example ]; then
                cp .env.example .env
                log_warning "Created .env from .env.example. Please update configuration."
            else
                log_error "No .env file found and no .env.example to copy from!"
                exit 1
            fi
        fi
        COMPOSE_FILE="docker-compose.yml"
    fi
    
    log_success "Environment configured"
}

# Build and start containers
build_containers() {
    log_info "Building Docker containers..."
    
    # Clean up any existing containers
    docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME down --remove-orphans
    
    # Build containers with no cache for production
    if [ "$ENVIRONMENT" = "production" ]; then
        docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME build --no-cache --pull
    else
        docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME build
    fi
    
    log_success "Containers built successfully"
}

# Start services
start_services() {
    log_info "Starting services..."
    
    docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME up -d
    
    # Wait for services to be ready
    log_info "Waiting for services to start..."
    sleep 30
    
    # Check if services are healthy
    if docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME ps | grep -q "unhealthy\|Exit"; then
        log_error "Some services failed to start properly"
        docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME logs
        exit 1
    fi
    
    log_success "All services started successfully"
}

# Laravel setup
setup_laravel() {
    log_info "Setting up Laravel application..."
    
    # Generate application key if not exists
    if ! grep -q "APP_KEY=base64:" .env; then
        log_info "Generating application key..."
        docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME exec php php artisan key:generate --ansi
    fi
    
    # Wait for database to be ready
    log_info "Waiting for database to be ready..."
    sleep 10
    
    # Run migrations
    log_info "Running database migrations..."
    docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME exec php php artisan migrate --force --no-interaction
    
    # Clear and cache configuration for production
    if [ "$ENVIRONMENT" = "production" ]; then
        log_info "Optimizing Laravel for production..."
        docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME exec php php artisan config:cache
        docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME exec php php artisan route:cache
        docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME exec php php artisan view:cache
        docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME exec php php artisan event:cache
    else
        log_info "Clearing Laravel caches for development..."
        docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME exec php php artisan config:clear
        docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME exec php php artisan route:clear
        docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME exec php php artisan view:clear
        docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME exec php php artisan cache:clear
    fi
    
    # Set proper permissions
    log_info "Setting file permissions..."
    docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME exec php chown -R www-data:www-data /var/www/html/storage
    docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME exec php chown -R www-data:www-data /var/www/html/bootstrap/cache
    
    log_success "Laravel setup completed"
}

# Display application info
show_info() {
    log_success "🎉 Deployment completed successfully!"
    echo ""
    echo "Application Information:"
    echo "======================="
    echo "Environment: $ENVIRONMENT"
    echo "Project: $PROJECT_NAME"
    echo ""
    echo "Services:"
    echo "- Web Application: http://localhost"
    echo "- Database: localhost:3306"
    echo "- Redis: localhost:6379"
    
    if [ "$ENVIRONMENT" = "development" ]; then
        echo "- Mailhog (Email testing): http://localhost:8025"
    fi
    
    echo ""
    echo "Useful Commands:"
    echo "==============="
    echo "View logs: docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME logs -f"
    echo "Stop services: docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME down"
    echo "Restart services: docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME restart"
    echo "Laravel Artisan: docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME exec php php artisan"
    echo ""
}

# Cleanup function
cleanup() {
    if [ $? -ne 0 ]; then
        log_error "Deployment failed. Cleaning up..."
        docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME down --remove-orphans
    fi
}

# Set trap for cleanup
trap cleanup EXIT

# Main execution
main() {
    log_info "Laravel Docker Deployment Started"
    echo "=================================="
    
    check_docker
    setup_environment
    build_containers
    start_services
    setup_laravel
    show_info
}

# Run main function
main