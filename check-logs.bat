@echo off
REM Log checking script for Windows

echo Checking Docker container logs...

REM Check if containers are running
docker-compose ps

echo.
echo === App Container Logs ===
docker-compose logs app

echo.
echo === MySQL Container Logs ===
docker-compose logs mysql

echo.
echo Log check completed!
pause