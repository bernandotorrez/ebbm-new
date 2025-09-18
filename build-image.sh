#!/bin/bash
# Optimized Docker Build Script for Laravel Filament 3
# Provides better caching and build performance

set -e

# Colors for output
RED='\\033[0;31m'
GREEN='\\033[0;32m'
YELLOW='\\033[1;33m'
BLUE='\\033[0;34m'
NC='\\033[0m' # No Color

# Configuration
IMAGE_NAME=\"ebbm-php\"
BUILD_ARGS=\"\"
NO_CACHE=false
PUSH_IMAGE=false
REGISTRY=\"\"
TAG=\"latest\"
DOCKER_BUILDKIT=1

# Helper functions
print_header() {
    echo -e \"${BLUE}======================================================${NC}\"
    echo -e \"${BLUE}$1${NC}\"
    echo -e \"${BLUE}======================================================${NC}\"
}

print_success() {
    echo -e \"${GREEN}✓ $1${NC}\"
}

print_warning() {
    echo -e \"${YELLOW}⚠ $1${NC}\"
}

print_error() {
    echo -e \"${RED}✗ $1${NC}\"
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --no-cache)
            NO_CACHE=true
            shift
            ;;
        --push)
            PUSH_IMAGE=true
            shift
            ;;
        --registry)
            REGISTRY=\"$2\"
            shift 2
            ;;
        --tag)
            TAG=\"$2\"
            shift 2
            ;;
        --php-version)
            BUILD_ARGS=\"$BUILD_ARGS --build-arg PHP_VERSION=$2\"
            shift 2
            ;;
        --help)
            echo \"Usage: $0 [OPTIONS]\"
            echo \"Options:\"
            echo \"  --no-cache      Build without using cache\"
            echo \"  --push          Push image to registry after build\"
            echo \"  --registry REG  Registry to push to\"
            echo \"  --tag TAG       Image tag (default: latest)\"
            echo \"  --php-version   PHP version to build (default: 8.3.17)\"
            echo \"  --help          Show this help message\"
            exit 0
            ;;
        *)
            print_error \"Unknown option: $1\"
            exit 1
            ;;
    esac
done

# Validate Docker
if ! command -v docker &> /dev/null; then
    print_error \"Docker is not installed or not in PATH\"
    exit 1
fi

# Enable BuildKit for better performance
export DOCKER_BUILDKIT=1
export COMPOSE_DOCKER_CLI_BUILD=1

print_header \"Building Optimized Laravel Filament 3 Docker Image\"

# Build image with multi-stage optimization
echo -e \"${YELLOW}Building image: ${IMAGE_NAME}:${TAG}${NC}\"

BUILD_CMD=\"docker build\"

if [ \"$NO_CACHE\" = true ]; then
    BUILD_CMD=\"$BUILD_CMD --no-cache\"
    print_warning \"Building without cache\"
fi

# Add build arguments
BUILD_CMD=\"$BUILD_CMD $BUILD_ARGS\"

# Build with cache mount for composer
BUILD_CMD=\"$BUILD_CMD --target production\"
BUILD_CMD=\"$BUILD_CMD -f docker/php/Dockerfile\"
BUILD_CMD=\"$BUILD_CMD -t ${IMAGE_NAME}:${TAG}\"
BUILD_CMD=\"$BUILD_CMD .\"

echo -e \"${YELLOW}Executing: $BUILD_CMD${NC}\"

if eval $BUILD_CMD; then
    print_success \"Docker image built successfully: ${IMAGE_NAME}:${TAG}\"
else
    print_error \"Docker build failed\"
    exit 1
fi

# Tag for registry if specified
if [ -n \"$REGISTRY\" ]; then
    FULL_IMAGE=\"${REGISTRY}/${IMAGE_NAME}:${TAG}\"
    docker tag \"${IMAGE_NAME}:${TAG}\" \"$FULL_IMAGE\"
    print_success \"Tagged image: $FULL_IMAGE\"
fi

# Push to registry if requested
if [ \"$PUSH_IMAGE\" = true ]; then
    if [ -n \"$REGISTRY\" ]; then
        echo -e \"${YELLOW}Pushing image to registry...${NC}\"
        if docker push \"$FULL_IMAGE\"; then
            print_success \"Image pushed successfully: $FULL_IMAGE\"
        else
            print_error \"Failed to push image\"
            exit 1
        fi
    else
        print_error \"Registry not specified for push operation\"
        exit 1
    fi
fi

# Show image information
echo -e \"${BLUE}Image Information:${NC}\"
docker images | grep \"$IMAGE_NAME\" | head -5

# Clean up dangling images
echo -e \"${YELLOW}Cleaning up dangling images...${NC}\"
docker image prune -f

print_success \"Build completed successfully!\"

# Show usage instructions
echo -e \"${BLUE}Usage Instructions:${NC}\"
echo -e \"  Development: docker-compose up -d\"
echo -e \"  Production:  docker-compose -f docker-compose.prod.yml up -d\"
echo -e \"  With dev tools: docker-compose --profile dev up -d\"