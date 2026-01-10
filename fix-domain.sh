#!/bin/bash

echo "========================================"
echo "   Domain Troubleshooting Script"
echo "========================================"
echo ""

# Get domain from user
read -p "Masukkan domain Anda (contoh: example.com): " DOMAIN

if [ -z "$DOMAIN" ]; then
    echo "[ERROR] Domain tidak boleh kosong!"
    exit 1
fi

echo ""
echo "Domain yang akan digunakan: $DOMAIN"
echo ""

echo "[1/7] Checking DNS resolution..."
nslookup $DOMAIN
echo ""
read -p "Press Enter to continue..."

echo "[2/7] Checking Docker containers..."
docker-compose ps
echo ""
read -p "Press Enter to continue..."

echo "[3/7] Checking port 80..."
netstat -tuln | grep :80
echo ""
read -p "Press Enter to continue..."

echo "[4/7] Checking firewall (UFW)..."
if command -v ufw &> /dev/null; then
    sudo ufw status | grep -E "80|443"
else
    echo "UFW not installed, skipping..."
fi
echo ""
read -p "Press Enter to continue..."

echo "[5/7] Updating nginx config..."
echo "File: docker/nginx/default.conf"
echo "Ganti: server_name localhost;"
echo "Dengan: server_name localhost $DOMAIN www.$DOMAIN _;"
echo ""
read -p "Press Enter setelah edit file (atau skip jika sudah diedit)..."

echo "[6/7] Rebuilding and restarting containers..."
docker-compose down
docker-compose build --no-cache
docker-compose up -d
echo ""

echo "[7/7] Waiting for containers to start..."
sleep 10

echo ""
echo "Testing localhost..."
curl -I http://localhost 2>/dev/null || echo "Failed to connect to localhost"
echo ""

echo "Testing domain from server..."
curl -I http://$DOMAIN 2>/dev/null || echo "Failed to connect to $DOMAIN"
echo ""

echo "========================================"
echo "   Troubleshooting Complete!"
echo "========================================"
echo ""
echo "Silakan test akses:"
echo "  - http://$DOMAIN"
echo "  - http://www.$DOMAIN"
echo ""
echo "Jika masih tidak bisa, cek:"
echo "  1. Firewall server: sudo ufw allow 80/tcp"
echo "  2. Cloud security group (allow port 80)"
echo "  3. DNS propagation (tunggu 1-24 jam)"
echo ""
echo "Lihat logs dengan: docker-compose logs -f app"
echo ""
