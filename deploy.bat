@echo off
REM Laravel Docker Deployment Script for Windows
REM Usage: deploy.bat [environment]
REM Environment: development (default) | production

setlocal EnableDelayedExpansion

set ENVIRONMENT=%1
if "%ENVIRONMENT%"=="" set ENVIRONMENT=development
set PROJECT_NAME=ebbm-new

echo 🚀 Starting Laravel Docker deployment for: %ENVIRONMENT%

REM Check if Docker is running
docker info >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker is not running. Please start Docker and try again.
    exit /b 1
)
echo [SUCCESS] Docker is running

REM Setup environment
echo [INFO] Setting up environment for: %ENVIRONMENT%

if "%ENVIRONMENT%"=="production" (
    if not exist .env.production (
        echo [ERROR] .env.production file not found!
        exit /b 1
    )
    copy .env.production .env >nul
    set COMPOSE_FILE=docker-compose.prod.yml
) else (
    if not exist .env (
        if exist .env.example (
            copy .env.example .env >nul
            echo [WARNING] Created .env from .env.example. Please update configuration.
        ) else (
            echo [ERROR] No .env file found and no .env.example to copy from!
            exit /b 1
        )
    )
    set COMPOSE_FILE=docker-compose.yml
)

echo [SUCCESS] Environment configured

REM Build containers
echo [INFO] Building Docker containers...

REM Clean up any existing containers
docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% down --remove-orphans

REM Build containers
if "%ENVIRONMENT%"=="production" (
    docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% build --no-cache --pull
) else (
    docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% build
)

if errorlevel 1 (
    echo [ERROR] Failed to build containers
    exit /b 1
)

echo [SUCCESS] Containers built successfully

REM Start services
echo [INFO] Starting services...

docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% up -d

if errorlevel 1 (
    echo [ERROR] Failed to start services
    exit /b 1
)

REM Wait for services to start
echo [INFO] Waiting for services to start...
timeout /t 30 /nobreak >nul

echo [SUCCESS] All services started successfully

REM Laravel setup
echo [INFO] Setting up Laravel application...

REM Generate application key if not exists
findstr /C:"APP_KEY=base64:" .env >nul
if errorlevel 1 (
    echo [INFO] Generating application key...
    docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% exec php php artisan key:generate --ansi
)

REM Wait for database
echo [INFO] Waiting for database to be ready...
timeout /t 10 /nobreak >nul

REM Run migrations
echo [INFO] Running database migrations...
docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% exec php php artisan migrate --force --no-interaction

if errorlevel 1 (
    echo [ERROR] Failed to run migrations
    exit /b 1
)

REM Configure Laravel based on environment
if "%ENVIRONMENT%"=="production" (
    echo [INFO] Optimizing Laravel for production...
    docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% exec php php artisan config:cache
    docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% exec php php artisan route:cache
    docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% exec php php artisan view:cache
    docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% exec php php artisan event:cache
) else (
    echo [INFO] Clearing Laravel caches for development...
    docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% exec php php artisan config:clear
    docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% exec php php artisan route:clear
    docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% exec php php artisan view:clear
    docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% exec php php artisan cache:clear
)

REM Set permissions
echo [INFO] Setting file permissions...
docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% exec php chown -R www-data:www-data /var/www/html/storage
docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% exec php chown -R www-data:www-data /var/www/html/bootstrap/cache

echo [SUCCESS] Laravel setup completed

REM Display information
echo.
echo [SUCCESS] 🎉 Deployment completed successfully!
echo.
echo Application Information:
echo =======================
echo Environment: %ENVIRONMENT%
echo Project: %PROJECT_NAME%
echo.
echo Services:
echo - Web Application: http://localhost
echo - Database: localhost:3306
echo - Redis: localhost:6379

if "%ENVIRONMENT%"=="development" (
    echo - Mailhog ^(Email testing^): http://localhost:8025
)

echo.
echo Useful Commands:
echo ===============
echo View logs: docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% logs -f
echo Stop services: docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% down
echo Restart services: docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% restart
echo Laravel Artisan: docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% exec php php artisan
echo.

pause