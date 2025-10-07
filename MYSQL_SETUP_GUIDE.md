# PHP MySQL Extension Setup Guide

## Issue: "could not find driver" Error

This error occurs when PHP's MySQL PDO extension is not enabled.

---

## Solution Steps

### Step 1: Locate your php.ini file

Your PHP configuration file is located at:
```
C:\Program Files\php-8.4.13\php.ini
```

### Step 2: Enable MySQL Extensions

1. Open the php.ini file with Administrator privileges (use Notepad or VS Code as Administrator)

2. Search for these lines and make sure they are **uncommented** (remove the semicolon `;` at the beginning):

```ini
extension=pdo_mysql
extension=mysqli
```

If the lines start with `;`, remove the semicolon:

**Before:**
```ini
;extension=pdo_mysql
;extension=mysqli
```

**After:**
```ini
extension=pdo_mysql
extension=mysqli
```

3. Save the file

### Step 3: Restart Your Web Server

If you're using:

**XAMPP:**
- Stop Apache and MySQL from XAMPP Control Panel
- Start them again

**Laragon:**
- Stop all services
- Start them again

**Built-in PHP Server:**
- Stop the current server (Ctrl+C)
- Start it again with `php artisan serve`

### Step 4: Verify the Extensions are Loaded

Run this command:
```bash
php -m | Select-String -Pattern "pdo_mysql"
```

You should see:
```
pdo_mysql
```

---

## Alternative: Use SQLite (Already Configured)

If you prefer to use SQLite instead of MySQL (good for development):

1. Your current `.env` was already using SQLite
2. No additional setup needed
3. Database file: `database/database.sqlite`

To switch back to SQLite, edit `.env`:
```env
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=course_registration_system
# DB_USERNAME=root
# DB_PASSWORD=
```

---

## Creating the MySQL Database

### Option 1: Using phpMyAdmin
1. Open phpMyAdmin (usually http://localhost/phpmyadmin)
2. Click "New" in the left sidebar
3. Database name: `course_registration_system`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### Option 2: Using MySQL Workbench
1. Open MySQL Workbench
2. Connect to your MySQL server
3. Create a new schema named `course_registration_system`

### Option 3: Using MySQL Command Line
```bash
mysql -u root -p
```
Then enter:
```sql
CREATE DATABASE course_registration_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Option 4: Using HeidiSQL or DBeaver
Both have GUI interfaces for creating databases easily.

---

## After Enabling MySQL Extension

### Update .env file

Make sure your `.env` file has:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=course_registration_system
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

**Note**: If your MySQL root user doesn't have a password, leave `DB_PASSWORD` empty.

### Test Database Connection

```bash
php artisan db:show
```

This should display your database information.

---

## Run Migrations

### Option 1: Fresh Migration with Seed Data
```bash
php artisan migrate:fresh --seed
```

This will:
- Drop all tables
- Create all tables
- Insert sample data (test users, courses, etc.)

### Option 2: Regular Migration
```bash
php artisan migrate
```

This will:
- Create all tables
- Preserve existing data

### Option 3: Migration without Seed
```bash
php artisan migrate
```

Then manually seed later:
```bash
php artisan db:seed
```

---

## Verify Setup

After running migrations, check tables were created:

```bash
php artisan db:table users --show
```

Or login to phpMyAdmin/MySQL Workbench and verify tables exist.

---

## Troubleshooting

### Error: "Access denied for user 'root'@'localhost'"

**Solution**: Set correct password in `.env`
```env
DB_PASSWORD=your_actual_mysql_password
```

### Error: "Database 'course_registration_system' doesn't exist"

**Solution**: Create the database first (see "Creating the MySQL Database" section above)

### Error: "could not find driver" persists

**Solutions**:
1. Make sure you edited the correct php.ini file (check with `php -r "echo php_ini_loaded_file();"`)
2. Verify extensions directory exists: Check `extension_dir` in php.ini
3. Restart your terminal/command prompt after changes
4. Restart your computer if nothing else works

### Extensions not loading

Check if extension files exist:
```
C:\Program Files\php-8.4.13\ext\php_pdo_mysql.dll
C:\Program Files\php-8.4.13\ext\php_mysqli.dll
```

If they don't exist, you may need to reinstall PHP or download a different PHP package that includes these extensions.

---

## Recommended: Use XAMPP or Laragon

For easier setup, consider using:

**XAMPP**: https://www.apachefriends.org/
- Includes Apache, MySQL, PHP pre-configured
- Everything works out of the box

**Laragon**: https://laragon.org/
- Modern, lightweight
- Auto-configures everything
- Better for Laravel development

Both include MySQL and all required PHP extensions enabled by default.

---

## Next Steps After Setup

Once your database is set up and migrations are successful:

1. ✅ Database structure is ready
2. ✅ Sample data is loaded (if you used --seed)
3. 🔄 Start building controllers
4. 🔄 Create views (Blade templates)
5. 🔄 Add authentication
6. 🔄 Build the registration workflow

Check the main `DATABASE_SETUP.md` file for detailed information about the database structure.

---

**Need Help?**

- Laravel Database Documentation: https://laravel.com/docs/database
- Laravel Migration Documentation: https://laravel.com/docs/migrations
- PHP MySQL Setup: https://www.php.net/manual/en/book.pdo.php
