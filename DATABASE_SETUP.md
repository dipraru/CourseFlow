# Database Setup Documentation
## University Course Registration System

### Overview
This document describes the complete database architecture for the University Course Registration System built with Laravel and MySQL.

---

## Database Architecture

### 1. **Users Table**
Stores all system users (students, advisors, department head, authority).

**Fields:**
- `id` - Primary key
- `name` - User's full name
- `email` - Unique email address
- `password` - Hashed password
- `role` - Enum: ['student', 'advisor', 'department_head', 'authority']
- `user_id` - Unique identifier (Student ID, Teacher ID)
- `is_active` - Boolean flag for active users
- `email_verified_at` - Email verification timestamp
- `remember_token` - For "Remember Me" functionality
- `timestamps` - created_at, updated_at

**Relationships:**
- Has one UserProfile
- Has many CourseRegistrations (as student)
- Has many RegistrationApprovals (as approver)
- Has many PaymentSlips (as student)
- Has many UserProfiles (as advisor)

---

### 2. **User Profiles Table**
Extended user information specific to students and faculty.

**Fields:**
- `id` - Primary key
- `user_id` - Foreign key to users table
- `batch_id` - Foreign key to batches (for students)
- `advisor_id` - Foreign key to users (for students)
- `phone` - Contact number
- `address` - Full address
- `date_of_birth` - Date of birth
- `department` - Department name (default: Computer Science)
- `designation` - Job title (for faculty/staff)
- `timestamps`

**Relationships:**
- Belongs to User
- Belongs to Batch (students)
- Belongs to User as Advisor (students)

---

### 3. **Batches Table**
Student admission batches/cohorts.

**Fields:**
- `id` - Primary key
- `name` - Batch name (e.g., "Batch 2023")
- `year` - Admission year
- `total_semesters` - Total semesters in program (default: 8)
- `is_active` - Boolean flag
- `timestamps`

**Relationships:**
- Has many UserProfiles (students)

---

### 4. **Semesters Table**
Academic semesters/terms.

**Fields:**
- `id` - Primary key
- `name` - Semester name (e.g., "Fall 2024")
- `type` - Enum: ['Spring', 'Summer', 'Fall']
- `year` - Academic year
- `semester_number` - Sequential number (1, 2, 3...)
- `registration_start_date` - Registration period start
- `registration_end_date` - Registration period end
- `semester_start_date` - Semester start
- `semester_end_date` - Semester end
- `is_active` - Active for registration
- `is_current` - Currently running semester
- `timestamps`

**Relationships:**
- Has many SemesterCourses
- Has many CourseRegistrations
- Has many Fees
- Has many PaymentSlips

---

### 5. **Courses Table**
Course catalog with all available courses.

**Fields:**
- `id` - Primary key
- `course_code` - Unique course code (e.g., "CSE101")
- `course_name` - Course title
- `description` - Course description
- `credit_hours` - Credit hours (decimal: 3.0, 1.5)
- `intended_semester` - Typical semester (1-8)
- `course_type` - Enum: ['theory', 'lab', 'theory_lab']
- `is_active` - Boolean flag
- `timestamps`

**Relationships:**
- Has many SemesterCourses

---

### 6. **Semester Courses Table**
Courses offered in specific semesters (pivot with additional data).

**Fields:**
- `id` - Primary key
- `semester_id` - Foreign key to semesters
- `course_id` - Foreign key to courses
- `max_students` - Maximum enrollment capacity
- `enrolled_students` - Current enrollment count
- `is_available` - Availability flag
- `timestamps`
- **Unique constraint**: semester_id + course_id

**Relationships:**
- Belongs to Semester
- Belongs to Course
- Has many CourseRegistrations

---

### 7. **Course Registrations Table**
Student course registration applications.

**Fields:**
- `id` - Primary key
- `student_id` - Foreign key to users
- `semester_id` - Foreign key to semesters
- `semester_course_id` - Foreign key to semester_courses
- `status` - Enum: ['pending', 'advisor_approved', 'head_approved', 'rejected', 'completed']
- `student_remarks` - Optional student notes
- `rejection_reason` - Reason if rejected
- `total_fee` - Calculated registration fee
- `applied_at` - Application timestamp
- `advisor_approved_at` - Advisor approval timestamp
- `head_approved_at` - Department head approval timestamp
- `rejected_at` - Rejection timestamp
- `timestamps`
- **Unique constraint**: student_id + semester_course_id + semester_id

**Relationships:**
- Belongs to User (student)
- Belongs to Semester
- Belongs to SemesterCourse
- Has many RegistrationApprovals

---

### 8. **Registration Approvals Table**
Tracks approval workflow (advisor → department head).

**Fields:**
- `id` - Primary key
- `course_registration_id` - Foreign key to course_registrations
- `approver_id` - Foreign key to users
- `approver_role` - Enum: ['advisor', 'department_head']
- `status` - Enum: ['pending', 'approved', 'rejected']
- `comments` - Approver's comments
- `action_taken_at` - Action timestamp
- `timestamps`

**Relationships:**
- Belongs to CourseRegistration
- Belongs to User (approver)

---

### 9. **Fees Table**
Fee structure for each semester.

**Fields:**
- `id` - Primary key
- `semester_id` - Foreign key to semesters
- `per_credit_fee` - Fee per credit hour
- `admission_fee` - Admission/registration fee
- `library_fee` - Library fee
- `lab_fee` - Laboratory fee
- `other_fees` - Miscellaneous fees
- `fee_description` - Description
- `is_active` - Boolean flag
- `timestamps`

