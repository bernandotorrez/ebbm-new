#!/bin/bash

# Deploy and check script for Unix/Linux/macOS

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

# Wait a moment for containers to initialize
sleep 10

# Show running containers
echo ""
echo "Running containers:"
docker-compose ps

# Check logs for errors
echo ""
echo "Checking for errors in container logs..."
echo ""

# Check app container logs
echo "=== App Container Logs ==="
if docker-compose logs app 2>&1 | grep -i "error\|fatal"; then
    echo "Errors found in app logs above."
else
    echo "No critical errors found in app logs."
fi

echo ""
echo "=== MySQL Container Logs ==="
if docker-compose logs mysql 2>&1 | grep -i "error\|fatal"; then
    echo "Errors found in MySQL logs above."
else
    echo "No critical errors found in MySQL logs."
fi

echo ""
echo "Deployment and error check completed!"