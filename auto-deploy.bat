@echo off
REM Automated deployment and error fixing script for Windows

echo === Laravel Docker Deployment ===
echo.

REM Check if Docker is running
echo Checking if Docker is running...
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo Error: Docker is not running. Please start Docker Desktop and try again.
    pause
    exit /b 1
)

echo Docker is running.
echo.

REM Stop any existing containers
echo Stopping any existing containers...
docker-compose down

REM Build and start containers
echo.
echo Building and starting containers...
docker-compose up -d --build

if %errorlevel% equ 0 (
    echo Containers built and started successfully!
) else (
    echo Error occurred while building/starting containers.
    echo Checking logs for details...
    docker-compose logs
    pause
    exit /b 1
)

REM Wait for containers to initialize
echo.
echo Waiting for containers to initialize...
timeout /t 15 /nobreak >nul

REM Show running containers
echo.
echo Running containers:
docker-compose ps

REM Monitor logs for errors and attempt to fix
echo.
echo Monitoring logs for errors...
echo.

:CHECK_LOGS
echo === Checking for errors ===
set ERROR_FOUND=0

REM Check app container logs for common errors
echo Checking app container logs...
for /f "delims=" %%a in ('docker-compose logs app ^| findstr /i "permission\|failed\|error\|exception\|fatal"') do (
    set ERROR_FOUND=1
    echo ERROR DETECTED: %%a
    
    REM Try to fix common issues
    if "%%a" == "*permission*" (
        echo Attempting to fix permission issues...
        docker-compose exec app chown -R www-data:www-data /var/www/html/storage
        docker-compose exec app chmod -R 755 /var/www/html/storage
    )
)

REM Check MySQL container logs for errors
echo Checking MySQL container logs...
for /f "delims=" %%a in ('docker-compose logs mysql ^| findstr /i "error\|fatal") do (
    set ERROR_FOUND=1
    echo ERROR DETECTED: %%a
)

if %ERROR_FOUND% equ 1 (
    echo.
    echo Errors were found. Attempting to restart containers...
    docker-compose restart
    timeout /t 10 /nobreak >nul
    goto CHECK_LOGS
) else (
    echo No critical errors found in logs.
)

echo.
echo === Deployment completed successfully! ===
echo Application should be accessible at http://localhost
echo.

REM Final status check
docker-compose ps

pause