**Relationships:**
- Belongs to Semester

---

### 10. **Payment Slips Table**
Generated payment slips for approved registrations.

**Fields:**
- `id` - Primary key
- `student_id` - Foreign key to users
- `semester_id` - Foreign key to semesters
- `slip_number` - Unique slip number
- `total_amount` - Total payable amount
- `credit_hours` - Total credit hours
- `fee_breakdown` - JSON: Detailed fee breakdown
- `registered_courses` - JSON: List of courses
- `status` - Enum: ['generated', 'downloaded']
- `generated_at` - Generation timestamp
- `downloaded_at` - Download timestamp
- `due_date` - Payment due date
- `timestamps`

**Relationships:**
- Belongs to User (student)
- Belongs to Semester

---

## Registration Workflow

```
1. Department Authority sets up:
   - Active Semester
   - Available Courses for Semester
   - Fee Structure

2. Student applies for courses:
   - Selects courses from available list
   - Status: 'pending'
   - Creates CourseRegistration records

3. Advisor reviews:
   - Receives notification
   - Approves/Rejects with comments
   - Creates RegistrationApproval record (role: advisor)
   - Status changes to: 'advisor_approved' or 'rejected'

4. Department Head reviews:
   - Receives notification (if advisor approved)
   - Approves/Rejects with comments
   - Creates RegistrationApproval record (role: department_head)
   - Status changes to: 'head_approved' or 'rejected'

5. Payment Slip Generation:
   - System generates PaymentSlip
   - Calculates total fees based on credit hours
   - Student can download slip
   - Manual bank payment (outside system)
```

---

## User Roles & Permissions

### 1. **Student**
- View available courses for current semester
- Apply for course registration
- View registration status
- Download payment slip (after approval)
- View own course history

### 2. **Advisor**
- View assigned students
- Review pending course registrations
- Approve/Reject registrations with comments
- View student registration history

### 3. **Department Head**
- View all registrations
- Review advisor-approved registrations
- Final approve/reject with comments
- View department-wide statistics

### 4. **Authority**
- Manage semesters (create, activate)
- Manage courses (add, edit)
- Assign courses to semesters
- Set fee structures
- Assign advisors to student batches
- View all system data and reports

---

## Setup Instructions

### 1. Database Configuration

Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=course_registration_system
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 2. Create Database

```bash
# Using MySQL command line
mysql -u root -p
CREATE DATABASE course_registration_system;
EXIT;
```

Or use phpMyAdmin, MySQL Workbench, or any database tool.

### 3. Run Migrations

```bash
php artisan migrate
```

This will create all tables with proper relationships.

### 4. Seed Sample Data (Optional)

```bash
php artisan db:seed
```

This creates:
- 1 Department Head (email: head@cs.edu, password: password)
- 1 Authority User (email: admin@cs.edu, password: password)
- 2 Advisors (advisor1@cs.edu, advisor2@cs.edu)
- 5 Sample Students (student1@cs.edu to student5@cs.edu)
- 2 Batches (2023, 2024)
- 1 Active Semester (Fall 2024)
- 5 Sample Courses
- Fee structure

### 5. Fresh Migration (if needed)

```bash
php artisan migrate:fresh --seed
```

This drops all tables and recreates them with seed data.

---

## Database Indexes

The following indexes are automatically created:
- Primary keys on all tables
- Unique indexes: email, user_id, course_code, slip_number
- Foreign key indexes for relationships
- Composite unique indexes for preventing duplicates

---

## Important Notes

1. **Cascading Deletes**: When a parent record is deleted, related records are handled:
   - User deleted → Profile, Registrations, Approvals cascade delete
   - Semester deleted → Semester Courses, Registrations cascade delete
   - Course Registration deleted → Approvals cascade delete

2. **Soft Deletes**: Not implemented. Consider adding if you need to keep historical data.

3. **Data Integrity**: 
   - Foreign key constraints ensure referential integrity
   - Unique constraints prevent duplicate registrations
   - Enum fields ensure valid status values

4. **JSON Fields**: 
   - `fee_breakdown` in payment_slips
   - `registered_courses` in payment_slips
   - Automatically cast to arrays in Laravel models

5. **Timestamps**: All tables have `created_at` and `updated_at` fields automatically managed by Laravel.

---

## ER Diagram Conceptual Overview

```
Users (1) ←→ (1) UserProfiles
  ↓ (1:M)
CourseRegistrations
  ↓ (1:M)
RegistrationApprovals

Batches (1) ←→ (M) UserProfiles

Semesters (1) ←→ (M) SemesterCourses ←→ (M) Courses
    ↓ (1:M)              ↓ (1:M)
  Fees           CourseRegistrations

Semesters (1) ←→ (M) PaymentSlips
```

---

## Future Enhancements

Consider adding:
1. **Audit Logging**: Track all changes to registrations
2. **Notifications Table**: For email/SMS notifications
3. **Documents Table**: For uploading supporting documents
4. **Academic Records**: Grades, transcripts
5. **Timetable/Schedule**: Class schedules and room assignments
6. **Prerequisites**: Course prerequisite management
7. **Waitlist**: For courses at capacity
8. **Online Payment Integration**: Payment gateway

---

## Support

For issues or questions:
- Check Laravel documentation: https://laravel.com/docs
- Review migration files in `database/migrations/`
- Check model relationships in `app/Models/`

---

**Last Updated**: October 4, 2025
**Laravel Version**: 11.x
**Database**: MySQL 5.7+
