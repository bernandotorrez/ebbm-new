#!/bin/bash

echo "========================================"
echo "   EBMP Basarnas - Docker Deployment"
echo "========================================"
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "[ERROR] Docker tidak berjalan!"
    echo "Silakan start Docker terlebih dahulu:"
    echo "  sudo systemctl start docker"
    exit 1
fi

# Check if .env exists
if [ ! -f .env ]; then
    echo "[ERROR] File .env tidak ditemukan!"
    echo ""
    echo "Silakan copy .env.production ke .env:"
    echo "  cp .env.production .env"
    echo ""
    echo "Kemudian edit file .env dan sesuaikan:"
    echo "  - APP_URL dengan domain Anda"
    echo "  - DB_PASSWORD dengan password yang aman"
    echo ""
    exit 1
fi

echo "[1/6] Stopping existing containers..."
docker-compose down

echo ""
echo "[2/6] Building Docker image (ini akan memakan waktu)..."
docker-compose build --no-cache

if [ $? -ne 0 ]; then
    echo "[ERROR] Build gagal!"
    exit 1
fi

echo ""
echo "[3/6] Starting containers..."
docker-compose up -d

if [ $? -ne 0 ]; then
    echo "[ERROR] Gagal start containers!"
    exit 1
fi

echo ""
echo "[4/6] Waiting for containers to be ready..."
sleep 15

echo ""
echo "[5/6] Running database migrations..."
docker-compose exec -T app php artisan migrate --force

echo ""
echo "[6/6] Checking container status..."
docker-compose ps

echo ""
echo "========================================"
echo "   Deployment Selesai!"
echo "========================================"
echo ""
echo "Aplikasi: http://localhost"
echo "Admin: http://localhost/admin"
echo ""
echo "Perintah berguna:"
echo "  - Lihat logs: docker-compose logs -f app"
echo "  - Clear cache: docker-compose exec app php artisan optimize:clear"
echo "  - Restart: docker-compose restart app"
echo ""
