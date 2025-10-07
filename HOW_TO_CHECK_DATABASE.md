# 🔍 HOW TO CHECK YOUR DATABASE - Quick Guide

## ✅ I've Created 3 Tools For You!

### Tool 1: Command Line Checker (RECOMMENDED)
**File:** `setup_and_verify_database.php`

**Run this command:**
```bash
php setup_and_verify_database.php
```

**This will show you:**
- ✅ Database connection status
- ✅ List of all tables
- ✅ Row count for each table
- ✅ Sample data from users, courses, semesters, batches
- ✅ Migration status
- ✅ Test account information

---

### Tool 2: Interactive Setup Helper
**File:** `setup_database.bat`

**Double-click this file** or run:
```bash
.\setup_database.bat
```

**This will:**
- Help you set the correct MySQL password
- Test database connection
- Show you all verification results
- Guide you through fixing any issues

---

### Tool 3: Web-Based Database Viewer
**File:** `public/database_viewer.html`

**Open in browser:**
```
http://localhost:8000/database_viewer.html
```
(After starting: `php artisan serve`)

**Shows:**
- Complete guide on how to view tables
- All artisan commands
- Test account credentials
- Expected table structure
- phpMyAdmin instructions

---

## 🚀 QUICK START (Follow These Steps)

### Step 1: Find Your MySQL Password

Your MySQL password depends on your setup:

**XAMPP:**
- Default: Usually empty (no password)
- Or check: XAMPP Control Panel → MySQL Config

**WAMP:**
- Default: Usually empty
- Or open phpMyAdmin → see login credentials

**Laragon:**
- Default: Empty
- Or check Laragon → MySQL settings

**Standalone MySQL:**
- You set this during installation
- Check your installation notes

---

### Step 2: Update .env File

1. Open `.env` file in VS Code
2. Find this line: `DB_PASSWORD=`
3. Add your password after the equals sign:

**If no password (empty):**
```env
DB_PASSWORD=
```

**If password is "root":**
```env
DB_PASSWORD=root
```

**If password is "password":**
```env
DB_PASSWORD=password
```

**If custom password:**
```env
DB_PASSWORD=your_actual_password
```

4. Save the file

---

### Step 3: Create the Database

**Option A - Using phpMyAdmin:**
1. Open: http://localhost/phpmyadmin
2. Click "New" in left sidebar
3. Database name: `course_registration_system`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

**Option B - Using MySQL Command Line:**
```bash
mysql -u root -p
# Enter your password when prompted
CREATE DATABASE course_registration_system;
EXIT;
```

**Option C - Using MySQL Workbench:**
1. Open MySQL Workbench
2. Connect to localhost
3. Right-click → Create Schema
4. Name: `course_registration_system`
5. Apply

---

### Step 4: Run Migrations

```bash
php artisan migrate:fresh --seed
```

**This will:**
- Create all 10 tables
- Add sample data (9 users, 2 batches, 1 semester, 5 courses)
- Set up test accounts

**Expected output:**
```
Migration table created successfully.
Migrating: 2025_10_04_160209_create_batches_table
Migrated:  2025_10_04_160209_create_batches_table
...
Seeding: Database\Seeders\DatabaseSeeder
Seeded:  Database\Seeders\DatabaseSeeder
Database seeding completed successfully.
```

---

### Step 5: Verify Everything Works

**Run the verification script:**
```bash
php setup_and_verify_database.php
```

**You should see:**
- ✅ Database connection successful
- ✅ 13 tables found (10 main + 3 Laravel default)
- ✅ Row counts showing data in tables
- ✅ Sample users, courses, semesters displayed

---

## 📊 How to View Your Tables

### Method 1: Laravel Artisan Commands

```bash
# Show all tables
php artisan db:show

# Show specific table structure
php artisan db:table users --show
php artisan db:table courses --show
php artisan db:table semesters --show

# Check migration status
php artisan migrate:status

# Open interactive console
php artisan tinker
# Then run: User::all()
# Or: DB::table('users')->get()
```

---

### Method 2: phpMyAdmin (Visual Interface)

1. Open: http://localhost/phpmyadmin
2. Login (usually root with your password)
3. Click "course_registration_system" in left sidebar
4. See all 13 tables listed
5. Click any table name to view data
6. Use "Browse" to see rows
7. Use "Structure" to see columns

---

### Method 3: MySQL Workbench (Professional Tool)

1. Open MySQL Workbench
2. Connect to Local instance
3. Click "course_registration_system" schema
4. Right-click any table → "Select Rows - Limit 1000"
5. View data in grid format
6. Edit, filter, and query easily

---

### Method 4: VS Code Extensions (Recommended!)

**Install one of these:**

**1. MySQL by Jun Han** (Most Popular)
- Install from Extensions (Ctrl+Shift+X)
- Click MySQL icon in sidebar
- Add connection (localhost, root, your_password)
- Browse tables visually

