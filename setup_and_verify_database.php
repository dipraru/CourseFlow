<?php
/**
 * Database Setup and Verification Script
 * Run this script to check and set up your database
 */

echo "========================================\n";
echo "DATABASE SETUP & VERIFICATION SCRIPT\n";
echo "========================================\n\n";

// Load environment variables
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Step 1: Checking Environment Configuration\n";
echo "-------------------------------------------\n";
echo "DB Connection: " . config('database.default') . "\n";
echo "DB Host: " . config('database.connections.mysql.host') . "\n";
echo "DB Port: " . config('database.connections.mysql.port') . "\n";
echo "DB Name: " . config('database.connections.mysql.database') . "\n";
echo "DB User: " . config('database.connections.mysql.username') . "\n";
echo "DB Password: " . (config('database.connections.mysql.password') ? '***SET***' : '(empty)') . "\n\n";

echo "Step 2: Testing Database Connection\n";
echo "------------------------------------\n";
try {
    DB::connection()->getPdo();
    echo "✅ Database connection successful!\n\n";
    
    echo "Step 3: Checking Database\n";
    echo "-------------------------\n";
    $dbName = DB::connection()->getDatabaseName();
    echo "✅ Connected to database: {$dbName}\n\n";
    
    echo "Step 4: Listing All Tables\n";
    echo "--------------------------\n";
    $tables = DB::select('SHOW TABLES');
    
    if (empty($tables)) {
        echo "⚠️  No tables found in database.\n";
        echo "   Run: php artisan migrate:fresh --seed\n\n";
    } else {
        $tableKey = 'Tables_in_' . $dbName;
        echo "✅ Found " . count($tables) . " tables:\n\n";
        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            echo "   📊 {$tableName}\n";
            
            // Count rows in each table
            try {
                $count = DB::table($tableName)->count();
                echo "      └─ {$count} rows\n";
            } catch (\Exception $e) {
                echo "      └─ Error counting rows\n";
            }
        }
        echo "\n";
    }
    
    echo "Step 5: Checking Migrations Status\n";
    echo "-----------------------------------\n";
    Artisan::call('migrate:status');
    echo Artisan::output();
    
    echo "\nStep 6: Sample Data Check\n";
    echo "-------------------------\n";
    
    // Check if we have users
    try {
        $userCount = DB::table('users')->count();
        echo "Users: {$userCount}\n";
        
        if ($userCount > 0) {
            echo "\nSample Users:\n";
            $users = DB::table('users')
                ->select('id', 'name', 'email', 'role', 'user_id')
                ->limit(5)
                ->get();
            
            foreach ($users as $user) {
                echo "  • {$user->name} ({$user->role}) - {$user->email}\n";
            }
        }
    } catch (\Exception $e) {
        echo "⚠️  Users table not found or empty\n";
    }
    
    echo "\n";
    
    // Check courses
    try {
        $courseCount = DB::table('courses')->count();
        echo "Courses: {$courseCount}\n";
        
        if ($courseCount > 0) {
            echo "\nSample Courses:\n";
            $courses = DB::table('courses')
                ->select('course_code', 'course_name', 'credit_hours')
                ->limit(5)
                ->get();
            
            foreach ($courses as $course) {
                echo "  • {$course->course_code} - {$course->course_name} ({$course->credit_hours} credits)\n";
            }
        }
    } catch (\Exception $e) {
        echo "⚠️  Courses table not found or empty\n";
    }
    
    echo "\n";
    
    // Check semesters
    try {
        $semesterCount = DB::table('semesters')->count();
        echo "Semesters: {$semesterCount}\n";
        
        if ($semesterCount > 0) {
            $semesters = DB::table('semesters')
                ->select('name', 'type', 'year', 'is_active')
                ->get();
            
            foreach ($semesters as $semester) {
                $status = $semester->is_active ? '✓ ACTIVE' : 'Inactive';
                echo "  • {$semester->name} ({$semester->type} {$semester->year}) [{$status}]\n";
            }
        }
    } catch (\Exception $e) {
        echo "⚠️  Semesters table not found or empty\n";
    }
    
    echo "\n";
    
    // Check batches
    try {
        $batchCount = DB::table('batches')->count();
        echo "Batches: {$batchCount}\n";
        
        if ($batchCount > 0) {
            $batches = DB::table('batches')
                ->select('name', 'year', 'is_active')
                ->get();
            
            foreach ($batches as $batch) {
                $status = $batch->is_active ? '✓ ACTIVE' : 'Inactive';
                echo "  • {$batch->name} (Year: {$batch->year}) [{$status}]\n";
            }
        }
    } catch (\Exception $e) {
        echo "⚠️  Batches table not found or empty\n";
    }
    
    echo "\n========================================\n";
    echo "✅ DATABASE VERIFICATION COMPLETE!\n";
    echo "========================================\n\n";
    
    if (empty($tables)) {
        echo "⚠️  NEXT STEPS:\n";
        echo "   1. Run: php artisan migrate:fresh --seed\n";
        echo "   2. Then run this script again to verify\n\n";
    } else {
        echo "🎉 Your database is set up correctly!\n\n";
        echo "📝 TEST ACCOUNTS (password: 'password' for all):\n";
        echo "   • Department Head: head@cs.edu\n";
        echo "   • Authority: admin@cs.edu\n";
        echo "   • Advisor: advisor1@cs.edu\n";
        echo "   • Student: student1@cs.edu\n\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Database Connection Failed!\n\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "🔧 TROUBLESHOOTING STEPS:\n";
    echo "-------------------------\n\n";
    
    // Check if it's a password issue
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "Issue: MySQL password is incorrect or not set\n\n";
        echo "Solutions:\n";
        echo "1. Find your MySQL password:\n";
        echo "   - Check XAMPP/WAMP/Laragon documentation\n";
        echo "   - Default passwords:\n";
        echo "     * XAMPP: Usually empty or 'root'\n";
        echo "     * WAMP: Usually empty\n";
        echo "     * Laragon: Usually empty\n\n";
        
        echo "2. Update your .env file:\n";
        echo "   DB_PASSWORD=your_mysql_password\n\n";
        
        echo "3. Try resetting MySQL password:\n";
        echo "   - Stop MySQL service\n";
        echo "   - Use MySQL admin tools to reset password\n\n";
    } 
    // Check if database doesn't exist
    elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "Issue: Database 'course_registration_system' does not exist\n\n";
        echo "Solutions:\n";
        echo "1. Create database using phpMyAdmin:\n";
        echo "   - Go to http://localhost/phpmyadmin\n";
        echo "   - Click 'New' on left sidebar\n";
        echo "   - Database name: course_registration_system\n";
        echo "   - Collation: utf8mb4_unicode_ci\n";
        echo "   - Click 'Create'\n\n";
        
        echo "2. Or use MySQL command line:\n";
        echo "   mysql -u root -p\n";
        echo "   CREATE DATABASE course_registration_system;\n";
        echo "   EXIT;\n\n";
    }
    // Check if MySQL is not running
    elseif (strpos($e->getMessage(), 'Connection refused') !== false || 
            strpos($e->getMessage(), 'No connection could be made') !== false) {
        echo "Issue: MySQL service is not running\n\n";
        echo "Solutions:\n";
        echo "1. Start MySQL service:\n";
        echo "   - XAMPP: Open XAMPP Control Panel, start MySQL\n";
        echo "   - WAMP: Open WAMP, start MySQL\n";
        echo "   - Laragon: Open Laragon, start all services\n";
        echo "   - Windows Service: Run 'services.msc', find MySQL, start it\n\n";
    }
    
    echo "4. After fixing, run this script again:\n";
    echo "   php setup_and_verify_database.php\n\n";
}
