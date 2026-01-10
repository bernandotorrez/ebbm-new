#!/bin/bash

# Domain diagnostic script
if [ -z "$1" ]; then
    echo "Usage: ./check-domain.sh yourdomain.com"
    exit 1
fi

DOMAIN=$1

echo "========================================"
echo "   Domain Diagnostic: $DOMAIN"
echo "========================================"
echo ""

echo "1️⃣  DNS Resolution"
echo "-------------------"
nslookup $DOMAIN
echo ""

echo "2️⃣  Container Status"
echo "-------------------"
docker-compose ps
echo ""

echo "3️⃣  Port 80 Listening"
echo "-------------------"
netstat -tuln | grep :80 || echo "Port 80 not listening!"
echo ""

echo "4️⃣  Firewall Status"
echo "-------------------"
if command -v ufw &> /dev/null; then
    sudo ufw status | grep -E "80|443" || echo "Port 80/443 not allowed in UFW"
else
    echo "UFW not installed"
fi
echo ""

echo "5️⃣  Nginx Config Check"
echo "-------------------"
docker-compose exec app cat /etc/nginx/http.d/default.conf | grep server_name || echo "Cannot read nginx config"
echo ""

echo "6️⃣  Test from Server"
echo "-------------------"
echo "Testing localhost..."
curl -I http://localhost 2>/dev/null | head -n 1 || echo "❌ Failed"

echo "Testing domain..."
curl -I http://$DOMAIN 2>/dev/null | head -n 1 || echo "❌ Failed"
echo ""

echo "7️⃣  Recent Logs"
echo "-------------------"
docker-compose logs --tail=20 app
echo ""

echo "========================================"
echo "   Diagnostic Complete"
echo "========================================"
echo ""
echo "If domain not accessible:"
echo "  1. Check DNS propagation (wait 1-24h)"
echo "  2. Allow port 80: sudo ufw allow 80/tcp"
echo "  3. Check cloud security group"
echo "  4. Update nginx server_name in docker/nginx/default.conf"
echo ""
