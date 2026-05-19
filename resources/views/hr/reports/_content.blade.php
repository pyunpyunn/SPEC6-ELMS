@php
    $employeeOptions = $employees->map(fn ($employee) => [
        'id' => $employee->id,
        'employee_id' => $employee->employee_id,
        'full_name' => $employee->full_name,
        'department_id' => $employee->department_id,
        'department' => $employee->departmentRecord?->name,
        'position_id' => $employee->position_id,
        'position' => $employee->positionRecord?->name ?? $employee->position,
    ])->values();

    $positionOptions = $positions->map(fn ($position) => [
        'id' => $position->id,
        'department_id' => $position->department_id,
        'name' => $position->name,
    ])->values();
@endphp

<div class="page-head" id="page-reports">
    <div>
        <h1>Analytics & Reports</h1>
    </div>
</div>

<div class="report-page"
    data-yearly-url="{{ route('reports.yearly-compensation') }}"
    data-individual-url="{{ route('reports.individual-balance') }}"
    data-yearly-export-url="{{ route('reports.yearly-compensation.export') }}"
    data-individual-export-url="{{ route('reports.individual-balance.export') }}"
    data-current-year="{{ $year }}">
    <section class="card report-section">
        <div class="card-h">
            <span>Yearly Compensation Review</span>
            <a class="btn btn-outline btn-sm" data-yearly-export href="#">Download CSV</a>
        </div>
        <div class="card-b">
            <div class="report-filters">
                <select data-yearly-department>
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
                <select data-yearly-year>
                    @foreach($yearOptions as $yearOption)
                        <option value="{{ $yearOption }}" @selected($yearOption == $year)>{{ $yearOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="report-state muted" data-yearly-loading>Loading yearly report...</div>
            <div class="report-table-title">Department Summary Table</div>
            <div class="table-wrap" data-yearly-table-wrap hidden>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Employee Count</th>
                            <th>Total Leave Days Taken</th>
                            <th>Avg Leave Per Employee</th>
                            <th>Sick Leave</th>
                            <th>Vacation Leave</th>
                            <th>Emergency Leave</th>
                            <th>Unpaid Leave</th>
                        </tr>
                    </thead>
                    <tbody data-yearly-body></tbody>
                </table>
            </div>
            <div class="report-state muted" data-yearly-empty hidden>No department leave summary found for the selected filters.</div>

            <div class="report-table-title report-table-title-spaced">Employee Compensation Breakdown Table</div>
            <div class="table-wrap" data-compensation-table-wrap hidden>
                <table class="report-table">
                    <thead data-compensation-head></thead>
                    <tbody data-compensation-body></tbody>
                </table>
            </div>
            <div class="report-pagination" data-compensation-pagination hidden>
                <button class="btn btn-outline btn-sm" type="button" data-compensation-prev>Previous</button>
                <span class="muted" data-compensation-page-info></span>
                <button class="btn btn-outline btn-sm" type="button" data-compensation-next>Next</button>
            </div>
            <div class="report-state muted" data-compensation-empty hidden>No employee compensation rows found for the selected filters.</div>
        </div>
    </section>

    <section class="card report-section">
        <div class="card-h">
            <span>Individual Balance Report</span>
            <a class="btn btn-outline btn-sm disabled-link" data-individual-export href="#" aria-disabled="true">Download Individual Report</a>
        </div>
        <div class="card-b">
            <div class="employee-search">
                <input type="search" data-employee-search placeholder="Search by employee name or ID" autocomplete="off">
                <div class="employee-results" data-employee-results hidden></div>
            </div>

            <div class="report-filters report-filters-individual">
                <select data-individual-department>
                    <option value="">Department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
                <select data-individual-position>
                    <option value="">Position</option>
                </select>
                <select data-individual-employee>
                    <option value="">Employee Name</option>
                </select>
                <select data-individual-year>
                    @foreach($yearOptions as $yearOption)
                        <option value="{{ $yearOption }}" @selected($yearOption == $year)>{{ $yearOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="report-state muted" data-individual-empty>Search or filter to load an employee's leave balance report.</div>
            <div class="report-state muted" data-individual-loading hidden>Loading individual balance report...</div>
            <div data-individual-report hidden>
                <div class="employee-report-head">
                    <div>
                        <strong data-report-name></strong>
                        <span class="muted" data-report-meta></span>
                    </div>
                    <span class="badge active" data-report-year></span>
                </div>
                <div class="table-wrap">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Leave Type</th>
                                <th>Total Entitlement</th>
                                <th>Days Used</th>
                                <th>Days Remaining</th>
                            </tr>
                        </thead>
                        <tbody data-individual-body></tbody>
                    </table>
                </div>
                <div class="compensation-summary">
                    <div class="report-table-title">Compensation Summary</div>
                    <div class="table-wrap">
                        <table class="report-table compensation-summary-table">
                            <thead>
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Days Used</th>
                                    <th>Days Remaining</th>
                                    <th>Total Compensation</th>
                                </tr>
                            </thead>
                            <tbody data-individual-compensation-body></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('.report-page');
    const employees = @json($employeeOptions);
    const positions = @json($positionOptions);
    const currentYear = root.dataset.currentYear;
    let resolvedEmployee = null;
    let searchEmployee = null;
    let compensationRowsCache = [];
    let compensationPage = 1;
    const compensationRowsPerPage = 7;

    const yearlyDepartment = root.querySelector('[data-yearly-department]');
    const yearlyYear = root.querySelector('[data-yearly-year]');
    const yearlyExport = root.querySelector('[data-yearly-export]');
    const yearlyLoading = root.querySelector('[data-yearly-loading]');
    const yearlyTableWrap = root.querySelector('[data-yearly-table-wrap]');
    const yearlyBody = root.querySelector('[data-yearly-body]');
    const yearlyEmpty = root.querySelector('[data-yearly-empty]');
    const compensationTableWrap = root.querySelector('[data-compensation-table-wrap]');
    const compensationHead = root.querySelector('[data-compensation-head]');
    const compensationBody = root.querySelector('[data-compensation-body]');
    const compensationEmpty = root.querySelector('[data-compensation-empty]');
    const compensationPagination = root.querySelector('[data-compensation-pagination]');
    const compensationPrev = root.querySelector('[data-compensation-prev]');
    const compensationNext = root.querySelector('[data-compensation-next]');
    const compensationPageInfo = root.querySelector('[data-compensation-page-info]');

    const searchInput = root.querySelector('[data-employee-search]');
    const searchResults = root.querySelector('[data-employee-results]');
    const individualDepartment = root.querySelector('[data-individual-department]');
    const individualPosition = root.querySelector('[data-individual-position]');
    const individualEmployee = root.querySelector('[data-individual-employee]');
    const individualYear = root.querySelector('[data-individual-year]');
    const individualExport = root.querySelector('[data-individual-export]');
    const individualEmpty = root.querySelector('[data-individual-empty]');
    const individualLoading = root.querySelector('[data-individual-loading]');
    const individualReport = root.querySelector('[data-individual-report]');
    const individualBody = root.querySelector('[data-individual-body]');
    const reportName = root.querySelector('[data-report-name]');
    const reportMeta = root.querySelector('[data-report-meta]');
    const reportYear = root.querySelector('[data-report-year]');
    const individualCompensationBody = root.querySelector('[data-individual-compensation-body]');

    const setHidden = (node, hidden) => node.hidden = hidden;
    const option = (value, text) => {
        const node = document.createElement('option');
        node.value = value;
        node.textContent = text;
        return node;
    };

    const exportUrl = (base, params) => {
        const url = new URL(base, window.location.origin);
        Object.entries(params).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                url.searchParams.set(key, value);
            }
        });
        return url.toString();
    };

    function renderCompensationPage() {
        const totalPages = Math.max(1, Math.ceil(compensationRowsCache.length / compensationRowsPerPage));
        compensationPage = Math.min(Math.max(compensationPage, 1), totalPages);
        const start = (compensationPage - 1) * compensationRowsPerPage;
        const pageRows = compensationRowsCache.slice(start, start + compensationRowsPerPage);

        compensationBody.innerHTML = '';
        pageRows.forEach((row) => {
            const tr = document.createElement('tr');
            ['employee_id', 'full_name', 'department', 'position'].forEach((key) => {
                const td = document.createElement('td');
                td.textContent = row[key];
                tr.appendChild(td);
            });
            row.leave_compensations.forEach((leaveCompensation) => {
                const days = document.createElement('td');
                days.textContent = leaveCompensation.days_used;
                tr.appendChild(days);

                const remaining = document.createElement('td');
                remaining.textContent = leaveCompensation.days_remaining;
                tr.appendChild(remaining);

                const compensation = document.createElement('td');
                compensation.textContent = leaveCompensation.total_compensation;
                tr.appendChild(compensation);
            });
            const total = document.createElement('td');
            total.textContent = row.total_leave_compensation;
            total.className = 'total-compensation-cell';
            tr.appendChild(total);
            compensationBody.appendChild(tr);
        });

        compensationPrev.disabled = compensationPage <= 1;
        compensationNext.disabled = compensationPage >= totalPages;
        compensationPageInfo.textContent = `Page ${compensationPage} of ${totalPages}`;
        setHidden(compensationPagination, compensationRowsCache.length <= compensationRowsPerPage);
    }

    async function loadYearlyReport() {
        setHidden(yearlyLoading, false);
        setHidden(yearlyTableWrap, true);
        setHidden(yearlyEmpty, true);
        setHidden(compensationTableWrap, true);
        setHidden(compensationEmpty, true);
        setHidden(compensationPagination, true);

        const params = {
            department: yearlyDepartment.value,
            year: yearlyYear.value,
        };
        yearlyExport.href = exportUrl(root.dataset.yearlyExportUrl, params);

        const url = exportUrl(root.dataset.yearlyUrl, params);
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        const payload = await response.json();
        const rows = payload.rows || [];
        const compensationTypes = payload.compensation_types || [];
        const compensationRows = payload.compensation_rows || [];

        yearlyBody.innerHTML = '';
        rows.forEach((row) => {
            const tr = document.createElement('tr');
            ['department', 'employee_count', 'total_leave_days_taken', 'avg_leave_per_employee', 'sick_leave', 'vacation_leave', 'emergency_leave', 'unpaid_leave'].forEach((key) => {
                const td = document.createElement('td');
                td.textContent = row[key];
                tr.appendChild(td);
            });
            yearlyBody.appendChild(tr);
        });

        compensationHead.innerHTML = '';
        const headerRow = document.createElement('tr');
        ['Employee ID', 'Full Name', 'Department', 'Position'].forEach((label) => {
            const th = document.createElement('th');
            th.textContent = label;
            headerRow.appendChild(th);
        });
        compensationTypes.forEach((type) => {
            [type.days_header, type.remaining_header, type.compensation_header].forEach((label) => {
                const th = document.createElement('th');
                th.textContent = label;
                headerRow.appendChild(th);
            });
        });
        const totalHeader = document.createElement('th');
        totalHeader.textContent = 'Total Leave Compensation';
        totalHeader.className = 'total-compensation-cell';
        headerRow.appendChild(totalHeader);
        compensationHead.appendChild(headerRow);

        compensationRowsCache = compensationRows;
        compensationPage = 1;
        renderCompensationPage();

        setHidden(yearlyLoading, true);
        setHidden(yearlyTableWrap, rows.length === 0);
        setHidden(yearlyEmpty, rows.length > 0);
        setHidden(compensationTableWrap, compensationRows.length === 0);
        setHidden(compensationEmpty, compensationRows.length > 0);
    }

    function filteredPositions() {
        return positions.filter((position) => !individualDepartment.value || String(position.department_id) === individualDepartment.value);
    }

    function filteredEmployees() {
        return employees.filter((employee) => {
            const departmentMatches = !individualDepartment.value || String(employee.department_id) === individualDepartment.value;
            const positionMatches = !individualPosition.value || String(employee.position_id) === individualPosition.value;
            return departmentMatches && positionMatches;
        });
    }

    function renderPositionOptions() {
        const selected = individualPosition.value;
        individualPosition.innerHTML = '';
        individualPosition.appendChild(option('', 'Position'));
        filteredPositions().forEach((position) => individualPosition.appendChild(option(position.id, position.name)));
        individualPosition.value = filteredPositions().some((position) => String(position.id) === selected) ? selected : '';
    }

    function renderEmployeeOptions() {
        const selected = individualEmployee.value;
        individualEmployee.innerHTML = '';
        individualEmployee.appendChild(option('', 'Employee Name'));
        filteredEmployees().forEach((employee) => individualEmployee.appendChild(option(employee.id, `${employee.full_name} - ${employee.employee_id}`)));
        individualEmployee.value = filteredEmployees().some((employee) => String(employee.id) === selected) ? selected : '';
    }

    function syncFiltersToEmployee(employee) {
        individualDepartment.value = employee.department_id || '';
        renderPositionOptions();
        individualPosition.value = employee.position_id || '';
        renderEmployeeOptions();
        individualEmployee.value = employee.id;
        individualYear.value = currentYear;
    }

    function clearResolvedReport() {
        resolvedEmployee = null;
        setHidden(individualReport, true);
        setHidden(individualLoading, true);
        setHidden(individualEmpty, false);
        individualExport.classList.add('disabled-link');
        individualExport.setAttribute('aria-disabled', 'true');
        individualExport.href = '#';
    }

    async function loadIndividualReport(employeeId) {
        if (!employeeId) {
            clearResolvedReport();
            return;
        }

        setHidden(individualEmpty, true);
        setHidden(individualReport, true);
        setHidden(individualLoading, false);

        const params = { employeeId, year: individualYear.value };
        const response = await fetch(exportUrl(root.dataset.individualUrl, params), { headers: { Accept: 'application/json' } });

        if (!response.ok) {
            clearResolvedReport();
            return;
        }

        const payload = await response.json();
        resolvedEmployee = payload.employee;
        reportName.textContent = payload.employee.full_name;
        reportMeta.textContent = `${payload.employee.department || 'No department'} | ${payload.employee.position || 'No position'} | ${payload.employee.employee_id || 'No employee ID'}`;
        reportYear.textContent = payload.year;
        individualBody.innerHTML = '';

        payload.balances.forEach((row) => {
            const tr = document.createElement('tr');
            ['leave_type', 'total_entitlement', 'days_used', 'days_remaining'].forEach((key) => {
                const td = document.createElement('td');
                td.textContent = row[key];
                tr.appendChild(td);
            });
            individualBody.appendChild(tr);
        });

        individualCompensationBody.innerHTML = '';
        payload.leave_compensation_summary.forEach((row) => {
            const tr = document.createElement('tr');
            ['leave_type', 'days_used', 'days_remaining', 'total_compensation'].forEach((key) => {
                const td = document.createElement('td');
                td.textContent = row[key];
                tr.appendChild(td);
            });
            individualCompensationBody.appendChild(tr);
        });
        const totalRow = document.createElement('tr');
        totalRow.className = 'compensation-total-row';
        ['Total Leave Compensation', '', '', payload.total_leave_compensation].forEach((value) => {
            const td = document.createElement('td');
            td.textContent = value;
            totalRow.appendChild(td);
        });
        individualCompensationBody.appendChild(totalRow);

        individualExport.href = exportUrl(root.dataset.individualExportUrl, params);
        individualExport.classList.remove('disabled-link');
        individualExport.removeAttribute('aria-disabled');
        setHidden(individualLoading, true);
        setHidden(individualReport, false);
    }

    function renderSearchResults() {
        const query = searchInput.value.trim().toLowerCase();
        searchResults.innerHTML = '';

        if (!query) {
            searchResults.hidden = true;
            return;
        }

        const matches = employees.filter((employee) => {
            return `${employee.full_name} ${employee.employee_id}`.toLowerCase().includes(query);
        }).slice(0, 8);

        matches.forEach((employee) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = `${employee.full_name} - ${employee.employee_id}`;
            button.addEventListener('click', () => {
                searchEmployee = employee;
                searchInput.value = `${employee.full_name} - ${employee.employee_id}`;
                searchResults.hidden = true;
                syncFiltersToEmployee(employee);
                loadIndividualReport(employee.id);
            });
            searchResults.appendChild(button);
        });

        searchResults.hidden = matches.length === 0;
    }

    function manualFilterChanged() {
        searchEmployee = null;
        searchInput.value = '';
        searchResults.hidden = true;
    }

    yearlyDepartment.addEventListener('change', loadYearlyReport);
    yearlyYear.addEventListener('change', loadYearlyReport);
    compensationPrev.addEventListener('click', () => {
        compensationPage -= 1;
        renderCompensationPage();
    });
    compensationNext.addEventListener('click', () => {
        compensationPage += 1;
        renderCompensationPage();
    });
    searchInput.addEventListener('input', renderSearchResults);

    individualDepartment.addEventListener('change', () => {
        manualFilterChanged();
        renderPositionOptions();
        renderEmployeeOptions();
        clearResolvedReport();
    });
    individualPosition.addEventListener('change', () => {
        manualFilterChanged();
        renderEmployeeOptions();
        clearResolvedReport();
    });
    individualEmployee.addEventListener('change', () => {
        manualFilterChanged();
        loadIndividualReport(individualEmployee.value);
    });
    individualYear.addEventListener('change', () => {
        manualFilterChanged();
        loadIndividualReport(individualEmployee.value || resolvedEmployee?.id);
    });
    individualExport.addEventListener('click', (event) => {
        if (!resolvedEmployee) {
            event.preventDefault();
        }
    });

    renderPositionOptions();
    renderEmployeeOptions();
    clearResolvedReport();
    loadYearlyReport();
});
</script>

