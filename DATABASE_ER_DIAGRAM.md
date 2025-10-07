# Entity Relationship Diagram - Course Registration System

## Visual Database Structure

```
┌─────────────────────────────────────────────────────────────────┐
│                    Course Registration System                    │
│                         Database Schema                          │
└─────────────────────────────────────────────────────────────────┘


┌──────────────────────────┐
│         USERS            │
│──────────────────────────│
│ PK  id                   │
│     name                 │
│ UK  email                │
│     password             │
│     role ◄────────────────── [student, advisor, department_head, authority]
│ UK  user_id              │
│     is_active            │
│     timestamps           │
└────────┬─────────────────┘
         │
         │ 1:1
         │
         ▼
┌──────────────────────────┐
│     USER_PROFILES        │
│──────────────────────────│
│ PK  id                   │
│ FK  user_id              │
│ FK  batch_id             │ ─────┐
│ FK  advisor_id           │ ◄───┐│
│     phone                │     ││
│     address              │     ││
│     date_of_birth        │     ││
│     department           │     ││
│     designation          │     ││
│     timestamps           │     ││
└──────────────────────────┘     ││
                                 ││
                                 ││
┌──────────────────────────┐     ││
│        BATCHES           │     ││
│──────────────────────────│     ││
│ PK  id                   │ ◄───┘│
│ UK  name                 │      │
│     year                 │      │
│     total_semesters      │      │
│     is_active            │      │
│     timestamps           │      │
└──────────────────────────┘      │
                                  │
                                  │
┌──────────────────────────────────────────────────────┐
│                   SEMESTERS                          │
│──────────────────────────────────────────────────────│
│ PK  id                                               │
│     name                                             │
│     type ◄─────────────── [Spring, Summer, Fall]     │
│     year                                             │
│     semester_number                                  │
│     registration_start_date                          │
│     registration_end_date                            │
│     semester_start_date                              │
│     semester_end_date                                │
│     is_active                                        │
│     is_current                                       │
│     timestamps                                       │
└────────┬───────────────────────────┬─────────────────┘
         │ 1:M                       │ 1:M
         │                           │
         ▼                           ▼
┌──────────────────────────┐   ┌──────────────────────────┐
│         FEES             │   │ SEMESTER_COURSES         │
│──────────────────────────│   │──────────────────────────│
│ PK  id                   │   │ PK  id                   │
│ FK  semester_id          │   │ FK  semester_id          │
│     per_credit_fee       │   │ FK  course_id            │ ────┐
│     admission_fee        │   │     max_students         │     │
│     library_fee          │   │     enrolled_students    │     │
│     lab_fee              │   │     is_available         │     │
│     other_fees           │   │     timestamps           │     │
│     fee_description      │   │ UK  (semester_id,        │     │
│     is_active            │   │      course_id)          │     │
│     timestamps           │   └────────┬─────────────────┘     │
└──────────────────────────┘            │ 1:M                   │
                                        │                       │
                                        ▼                       │
┌────────────────────────────────────────────────────┐          │
│           COURSE_REGISTRATIONS                     │          │
│────────────────────────────────────────────────────│          │
│ PK  id                                             │          │
│ FK  student_id ◄─────────────────────────┐         │          │
│ FK  semester_id                          │         │          │
│ FK  semester_course_id                   │         │          │
│     status ◄─────────────────────────────┼──────────── [pending, advisor_approved,
│     student_remarks                      │         │           head_approved, rejected,
│     rejection_reason                     │         │           completed]
│     total_fee                            │         │
│     applied_at                           │         │
│     advisor_approved_at                  │         │
│     head_approved_at                     │         │
│     rejected_at                          │         │
│     timestamps                           │         │
│ UK  (student_id, semester_course_id,     │         │
│      semester_id)                        │         │
└────────┬───────────────────────────────────        │
         │ 1:M                                        │
         │                                            │
         ▼                                            │
┌──────────────────────────────────────────┐          │
│     REGISTRATION_APPROVALS               │          │
│──────────────────────────────────────────│          │
│ PK  id                                   │          │
│ FK  course_registration_id               │          │
│ FK  approver_id ◄────────────────────────┘          │
│     approver_role ◄───────────  [advisor, dept_head]│
│     status ◄────────────────── [pending, approved,  │
│     comments                     rejected]          │
│     action_taken_at                                 │
│     timestamps                                      │
└─────────────────────────────────────────────────────┘


┌──────────────────────────────────────────┐          │
│         PAYMENT_SLIPS                    │          │
│──────────────────────────────────────────│          │
│ PK  id                                   │          │
│ FK  student_id ◄─────────────────────────┘          │
│ FK  semester_id                                     │
│ UK  slip_number                                     │
│     total_amount                                    │
│     credit_hours                                    │
│     fee_breakdown (JSON)                            │
│     registered_courses (JSON)                       │
│     status ◄────────────── [generated, downloaded]  │
│     generated_at                                    │
│     downloaded_at                                   │
│     due_date                                        │
│     timestamps                                      │
└─────────────────────────────────────────────────────┘


┌──────────────────────────┐
│        COURSES           │
│──────────────────────────│
│ PK  id                   │ ◄───────────────────────┘
│ UK  course_code          │
│     course_name          │
│     description          │
│     credit_hours         │
│     intended_semester    │
│     course_type ◄──────────── [theory, lab, theory_lab]
│     is_active            │
│     timestamps           │
└──────────────────────────┘
```

