@echo off
REM Deploy Script dengan SSL untuk Windows
REM Usage: deploy-with-ssl.bat

echo ==========================================
echo   Deployment Laravel + Nginx + SSL
echo ==========================================
echo.

REM Check SSL files
echo Checking SSL files...
if not exist "docker\ssl\fullchain.pem" (
    echo Error: docker\ssl\fullchain.pem not found!
    echo Please copy SSL certificate to docker\ssl\
    exit /b 1
)

if not exist "docker\ssl\basarnas.go.id.key" (
    echo Error: docker\ssl\basarnas.go.id.key not found!
    echo Please copy SSL key to docker\ssl\
    exit /b 1
)

echo SSL files found
echo.

REM Check .env file
echo Checking .env file...
if not exist ".env" (
    echo Error: .env file not found!
    echo Please copy .env.example to .env and configure it
    exit /b 1
)
echo .env file found
echo.

REM Stop existing containers
echo Stopping existing containers...
docker-compose down
echo Containers stopped
echo.

REM Build images
echo Building Docker images...
docker-compose build --no-cache
echo Images built
echo.

REM Start containers
echo Starting containers...
docker-compose up -d
echo Containers started
echo.

REM Wait for containers
echo Waiting for containers to be ready...
timeout /t 10 /nobreak > nul
echo.

REM Check container status
echo Container status:
docker-compose ps
echo.

REM Final message
echo ==========================================
echo   Deployment Complete!
echo ==========================================
echo.
echo Next steps:
echo   1. Check logs: docker-compose logs -f
echo   2. Test access: https://e-bmp.basarnas.go.id
echo   3. Login admin: https://e-bmp.basarnas.go.id/admin
echo.
echo Documentation:
echo   - Full guide: DEPLOYMENT-FINAL.md
echo   - Troubleshooting: DEPLOYMENT-FINAL.md
echo.
pause
