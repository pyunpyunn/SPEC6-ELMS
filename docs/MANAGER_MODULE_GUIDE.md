# Manager Module Guide

## Scope

The Manager module covers the manager role in the Employee Leave Management System. It focuses on department/team leave monitoring, leave approval, team calendar viewing, manager personal leave filing, notifications, and profile management.

## Test Account

Use the seeded manager account:

```text
Email: manager@test.com
Password: password
```

After login, the user is redirected to:

```text
/manager/dashboard
```

## Main Routes

| Route | Name | Purpose |
| --- | --- | --- |
| `/manager/dashboard` | `manager.dashboard` | Manager dashboard with team request summary and mini calendar |
| `/manager/approvals` | `manager.approvals.index` | Approval inbox for team leave requests |
| `/manager/approvals/{leaveApplication}` | `manager.approvals.show` | Leave request review screen |
| `/manager/approvals/{leaveApplication}/review` | `manager.approvals.review` | Approve/reject a pending team leave request |
| `/manager/calendar` | `manager.calendar` | Team leave calendar |
| `/manager/team` | `manager.team` | Team overview and balances |
| `/manager/my-leave` | `manager.my-leave` | Manager personal leave application/history |
| `/manager/notifications` | `manager.notifications` | Manager notifications |
| `/manager/profile` | `manager.profile` | Manager profile page |

All routes are protected by:

```php
Route::middleware('role:manager')
```

## Important Files

| File | Purpose |
| --- | --- |
| `app/Http/Controllers/Manager/ManagerController.php` | Main manager business logic |
| `app/Http/Requests/Manager/LeaveDecisionRequest.php` | Validation for approve/reject remarks |
| `app/Http/Requests/Manager/StoreManagerLeaveRequest.php` | Validation for manager leave filing |
| `resources/views/manager/layout.blade.php` | Manager sidebar/header layout |
| `resources/views/manager/dashboard.blade.php` | Manager dashboard |
| `resources/views/manager/approvals.blade.php` | Approval inbox |
| `resources/views/manager/request-show.blade.php` | Request review page |
| `resources/views/manager/calendar.blade.php` | Team calendar |
| `resources/views/manager/team.blade.php` | Team overview |
| `resources/views/manager/my-leave.blade.php` | Manager leave filing/history |
| `resources/views/manager/profile.blade.php` | Profile and password update |
| `public/manager-portal.css` | Manager module styling |

## Features Covered

### Dashboard

- Shows pending team requests.
- Shows team members on leave today as a stat.
- Shows approved leave count for the current month.
- Displays a compact mini calendar based on approved team leave records.
- Provides direct access to the approval inbox.

### Approval Inbox

- Lists leave requests from employees in the manager’s team/department.
- Supports search, status filter, and leave type filter.
- Uses pagination.
- Allows managers to open a request review page.

### Leave Review

- Managers can approve or reject pending leave requests.
- Remarks are required.
- Approval checks the employee’s remaining leave balance before updating.
- Approved requests increment used leave balance.
- Employees receive an in-app notification after approval/rejection.

### Team Calendar

- Displays approved team leaves in a calendar grid.
- Uses colored event labels by leave type.
- Includes checkable legends for Vacation, Sick, Emergency, Maternity, and Bereavement.
- Checking/unchecking a legend item shows or hides that leave type without reloading the page.

### Team Overview

- Lists active team members.
- Shows position, status, vacation balance, and sick balance.
- Uses pagination.

### Manager Personal Leave

- Managers can file their own leave requests.
- Requests are submitted to HR for review.
- Remaining leave balance is validated before submission.
- HR admins receive an in-app notification.

### Profile

- Editable fields: first name, last name, email, phone, address.
- Locked fields are grayed out: employee ID, role, department, position.
- If Save Profile is clicked without changes, the system shows a warning instead of a success message.
- Password change is handled separately.

## Data Scope

The manager sees team employees using this logic:

- Employees directly assigned to the manager through `manager_id`.
- Employees in the same department as the manager.
- The manager’s own employee record is excluded from team approval lists.

## Running Checks

Use these commands after changes:

```powershell
php artisan route:list --name=manager
php artisan view:cache
php artisan view:clear
php artisan test
```

Use `view:clear` after `view:cache` during development so Blade changes are picked up normally.

## Notes

- The Manager module uses Blade templates and Eloquent ORM.
- No raw SQL is used in the manager workflow.
- Leave approval and balance updates run inside a database transaction.
- The UI follows the `docs/prototypes/kuan1.html` visual direction: green palette, compact sidebar, header dropdowns, and calendar layout.
