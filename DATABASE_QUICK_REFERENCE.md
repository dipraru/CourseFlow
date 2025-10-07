# Course Registration System - Database Schema Quick Reference

## Table Summary

| Table Name | Purpose | Key Relationships |
|------------|---------|------------------|
| users | All system users | → user_profiles, course_registrations |
| user_profiles | Extended user info | ← users, batches |
| batches | Student cohorts | → user_profiles |
| semesters | Academic terms | → semester_courses, fees |
| courses | Course catalog | → semester_courses |
| semester_courses | Courses per semester | ← semesters, courses → registrations |
| course_registrations | Registration applications | ← users, semester_courses → approvals |
| registration_approvals | Approval workflow | ← course_registrations, users |
| fees | Semester fee structure | ← semesters |
| payment_slips | Payment documents | ← users, semesters |

---

## Status Flow

### Course Registration Status
```
pending → advisor_approved → head_approved → completed
   ↓              ↓                ↓
rejected      rejected        rejected
```

### Approval Status (per approval record)
```
pending → approved
   ↓
rejected
```

### Payment Slip Status
```
generated → downloaded
```

---

## Key Fields by Table

### users
- role: student | advisor | department_head | authority
- user_id: Unique identifier (STU0001, ADV001, etc.)

### semesters
- type: Spring | Summer | Fall
- is_active: Can register for this semester
- is_current: Currently running semester

### courses
- course_type: theory | lab | theory_lab
- intended_semester: 1-8 (typical semester number)

### course_registrations
- status: pending | advisor_approved | head_approved | rejected | completed

### registration_approvals
- approver_role: advisor | department_head
- status: pending | approved | rejected

---

## Sample Queries

### Get all pending registrations for an advisor
```sql
SELECT cr.*, u.name as student_name, c.course_name
FROM course_registrations cr
JOIN users u ON cr.student_id = u.id
JOIN semester_courses sc ON cr.semester_course_id = sc.id
JOIN courses c ON sc.course_id = c.id
JOIN user_profiles up ON u.id = up.user_id
WHERE up.advisor_id = ? AND cr.status = 'pending';
```

### Get available courses for current semester
```sql
SELECT c.*, sc.max_students, sc.enrolled_students
FROM courses c
JOIN semester_courses sc ON c.id = sc.course_id
JOIN semesters s ON sc.semester_id = s.id
WHERE s.is_active = 1 AND sc.is_available = 1;
```

### Calculate registration fee
```sql
SELECT 
    f.per_credit_fee * SUM(c.credit_hours) + 
    f.admission_fee + f.library_fee + f.lab_fee + f.other_fees as total_fee
FROM course_registrations cr
JOIN semester_courses sc ON cr.semester_course_id = sc.id
JOIN courses c ON sc.course_id = c.id
JOIN fees f ON cr.semester_id = f.semester_id
WHERE cr.student_id = ? AND cr.semester_id = ?
GROUP BY cr.student_id;
```

---

## Default Test Users (After Seeding)

| Role | Email | Password | User ID |
|------|-------|----------|---------|
| Department Head | head@cs.edu | password | HEAD001 |
| Authority | admin@cs.edu | password | ADMIN001 |
| Advisor 1 | advisor1@cs.edu | password | ADV001 |
| Advisor 2 | advisor2@cs.edu | password | ADV002 |
| Student 1 | student1@cs.edu | password | STU0001 |
| Student 2 | student2@cs.edu | password | STU0002 |
| Student 3 | student3@cs.edu | password | STU0003 |
| Student 4 | student4@cs.edu | password | STU0004 |
| Student 5 | student5@cs.edu | password | STU0005 |

---

## Migration Order

1. 0001_01_01_000000_create_users_table.php
2. 0001_01_01_000001_create_cache_table.php
3. 0001_01_01_000002_create_jobs_table.php
4. 2025_10_04_160209_create_batches_table.php
5. 2025_10_04_160222_create_semesters_table.php
6. 2025_10_04_160229_create_courses_table.php
7. 2025_10_04_160238_create_semester_courses_table.php
8. 2025_10_04_160248_create_course_registrations_table.php
9. 2025_10_04_160258_create_registration_approvals_table.php
10. 2025_10_04_160307_create_payment_slips_table.php
11. 2025_10_04_160316_create_fees_table.php
12. 2025_10_04_160326_create_user_profiles_table.php

**Note**: user_profiles must be last because it references both users and batches.

---

## Common Commands

```bash
# Fresh migration with seed data
php artisan migrate:fresh --seed

# Check migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback

# Reset and re-run all migrations
php artisan migrate:refresh

# Run seeders only
php artisan db:seed

# Create new migration
php artisan make:migration create_table_name

# Create new model
php artisan make:model ModelName

# Create model with migration
php artisan make:model ModelName -m
```

---

**Quick Start After Clone:**

1. Copy `.env.example` to `.env`
2. Set database credentials
3. Run: `php artisan migrate:fresh --seed`
4. Login with any test user above