---

## Relationship Types

### One-to-One (1:1)
- **Users** ↔ **UserProfiles**: Each user has one profile

### One-to-Many (1:M)
- **Users** → **UserProfiles** (as advisor)
- **Batches** → **UserProfiles** (students)
- **Semesters** → **SemesterCourses**
- **Semesters** → **CourseRegistrations**
- **Semesters** → **Fees**
- **Semesters** → **PaymentSlips**
- **Courses** → **SemesterCourses**
- **SemesterCourses** → **CourseRegistrations**
- **Users** → **CourseRegistrations** (as student)
- **Users** → **PaymentSlips** (as student)
- **CourseRegistrations** → **RegistrationApprovals**
- **Users** → **RegistrationApprovals** (as approver)

### Many-to-Many (M:M)
- **Semesters** ↔ **Courses** (through **SemesterCourses**)
- **Students** ↔ **Courses** (through **CourseRegistrations**)

---

## Data Flow Diagram

```
┌─────────────────────────────────────────────────────────┐
│                  REGISTRATION WORKFLOW                   │
└─────────────────────────────────────────────────────────┘

1. SETUP PHASE (Authority)
   ┌─────────────┐
   │  Authority  │
   └──────┬──────┘
          │
          ├──► Create/Activate Semester
          ├──► Add Courses to Semester (SemesterCourses)
          └──► Set Fee Structure
          

2. APPLICATION PHASE (Student)
   ┌─────────────┐
   │   Student   │
   └──────┬──────┘
          │
          ├──► View Available Courses
          ├──► Select Courses
          └──► Submit Registration
                     │
                     ▼
          [CourseRegistration: status=pending]


3. ADVISOR APPROVAL (Advisor)
   ┌─────────────┐
   │   Advisor   │
   └──────┬──────┘
          │
          ├──► View Pending Registrations
          ├──► Review Student Selections
          └──► Approve/Reject
                     │
                     ├─ Approve ──► [status=advisor_approved]
                     │              [RegistrationApproval created]
                     │
                     └─ Reject ───► [status=rejected]


4. HEAD APPROVAL (Department Head)
   ┌─────────────────┐
   │ Department Head │
   └────────┬────────┘
            │
            ├──► View Advisor-Approved Registrations
            ├──► Review Registration
            └──► Final Approve/Reject
                       │
                       ├─ Approve ──► [status=head_approved]
                       │              [RegistrationApproval created]
                       │              [PaymentSlip generated]
                       │
                       └─ Reject ───► [status=rejected]


5. PAYMENT PHASE (Student)
   ┌─────────────┐
   │   Student   │
   └──────┬──────┘
          │
          ├──► View Approved Registration
          ├──► Download Payment Slip (PDF)
          └──► Pay at Bank (Manual)
```

