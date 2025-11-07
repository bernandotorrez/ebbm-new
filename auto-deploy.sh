#!/bin/bash

echo "=== Laravel Docker Deployment ==="
echo ""

# -- 1. Tentukan perintah docker (docker atau sudo docker)
DOCKER="docker"
if ! $DOCKER info >/dev/null 2>&1; then
    # coba pakai sudo
    if sudo docker info >/dev/null 2>&1; then
        DOCKER="sudo docker"
    else
        echo "Error: Docker is not running or you don't have permission to access the Docker daemon."
        echo "Hint: jalankan: sudo usermod -aG docker \$USER && logout/login"
        exit 1
    fi
fi

echo "Docker is running and accessible via: $DOCKER"
echo ""

# -- 2. Deteksi docker compose (plugin baru atau binary lama)
if $DOCKER compose version >/dev/null 2>&1; then
    DC="$DOCKER compose"
elif docker-compose version >/dev/null 2>&1; then
    DC="docker-compose"
else
    echo "Error: Docker Compose not found."
    exit 1
fi

echo "Using Docker Compose command: $DC"
echo ""

echo "Stopping any existing containers..."
$DC down

echo ""
echo "Building and starting containers..."
if $DC up -d --build; then
    echo "Containers built and started successfully!"
else
    echo "Error occurred while building/starting containers."
    echo "Checking logs for details..."
    $DC logs
    exit 1
fi

echo ""
echo "Waiting for containers to initialize..."
sleep 15

echo ""
echo "Running containers:"
$DC ps

echo ""
echo "Monitoring logs for errors..."
echo ""

check_and_fix_errors() {
    ERROR_FOUND=0

    echo "Checking app container logs..."
    if $DC logs app 2>&1 | grep -iE "permission|failed|error|exception|fatal"; then
        ERROR_FOUND=1
        echo "Errors detected in app container. Attempting to fix..."

        if $DC logs app 2>&1 | grep -i "permission"; then
            echo "Fixing storage permissions..."
            $DC exec app chown -R www-data:www-data /var/www/html/storage >/dev/null 2>&1
            $DC exec app chmod -R 755 /var/www/html/storage >/dev/null 2>&1
        fi
    fi

    echo "Checking MySQL container logs..."
    if $DC logs mysql 2>&1 | grep -iE "error|fatal"; then
        ERROR_FOUND=1
        echo "Errors detected in MySQL container."
    fi

    return $ERROR_FOUND
}

if check_and_fix_errors; then
    echo ""
    echo "No critical errors found in logs."
else
    echo ""
    echo "Errors were found. Attempting to restart containers..."
    $DC restart
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

$DC ps
