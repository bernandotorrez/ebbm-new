@echo off
REM Deploy and check script for Windows

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

REM Wait a moment for containers to initialize
timeout /t 10 /nobreak >nul

REM Show running containers
echo.
echo Running containers:
docker-compose ps

REM Check logs for errors
echo.
echo Checking for errors in container logs...
echo.

REM Check app container logs
echo === App Container Logs ===
for /f "delims=" %%a in ('docker-compose logs app ^| findstr "error\|Error\|ERROR\|fatal\|Fatal\|FATAL"') do (
    echo ERROR FOUND: %%a
)

if %errorlevel% equ 0 (
    echo No critical errors found in app logs.
)

echo.
echo === MySQL Container Logs ===
for /f "delims=" %%a in ('docker-compose logs mysql ^| findstr "error\|Error\|ERROR\|fatal\|Fatal\|FATAL"') do (
    echo ERROR FOUND: %%a
)

if %errorlevel% equ 0 (
    echo No critical errors found in MySQL logs.
)

echo.
echo Deployment and error check completed!
pause