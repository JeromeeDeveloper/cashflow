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
                                    <div class="input-group" style="max-width: 280px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                                        <input type="month" id="reporting_period" class="form-control" value="{{ date('Y-m') }}">
                                    </div>
                                    <select id="branch_filter" class="form-select" style="max-width: 150px;">
                                        <option value="">All Branches</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>

                                    <button id="btnAdd" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Entry</button>
                                    <button id="btnExport" class="btn btn-success"><i class="bi bi-download me-2"></i>Export</button>
                                    <span class="badge rounded-pill bg-light text-dark border d-flex align-items-center px-3" data-bs-toggle="tooltip" data-bs-placement="top" title="Using the selected period, the system will generate a consolidated cashflow.">
                                        <i class="bi bi-lightning-charge-fill text-warning me-2"></i>
                                        Generates cashflow
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="table-cashflow">
                                        <thead>
                                            <tr>
                                                <th>Account Code</th>
                                                <th>Account Name</th>
                                                <th>Account Type</th>
                                                <th>Category</th>
                                                <th>Branch</th>
                                                <th class="text-end">Actual Amount</th>
                                                <th class="text-end">Projection %</th>
                                                <th class="text-end">Projected Amount</th>
                                                <th class="text-end">Total</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($cashflows as $cashflow)
                                                <tr data-id="{{ $cashflow->id }}">
                                                    <td>{{ $cashflow->account_code }}</td>
                                                    <td>{{ $cashflow->account_name }}</td>
                                                    <td>
                                                        @switch($cashflow->account_type)
                                                            @case('Asset')
                                                                <span class="badge bg-primary">{{ $cashflow->account_type }}</span>
                                                                @break
                                                            @case('Liability')
                                                                <span class="badge bg-danger">{{ $cashflow->account_type }}</span>
                                                                @break
                                                            @case('Equity')
                                                                <span class="badge bg-success">{{ $cashflow->account_type }}</span>
                                                                @break
                                                            @case('Income')
                                                                <span class="badge bg-info">{{ $cashflow->account_type }}</span>
                                                                @break
                                                            @case('Expense')
                                                                <span class="badge bg-warning text-dark">{{ $cashflow->account_type }}</span>
                                                                @break
                                                            @default
                                                                <span class="badge bg-secondary">{{ $cashflow->account_type }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td>
                                                        @switch($cashflow->cashflow_category)
                                                            @case('Operating')
                                                                <span class="badge bg-info text-dark">{{ $cashflow->cashflow_category }}</span>
                                                                @break
                                                            @case('Investing')
                                                                <span class="badge bg-warning text-dark">{{ $cashflow->cashflow_category }}</span>
                                                                @break
                                                            @case('Financing')
                                                                <span class="badge bg-success">{{ $cashflow->cashflow_category }}</span>
                                                                @break
                                                            @default
                                                                <span class="badge bg-secondary">{{ $cashflow->cashflow_category }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td>{{ $cashflow->branch->name ?? 'N/A' }}</td>
                                                    <td class="text-end">{{ number_format($cashflow->actual_amount, 2) }}</td>
                                                    <td class="text-end">{{ number_format($cashflow->projection_percentage, 2) }}%</td>
                                                    <td class="text-end">{{ number_format($cashflow->projected_amount, 2) }}</td>
                                                    <td class="text-end">{{ number_format($cashflow->total, 2) }}</td>
                                                    <td class="text-end">
                                                        <button class="btn btn-sm btn-outline-primary btn-view" title="View Details" data-id="{{ $cashflow->id }}"><i class="bi bi-eye"></i></button>
                                                        <button class="btn btn-sm btn-outline-secondary btn-edit" title="Edit" data-id="{{ $cashflow->id }}"><i class="bi bi-pencil"></i></button>
                                                        <button class="btn btn-sm btn-outline-danger btn-delete" title="Delete" data-id="{{ $cashflow->id }}"><i class="bi bi-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted py-4">
                                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                                        No cash flow entries found
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light">
                                                <th colspan="8" class="text-end">Total Operating</th>
                                                <th class="text-end" id="totalOperating">0.00</th>
                                                <th></th>
                                            </tr>
                                            <tr class="table-light">
                                                <th colspan="8" class="text-end">Total Investing</th>
                                                <th class="text-end" id="totalInvesting">0.00</th>
                                                <th></th>
                                            </tr>
                                            <tr class="table-light">
                                                <th colspan="8" class="text-end">Total Financing</th>
                                                <th class="text-end" id="totalFinancing">0.00</th>
                                                <th></th>
                                            </tr>
                                            <tr class="table-secondary">
                                                <th colspan="8" class="text-end">Net Cash Flow</th>
                                                <th class="text-end" id="netCashflow">0.00</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
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
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // CSRF token for Laravel
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            // Enable Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize DataTable
            const cashflowTable = document.querySelector('#table-cashflow');
            if (cashflowTable && window.simpleDatatables) {
                new simpleDatatables.DataTable(cashflowTable);
            }

            // Modal instances
            const cashflowModal = new bootstrap.Modal(document.getElementById('cashflowModal'));
            const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

            // Current editing ID
            let currentEditId = null;

            // Load initial data and calculate totals
            loadCashflows();
            updateSummary();

            // Add button
            document.getElementById('btnAdd').addEventListener('click', function() {
                currentEditId = null;
                document.getElementById('cashflowModalLabel').textContent = 'Add Cash Flow Entry';
                document.getElementById('cashflowForm').reset();
                document.getElementById('cashflow_id').value = '';
                cashflowModal.show();
            });

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

            // Branch filter
            document.getElementById('branch_filter').addEventListener('change', function() {
                loadCashflows();
            });

            // Month filter
            document.getElementById('reporting_period').addEventListener('change', function() {
                loadCashflows();
            });


            // Export button
            document.getElementById('btnExport').addEventListener('click', function() {
                exportCashflows();
            });

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
                const monthInput = document.getElementById('reporting_period');
                const branchFilter = document.getElementById('branch_filter');

                const [year, month] = monthInput.value.split('-');
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                                  'July', 'August', 'September', 'October', 'November', 'December'];

                const params = new URLSearchParams({
                    year: year,
                    month: monthNames[parseInt(month) - 1],
                    branch_id: branchFilter.value
                });

                fetch(`{{ route('head.cashflows.index') }}?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateTable(data.data);
                            updateSummary();
                        }
                    })
                    .catch(error => {
                        console.error('Error loading cashflows:', error);
                        showAlert('Error loading cashflows', 'error');
                    });
            }

            function updateTable(cashflows) {
                const tbody = document.querySelector('#table-cashflow tbody');
                tbody.innerHTML = '';

                if (cashflows.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                No cash flow entries found
                            </td>
                        </tr>
                    `;
                    return;
                }

                cashflows.forEach(cashflow => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', cashflow.id);
                    row.innerHTML = `
                        <td>${cashflow.account_code || 'N/A'}</td>
                        <td>${cashflow.account_name || 'N/A'}</td>
                        <td>${getAccountTypeBadge(cashflow.account_type)}</td>
                        <td>${getCategoryBadge(cashflow.cashflow_category)}</td>
                        <td>${cashflow.branch?.name || 'N/A'}</td>
                        <td class="text-end">${formatNumber(cashflow.actual_amount)}</td>
                        <td class="text-end">${formatNumber(cashflow.projection_percentage)}%</td>
                        <td class="text-end">${formatNumber(cashflow.projected_amount)}</td>
                        <td class="text-end">${formatNumber(cashflow.total)}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary btn-view" title="View Details" data-id="${cashflow.id}"><i class="bi bi-eye"></i></button>
                            <button class="btn btn-sm btn-outline-secondary btn-edit" title="Edit" data-id="${cashflow.id}"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger btn-delete" title="Delete" data-id="${cashflow.id}"><i class="bi bi-trash"></i></button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                // Re-attach event listeners
                attachEventListeners();
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

            function updateSummary() {
                const monthInput = document.getElementById('reporting_period');
                const branchFilter = document.getElementById('branch_filter');

                const [year, month] = monthInput.value.split('-');
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                                  'July', 'August', 'September', 'October', 'November', 'December'];

                const params = new URLSearchParams({
                    year: year,
                    month: monthNames[parseInt(month) - 1],
                    branch_id: branchFilter.value
                });

                fetch(`{{ route('head.cashflows.summary') }}?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const summary = data.data;
                            document.getElementById('totalOperating').textContent = formatNumber(summary.total_operating);
                            document.getElementById('totalInvesting').textContent = formatNumber(summary.total_investing);
                            document.getElementById('totalFinancing').textContent = formatNumber(summary.total_financing);
                            document.getElementById('netCashflow').textContent = formatNumber(summary.net_cashflow);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading summary:', error);
                    });
            }

            function exportCashflows() {
                const monthInput = document.getElementById('reporting_period');
                const branchFilter = document.getElementById('branch_filter');

                const [year, month] = monthInput.value.split('-');
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                                  'July', 'August', 'September', 'October', 'November', 'December'];

                const params = new URLSearchParams({
                    year: year,
                    month: monthNames[parseInt(month) - 1],
                    branch_id: branchFilter.value
                });

                fetch(`{{ route('head.cashflows.export') }}?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // In a real app, you might download a file
                            // For now, we'll show the data
                            console.log('Export data:', data.data);
                            showAlert('Export data prepared successfully', 'success');
                        }
                    })
                    .catch(error => {
                        console.error('Error exporting cashflows:', error);
                        showAlert('Error exporting cashflows', 'error');
                    });
            }

            function getAccountTypeBadge(type) {
                const badges = {
                    'Asset': '<span class="badge bg-primary">Asset</span>',
                    'Liability': '<span class="badge bg-danger">Liability</span>',
                    'Equity': '<span class="badge bg-success">Equity</span>',
                    'Income': '<span class="badge bg-info">Income</span>',
                    'Expense': '<span class="badge bg-warning text-dark">Expense</span>'
                };
                return badges[type] || `<span class="badge bg-secondary">${type}</span>`;
            }

            function getCategoryBadge(category) {
                const badges = {
                    'Operating': '<span class="badge bg-info text-dark">Operating</span>',
                    'Investing': '<span class="badge bg-warning text-dark">Investing</span>',
                    'Financing': '<span class="badge bg-success">Financing</span>'
                };
                return badges[category] || `<span class="badge bg-secondary">${category}</span>`;
            }

            function formatNumber(number) {
                return parseFloat(number || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function showAlert(message, type = 'info') {
                const toast = document.getElementById('toast');
                const toastIcon = document.getElementById('toast-icon');
                const toastTitle = document.getElementById('toast-title');
                const toastMessage = document.getElementById('toast-message');

                // Set icon and title based on type
                switch(type) {
                    case 'success':
                        toastIcon.className = 'bi bi-check-circle-fill me-2 text-success';
                        toastTitle.textContent = 'Success';
                        break;
                    case 'error':
                        toastIcon.className = 'bi bi-exclamation-triangle-fill me-2 text-danger';
                        toastTitle.textContent = 'Error';
                        break;
                    case 'warning':
                        toastIcon.className = 'bi bi-exclamation-circle-fill me-2 text-warning';
                        toastTitle.textContent = 'Warning';
                        break;
                    default:
                        toastIcon.className = 'bi bi-info-circle me-2 text-info';
                        toastTitle.textContent = 'Information';
                }

                toastMessage.textContent = message;

                // Show toast
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
            }
        });
    </script>
</body>

</html>