<style>
    .report-page{display:grid;gap:18px}
    .report-section .card-h{align-items:center}
    .report-filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}
    .report-filters select,.employee-search input{min-width:190px;padding:9px 11px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text)}
    .report-filters-individual select{flex:1 1 190px}
    .report-state{padding:12px 0}
    .report-table-title{font-size:14px;font-weight:800;color:var(--text);margin:4px 0 10px}
    .report-table-title-spaced{margin-top:22px}
    .employee-search{position:relative;margin-bottom:12px}
    .employee-search input{width:100%}
    .employee-results{position:absolute;z-index:20;left:0;right:0;top:calc(100% + 4px);background:var(--surface);border:1px solid var(--border);border-radius:8px;box-shadow:var(--shadow);overflow:hidden}
    .employee-results button{display:block;width:100%;padding:10px 12px;background:transparent;border:0;border-bottom:1px solid var(--border);text-align:left;color:var(--text);cursor:pointer}
    .employee-results button:hover{background:var(--surface2)}
    .employee-report-head{display:flex;justify-content:space-between;gap:12px;align-items:center;margin:14px 0}
    .employee-report-head strong{display:block;margin-bottom:4px}
    .disabled-link{opacity:.5;pointer-events:none}
    .status-exceeded{color:var(--danger);font-weight:700}
    .total-compensation-cell{font-weight:800;color:var(--success)}
    .compensation-summary{margin-top:18px}
    .compensation-total-row td{font-weight:800;color:var(--success);border-top:2px solid rgba(42,117,84,.28);background:var(--success-bg)}
    .report-pagination{display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-top:12px}
    .report-pagination button:disabled{opacity:.45;cursor:not-allowed}
    .report-table{width:100%}
    .report-table th,.report-table td{white-space:nowrap}
    @media(max-width:760px){
        .report-filters select{width:100%}
        .employee-report-head{align-items:flex-start;flex-direction:column}
    }
</style>
