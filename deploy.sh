#!/bin/bash

# Deployment script for Unix/Linux/macOS

echo "Building and deploying Docker containers..."

# Check if Docker is running
if ! docker info >/dev/null 2>&1; then
    echo "Error: Docker is not running. Please start Docker and try again."
    exit 1
fi

# Build and start containers
if docker-compose up -d --build; then
    echo "Containers built and started successfully!"
else
    echo "Error occurred while building/starting containers."
    exit 1
fi

# Show running containers
echo ""
echo "Running containers:"
docker-compose ps

echo ""
echo "Deployment completed!"