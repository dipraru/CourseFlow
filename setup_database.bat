@echo off
echo ========================================
echo DATABASE SETUP HELPER
echo ========================================
echo.

echo Checking MySQL Service Status...
sc query MySQL80 | find "RUNNING" >nul
if %errorlevel%==0 (
    echo [OK] MySQL service is running
) else (
    echo [WARNING] MySQL service may not be running
    echo Please start MySQL from XAMPP/WAMP/Laragon or Windows Services
    echo.
)

echo.
echo ========================================
echo STEP 1: Configure Database Password
echo ========================================
echo.
echo Your .env file is currently set to:
echo DB_PASSWORD=(empty)
echo.
echo Common MySQL default passwords:
echo   1. (empty) - No password
echo   2. root
echo   3. (custom password)
echo.

:ask_password
set /p password_choice="Enter your MySQL root password (or press Enter if no password): "

echo.
echo Updating .env file...
powershell -Command "(Get-Content .env) -replace '^DB_PASSWORD=.*', 'DB_PASSWORD=%password_choice%' | Set-Content .env"

echo.
echo ========================================
echo STEP 2: Testing Database Connection
echo ========================================
echo.

php setup_and_verify_database.php

echo.
echo ========================================
echo.
echo If connection failed above, you can:
echo   1. Run this script again with different password
echo   2. Create database manually in phpMyAdmin
echo   3. Check MYSQL_SETUP_GUIDE.md for detailed help
echo.
pause