---

## Status State Diagram

```
CourseRegistration.status Flow:
════════════════════════════════

       START
         │
         ▼
    ┌─────────┐
    │ pending │ ◄────┐
    └────┬────┘      │
         │           │
         ▼           │
  ┌──────────────────┤
  │                  │
  ▼                  │
┌─────────────────┐  │
│ advisor_approved│  │
└────┬────────────┘  │
     │               │
     ▼               │
┌─────────────────┐  │
│  head_approved  │  │
└────┬────────────┘  │
     │               │
     ▼               │
┌────────────┐       │
│ completed  │       │
└────────────┘       │
                     │
Any stage can go to: │
         │           │
         ▼           │
    ┌──────────┐    │
    │ rejected │────┘
    └──────────┘
    
    (Student can reapply
     next semester)
```

---

## Database Indexes

### Primary Keys
- All tables have `id` as primary key (auto-increment)

### Unique Indexes
- `users.email`
- `users.user_id`
- `batches.name`
- `courses.course_code`
- `payment_slips.slip_number`
- `semester_courses (semester_id, course_id)`
- `course_registrations (student_id, semester_course_id, semester_id)`

### Foreign Key Indexes
- Automatically created on all FK columns
- Improves join performance

---

## Cascade Rules

### ON DELETE CASCADE
- User deleted → UserProfile deleted
- User deleted → CourseRegistrations deleted
- User deleted → PaymentSlips deleted
- Semester deleted → SemesterCourses deleted
- Semester deleted → CourseRegistrations deleted
- CourseRegistration deleted → RegistrationApprovals deleted

### ON DELETE SET NULL
- Batch deleted → UserProfile.batch_id = NULL
- Advisor deleted → UserProfile.advisor_id = NULL

---

## JSON Field Structure

### payment_slips.fee_breakdown
```json
{
  "per_credit": 500.00,
  "credit_hours": 13.5,
  "subtotal": 6750.00,
  "admission_fee": 1000.00,
  "library_fee": 200.00,
  "lab_fee": 300.00,
  "other_fees": 100.00,
  "total": 8350.00
}
```

### payment_slips.registered_courses
```json
[
  {
    "id": 1,
    "code": "CSE101",
    "name": "Introduction to Programming",
    "credits": 3.0
  },
  {
    "id": 2,
    "code": "CSE102",
    "name": "Programming Lab",
    "credits": 1.5
  }
]
```

---

## Security Considerations

1. **Password Hashing**: Uses bcrypt (Laravel default)
2. **CSRF Protection**: Laravel middleware
3. **SQL Injection**: Protected by Eloquent ORM
4. **XSS Protection**: Blade templates auto-escape
5. **Mass Assignment**: Protected by `$fillable` arrays

---

## Performance Optimization

1. **Eager Loading**: Use `with()` for relationships
2. **Indexing**: All foreign keys indexed
3. **Caching**: Cache fee structures, courses
4. **Pagination**: For large result sets
5. **Query Optimization**: Use `select()` for specific columns

---

**Legend:**
- PK = Primary Key
- FK = Foreign Key
- UK = Unique Key
- 1:1 = One-to-One Relationship
- 1:M = One-to-Many Relationship
- M:M = Many-to-Many Relationship

---

**Note**: This is a text-based representation. For a visual ER diagram, consider using tools like:
- MySQL Workbench
- dbdiagram.io
- DrawSQL
- Lucidchart
- ERDPlus
