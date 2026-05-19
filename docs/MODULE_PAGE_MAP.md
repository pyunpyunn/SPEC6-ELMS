# Module and Page Location Guide

Use this file during technical defense when you need to explain where a feature lives.

## Global Files

| Feature | Location |
| --- | --- |
| Web routes | `routes/web.php` |
| Fortify auth setup | `app/Providers/FortifyServiceProvider.php` |
| Registration action | `app/Actions/Fortify/CreateNewUser.php` |
| Role middleware | `app/Http/Middleware/RoleMiddleware.php` |
| Account approval middleware | `app/Http/Middleware/EnsureAccountApproved.php` |
| Profile completion middleware | `app/Http/Middleware/EnsureProfileComplete.php` |
| Eloquent models | `app/Models` |
| Form Request validation | `app/Http/Requests` |
| Database migrations | `database/migrations` |
| Database seeders | `database/seeders` |
| Position and employee ID config | `config/positions.php` |

## Styling and Layouts

| Area | Layout | Stylesheet |
| --- | --- | --- |
| HR Admin | `resources/views/admin/layout.blade.php` | `public/hr-prototype.css` |
| Manager | `resources/views/manager/layout.blade.php` | `public/manager-portal.css` |
| Employee | `resources/views/layouts/employee.blade.php` | `public/employee-prototype.css` |
| Auth/shared pages | `resources/views/layouts/app.blade.php` | `public/employee-prototype.css`, `resources/css/app.css` |

## HR Admin Pages

| Page or Feature | URL | Route Name | Controller | Blade View |
| --- | --- | --- | --- | --- |
| Dashboard | `/admin/dashboard` | `admin.dashboard` | `Admin/DashboardController.php`, `Hr/HrController.php` | `resources/views/admin/dashboard.blade.php` |
| User verification | `/admin/users/pending` | `admin.users.pending` | `Admin/UserController.php`, `Hr/HrController.php` | `resources/views/admin/users/pending.blade.php` |
| User list | `/admin/users` | `admin.users.index` | `Admin/UserController.php`, `Hr/HrController.php` | `resources/views/admin/users/pending.blade.php` |
| Employee directory CRUD | `/admin/employees` | `admin.employees.*` | `Admin/EmployeeController.php`, `Hr/HrController.php` | `resources/views/admin/employees/index.blade.php` |
| Employee form partial | Used inside employee page | N/A | `Hr/HrController.php` | `resources/views/admin/employees/partials/form.blade.php` |
| Employee profile/detail | `/admin/employees/{employee}` | `admin.employees.show` | `Admin/EmployeeController.php`, `Hr/HrController.php` | `resources/views/admin/profile/show.blade.php` |
| Departments | `/admin/departments` | `admin.departments.*` | `Admin/DepartmentController.php`, `Hr/HrController.php` | `resources/views/admin/departments/index.blade.php` |
| Department form partial | Used inside departments page | N/A | `Hr/HrController.php` | `resources/views/admin/departments/partials/form.blade.php` |
| Leave type configuration | `/admin/leave-types` | `admin.leave-types.*` | `Admin/LeaveTypeController.php`, `Hr/HrController.php` | `resources/views/admin/leave-types/index.blade.php` |
| Master request log | `/admin/requests` | `admin.requests.index` | `Admin/LeaveRequestController.php`, `Hr/HrController.php` | `resources/views/admin/requests/index.blade.php` |
| Reports | `/admin/reports` | `admin.reports.index` | `Admin/ReportController.php`, `Hr/HrController.php` | `resources/views/admin/reports/index.blade.php` |
| CSV export | `/admin/reports/export` | `admin.reports.export` | `Admin/ReportController.php`, `Hr/HrController.php` | streamed CSV response |
| Company calendar | `/admin/calendar` | `admin.calendar` | `Admin/ReportController.php`, `Hr/HrController.php` | `resources/views/admin/calendar.blade.php` |
| HR own leave | `/admin/my-leave` | `admin.my-leave` | `Admin/ProfileController.php`, `Hr/HrController.php` | `resources/views/admin/my-leave.blade.php` |
| HR notifications | `/admin/notifications` | `admin.notifications` | `Admin/ProfileController.php`, `Hr/HrController.php` | `resources/views/admin/notifications.blade.php` |
| HR profile | `/admin/profile` | `admin.profile` | `Admin/ProfileController.php`, `Hr/HrController.php` | `resources/views/admin/profile/show.blade.php` |

## Manager Pages

| Page or Feature | URL | Route Name | Controller | Blade View |
| --- | --- | --- | --- | --- |
| Dashboard | `/manager/dashboard` | `manager.dashboard` | `Manager/DashboardController.php`, `Manager/ManagerController.php` | `resources/views/manager/dashboard.blade.php` |
| Approval inbox | `/manager/approvals` | `manager.approvals.index` | `Manager/LeaveApprovalController.php`, `Manager/ManagerController.php` | `resources/views/manager/approvals/index.blade.php` |
| Review leave request | `/manager/approvals/{leaveApplication}` | `manager.approvals.show` | `Manager/LeaveApprovalController.php`, `Manager/ManagerController.php` | `resources/views/manager/approvals/show.blade.php` |
| Approve/reject request | PATCH approval routes | `manager.approvals.*` | `Manager/LeaveApprovalController.php`, `Manager/ManagerController.php` | redirects back to approval pages |
| Team calendar | `/manager/calendar` | `manager.calendar` | `Manager/ManagerController.php` | `resources/views/manager/calendar.blade.php` |
| Team overview | `/manager/team` | `manager.team` | `Manager/ManagerController.php` | `resources/views/manager/team.blade.php` |
| Manager own leave | `/manager/my-leave` | `manager.my-leave` | `Manager/ManagerController.php` | `resources/views/manager/my-leave.blade.php` |
| Notifications | `/manager/notifications` | `manager.notifications` | `Manager/ManagerController.php` | `resources/views/manager/notifications.blade.php` |
| Profile | `/manager/profile` | `manager.profile` | `Manager/ManagerController.php` | `resources/views/manager/profile.blade.php` |

