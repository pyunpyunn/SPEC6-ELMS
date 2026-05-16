# ELMS Design Specification

## Product Identity

The Employee Leave Management System uses a compact administrative dashboard style. The interface is built for repeated HR and manager work: scanning tables, filtering records, reviewing leave requests, and moving between role-specific pages quickly.

## Visual Language

- Primary color: deep green `#2a6349`
- Primary dark: `#1c4a36`
- Accent green: `#48a47e`
- Page background: `#f3f6f4` or `#f5f8f5`
- Surface: `#ffffff`
- Secondary surface: `#eef2ef`
- Border: `#dde5e0`
- Text: `#172b22`
- Secondary text: `#42614f`
- Muted text: `#78957f`
- Success: `#2a7554`
- Warning: `#b87214`
- Danger: `#b83030`
- Info: `#2256a0`

## Typography

- Main font: Plus Jakarta Sans
- Monospace font: JetBrains Mono
- Main page headings: 21px, bold
- Card titles: 13px to 16px, bold
- Table text: 12px to 12.5px
- Labels and metadata: 10px to 12px

## Layout System

- Main application shell: left sidebar, top header, scrollable content area.
- Sidebar width: 248px.
- Header height: 62px.
- Main content padding: 26px on desktop, 18px on small screens.
- Card radius: 12px.
- Small control radius: 7px.
- Standard card padding: 18px to 20px.
- Standard grid gap: 16px.

## Navigation

- HR Admin uses `resources/views/admin/layout.blade.php`.
- Manager uses `resources/views/manager/layout.blade.php`.
- Employee uses `resources/views/layouts/employee.blade.php`.
- Auth pages use `resources/views/layouts/app.blade.php`.

Each role has a role-specific sidebar. Active pages are highlighted using the `active` class and route name checks such as `request()->routeIs(...)`.

## Components

- Cards: bordered white surfaces for dashboards, forms, reports, and detail panels.
- Tables: compact records with uppercase headers, hover rows, and horizontal scrolling.
- Badges: status and role labels using color-coded pill styles.
- Flash messages: success, warning, and error alert blocks.
- Forms: two-column grids on desktop and one-column grids on mobile.
- Modals: used for focused actions such as leave application forms.
- Calendars: month grids with colored leave-type event chips.
- Notification dropdowns: top-header menu with unread count refresh.

## Responsive Behavior

- Dashboard grids collapse from four columns to two columns below about 1100px.
- Main dashboard layouts collapse to one column below about 1200px.
- Sidebars become stacked or full-width on small screens.
- Form grids collapse to one column below about 780px.

## Style Files

- HR/Admin styles: `public/hr-prototype.css`
- Manager styles: `public/manager-portal.css`
- Employee and auth styles: `public/employee-prototype.css`
- Vite entry CSS: `resources/css/app.css`

## Design Intent

The system should feel professional, readable, and operations-focused. It avoids decorative landing-page patterns and prioritizes clean navigation, dense information, filters, consistent status colors, and predictable CRUD screens.
