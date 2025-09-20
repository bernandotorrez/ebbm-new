# Laravel 11 + Filament 3 Docker Deployment

This project includes a production-ready Docker setup for a Laravel 11 + Filament 3 application.

## Deployment Scripts

We've provided several scripts to make deployment easier:

### Windows
- `deploy.bat` - Builds and starts the Docker containers
- `check-logs.bat` - Checks the container logs for errors
- `deploy-and-check.bat` - Deploys and automatically checks for errors
- `init-app.bat` - Initializes the Laravel application (run this first on local development)

### Unix/Linux/macOS
- `deploy.sh` - Builds and starts the Docker containers
- `check-logs.sh` - Checks the container logs for errors
- `deploy-and-check.sh` - Deploys and automatically checks for errors
- `init-app.sh` - Initializes the Laravel application (run this first on local development)

## Deployment Process

1. **Initialize the application** (local development only):
   ```
   ./init-app.sh  # Unix/Linux/macOS
   init-app.bat   # Windows
   ```

2. **Deploy the application**:
   ```
   ./deploy.sh  # Unix/Linux/macOS
   deploy.bat   # Windows
   ```

3. **Check for errors**:
   ```
   ./check-logs.sh  # Unix/Linux/macOS
   check-logs.bat   # Windows
   ```

## Docker Services

The docker-compose.yml file defines the following services:
- `app` - The main Laravel application with Nginx and PHP-FPM
- `mysql` - Percona MySQL database

## Configuration

All configuration files are located in the `docker/` directory:
- `docker/php/` - PHP configuration
- `docker/nginx/` - Nginx configuration
- `docker/supervisor/` - Supervisor configuration
- `docker/entrypoints/` - Entrypoint scripts

## Environment Variables

Make sure to configure your `.env` file with the appropriate values for your environment.

## Accessing the Application

Once deployed, the application will be accessible at http://localhost