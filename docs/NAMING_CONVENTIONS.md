# Naming Conventions

## Roles

- Database role values: `employee`, `manager`, `hr_admin`.
- Folder and route naming uses `admin` for HR Admin pages because it is shorter and easier to scan.

## Routes

- HR Admin routes use `admin.*` names and `/admin` URLs.
- Manager routes use `manager.*` names and `/manager` URLs.
- Employee routes use `employee.*` names and `/employee` URLs.
- Resource routes use plural nouns: `employees`, `leave-types`, and `leaves`.

## Controllers

- Admin controllers live in `App\Http\Controllers\Admin`.
- Manager controllers live in `App\Http\Controllers\Manager`.
- Employee controllers live in `App\Http\Controllers\Employee`.
- Shared models stay in `App\Models`.

## Views

- Admin views live in `resources/views/admin`.
- Manager views live in `resources/views/manager`.
- Employee views live in `resources/views/employee`.
- Shared layouts and layout helpers live in `resources/views/layouts`.

## Validation

- Shared Form Request classes live in `app/Http/Requests`.
- Role-specific request classes may live in subfolders such as `app/Http/Requests/Manager`.