**2. Database Client by cweijan**
- Install from Extensions
- Click Database icon
- Connect to MySQL
- View tables, data, run queries

**3. SQLTools**
- Install SQLTools + MySQL Driver
- Configure connection
- Query and browse tables

---

## 🧪 Test Your Setup

### Check if MySQL is Running

**Windows PowerShell:**
```powershell
Get-Service -Name "*mysql*" | Select-Object Name, Status
```

**Should show:** Status = Running

---

### Quick Connection Test

```bash
php artisan db:show
```

**Success output:**
```
MySQL ................................................................  8.0
Database .......................................... course_registration_system
Host ............................................................... 127.0.0.1
Port ..................................................................... 3306
Username .................................................................. root
```

---

### View Sample Data

```bash
php artisan tinker
```

**Then run these:**
```php
// Count users
User::count()

// Show all users
User::all()

// Show students only
User::where('role', 'student')->get()

// Count courses
Course::count()

// Show all courses
Course::all()

// Check active semester
Semester::where('is_active', true)->first()
```

Press Ctrl+D or type `exit` to quit tinker.

---

## ❌ Common Issues & Solutions

### Issue 1: "Access denied for user 'root'@'localhost'"

**Solution:**
- Check your DB_PASSWORD in .env
- Try common passwords: empty, root, password
- Use phpMyAdmin to see what password works
- Update .env with correct password

---

### Issue 2: "Unknown database 'course_registration_system'"

**Solution:**
- Database doesn't exist yet
- Create it using phpMyAdmin or MySQL command
- See Step 3 above

---

### Issue 3: "could not find driver"

**Solution:**
- PDO MySQL extension not enabled
- Edit php.ini (Administrator mode)
- Uncomment: `extension=pdo_mysql`
- Restart terminal
- See MYSQL_SETUP_GUIDE.md

---

### Issue 4: MySQL service not running

**Solution:**
- Start XAMPP/WAMP/Laragon
- Or run: `net start MySQL80`
- Check Windows Services

---

### Issue 5: "No tables found"

**Solution:**
- Migrations haven't run yet
- Run: `php artisan migrate:fresh --seed`
- Then verify again

---

## 📋 Expected Tables After Migration

| Table Name | Rows (After Seed) | Purpose |
|------------|-------------------|---------|
| users | 9 | All system users |
| user_profiles | 9 | Extended user info |
| batches | 2 | Student cohorts |
| semesters | 1 | Academic terms |
| courses | 5 | Course catalog |
| semester_courses | 5 | Courses per semester |
| fees | 1 | Fee structure |
| course_registrations | 0 | Registration apps (empty) |
| registration_approvals | 0 | Approvals (empty) |
| payment_slips | 0 | Payment docs (empty) |
| migrations | 12 | Laravel migrations |
| cache | 0 | Laravel cache |
| sessions | 0 | User sessions |

**Total: 13 tables**

---

## 🎯 Test Accounts (After Seeding)

| Role | Email | Password | User ID |
|------|-------|----------|---------|
| Department Head | head@cs.edu | password | HEAD001 |
| Authority | admin@cs.edu | password | ADMIN001 |
| Advisor | advisor1@cs.edu | password | ADV001 |
| Advisor | advisor2@cs.edu | password | ADV002 |
| Student | student1@cs.edu | password | STU0001 |
| Student | student2@cs.edu | password | STU0002 |
| Student | student3@cs.edu | password | STU0003 |
| Student | student4@cs.edu | password | STU0004 |
| Student | student5@cs.edu | password | STU0005 |

---

## ✅ Success Checklist

- [ ] MySQL service is running
- [ ] .env file has correct DB_PASSWORD
- [ ] Database "course_registration_system" exists
- [ ] `php artisan migrate:fresh --seed` completed successfully
- [ ] `php setup_and_verify_database.php` shows all tables
- [ ] Can login to phpMyAdmin and see tables
- [ ] Test accounts are visible in users table
- [ ] All 13 tables exist with expected data

---

## 🚀 What's Next?

Once everything above is ✅:

1. **Read:** PROJECT_ROADMAP.md - Phase 2
2. **Install:** Laravel Breeze for authentication
3. **Create:** Role-based middleware
4. **Build:** Dashboard views for each role
5. **Implement:** Course registration workflow

---

## 📞 Need More Help?

**Check these files:**
- `DATABASE_SETUP.md` - Complete database documentation
- `MYSQL_SETUP_GUIDE.md` - MySQL troubleshooting
- `DATABASE_QUICK_REFERENCE.md` - Quick commands
- `DATABASE_ER_DIAGRAM.md` - Visual structure

**Or use the tools I created:**
- `setup_and_verify_database.php` - Full verification
- `setup_database.bat` - Interactive helper
- `public/database_viewer.html` - Web guide

---

**You're doing great! Follow these steps and your database will be up and running! 🎉**
