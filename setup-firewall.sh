#!/bin/bash

# Firewall setup script for Ubuntu/Debian
echo "========================================"
echo "   Firewall Setup for Web Server"
echo "========================================"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "Please run as root or with sudo"
    echo "Usage: sudo ./setup-firewall.sh"
    exit 1
fi

# Check if UFW is installed
if ! command -v ufw &> /dev/null; then
    echo "UFW not found. Installing..."
    apt-get update
    apt-get install -y ufw
fi

echo "Configuring UFW firewall..."
echo ""

# Allow SSH (important!)
# echo "✅ Allowing SSH (port 22)..."
# ufw allow 22/tcp

# Allow HTTP
echo "✅ Allowing HTTP (port 80)..."
ufw allow 80/tcp

# Allow HTTPS
echo "✅ Allowing HTTPS (port 443)..."
ufw allow 443/tcp

# Enable UFW
echo ""
echo "Enabling UFW..."
ufw --force enable

echo ""
echo "========================================"
echo "   Firewall Status"
echo "========================================"
ufw status verbose

echo ""
echo "✅ Firewall configured successfully!"
echo ""
echo "Allowed ports:"
echo "  - 22 (SSH)"
echo "  - 80 (HTTP)"
echo "  - 443 (HTTPS)"
echo ""
