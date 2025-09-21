#!/bin/bash

# Log checking script for Unix/Linux/macOS

echo "Checking Docker container logs..."

# Check if containers are running
docker-compose ps

echo ""
echo "=== App Container Logs ==="
docker-compose logs app

echo ""
echo "=== MySQL Container Logs ==="
docker-compose logs mysql

echo ""
echo "Log check completed!"