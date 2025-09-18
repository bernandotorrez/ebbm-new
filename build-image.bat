@echo off
REM Optimized Docker Build Script for Laravel Filament 3 (Windows)
REM Provides better caching and build performance

setlocal enabledelayedexpansion

REM Colors for output
set \"RED=[91m\"
set \"GREEN=[92m\"
set \"YELLOW=[93m\"
set \"BLUE=[94m\"
set \"NC=[0m\"

REM Configuration
set \"IMAGE_NAME=ebbm-php\"
set \"BUILD_ARGS=\"
set \"NO_CACHE=false\"
set \"PUSH_IMAGE=false\"
set \"REGISTRY=\"
set \"TAG=latest\"
set \"DOCKER_BUILDKIT=1\"

REM Parse command line arguments
:parse_args
if \"%~1\"==\"\" goto end_parse
if \"%~1\"==\"--no-cache\" (
    set \"NO_CACHE=true\"
    shift
    goto parse_args
)
if \"%~1\"==\"--push\" (
    set \"PUSH_IMAGE=true\"
    shift
    goto parse_args
)
if \"%~1\"==\"--registry\" (
    set \"REGISTRY=%~2\"
    shift
    shift
    goto parse_args
)
if \"%~1\"==\"--tag\" (
    set \"TAG=%~2\"
    shift
    shift
    goto parse_args
)
if \"%~1\"==\"--php-version\" (
    set \"BUILD_ARGS=!BUILD_ARGS! --build-arg PHP_VERSION=%~2\"
    shift
    shift
    goto parse_args
)
if \"%~1\"==\"--help\" (
    echo Usage: %0 [OPTIONS]
    echo Options:
    echo   --no-cache      Build without using cache
    echo   --push          Push image to registry after build
    echo   --registry REG  Registry to push to
    echo   --tag TAG       Image tag (default: latest)
    echo   --php-version   PHP version to build (default: 8.3.17)
    echo   --help          Show this help message
    exit /b 0
)
echo %RED%Unknown option: %~1%NC%
exit /b 1

:end_parse

REM Validate Docker
docker --version >nul 2>&1
if errorlevel 1 (
    echo %RED%Docker is not installed or not in PATH%NC%
    exit /b 1
)

REM Enable BuildKit for better performance
set \"DOCKER_BUILDKIT=1\"
set \"COMPOSE_DOCKER_CLI_BUILD=1\"

echo %BLUE%======================================================%NC%
echo %BLUE%Building Optimized Laravel Filament 3 Docker Image%NC%
echo %BLUE%======================================================%NC%

REM Build image with multi-stage optimization
echo %YELLOW%Building image: %IMAGE_NAME%:%TAG%%NC%

set \"BUILD_CMD=docker build\"

if \"%NO_CACHE%\"==\"true\" (
    set \"BUILD_CMD=!BUILD_CMD! --no-cache\"
    echo %YELLOW%Building without cache%NC%
)

REM Add build arguments
set \"BUILD_CMD=!BUILD_CMD! !BUILD_ARGS!\"

REM Build with multi-stage target
set \"BUILD_CMD=!BUILD_CMD! --target production\"
set \"BUILD_CMD=!BUILD_CMD! -f docker/php/Dockerfile\"
set \"BUILD_CMD=!BUILD_CMD! -t %IMAGE_NAME%:%TAG%\"
set \"BUILD_CMD=!BUILD_CMD! .\"

echo %YELLOW%Executing: !BUILD_CMD!%NC%

!BUILD_CMD!
if errorlevel 1 (
    echo %RED%Docker build failed%NC%
    exit /b 1
)

echo %GREEN%Docker image built successfully: %IMAGE_NAME%:%TAG%%NC%

REM Tag for registry if specified
if not \"%REGISTRY%\"==\"\" (
    set \"FULL_IMAGE=%REGISTRY%/%IMAGE_NAME%:%TAG%\"
    docker tag \"%IMAGE_NAME%:%TAG%\" \"!FULL_IMAGE!\"
    echo %GREEN%Tagged image: !FULL_IMAGE!%NC%
)

REM Push to registry if requested
if \"%PUSH_IMAGE%\"==\"true\" (
    if not \"%REGISTRY%\"==\"\" (
        echo %YELLOW%Pushing image to registry...%NC%
        docker push \"!FULL_IMAGE!\"
        if errorlevel 1 (
            echo %RED%Failed to push image%NC%
            exit /b 1
        )
        echo %GREEN%Image pushed successfully: !FULL_IMAGE!%NC%
    ) else (
        echo %RED%Registry not specified for push operation%NC%
        exit /b 1
    )
)

REM Show image information
echo %BLUE%Image Information:%NC%
docker images | findstr \"%IMAGE_NAME%\"

REM Clean up dangling images
echo %YELLOW%Cleaning up dangling images...%NC%
docker image prune -f

echo %GREEN%Build completed successfully!%NC%

REM Show usage instructions
echo %BLUE%Usage Instructions:%NC%
echo   Development: docker-compose up -d
echo   Production:  docker-compose -f docker-compose.prod.yml up -d
echo   With dev tools: docker-compose --profile dev up -d

pause