# HR Module Guide

## Default Access
- HR account: `hr@company.com`
- Password: `password`
- Temporary password for HR-created employees: `temp_pass`

## Main Routes
- `/hr/dashboard` - HR dashboard with department filter, cards, recent requests, department leave summary, and calendar shortcut.
- `/hr/users/pending` - pending user verification and activation.
- `/hr/employees` - employee master list with CRUD-style create/edit/deactivate.
- `/hr/departments` - department CRUD and department employee viewer.
- `/hr/leave-types` - leave type configuration and add leave type.
- `/hr/my-leave` - simplified HR user's own leave balances, request history, and yearly compensation estimate.
- `/hr/requests` - master request log. Add `?department_id=ID` for per-department request logs.
- `/hr/reports` - department summaries, yearly compensation, individual balance report.
- `/hr/reports/export?type=leaves` - CSV leave export.
- `/hr/reports/export?type=balances` - CSV balance export.
- `/hr/calendar` - global approved leave calendar.
- `/notifications` - user/system notifications.
- `/profile` - HR profile.

## Database Naming
- `users.status`: `pending`, `active`, `inactive`.
- `users.pending_employee_id`: employee ID entered during registration and used by HR verification.
- `users.role`: `employee`, `manager`, `hr_admin`.
- `employees.employee_id`: public company employee identifier, format `EMP-0001`.
- `departments.code`: short department code such as `HR`, `IT`, `FIN`.
- `leave_balances`: yearly balance per employee and leave type.
- `system_notifications`: in-app notifications for HR and users.

## Seeded Leave Types
- Sick Leave: 15 days. Medical proof is required for 3 or more days.
- Maternity Leave: 105 days, with notes for solo parent and stillbirth/miscarriage.
- Paternity Leave: 7 days.
- Bereavement Leave: 15 days.
- Vacation Leave: 15 days.
- Special Emergency Leave: 15 days.

## Implementation Notes
- HR verification creates the employee record, sets the user active, seeds current-year leave balances, and sends a notification.
- Normal leave day counts are stored as integers. Money values such as `daily_rate` and CSV compensation amounts keep decimal formatting.
- Calendar supports monthly and weekly views, department filtering, and leave-type checkbox filtering.
- Updating a leave type allocation recalculates current-year leave balances.
- Approving a leave request increments the matching current-year used balance.
- CSV exports use `response()->streamDownload()` with professional unique filenames.
- Department summary rows link directly to the master request log with the department filter applied.
