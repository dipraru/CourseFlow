@echo off
setlocal enabledelayedexpansion

echo ========================================
echo MySQL PASSWORD FINDER ^& DATABASE SETUP
echo ========================================
echo.

echo Testing common MySQL passwords...
echo.

set "passwords= root password mysql 123456 admin toor"
set "found=0"

for %%p in (%passwords%) do (
    echo Testing password: %%p
    mysql -u root --password=%%p -e "SELECT 'SUCCESS' as Result;" 2>nul
    if !errorlevel! equ 0 (
        echo.
        echo ========================================
        echo SUCCESS! Password found: %%p
        echo ========================================
        echo.
        set "found=1"
        set "correct_password=%%p"
        goto :found
    )
)

echo Testing empty password...
mysql -u root -e "SELECT 'SUCCESS' as Result;" 2>nul
if !errorlevel! equ 0 (
    echo.
    echo ========================================
    echo SUCCESS! No password required (empty)
    echo ========================================
    echo.
    set "found=1"
    set "correct_password="
    goto :found
)

:notfound
echo.
echo ========================================
echo PASSWORD NOT FOUND
echo ========================================
echo.
echo None of the common passwords worked.
echo.
echo Please try one of these options:
echo.
echo 1. RESET MySQL PASSWORD:
echo    - Stop MySQL service
echo    - Open MySQL Workbench
echo    - Use password reset feature
echo.
echo 2. CHECK MySQL Workbench saved connections:
echo    - Open MySQL Workbench
echo    - Look at your saved connections
echo    - Check what password is saved
echo.
echo 3. USE MySQL Workbench to connect:
echo    - If you can connect in Workbench
echo    - Note what password you use
echo    - Update .env file manually
echo.
echo 4. INSTALL phpMyAdmin (if not installed):
echo    - Download from phpmyadmin.net
echo    - Or use MySQL Workbench instead
echo.
pause
exit /b 1

:found
echo Updating .env file...
powershell -Command "(Get-Content .env) -replace '^DB_PASSWORD=.*', 'DB_PASSWORD=%correct_password%' | Set-Content .env"
echo ✓ .env file updated
echo.

echo Testing database connection...
php artisan config:clear >nul 2>&1
php artisan db:show >nul 2>&1
if !errorlevel! equ 0 (
    echo ✓ Database connection successful!
    echo.
) else (
    echo Creating database...
    mysql -u root --password=%correct_password% -e "CREATE DATABASE IF NOT EXISTS course_registration_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
    if !errorlevel! equ 0 (
        echo ✓ Database created: course_registration_system
        echo.
    ) else (
        echo ✗ Failed to create database
        echo Please create it manually in MySQL Workbench or phpMyAdmin
        echo.
        pause
        exit /b 1
    )
)

echo ========================================
echo RUNNING MIGRATIONS
echo ========================================
echo.
echo This will create all tables and add sample data...
echo.
pause

php artisan migrate:fresh --seed

echo.
echo ========================================
echo VERIFICATION
echo ========================================
echo.

php setup_and_verify_database.php

echo.
echo ========================================
echo SETUP COMPLETE!
echo ========================================
echo.
echo Your password is: %correct_password%
echo Database: course_registration_system
echo.
echo You can now:
echo 1. Run: php artisan serve
echo 2. Visit: http://127.0.0.1:8000
echo 3. Check phpMyAdmin: http://localhost/phpmyadmin
echo.
pause
