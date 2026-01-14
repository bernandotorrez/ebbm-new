#!/bin/bash

# Deploy Script dengan SSL
# Usage: ./deploy-with-ssl.sh

set -e

echo "=========================================="
echo "  Deployment Laravel + Nginx + SSL"
echo "=========================================="
echo ""

# Check SSL files
echo "🔍 Checking SSL files..."
if [ ! -f "docker/ssl/fullchain.pem" ]; then
    echo "❌ Error: docker/ssl/fullchain.pem not found!"
    echo "   Please copy SSL certificate to docker/ssl/"
    exit 1
fi

if [ ! -f "docker/ssl/basarnas.go.id.key" ]; then
    echo "❌ Error: docker/ssl/basarnas.go.id.key not found!"
    echo "   Please copy SSL key to docker/ssl/"
    exit 1
fi

echo "✅ SSL files found"

# Fix SSL permissions
echo ""
echo "🔧 Setting SSL permissions..."
chmod 644 docker/ssl/fullchain.pem
chmod 600 docker/ssl/basarnas.go.id.key
echo "✅ SSL permissions set"

# Check .env file
echo ""
echo "🔍 Checking .env file..."
if [ ! -f ".env" ]; then
    echo "❌ Error: .env file not found!"
    echo "   Please copy .env.example to .env and configure it"
    exit 1
fi
echo "✅ .env file found"

# Stop existing containers
echo ""
echo "🛑 Stopping existing containers..."
docker-compose down
echo "✅ Containers stopped"

# Build images
echo ""
echo "🏗️  Building Docker images..."
docker-compose build --no-cache
echo "✅ Images built"

# Start containers
echo ""
echo "🚀 Starting containers..."
docker-compose up -d
echo "✅ Containers started"

# Wait for containers to be ready
echo ""
echo "⏳ Waiting for containers to be ready..."
sleep 10

# Check container status
echo ""
echo "📊 Container status:"
docker-compose ps

# Check logs for errors
echo ""
echo "🔍 Checking for errors in logs..."
if docker-compose logs | grep -i "error" | grep -v "error_log"; then
    echo "⚠️  Warning: Some errors found in logs"
    echo "   Please check: docker-compose logs"
else
    echo "✅ No critical errors found"
fi

# Test HTTP redirect
echo ""
echo "🧪 Testing HTTP redirect..."
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/ || echo "000")
if [ "$HTTP_STATUS" = "301" ] || [ "$HTTP_STATUS" = "302" ]; then
    echo "✅ HTTP redirects to HTTPS (Status: $HTTP_STATUS)"
else
    echo "⚠️  HTTP status: $HTTP_STATUS (expected 301 or 302)"
fi

# Test HTTPS
echo ""
echo "🧪 Testing HTTPS..."
HTTPS_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -k https://localhost/ || echo "000")
if [ "$HTTPS_STATUS" = "200" ]; then
    echo "✅ HTTPS working (Status: $HTTPS_STATUS)"
else
    echo "⚠️  HTTPS status: $HTTPS_STATUS (expected 200)"
fi

# Final message
echo ""
echo "=========================================="
echo "  Deployment Complete!"
echo "=========================================="
echo ""
echo "📝 Next steps:"
echo "   1. Check logs: docker-compose logs -f"
echo "   2. Test access: https://e-bmp.basarnas.go.id"
echo "   3. Login admin: https://e-bmp.basarnas.go.id/admin"
echo ""
echo "📚 Documentation:"
echo "   - Full guide: DEPLOYMENT-FINAL.md"
echo "   - Troubleshooting: DEPLOYMENT-FINAL.md#troubleshooting"
echo ""
