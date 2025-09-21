@echo off
REM Initialize Laravel application

echo Initializing Laravel application...

REM Install PHP dependencies
echo Installing PHP dependencies...
composer install

REM Install Node.js dependencies
echo Installing Node.js dependencies...
npm install

REM Generate application key
echo Generating application key...
php artisan key:generate

REM Run database migrations
echo Running database migrations...
php artisan migrate

echo Laravel application initialized successfully!
pause