@echo off
setlocal EnableDelayedExpansion

echo ======================================================
echo EBBM Laravel Project Docker Deployment Script
echo ======================================================
echo.

REM Set color output
for /F %%a in ('echo prompt $E ^| cmd') do set "ESC=%%a"
set "GREEN=%ESC%[32m"
set "RED=%ESC%[31m"
set "YELLOW=%ESC%[33m"
set "BLUE=%ESC%[34m"
set "RESET=%ESC%[0m"

REM Default values
set "ENVIRONMENT=local"
set "BUILD_FRESH=false"
set "WITH_DEV_TOOLS=false"

REM Parse command line arguments
:parse_args
if "%~1"=="" goto :args_done
if "%~1"=="--env" (
    set "ENVIRONMENT=%~2"
    shift
    shift
    goto :parse_args
)
if "%~1"=="--fresh" (
    set "BUILD_FRESH=true"
    shift
    goto :parse_args
)
if "%~1"=="--dev" (
    set "WITH_DEV_TOOLS=true"
    shift
    goto :parse_args
)
if "%~1"=="--help" goto :show_help
shift
goto :parse_args

:args_done

echo %BLUE%Environment: %ENVIRONMENT%%RESET%
echo %BLUE%Fresh build: %BUILD_FRESH%%RESET%
echo %BLUE%Dev tools: %WITH_DEV_TOOLS%%RESET%
echo.

REM Check if Docker is running
echo %YELLOW%Checking Docker...%RESET%
docker --version >nul 2>&1
if errorlevel 1 (
    echo %RED%Error: Docker is not installed or not running%RESET%
    echo Please install Docker Desktop and ensure it's running
    pause
    exit /b 1
)

docker-compose --version >nul 2>&1
if errorlevel 1 (
    echo %RED%Error: Docker Compose is not available%RESET%
    pause
    exit /b 1
)

echo %GREEN%Docker is ready!%RESET%
echo.

REM Environment-specific setup
if "%ENVIRONMENT%"=="production" (
    echo %YELLOW%Setting up production environment...%RESET%
    
    REM Check if .env.production exists
    if not exist ".env.production" (
        echo %RED%Error: .env.production file not found%RESET%
        echo Please copy .env.production template and configure it
        pause
        exit /b 1
    )
    
    REM Create secrets directory
    if not exist "secrets" mkdir secrets
    
    REM Create secret files if they don't exist
    if not exist "secrets\mysql_root_password.txt" (
        echo your_secure_root_password> secrets\mysql_root_password.txt
        echo %YELLOW%Created secrets\mysql_root_password.txt with default password%RESET%
        echo %RED%Please update this file with a secure password!%RESET%
    )
    
    if not exist "secrets\mysql_password.txt" (
        echo your_secure_database_password> secrets\mysql_password.txt
        echo %YELLOW%Created secrets\mysql_password.txt with default password%RESET%
        echo %RED%Please update this file with a secure password!%RESET%
    )
    
    set "COMPOSE_FILE=docker-compose.prod.yml"
    set "PROJECT_NAME=ebbm-prod"
) else (
    echo %YELLOW%Setting up %ENVIRONMENT% environment...%RESET%
    set "COMPOSE_FILE=docker-compose.yml"
    set "PROJECT_NAME=ebbm-dev"
)

REM Stop existing containers
echo %YELLOW%Stopping existing containers...%RESET%
docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% down

REM Remove old images if fresh build
if "%BUILD_FRESH%"=="true" (
    echo %YELLOW%Removing old images...%RESET%
    docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% down --rmi all --volumes --remove-orphans
    docker system prune -f
)

REM Build and start containers
echo %YELLOW%Building and starting containers...%RESET%

if "%WITH_DEV_TOOLS%"=="true" (
    docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% --profile dev up -d --build
) else (
    docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% up -d --build
)

if errorlevel 1 (
    echo %RED%Error: Failed to start containers%RESET%
    echo Check the logs for more information:
    echo docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% logs
    pause
    exit /b 1
)

REM Wait for containers to be ready
echo %YELLOW%Waiting for containers to be ready...%RESET%
timeout /t 10 /nobreak >nul

REM Show container status
echo.
echo %GREEN%Container Status:%RESET%
docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% ps

REM Show access information
echo.
echo %GREEN%======================================================%RESET%
echo %GREEN%Deployment completed successfully!%RESET%
echo %GREEN%======================================================%RESET%
echo.
echo %BLUE%Application URL:%RESET% http://localhost
if "%WITH_DEV_TOOLS%"=="true" (
    echo %BLUE%PHPMyAdmin:%RESET% http://localhost:8080
    echo %BLUE%Redis Commander:%RESET% http://localhost:8081
)
if "%ENVIRONMENT%"=="local" (
    echo %BLUE%Vite Dev Server:%RESET% http://localhost:5173
)
echo.
echo %YELLOW%Useful commands:%RESET%
echo docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% logs    - View logs
echo docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% exec php bash - Access PHP container
echo docker-compose -f %COMPOSE_FILE% -p %PROJECT_NAME% down   - Stop containers
echo.

goto :end

:show_help
echo Usage: deploy.bat [options]
echo.
echo Options:
echo   --env ENVIRONMENT    Set environment (local/production) [default: local]
echo   --fresh             Remove all images and rebuild from scratch
echo   --dev               Include development tools (PHPMyAdmin, Redis Commander)
echo   --help              Show this help message
echo.
echo Examples:
echo   deploy.bat                           - Deploy local environment
echo   deploy.bat --env production          - Deploy production environment
echo   deploy.bat --fresh --dev             - Fresh build with dev tools
echo   deploy.bat --env production --fresh  - Fresh production deployment
echo.

:end
pause