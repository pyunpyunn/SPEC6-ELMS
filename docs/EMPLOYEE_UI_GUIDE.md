# Employee UI/UX and Naming Conventions Guide

## 1. Design & Structure Overview
- **Layout:** Use sidebar, header, and main content area as in the prototype.
- **UI Components:** Use cards, tables, modals, tabs, badges, and forms for employee pages.
- **UX Patterns:** Consistent filters, search, pagination, and CRUD actions. Use modals for forms.

## 2. Employee Pages: What to Copy
- **Sidebar Navigation:** `.sidebar`, `.sb-item` for navigation links like "Employees".
- **Header:** `.header`, `.profile-area`, notification components for top bar.
- **Tables:** `.table-wrap`, `table`, `th`, `td` for employee lists. Use badges for status.
- **Cards:** `.card`, `.card-header`, `.card-body`, `.card-footer` for employee details.
- **Forms:** `.form-grid`, `.form-group`, and input styles for employee creation/edit.
- **Modals:** `.modal`, `.modal-header`, `.modal-body`, `.modal-footer` for add/edit actions.
- **Badges:** `.badge-emp` for employee role, `.badge-active`, `.badge-inactive` for status.

## 3. Naming Conventions to Apply
- **Database/Field Names:**
  - `employees.employee_id` (format: EMP-0001)
  - `users.status` (`pending`, `active`, `inactive`)
  - `users.role` (`employee`, `manager`, `hr_admin`)
- **Class/ID Naming:**
  - Use `employee-` prefix for custom classes/IDs (e.g., `employee-table`, `employee-card`).
  - Use `data-employee-id` attributes for JS hooks.
- **Status Badges:**
  - `.badge-active`, `.badge-inactive` for employee status.
- **Role Badges:**
  - `.badge-emp` for employee role.
- **Department Codes:**
  - Use `departments.code` (e.g., `HR`, `IT`) for department display.

## 4. Example: Employee List Table

```html
<div class="table-wrap employee-table">
  <table>
    <thead>
      <tr>
        <th>Employee ID</th>
        <th>Name</th>
        <th>Department</th>
        <th>Status</th>
        <th>Role</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr data-employee-id="EMP-0001">
        <td>EMP-0001</td>
        <td class="td-name">Jane Doe</td>
        <td><span class="badge badge-info">IT</span></td>
        <td><span class="badge badge-active">Active</span></td>
        <td><span class="badge badge-emp">Employee</span></td>
        <td>
          <button class="btn btn-sm btn-outline">Edit</button>
          <button class="btn btn-sm btn-danger">Deactivate</button>
        </td>
      </tr>
      <!-- ...existing code... -->
    </tbody>
  </table>
</div>
```

## 5. Example: Employee Profile Card

```html
<div class="card employee-card">
  <div class="card-header">
    <span class="card-title">Employee Profile</span>
  </div>
  <div class="card-body">
    <div class="detail-row">
      <span class="dl">Employee ID:</span>
      <span class="dv">EMP-0001</span>
    </div>
    <div class="detail-row">
      <span class="dl">Name:</span>
      <span class="dv">Jane Doe</span>
    </div>
    <div class="detail-row">
      <span class="dl">Department:</span>
      <span class="dv">IT</span>
    </div>
    <div class="detail-row">
      <span class="dl">Status:</span>
      <span class="dv"><span class="badge badge-active">Active</span></span>
    </div>
    <div class="detail-row">
      <span class="dl">Role:</span>
      <span class="dv"><span class="badge badge-emp">Employee</span></span>
    </div>
    <!-- ...existing code... -->
  </div>
</div>
```

## 6. General Recommendations
- **Uniformity:** Use badge, table, and card classes from the prototype for all employee-related UI.
- **Naming:** Use HQ’s field and status names in HTML and JS/data attributes.
- **Accessibility:** Keep semantic structure (tables for data, forms for input, buttons for actions).
- **Responsiveness:** Retain grid and flex layouts for mobile compatibility.
