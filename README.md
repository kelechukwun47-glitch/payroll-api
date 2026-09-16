
## How to setup

This project establishes a secure, versioned API layer for core human resource workflows. The application enforces stateless authentication via Sanctum tokens, uses ULIDs for non-sequential primary key security, standardizes JSON output using Eloquent Resources, and isolates domain workflows into explicit V1 API controllers.

### Prerequisites
- **PHP:** >=8.2+   v(8.232)
- **Composer:** Installed globally
- **Database:** MySQL
- **Tooling:** Postman, XAMPP / Local PHP CLI


## 2. Step-by-Step Build Process (From Scratch)

### Phase 1: Project Initialization & Environment Setup
1. **Scaffolded Laravel Application:**
   Initialized a fresh Laravel 12 project and configured local environment variables (`.env`) for MySQL database connections.

2. **Configured Authentication:**
   Installed and published **Laravel Sanctum** to handle API token generation (`HasApiTokens`).

### Phase 2: Database Schema & Model Mappings
1. **Created Core Migrations:**
   Designed database tables for `users`, `departments`, `employees`, `attendances`, `leave_types`, `leave_requests`, and `payrolls`.

2. **Applied ULID Primary Keys:**
   Added the `HasUlids` trait across all Eloquent models to use ULIDs as primary keys.

3. **Established Eloquent Relationships:**
   - `User` **HasOne** `Employee`
   - `Department` **HasMany** `Employee`
   - `Employee` **BelongsTo** `Department` & **BelongsTo** `User` (and `BelongsTo` manager)
   - `Employee` **HasMany** `Attendance` & `LeaveRequest`



### Phase 3: Controller & Service Implementation

1. **Auth & Department Modules:**
   Built `AuthController` for issuing and revoking Sanctum tokens, and `DepartmentController` with eager-loaded head profiles and employee counters.

2. **Attendance Tracking:**
   Implemented `AttendanceController::clockIn()` and `clockOut()`. Configured business logic to resolve the authenticated user (`$request->user()->employee`), prevent duplicate same-day clock-ins, and auto-flag status (`present` vs. `late` relative to 09:00 AM).

3. **Leave Processing:**
   Built submission (`POST /leave-requests`) and approval (`POST /leave-requests/{id}/approve`) endpoints.

4. **Payroll Processing:**
   Implemented `PayrollController` wrapped inside database transactions (`DB::transaction`) to compute salaries, tax deductions, and generate monthly payslips.

---

## 3. Real Development Challenges & Problem-Solving Log

### Challenge 1: Namespace & Composer Autoload Collisions

- **Problem:** API calls to `/api/v1/departments` threw a `500 Internal Server Error` stating `Call to undefined method App\Http\Controllers\Api\V1\DepartmentController::index()`.
- **Root Cause:** Redundant outer controller stubs existed in `app/Http/Controllers/` alongside the versioned files in `app/Http/Controllers/Api/V1/`. Composer bound routes to the empty root stub.

- **Fix:** Removed outer duplicate controller files, locked route groups strictly to `App\Http\Controllers\Api\V1\`, and executed `composer dump-autoload` and `php artisan route:clear`.

### Challenge 2: User-to-Employee Data Linkage

- **Problem:** Calling `/api/v1/attendance/clock-in` returned `422 Unprocessable Entity` ("User is not linked to an employee record.").

- **Root Cause:** The `User` model was missing an explicit `HasOne` relationship to `Employee`, and the seeded database user lacked an assigned `user_id` foreign key in the `employees` table.
- **Fix:**
  1. Explicitly defined the `employee()` relationship method in `User.php`.
  2. Linked the seeded admin user to the primary employee record using interactive `php artisan tinker`.
  3. Re-authenticated in Postman to generate a fresh token with full context.

### Challenge 3: PowerShell CLI Variable Interpolation
- **Problem:** Running inline Tinker commands (`artisan tinker --execute`) in PowerShell threw `ParseErrorException`.

- **Root Cause:** PowerShell interprets variables like `$user` as internal shell variables before handing execution to PHP.
- **Fix:** Transitioned to running database updates interactively inside the native `php artisan tinker` prompt.

---

## 4. How to Set Up & Run Locally

### Prerequisites
- PHP >= 8.2
- Composer
- MySQL Server
- Postman (for testing)


### Setup Instructions


1. **Clone the repository:**
   git clone https://github.com/kelechukwun47-glitch/payroll-api.git
   cd payroll-api

2. **Install PHP dependencies:**
   composer install

3. **Configure Environment:**
   cp .env.example .env
   (Update your MySQL database credentials DB_DATABASE, DB_USERNAME, DB_PASSWORD inside .env)

4. **Generate Application Key:**
   php artisan key:generate

5. **Run Migrations & Seeders:**
   php artisan migrate --seed

6. **Start the Local API Server:**
   php artisan serve
   (The server will start at http://127.0.0.1:8000)

---

## 5. Endpoints & API Reference

Required Request Header: Accept: application/json
Protected Routes Header: Authorization: Bearer <TOKEN>

- **POST /api/v1/login**
  - Auth Required: No
  - Payload: {"email": "...", "password": "..."}
  - Expected Status: 200 OK (Returns Token)

- **GET /api/v1/departments**
  - Auth Required: Bearer
  - Description: Eager-loads heads and employee counts
  - Expected Status: 200 OK (Paginated)

- **POST /api/v1/departments**
  - Auth Required: Bearer
  - Payload: {"name": "Engineering", "code": "ENG"}
  - Expected Status: 201 Created

- **GET /api/v1/employees**
  - Auth Required: Bearer
  - Description: Eager-loads assigned user context
  - Expected Status: 200 OK

- **POST /api/v1/attendance/clock-in**
  - Auth Required: Bearer
  - Description: Auto-resolves $request->user()->employee
  - Expected Status: 201 Created / 422 (Duplicate)

- **POST /api/v1/attendance/clock-out**
  - Auth Required: Bearer
  - Description: Closes active attendance record
  - Expected Status: 200 OK

- **POST /api/v1/leave-requests**
  - Auth Required: Bearer
  - Payload: {"leave_type_id": "...", "start_date": "...", "end_date": "..."}
  - Expected Status: 201 Created (Pending)

- **POST /api/v1/leave-requests/{id}/approve**
  - Auth Required: Bearer
  - Description: Approve leave request by ULID {id}
  - Expected Status: 200 OK (Approved)

- **POST /api/v1/payrolls/generate**
  - Auth Required: Bearer
  - Payload: {"month": 9, "year": 2026}
  - Expected Status: 200 OK (Generates Payslips)

## 6. Postman Testing Workflow

1. **Authenticate:** Send a POST request to /api/v1/login with valid credentials. Copy the plain-text token from the response.

2. **Set Headers:** Add Authorization: Bearer <TOKEN> and Accept: application/json to your Postman request headers.

3. **Execute API Calls:** Test department creation, employee listing, clock-in/out, leave submission and approval, and monthly payroll calculation.