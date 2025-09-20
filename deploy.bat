@echo off
REM Deployment script for Windows

echo Building and deploying Docker containers...

REM Check if Docker is running
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo Error: Docker is not running. Please start Docker Desktop and try again.
    pause
    exit /b 1
)

REM Build and start containers
docker-compose up -d --build

if %errorlevel% equ 0 (
    echo Containers built and started successfully!
) else (
    echo Error occurred while building/starting containers.
    pause
    exit /b 1
)

REM Show running containers
echo.
echo Running containers:
docker-compose ps

echo.
echo Deployment completed!
pause