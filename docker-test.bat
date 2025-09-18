@echo off
REM Docker Setup Validation Script for Windows
REM Tests the Docker configuration without full deployment

echo Testing Docker Configuration...
echo =====================================

REM Test 1: Docker availability
echo [TEST 1] Checking Docker availability...
docker --version > nul 2>&1
if errorlevel 1 (
    echo [FAIL] Docker not found or not running
    exit /b 1
) else (
    echo [PASS] Docker is available
)

REM Test 2: Docker Compose availability  
echo [TEST 2] Checking Docker Compose availability...
docker-compose --version > nul 2>&1
if errorlevel 1 (
    echo [FAIL] Docker Compose not found
    exit /b 1
) else (
    echo [PASS] Docker Compose is available
)

REM Test 3: Dockerfile syntax validation
echo [TEST 3] Validating Dockerfile syntax...
docker build --dry-run -f docker/php/Dockerfile . > nul 2>&1
if errorlevel 1 (
    echo [FAIL] Dockerfile has syntax errors
    exit /b 1
) else (
    echo [PASS] Dockerfile syntax is valid
)

REM Test 4: docker-compose.yml validation
echo [TEST 4] Validating docker-compose.yml...
docker-compose config > nul 2>&1
if errorlevel 1 (
    echo [FAIL] docker-compose.yml has errors
    exit /b 1
) else (
    echo [PASS] docker-compose.yml is valid
)

REM Test 5: Environment file check
echo [TEST 5] Checking environment files...
if not exist .env.example (
    echo [FAIL] .env.example not found
    exit /b 1
) else (
    echo [PASS] .env.example exists
)

if not exist .env.production (
    echo [FAIL] .env.production not found
    exit /b 1
) else (
    echo [PASS] .env.production exists
)

REM Test 6: Required directories
echo [TEST 6] Checking Docker directories...
if not exist docker\php (
    echo [FAIL] docker/php directory missing
    exit /b 1
)
if not exist docker\nginx (
    echo [FAIL] docker/nginx directory missing  
    exit /b 1
)
if not exist docker\mysql (
    echo [FAIL] docker/mysql directory missing
    exit /b 1
)
if not exist docker\redis (
    echo [FAIL] docker/redis directory missing
    exit /b 1
)
echo [PASS] All Docker directories exist

REM Test 7: Configuration files
echo [TEST 7] Checking configuration files...
if not exist docker\php\Dockerfile (
    echo [FAIL] PHP Dockerfile missing
    exit /b 1
)
if not exist docker\nginx\default.conf (
    echo [FAIL] Nginx configuration missing
    exit /b 1
)
if not exist docker\mysql\my.cnf (
    echo [FAIL] MySQL configuration missing
    exit /b 1
)
echo [PASS] All configuration files exist

echo.
echo =====================================
echo [SUCCESS] All tests passed!
echo.
echo Your Docker setup is ready for deployment.
echo.
echo Next steps:
echo 1. Copy .env.example to .env and configure
echo 2. Run: .\deploy.bat development
echo 3. Access application at http://localhost
echo.
pause