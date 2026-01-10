#!/bin/bash

# Quick deployment script - no prompts
set -e

echo "🚀 Quick Deploy Starting..."

# Check Docker
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker not running"
    exit 1
fi

# Check .env
if [ ! -f .env ]; then
    echo "❌ .env file not found"
    echo "Run: cp .env.production .env"
    exit 1
fi

echo "📦 Building..."
docker-compose down
docker-compose build --no-cache

echo "🔄 Starting..."
docker-compose up -d

echo "⏳ Waiting..."
sleep 10

echo "✅ Done!"
docker-compose ps

echo ""
echo "Access: http://localhost"
echo "Logs: docker-compose logs -f app"
