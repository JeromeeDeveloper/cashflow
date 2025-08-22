<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cash Flow - Head Office</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/vendors/iconly/bold.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/perfect-scrollbar/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/simple-datatables/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/x-icon">
</head>

<body>
    @include('layouts.header')
    <div id="app">
        @include('layouts.sidebar')
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h3>Cash Flow</h3>
                    <p class="text-subtitle text-muted">Consolidated cash flow data from all branches</p>
                </div>
            </div>

            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <h4 class="mb-0">Cash Flow Summary</h4>
                                <div class="d-flex align-items-center flex-wrap gap-2 justify-content-end">
                                    <!-- Table Filters -->
                                    <div class="input-group" style="max-width: 280px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                                        <input type="month" id="table_start_period" class="form-control" value="{{ $currentYear }}-{{ str_pad(array_search($currentMonth, ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']) + 1, 2, '0', STR_PAD_LEFT) }}">
                                    </div>
                                    <select id="table_branch_filter" class="form-select" style="max-width: 180px;">
                                        <option value="">All Branches</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    <select id="table_period" class="form-select" style="max-width: 150px;">
                                        <option value="3">3 Months</option>
                                        <option value="6">6 Months</option>
                                        <option value="12">12 Months</option>
                                        <option value="36">3 Years</option>
                                        <option value="60">5 Years</option>
                                    </select>


                                    <button id="btnExport" class="btn btn-success"><i class="bi bi-download me-2"></i>Export Cashflow Planning Report</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-2 small text-muted" id="selectionSummary"></div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="table-cashflow">
                                        <thead id="cf-thead">
                                            <!-- Dynamic header will be built by JS to mirror Excel export -->
                                        </thead>
                                        <tbody>
                                            <!-- Table content will be populated dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Add/Edit Modal -->
            <div class="modal fade" id="cashflowModal" tabindex="-1" aria-labelledby="cashflowModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cashflowModalLabel">Add Cash Flow Entry</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="cashflowForm">
                                <input type="hidden" id="cashflow_id" name="cashflow_id">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="account_code" class="form-label">Account Code</label>
                                        <input type="text" class="form-control" id="account_code" name="account_code" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="account_name" class="form-label">Account Name</label>
                                        <input type="text" class="form-control" id="account_name" name="account_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="account_type" class="form-label">Account Type</label>
                                        <select class="form-select" id="account_type" name="account_type" required>
                                            <option value="">Select Account Type</option>
                                            <option value="Asset">Asset</option>
                                            <option value="Liability">Liability</option>
                                            <option value="Equity">Equity</option>
                                            <option value="Income">Income</option>
                                            <option value="Expense">Expense</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cashflow_category" class="form-label">Cash Flow Category</label>
                                        <select class="form-select" id="cashflow_category" name="cashflow_category" required>
                                            <option value="">Select Category</option>
                                            <option value="Operating">Operating</option>
                                            <option value="Investing">Investing</option>
                                            <option value="Financing">Financing</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="branch_id" class="form-label">Branch</label>
                                        <select class="form-select" id="branch_id" name="branch_id" required>
                                            <option value="">Select Branch</option>
                                            <option value="1">Main Office</option>
                                            <option value="2">Branch 1</option>
                                            <option value="3">Branch 2</option>
                                            <option value="4">Branch 3</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="year" class="form-label">Year</label>
                                        <input type="number" class="form-control" id="year" name="year" min="2000" max="2100" value="{{ date('Y') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="month" class="form-label">Month</label>
                                        <select class="form-select" id="month" name="month" required>
                                            <option value="">Select Month</option>
                                            <option value="January">January</option>
                                            <option value="February">February</option>
                                            <option value="March">March</option>
                                            <option value="April">April</option>
                                            <option value="May">May</option>
                                            <option value="June">June</option>
                                            <option value="July">July</option>
                                            <option value="August">August</option>
                                            <option value="September">September</option>
                                            <option value="October">October</option>
                                            <option value="November">November</option>
                                            <option value="December">December</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="actual_amount" class="form-label">Actual Amount</label>
                                        <input type="number" class="form-control" id="actual_amount" name="actual_amount" step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="projection_percentage" class="form-label">Projection Percentage</label>
                                        <input type="number" class="form-control" id="projection_percentage" name="projection_percentage" step="0.01" min="0" max="100" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="projected_amount" class="form-label">Projected Amount</label>
                                        <input type="number" class="form-control" id="projected_amount" name="projected_amount" step="0.01" min="0" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="total" class="form-label">Total</label>
                                        <input type="number" class="form-control" id="total" name="total" step="0.01" min="0" readonly>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="btnSave">Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View Modal -->
            <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewModalLabel">Cash Flow Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Account Code:</label>
                                    <p id="view_account_code"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Account Name:</label>
                                    <p id="view_account_name"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Account Type:</label>
                                    <p id="view_account_type"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Category:</label>
                                    <p id="view_cashflow_category"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Branch:</label>
                                    <p id="view_branch"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Period:</label>
                                    <p id="view_period"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Actual Amount:</label>
                                    <p id="view_actual_amount"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Projection %:</label>
                                    <p id="view_projection_percentage"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Projected Amount:</label>
                                    <p id="view_projected_amount"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Total:</label>
                                    <p id="view_total"></p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this cash flow entry? This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="btnConfirmDelete">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="{{ asset('assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/simple-datatables/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/vendors/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Pass initial data from controller to JavaScript -->
    <script>
        const initialCashflows = @json($cashflows ?? []);
        const initialBranches = @json($branches ?? []);
        console.log('Initial cashflows from controller:', initialCashflows);
        console.log('Initial branches from controller:', initialBranches);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // CSRF token for Laravel
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            // Enable Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Do not initialize DataTable to avoid DOM sync issues on dynamic rebuilds

            // Modal instances
            const cashflowModal = new bootstrap.Modal(document.getElementById('cashflowModal'));
            const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

            // Current editing ID
            let currentEditId = null;

            // Build header and load initial data
            buildTableHeader();
            let lastLoadedCashflows = Array.isArray(initialCashflows) ? initialCashflows : [];
            if (lastLoadedCashflows.length > 0) {
                updateTable(lastLoadedCashflows);
            } else {
                // Check if current filter has data, if not, find the most recent month with data
                checkAndAdjustFilter();
            }


            // Edit buttons
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    currentEditId = id;
                    document.getElementById('cashflowModalLabel').textContent = 'Edit Cash Flow Entry';
                    loadCashflowForEdit(id);
                    cashflowModal.show();
                });
            });

            // View buttons
            document.querySelectorAll('.btn-view').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    loadCashflowForView(id);
                    viewModal.show();
                });
            });

            // Delete buttons
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    currentEditId = id;
                    deleteModal.show();
                });
            });

            // Save button
            document.getElementById('btnSave').addEventListener('click', function() {
                if (validateForm()) {
                    if (currentEditId) {
                        updateCashflow();
                    } else {
                        createCashflow();
                    }
                }
            });

            // Confirm delete button
            document.getElementById('btnConfirmDelete').addEventListener('click', function() {
                deleteCashflow();
            });

            // Auto-calculate projected amount and total
            document.getElementById('actual_amount').addEventListener('input', calculateAmounts);
            document.getElementById('projection_percentage').addEventListener('input', calculateAmounts);

            // Table filters
            document.getElementById('table_branch_filter').addEventListener('change', function() {
                loadCashflows();
            });
            document.getElementById('table_start_period').addEventListener('change', function() {
                buildTableHeader();
                loadCashflows();
            });
            document.getElementById('table_period').addEventListener('change', function() {
                buildTableHeader();
                if (lastLoadedCashflows.length > 0) updateTable(lastLoadedCashflows);
            });

            // Export button with SweetAlert
            document.getElementById('btnExport').addEventListener('click', function() {
                showExportConfirmation();
            });

            function showExportConfirmation() {
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June','July', 'August', 'September', 'October', 'November', 'December'];
                const currentMonthValue = document.getElementById('table_start_period').value || '{{ $currentYear }}-{{ str_pad(array_search($currentMonth, ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']) + 1, 2, '0', STR_PAD_LEFT) }}';
                const [curYear, curMonth] = currentMonthValue.split('-');
                const currentBranchId = document.getElementById('table_branch_filter').value || '';
                const currentPeriod = document.getElementById('table_period').value || '3';

                const branches = @json($branches->map(fn($b) => ['id' => $b->id, 'name' => $b->name]));
                const branchOptions = ['<option value="">All Branches</option>']
                    .concat(branches.map(b => `<option value="${b.id}" ${String(b.id)===String(currentBranchId)?'selected':''}>${b.name}</option>`))
                    .join('');

                Swal.fire({
                    title: 'Export Cash Flow Report',
                    html: `
                        <div class="text-start">
                            <div class="mb-2">Start Period</div>
                            <input type="month" id="export_start_period" class="form-control" value="${curYear}-${curMonth}">
                            <div class="mt-3 mb-2">Branch</div>
                            <select id="export_branch" class="form-select">${branchOptions}</select>
                            <div class="mt-3 mb-2">Period</div>
                            <select id="export_period" class="form-select">
                                <option value="3" ${currentPeriod==='3'?'selected':''}>3 Months</option>
                                <option value="6" ${currentPeriod==='6'?'selected':''}>6 Months</option>
                                <option value="12" ${currentPeriod==='12'?'selected':''}>12 Months</option>
                                <option value="36" ${currentPeriod==='36'?'selected':''}>3 Years</option>
                                <option value="60" ${currentPeriod==='60'?'selected':''}>5 Years</option>
                            </select>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-download me-2"></i>Export',
                    cancelButtonText: 'Cancel',
                    showLoaderOnConfirm: true,
                    preConfirm: () => exportCashflowsFromModal()
                });
            }

            function calculateAmounts() {
                const actualAmount = parseFloat(document.getElementById('actual_amount').value) || 0;
                const projectionPercentage = parseFloat(document.getElementById('projection_percentage').value) || 0;

                const projectedAmount = actualAmount * (projectionPercentage / 100);
                const total = projectedAmount;

                document.getElementById('projected_amount').value = projectedAmount.toFixed(2);
                document.getElementById('total').value = total.toFixed(2);
            }

            function validateForm() {
                const form = document.getElementById('cashflowForm');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return false;
                }
                return true;
            }

            function loadCashflows() {
                const monthInput = document.getElementById('table_start_period');
                const branchFilter = document.getElementById('table_branch_filter');

                const [year, month] = monthInput.value.split('-');
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                                  'July', 'August', 'September', 'October', 'November', 'December'];

                const params = new URLSearchParams({
                    year: year,
                    month: monthNames[parseInt(month) - 1]
                });
                if (branchFilter.value) {
                    params.set('branch_id', branchFilter.value);
                }

                fetch(`{{ route('head.cashflows.index') }}?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            lastLoadedCashflows = Array.isArray(data.data) ? data.data : [];
                            updateTable(lastLoadedCashflows);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading cashflows:', error);
                        showAlert('Error loading cashflows', 'error');
                    });
            }

            function checkAndAdjustFilter() {
                const monthInput = document.getElementById('table_start_period');
                const branchFilter = document.getElementById('table_branch_filter');

                const [year, month] = monthInput.value.split('-');
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                                  'July', 'August', 'September', 'October', 'November', 'December'];

                const params = new URLSearchParams({
                    year: year,
                    month: monthNames[parseInt(month) - 1]
                });
                if (branchFilter.value) {
                    params.set('branch_id', branchFilter.value);
                }

                fetch(`{{ route('head.cashflows.index') }}?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            lastLoadedCashflows = Array.isArray(data.data) ? data.data : [];
                            if (lastLoadedCashflows.length === 0) {
                                // No data for current filter, find the most recent month with data
                                findMostRecentData();
                            } else {
                                updateTable(lastLoadedCashflows);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error checking filter data:', error);
                        // If there's an error, try to find most recent data
                        findMostRecentData();
                    });
            }

            function findMostRecentData() {
                // Get all available data to find the most recent month
                fetch(`{{ route('head.cashflows.all') }}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                            // Find the most recent month with data
                            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                                              'July', 'August', 'September', 'October', 'November', 'December'];

                            let mostRecent = null;
                            data.data.forEach(cashflow => {
                                const year = parseInt(cashflow.year);
                                const monthIndex = monthNames.indexOf(cashflow.month);

                                if (!mostRecent ||
                                    year > mostRecent.year ||
                                    (year === mostRecent.year && monthIndex > mostRecent.monthIndex)) {
                                    mostRecent = {
                                        year: year,
                                        month: cashflow.month,
                                        monthIndex: monthIndex
                                    };
                                }
                            });

                            if (mostRecent) {
                                // Update the filter to the most recent month with data
                                const monthInput = document.getElementById('table_start_period');
                                const monthNumber = (mostRecent.monthIndex + 1).toString().padStart(2, '0');
                                monthInput.value = `${mostRecent.year}-${monthNumber}`;

                                // Reload data with the new filter
                                loadCashflows();

                                // Show a notification to the user
                                showAlert(`No data found for the selected period. Showing data for ${mostRecent.month} ${mostRecent.year} instead.`, 'info');
                            } else {
                                // No data at all, show empty table
                                updateTable([]);
                            }
                        } else {
                            // No data at all, show empty table
                            updateTable([]);
                        }
                    })
                    .catch(error => {
                        console.error('Error finding most recent data:', error);
                        // Show empty table as fallback
                        updateTable([]);
                    });
            }

            function updateTable(cashflows) {
                const tbody = document.querySelector('#table-cashflow tbody');
                tbody.innerHTML = '';

                const period = parseInt(document.getElementById('table_period').value || '3', 10);
                const { labels } = getPeriodLabels();
                const branchSelected = (document.getElementById('table_branch_filter').value || '').trim();

                if (!Array.isArray(cashflows) || cashflows.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="${4 + period}" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                No cash flow entries found
                            </td>
                        </tr>
                    `;
                    return;
                }

                // Split by type
                let receipts = cashflows.filter(c => (c.cashflow_type || '').toLowerCase() === 'receipts');
                let disbursements = cashflows.filter(c => (c.cashflow_type || '').toLowerCase() === 'disbursements');

                // When "All Branches" is selected, consolidate rows by GL account across branches
                if (!branchSelected) {
                    const groupByAccount = (items) => {
                        const map = new Map();
                        items.forEach(c => {
                            const key = c.gl_account?.id ?? c.gl_account_id ?? (c.account_name || 'N/A');
                            const nameRaw = c.gl_account?.account_name || c.account_name || 'N/A';
                            const isChild = c.gl_account && c.gl_account.parent_id;
                            const displayName = isChild ? `&gt; ${nameRaw}` : nameRaw;
                            const actual = parseFloat(c.actual_amount) || 0;
                            const proj = parseFloat(c.projection_percentage) || 0;

                            if (!map.has(key)) {
                                map.set(key, { name: displayName, actual: 0, projections: Array(period).fill(0) });
                            }
                            const entry = map.get(key);
                            entry.actual += actual;
                            let current = actual;
                            for (let i = 0; i < period; i++) {
                                current = current * (1 + (proj / 100));
                                entry.projections[i] += current;
                            }
                        });
                        return Array.from(map.values());
                    };

                    receipts = groupByAccount(receipts);
                    disbursements = groupByAccount(disbursements);
                }

                // Beginning balance = sum of all receipts actuals
                const beginningBalance = (!branchSelected)
                    ? receipts.reduce((sum, c) => sum + (parseFloat(c.actual) || 0), 0)
                    : receipts.reduce((sum, c) => sum + (parseFloat(c.actual_amount) || 0), 0);

                // Helper to build a row
                const makeRow = (cells) => `<tr>${cells.join('')}</tr>`;
                const th = (content, extra='') => `<th ${extra}>${content}</th>`;
                const td = (content, cls='') => `<td class="${cls}">${content}</td>`;

                // Row: CASH BEGINNING BALANCE
                const beginningCells = [
                    td('CASH BEGINNING BALANCE'),
                    td(formatNumber(beginningBalance), 'text-end'),
                    td('-', 'text-end')
                ];
                for (let i = 0; i < period; i++) beginningCells.push(td(formatNumber(beginningBalance), 'text-end'));
                beginningCells.push(td(formatNumber(beginningBalance * period), 'text-end'));
                tbody.insertAdjacentHTML('beforeend', makeRow(beginningCells));

                // Section: ADD: RECEIPTS
                const sectionEmpty = new Array(period + 3).fill(td('-', 'text-end')).join('');
                tbody.insertAdjacentHTML('beforeend', makeRow([
                    td('ADD: RECEIPTS'),
                    td('-', 'text-end'),
                    td('-', 'text-end'),
                    ...Array.from({length: period}, () => td('-', 'text-end')),
                    td('-', 'text-end')
                ]));

                // Totals per period
                const receiptsTotals = Array.from({length: period}, () => 0);
                let receiptsGrand = 0;

                // Receipt rows
                receipts.forEach(c => {
                    let name, actual, projections, inputsCell;
                    if (!branchSelected) {
                        // Consolidated row (read-only projection column)
                        name = c.name;
                        actual = parseFloat(c.actual) || 0;
                        projections = c.projections || Array(period).fill(0);
                        inputsCell = '-';
                    } else {
                        name = (c.gl_account && c.gl_account.parent_id) ? `&gt; ${c.gl_account.account_name}` : (c.gl_account?.account_name || c.account_name || 'N/A');
                        actual = parseFloat(c.actual_amount) || 0;
                        const proj = parseFloat(c.projection_percentage) || 0;
                        inputsCell = `<input type="number" class="form-control form-control-sm text-end projection-input" value="${proj}" min="0" max="100" step="0.01" data-id="${c.id}" style="width: 80px;">`;
                        projections = [];
                        let current = actual;
                        for (let i = 0; i < period; i++) {
                            current = current * (1 + (proj / 100));
                            projections.push(current);
                        }
                    }

                    // Update totals
                    for (let i = 0; i < period; i++) receiptsTotals[i] += projections[i] || 0;
                    const sumProj = projections.reduce((a,b) => a+b, 0);
                    receiptsGrand += sumProj;

                    const cells = [
                        td(name),
                        td(formatNumber(actual), 'text-end'),
                        td(inputsCell, 'text-end')
                    ];
                    projections.forEach(v => cells.push(td(formatNumber(v), 'text-end')));
                    cells.push(td(formatNumber(sumProj), 'text-end'));
                    tbody.insertAdjacentHTML('beforeend', makeRow(cells));
                });

                // TOTAL CASH AVAILABLE
                const tcaCells = [ td('TOTAL CASH AVAILABLE'), td(formatNumber(beginningBalance), 'text-end'), td('-', 'text-end') ];
                for (let i = 0; i < period; i++) tcaCells.push(td(formatNumber(beginningBalance + receiptsTotals[i]), 'text-end'));
                tcaCells.push(td(formatNumber(beginningBalance * period + receiptsGrand), 'text-end'));
                tbody.insertAdjacentHTML('beforeend', makeRow(tcaCells));

                // Section: LESS: DISBURSEMENTS
                tbody.insertAdjacentHTML('beforeend', makeRow([
                    td('LESS: DISBURSEMENTS'),
                    td('-', 'text-end'),
                    td('-', 'text-end'),
                    ...Array.from({length: period}, () => td('-', 'text-end')),
                    td('-', 'text-end')
                ]));

                const disbTotals = Array.from({length: period}, () => 0);
                let disbGrand = 0;

                disbursements.forEach(c => {
                    let name, actual, projections, inputsCell;
                    if (!branchSelected) {
                        name = c.name;
                        actual = parseFloat(c.actual) || 0;
                        projections = c.projections || Array(period).fill(0);
                        inputsCell = '-';
                    } else {
                        name = (c.gl_account && c.gl_account.parent_id) ? `&gt; ${c.gl_account.account_name}` : (c.gl_account?.account_name || c.account_name || 'N/A');
                        actual = parseFloat(c.actual_amount) || 0;
                        const proj = parseFloat(c.projection_percentage) || 0;
                        inputsCell = `<input type=\"number\" class=\"form-control form-control-sm text-end projection-input\" value=\"${proj}\" min=\"0\" max=\"100\" step=\"0.01\" data-id=\"${c.id}\" style=\"width: 80px;\">`;
                        projections = [];
                        let current = actual;
                        for (let i = 0; i < period; i++) {
                            current = current * (1 + (proj / 100));
                            projections.push(current);
                        }
                    }

                    for (let i = 0; i < period; i++) disbTotals[i] += projections[i] || 0;
                    const sumProj = projections.reduce((a,b) => a+b, 0);
                    disbGrand += sumProj;

                    const cells = [
                        td(name),
                        td(formatNumber(actual), 'text-end'),
                        td(inputsCell, 'text-end')
                    ];
                    projections.forEach(v => cells.push(td(formatNumber(v), 'text-end')));
                    cells.push(td(formatNumber(sumProj), 'text-end'));
                    tbody.insertAdjacentHTML('beforeend', makeRow(cells));
                });

                // TOTAL DISBURSEMENTS
                const totalDisbActual = (!branchSelected)
                    ? disbursements.reduce((s, c) => s + (parseFloat(c.actual) || 0), 0)
                    : disbursements.reduce((s, c) => s + (parseFloat(c.actual_amount) || 0), 0);
                const tdCells = [ td('TOTAL DISBURSEMENTS'), td(formatNumber(totalDisbActual), 'text-end'), td('-', 'text-end') ];
                for (let i = 0; i < period; i++) tdCells.push(td(formatNumber(disbTotals[i]), 'text-end'));
                tdCells.push(td(formatNumber(disbGrand), 'text-end'));
                tbody.insertAdjacentHTML('beforeend', makeRow(tdCells));

                // CASH ENDING BALANCE (mirror export formula)
                const cebCells = [ td('CASH ENDING BALANCE'), td(formatNumber(beginningBalance), 'text-end'), td('-', 'text-end') ];
                for (let i = 0; i < period; i++) {
                    const value = beginningBalance + (beginningBalance + receiptsTotals[i]) - disbTotals[i];
                    cebCells.push(td(formatNumber(value), 'text-end'));
                }
                const cebTotal = (beginningBalance * period) + ((beginningBalance * period) + receiptsGrand) - disbGrand;
                cebCells.push(td(formatNumber(cebTotal), 'text-end'));
                tbody.insertAdjacentHTML('beforeend', makeRow(cebCells));

                // Attach listeners for dynamic elements
                attachEventListeners();
            }

            function getPeriodLabels() {
                const monthInput = document.getElementById('table_start_period');
                const exportPeriod = document.getElementById('table_period');
                const [yearStr, monthStr] = (monthInput.value || '').split('-');
                const period = parseInt(exportPeriod.value || '3', 10);
                const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                const startIndex = Math.max(0, (parseInt(monthStr, 10) || 1) - 1);
                const labels = [];
                for (let i = 0; i < period; i++) labels.push(months[(startIndex + i) % 12]);
                const selectedLabel = months[startIndex];
                return { labels, selectedLabel };
            }

            function buildTableHeader() {
                const thead = document.getElementById('cf-thead');
                const exportPeriod = parseInt(document.getElementById('table_period').value || '3', 10);
                const { labels, selectedLabel } = getPeriodLabels();

                const topRow = document.createElement('tr');
                topRow.innerHTML = `
                    <th rowspan="2">PARTICULARS</th>
                    <th rowspan="2" class="text-end">ACTUAL</th>
                    <th rowspan="2" class="text-end">PROJECTION %</th>
                    <th colspan="${exportPeriod}" class="text-center">CASH PROJECTION/PLAN</th>
                    <th rowspan="2" class="text-end">TOTAL</th>
                `;

                const secondRow = document.createElement('tr');
                secondRow.innerHTML = labels.map(l => `<th class="text-end">${l}</th>`).join('');

                thead.innerHTML = '';
                thead.appendChild(topRow);
                thead.appendChild(secondRow);
            }

            function attachEventListeners() {
                // Edit buttons
                document.querySelectorAll('.btn-edit').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        currentEditId = id;
                        document.getElementById('cashflowModalLabel').textContent = 'Edit Cash Flow Entry';
                        loadCashflowForEdit(id);
                        cashflowModal.show();
                    });
                });

                // View buttons
                document.querySelectorAll('.btn-view').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        loadCashflowForView(id);
                        viewModal.show();
                    });
                });

                // Delete buttons
                document.querySelectorAll('.btn-delete').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        currentEditId = id;
                        deleteModal.show();
                    });
                });

                // Projection input changes
                document.querySelectorAll('.projection-input').forEach(input => {
                    input.addEventListener('change', function() {
                        const id = this.getAttribute('data-id');
                        const newValue = parseFloat(this.value) || 0;
                        updateProjectionPercentage(id, newValue);
                    });
                });
            }

            function updateProjectionPercentage(id, newValue) {
                fetch(`{{ url('head/cashflows') }}/${id}/projection`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        projection_percentage: newValue
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        showAlert(data.message || 'Failed to update projection percentage', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error updating projection percentage:', error);
                    showAlert('Error updating projection percentage', 'error');
                });
            }

            function loadCashflowForEdit(id) {
                fetch(`{{ route('head.cashflows.index') }}/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const cashflow = data.data;
                            document.getElementById('account_code').value = cashflow.account_code || '';
                            document.getElementById('account_name').value = cashflow.account_name || '';
                            document.getElementById('account_type').value = cashflow.account_type || '';
                            document.getElementById('cashflow_category').value = cashflow.cashflow_category || '';
                            document.getElementById('branch_id').value = cashflow.branch_id || '';
                            document.getElementById('year').value = cashflow.year || '';
                            document.getElementById('month').value = cashflow.month || '';
                            document.getElementById('actual_amount').value = cashflow.actual_amount || '';
                            document.getElementById('projection_percentage').value = cashflow.projection_percentage || '';
                            calculateAmounts();
                        }
                    })
                    .catch(error => {
                        console.error('Error loading cashflow:', error);
                        showAlert('Error loading cashflow data', 'error');
                    });
            }

            function loadCashflowForView(id) {
                fetch(`{{ route('head.cashflows.index') }}/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const cashflow = data.data;
                            document.getElementById('view_account_code').textContent = cashflow.account_code || 'N/A';
                            document.getElementById('view_account_name').textContent = cashflow.account_name || 'N/A';
                            document.getElementById('view_account_type').textContent = cashflow.account_type || 'N/A';
                            document.getElementById('view_cashflow_category').textContent = cashflow.cashflow_category || 'N/A';
                            document.getElementById('view_branch').textContent = cashflow.branch?.name || 'N/A';
                            document.getElementById('view_period').textContent = `${cashflow.month} ${cashflow.year}`;
                            document.getElementById('view_actual_amount').textContent = `₱ ${formatNumber(cashflow.actual_amount)}`;
                            document.getElementById('view_projection_percentage').textContent = `${formatNumber(cashflow.projection_percentage)}%`;
                            document.getElementById('view_projected_amount').textContent = `₱ ${formatNumber(cashflow.projected_amount)}`;
                            document.getElementById('view_total').textContent = `₱ ${formatNumber(cashflow.total)}`;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading cashflow:', error);
                        showAlert('Error loading cashflow data', 'error');
                    });
            }

            function createCashflow() {
                const formData = new FormData(document.getElementById('cashflowForm'));

                fetch('{{ route('head.cashflows.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        cashflowModal.hide();
                        loadCashflows();
                    } else {
                        showAlert(data.message || 'Failed to create cashflow', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error creating cashflow:', error);
                    showAlert('Error creating cashflow', 'error');
                });
            }

            function updateCashflow() {
                const formData = new FormData(document.getElementById('cashflowForm'));
                formData.append('_method', 'PUT');

                fetch(`{{ route('head.cashflows.index') }}/${currentEditId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        cashflowModal.hide();
                        loadCashflows();
                    } else {
                        showAlert(data.message || 'Failed to update cashflow', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error updating cashflow:', error);
                    showAlert('Error updating cashflow', 'error');
                });
            }

            function deleteCashflow() {
                fetch(`{{ route('head.cashflows.index') }}/${currentEditId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        deleteModal.hide();
                        loadCashflows();
                    } else {
                        showAlert(data.message || 'Failed to delete cashflow', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error deleting cashflow:', error);
                    showAlert('Error deleting cashflow', 'error');
                });
            }

            function exportCashflowsFromModal() {
                const monthStr = (document.getElementById('export_start_period').value || '{{ $currentYear }}-{{ str_pad(array_search($currentMonth, ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']) + 1, 2, '0', STR_PAD_LEFT) }}');
                const [year, month] = monthStr.split('-');
                const branchId = (document.getElementById('export_branch').value || '');
                const periodValue = (document.getElementById('export_period').value || '3');
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June','July', 'August', 'September', 'October', 'November', 'December'];

                const params = new URLSearchParams({
                    year: year,
                    month: monthNames[parseInt(month) - 1],
                    period: periodValue
                });
                if (branchId) {
                    params.set('branch_id', branchId);
                }

                const downloadUrl = `{{ route('head.cashflows.export') }}?${params}`;

                return fetch(downloadUrl)
                    .then(response => {
                        if (response.ok) {
                            return response.blob();
                        }
                        throw new Error('Export failed');
                    })
                    .then(blob => {
                        // Create blob URL and download
                        const url = window.URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;

                        const pVal = parseInt(periodValue);
                        const periodText = pVal <= 12 ? `${pVal}months` : `${pVal}years`;
                        link.download = `cashflow_${monthNames[parseInt(month) - 1]}_${year}_${periodText}.xlsx`;

                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        // Clean up blob URL
                        window.URL.revokeObjectURL(url);

                        // Show success message
                        return Swal.fire({
                            title: 'Export Successful!',
                            text: 'Your cash flow report has been downloaded.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    })
                    .catch(error => {
                        console.error('Export error:', error);
                        return Swal.fire({
                            title: 'Export Failed',
                            text: 'There was an error generating the export. Please try again.',
                            icon: 'error'
                        });
                    });
            }

            function formatNumber(number) {
                return parseFloat(number || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function showAlert(message, type = 'info') {
                Swal.fire({
                    title: type.charAt(0).toUpperCase() + type.slice(1),
                    text: message,
                    icon: type,
                    timer: type === 'success' ? 2000 : undefined,
                    showConfirmButton: type !== 'success'
                });
            }
        });
    </script>
</body>

</html>
