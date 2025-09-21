#!/bin/bash

# Automated deployment and error fixing script for Unix/Linux/macOS

echo "=== Laravel Docker Deployment ==="
echo ""

# Check if Docker is running
echo "Checking if Docker is running..."
if ! docker info >/dev/null 2>&1; then
    echo "Error: Docker is not running. Please start Docker and try again."
    exit 1
fi

echo "Docker is running."
echo ""

# Stop any existing containers
echo "Stopping any existing containers..."
docker-compose down

# Build and start containers
echo ""
echo "Building and starting containers..."
if docker-compose up -d --build; then
    echo "Containers built and started successfully!"
else
    echo "Error occurred while building/starting containers."
    echo "Checking logs for details..."
    docker-compose logs
    exit 1
fi

# Wait for containers to initialize
echo ""
echo "Waiting for containers to initialize..."
sleep 15

# Show running containers
echo ""
echo "Running containers:"
docker-compose ps

# Monitor logs for errors and attempt to fix
echo ""
echo "Monitoring logs for errors..."
echo ""

# Function to check for errors and attempt fixes
check_and_fix_errors() {
    ERROR_FOUND=0
    
    # Check app container logs for common errors
    echo "Checking app container logs..."
    if docker-compose logs app 2>&1 | grep -i "permission\|failed\|error\|exception\|fatal"; then
        ERROR_FOUND=1
        echo "Errors detected in app container. Attempting to fix..."
        
        # Try to fix common permission issues
        if docker-compose logs app 2>&1 | grep -i "permission"; then
            echo "Attempting to fix permission issues..."
            docker-compose exec app chown -R www-data:www-data /var/www/html/storage
            docker-compose exec app chmod -R 755 /var/www/html/storage
        fi
    fi
    
    # Check MySQL container logs for errors
    echo "Checking MySQL container logs..."
    if docker-compose logs mysql 2>&1 | grep -i "error\|fatal"; then
        ERROR_FOUND=1
        echo "Errors detected in MySQL container."
    fi
    
    return $ERROR_FOUND
}

# Check for errors and fix if found
if check_and_fix_errors; then
    echo ""
    echo "No critical errors found in logs."
else
    echo ""
    echo "Errors were found. Attempting to restart containers..."
    docker-compose restart
    sleep 10
    if check_and_fix_errors; then
        echo "Errors resolved after restart."
    else
        echo "Some errors persist after restart. Manual intervention may be required."
    fi
fi

echo ""
echo "=== Deployment completed successfully! ==="
echo "Application should be accessible at http://localhost"
echo ""

# Final status check
docker-compose ps