#!/bin/bash

# All-in-one setup script for Linux
set -e

echo "========================================"
echo "   EBMP Basarnas - Complete Setup"
echo "========================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
    echo -e "${RED}[ERROR] Please run as normal user (not root)${NC}"
    echo "This script will ask for sudo when needed"
    exit 1
fi

# Check Docker
echo -e "${YELLOW}[1/8] Checking Docker...${NC}"
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Docker not found!${NC}"
    echo "Install Docker first: https://docs.docker.com/engine/install/"
    exit 1
fi

if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}Docker not running!${NC}"
    echo "Start Docker: sudo systemctl start docker"
    exit 1
fi
echo -e "${GREEN}✓ Docker OK${NC}"
echo ""

# Check docker-compose
echo -e "${YELLOW}[2/8] Checking docker-compose...${NC}"
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}docker-compose not found!${NC}"
    echo "Install: sudo apt install docker-compose"
    exit 1
fi
echo -e "${GREEN}✓ docker-compose OK${NC}"
echo ""

# Make scripts executable
echo -e "${YELLOW}[3/8] Making scripts executable...${NC}"
chmod +x *.sh
echo -e "${GREEN}✓ Scripts executable${NC}"
echo ""

# Setup .env
echo -e "${YELLOW}[4/8] Setting up .env file...${NC}"
if [ ! -f .env ]; then
    cp .env.production .env
    echo -e "${GREEN}✓ .env created from .env.production${NC}"
    echo ""
    echo -e "${YELLOW}IMPORTANT: Edit .env file!${NC}"
    echo "You need to update:"
    echo "  - APP_URL=http://yourdomain.com"
    echo "  - DB_PASSWORD=your_secure_password"
    echo ""
    read -p "Press Enter to edit .env now (or Ctrl+C to skip)..."
    ${EDITOR:-nano} .env
else
    echo -e "${GREEN}✓ .env already exists${NC}"
fi
echo ""

# Get domain
echo -e "${YELLOW}[5/8] Domain configuration...${NC}"
read -p "Enter your domain (e.g., example.com) or press Enter to skip: " DOMAIN

if [ ! -z "$DOMAIN" ]; then
    echo "Updating nginx config with domain: $DOMAIN"
    
    # Backup original
    cp docker/nginx/default.conf docker/nginx/default.conf.backup
    
    # Update server_name
    sed -i "s/server_name localhost xxx.com www.xxx.com _;/server_name localhost $DOMAIN www.$DOMAIN _;/" docker/nginx/default.conf
    
    echo -e "${GREEN}✓ Nginx config updated${NC}"
    
    # Update .env APP_URL
    sed -i "s|APP_URL=.*|APP_URL=http://$DOMAIN|" .env
    echo -e "${GREEN}✓ .env APP_URL updated${NC}"
else
    echo "Skipping domain configuration"
fi
echo ""

# Setup firewall
echo -e "${YELLOW}[6/8] Setting up firewall...${NC}"
if command -v ufw &> /dev/null; then
    echo "UFW detected. Setting up firewall rules..."
    echo "This requires sudo access."
    
    sudo ufw allow 22/tcp comment 'SSH'
    sudo ufw allow 80/tcp comment 'HTTP'
    sudo ufw allow 443/tcp comment 'HTTPS'
    
    # Enable UFW if not already enabled
    sudo ufw --force enable
    
    echo -e "${GREEN}✓ Firewall configured${NC}"
    sudo ufw status
else
    echo -e "${YELLOW}UFW not found, skipping firewall setup${NC}"
fi
echo ""

# Build and start
echo -e "${YELLOW}[7/8] Building and starting containers...${NC}"
echo "This will take several minutes..."
echo ""

docker-compose down 2>/dev/null || true
docker-compose build --no-cache

if [ $? -ne 0 ]; then
    echo -e "${RED}[ERROR] Build failed!${NC}"
    exit 1
fi

docker-compose up -d

if [ $? -ne 0 ]; then
    echo -e "${RED}[ERROR] Failed to start containers!${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Containers started${NC}"
echo ""

# Wait for containers
echo "Waiting for containers to be ready..."
sleep 15

# Verify
echo -e "${YELLOW}[8/8] Verifying installation...${NC}"
echo ""

echo "Container status:"
docker-compose ps
echo ""

echo "Testing localhost..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200\|302"; then
    echo -e "${GREEN}✓ Localhost accessible${NC}"
else
    echo -e "${RED}✗ Localhost not accessible${NC}"
fi
echo ""

if [ ! -z "$DOMAIN" ]; then
    echo "Testing domain: $DOMAIN"
    if curl -s -o /dev/null -w "%{http_code}" http://$DOMAIN | grep -q "200\|302"; then
        echo -e "${GREEN}✓ Domain accessible${NC}"
    else
        echo -e "${YELLOW}⚠ Domain not accessible yet${NC}"
        echo "This is normal if DNS hasn't propagated yet (wait 1-24 hours)"
    fi
    echo ""
fi

echo "========================================"
echo -e "${GREEN}   Setup Complete!${NC}"
echo "========================================"
echo ""
echo "Access your application:"
echo "  - http://localhost"
if [ ! -z "$DOMAIN" ]; then
    echo "  - http://$DOMAIN"
    echo "  - http://www.$DOMAIN"
fi
echo "  - Admin: http://localhost/admin"
echo ""
echo "Useful commands:"
echo "  - View logs: docker-compose logs -f app"
echo "  - Restart: docker-compose restart app"
echo "  - Stop: docker-compose down"
echo "  - Clear cache: docker-compose exec app php artisan optimize:clear"
echo ""
echo "Troubleshooting:"
echo "  - Check domain: ./check-domain.sh $DOMAIN"
echo "  - Fix issues: ./fix-domain.sh"
echo ""
echo "Documentation:"
echo "  - LINUX-QUICK-START.md"
echo "  - README-LINUX-DEPLOYMENT.md"
echo "  - DEPLOYMENT-README.md"
echo ""

# Show logs
read -p "Show container logs? (y/n): " SHOW_LOGS
if [ "$SHOW_LOGS" = "y" ] || [ "$SHOW_LOGS" = "Y" ]; then
    docker-compose logs --tail=50 app
fi

echo ""
echo -e "${GREEN}Done! 🎉${NC}"
