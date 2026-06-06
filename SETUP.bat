@echo off
REM SPK Bansos - Complete Setup & Launch Script
REM Run this script to automatically setup dan launch project

echo.
echo ========================================
echo    SPK Bansos - Project Setup & Launch
echo ========================================
echo.

REM Check if .env exists
if not exist .env (
    echo [1/5] Creating .env file from .env.example...
    copy .env.example .env
    echo ✓ .env created
) else (
    echo [1/5] .env already exists
)

echo.

REM Check PHP
echo [2/5] Checking PHP installation...
php -v >nul 2>&1
if errorlevel 1 (
    echo ✗ PHP not found. Please install PHP 8.2+
    pause
    exit /b 1
) else (
    echo ✓ PHP found
)

echo.

REM Check MySQL
echo [3/5] Checking MySQL connection...
mysql -h 127.0.0.1 -u root -e "SELECT 1;" >nul 2>&1
if errorlevel 1 (
    echo ! MySQL not accessible at 127.0.0.1:3306 (root)
    echo   Please ensure MySQL is running
    echo   Or edit .env to use SQLite instead
    echo.
    echo   To use SQLite instead:
    echo   - Change DB_CONNECTION=sqlite in .env
    echo   - Remove DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
    echo.
    pause
    goto :continue_anyway
) else (
    echo ✓ MySQL connected
)

:continue_anyway

REM Create database if not exists
echo.
echo [4/5] Setting up database...
php artisan migrate --force >nul 2>&1
if errorlevel 1 (
    echo ! Migration warning (might be OK if already migrated)
) else (
    echo ✓ Migrations completed
)

php artisan db:seed --force >nul 2>&1
if errorlevel 1 (
    echo ! Seeding completed (or already seeded)
) else (
    echo ✓ Database seeded with test data
)

echo.
echo ========================================
echo    Ready to Launch!
echo ========================================
echo.
echo Step 1: Backend Server
echo   Run in Terminal 1: php artisan serve
echo   URL: http://127.0.0.1:8000
echo.
echo Step 2: Frontend Dev Server  
echo   Run in Terminal 2: npm run dev
echo   URL: http://127.0.0.1:5173 (or next available port)
echo.
echo Test Credentials:
echo   Admin: admin@example.com / password
echo   KLS: kls@example.com / password
echo   Stasi: stasi@example.com / password
echo   KLP: klp@example.com / password
echo   Paroki: paroki@example.com / password
echo.
echo Documentation:
echo   - User Guide: MODULES_USER_GUIDE.md
echo   - Dev Guide: MODULES_DEVELOPER_GUIDE.md
echo   - Quick Ref: MODULES_QUICK_REFERENCE.md
echo.
pause