## Employee Pages

| Page or Feature | URL | Route Name | Controller | Blade View |
| --- | --- | --- | --- | --- |
| Dashboard | `/employee/dashboard` | `employee.dashboard` | `Employee/DashboardController.php`, `EmployeePortalController.php` | `resources/views/employee/dashboard.blade.php` |
| Leave history and filing | `/employee/leaves` | `employee.leaves.index` | `Employee/LeaveApplicationController.php`, `EmployeePortalController.php` | `resources/views/employee/leaves/index.blade.php` |
| Leave create page | `/employee/leaves/create` | `employee.leaves.create` | `Employee/LeaveApplicationController.php` | `resources/views/employee/leaves/create.blade.php` |
| Leave detail | `/employee/leaves/{leaveApplication}` | `employee.leaves.show` | `Employee/LeaveApplicationController.php` | `resources/views/employee/leaves/show.blade.php` |
| Apply leave modal | Included in leave pages | N/A | `EmployeePortalController.php` | `resources/views/employee/partials/apply-leave-modal.blade.php` |
| Reports and balances | `/employee/reports` | `employee.reports` | `Employee/LeaveApplicationController.php`, `EmployeePortalController.php` | `resources/views/employee/reports.blade.php` |
| Leave balances alias | `/employee/leave-balances` | `employee.leave-balances` | `Employee/LeaveApplicationController.php` | `resources/views/employee/reports.blade.php` |
| Notifications | `/employee/notifications` | `employee.notifications` | `Employee/LeaveApplicationController.php` | `resources/views/employee/notifications.blade.php` |
| Profile | `/employee/profile` | `employee.profile` | `Employee/LeaveApplicationController.php` | `resources/views/employee/profile.blade.php` |

## Validation Classes

| Validation Purpose             | Form Request                                                 |
| ------------------------------ | ------------------------------------------------------------ |
| HR employee create/update      | `app/Http/Requests/Hr/EmployeeRequest.php`                   |
| HR user activation             | `app/Http/Requests/Hr/ActivateUserRequest.php`               |
| HR employee deactivation       | `app/Http/Requests/Hr/DeactivateEmployeeRequest.php`         |
| HR department create/update    | `app/Http/Requests/Hr/DepartmentRequest.php`                 |
| HR leave type create/update    | `app/Http/Requests/Hr/LeaveTypeRequest.php`                  |
| HR leave decision              | `app/Http/Requests/Hr/LeaveDecisionRequest.php`              |
| HR own leave filing            | `app/Http/Requests/Hr/StoreHrLeaveRequest.php`               |
| HR profile update              | `app/Http/Requests/Hr/ProfileRequest.php`                    |
| Employee leave filing          | `app/Http/Requests/StoreLeaveApplicationRequest.php`         |
| Manager leave decision         | `app/Http/Requests/Manager/LeaveDecisionRequest.php`         |
| Manager own leave filing       | `app/Http/Requests/Manager/StoreManagerLeaveRequest.php`     |
| Manager profile update         | `app/Http/Requests/Manager/ProfileRequest.php`               |
| Password update                | `app/Http/Requests/UpdatePasswordRequest.php`                |

## Model Relationships

| Model                    | Key Relationships                                                                             |
| -------------------------| ----------------------------------------------------------------------------------------------|
| `User`                   | `employee`, `department`, `position`, `notifications`                                         |
| `Employee`               | `user`, `departmentRecord`, `positionRecord`, `manager`, `leaveApplications`, `leaveBalances` |
| `Department`             | `managerUser`, `manager`, `employees`, `positions`, `users`                                   |
| `Position`               | `department`, `users`, `employees`                                                            |
| `LeaveType`              | `applications`, `balances`                                                                    |
| `LeaveApplication`       | `employee`, `leaveType`, `reviewer`                                                           |
| `LeaveBalance`           | `employee`, `leaveType`                                                                       |
| `SystemNotification`     | `user`                                                                                        |

## Seeders

| Seeder                       | Purpose                                              |
| -----------------------------| -----------------------------------------------------|
| `DatabaseSeeder.php`         | Runs all seeders in order                            |
| `UserSeeder.php`             | Creates the single HR account                        |
| `DepartmentSeeder.php`       | Creates HR, IT, FIN, and OPS departments             |
| `PositionSeeder.php`         | Creates department-specific positions                |
| `EmployeeSeeder.php`         | Creates Kathleen Barro's linked HR employee profile  |
| `LeaveTypeSeeder.php`        | Creates default leave types                          |
| `LeaveBalanceSeeder.php`     | Seeds current-year leave balances                    |
| `LeaveApplicationSeeder.php` | Left empty so leave requests start clean             |
