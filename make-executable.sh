#!/bin/bash

# Make all shell scripts executable
echo "Making shell scripts executable..."

chmod +x setup-firewall.sh
chmod +x fix-domain.sh
chmod +x deploy-production.sh
chmod +x quick-deploy.sh
chmod +x check-domain.sh
chmod +x deploy.sh
chmod +x deploy-silent.sh
chmod +x deploy-after-pull.sh
chmod +x deploy-and-check.sh
chmod +x auto-deploy.sh
chmod +x check-logs.sh
chmod +x init-app.sh

echo "✅ Done! All scripts are now executable."
echo ""
echo "Available scripts:"
echo "  ./deploy-production.sh    - Full production deployment"
echo "  ./quick-deploy.sh         - Quick deploy (no prompts)"
echo "  ./fix-domain.sh           - Domain troubleshooting"
echo "  ./check-domain.sh         - Domain diagnostic"
echo "  ./setup-firewall.sh       - Setup firewall (requires sudo)"
echo ""
