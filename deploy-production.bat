@echo off
echo ========================================
echo   EBMP Basarnas - Docker Deployment
echo ========================================
echo.

REM Check if Docker is running
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker tidak berjalan!
    echo Silakan start Docker Desktop terlebih dahulu.
    pause
    exit /b 1
)

REM Check if .env exists
if not exist .env (
    echo [ERROR] File .env tidak ditemukan!
    echo.
    echo Silakan copy .env.production ke .env:
    echo   copy .env.production .env
    echo.
    echo Kemudian edit file .env dan sesuaikan:
    echo   - APP_URL dengan domain Anda
    echo   - DB_PASSWORD dengan password yang aman
    echo.
    pause
    exit /b 1
)

echo [1/6] Stopping existing containers...
docker-compose down

echo.
echo [2/6] Building Docker image (ini akan memakan waktu)...
docker-compose build --no-cache

if errorlevel 1 (
    echo [ERROR] Build gagal!
    pause
    exit /b 1
)

echo.
echo [3/6] Starting containers...
docker-compose up -d

if errorlevel 1 (
    echo [ERROR] Gagal start containers!
    pause
    exit /b 1
)

echo.
echo [4/6] Waiting for containers to be ready...
timeout /t 15 /nobreak > nul

echo.
echo [5/6] Running database migrations...
docker-compose exec -T app php artisan migrate --force

echo.
echo [6/6] Checking container status...
docker-compose ps

echo.
echo ========================================
echo   Deployment Selesai!
echo ========================================
echo.
echo Aplikasi: http://localhost
echo Admin: http://localhost/admin
echo.
echo Perintah berguna:
echo   - Lihat logs: docker-compose logs -f app
echo   - Clear cache: docker-compose exec app php artisan optimize:clear
echo   - Restart: docker-compose restart app
echo.
pause
