@echo off
echo ========================================
echo   Domain Troubleshooting Script
echo ========================================
echo.

REM Get domain from user
set /p DOMAIN="Masukkan domain Anda (contoh: example.com): "

if "%DOMAIN%"=="" (
    echo [ERROR] Domain tidak boleh kosong!
    pause
    exit /b 1
)

echo.
echo Domain yang akan digunakan: %DOMAIN%
echo.

echo [1/6] Checking DNS resolution...
nslookup %DOMAIN%
echo.
pause

echo [2/6] Checking Docker containers...
docker-compose ps
echo.
pause

echo [3/6] Checking port 80...
netstat -an | findstr :80
echo.
pause

echo [4/6] Updating nginx config...
echo Silakan edit file docker/nginx/default.conf
echo Ganti: server_name localhost;
echo Dengan: server_name localhost %DOMAIN% www.%DOMAIN% _;
echo.
pause

echo [5/6] Rebuilding and restarting containers...
docker-compose down
docker-compose build --no-cache
docker-compose up -d
echo.

echo [6/6] Waiting for containers to start...
timeout /t 10 /nobreak > nul

echo.
echo Testing localhost...
curl -I http://localhost
echo.

echo.
echo ========================================
echo   Troubleshooting Complete!
echo ========================================
echo.
echo Silakan test akses:
echo   - http://%DOMAIN%
echo   - http://www.%DOMAIN%
echo.
echo Jika masih tidak bisa, cek:
echo   1. Firewall server (allow port 80)
echo   2. Cloud security group (allow port 80)
echo   3. DNS propagation (tunggu 1-24 jam)
echo.
echo Lihat logs dengan: docker-compose logs -f app
echo.
pause
