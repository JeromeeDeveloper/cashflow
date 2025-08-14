<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cash Flow - {{ $branch->name }}</title>

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
                    <h3>Cash Flow - {{ $branch->name }}</h3>
                    <p class="text-subtitle text-muted">View cash flow data for your branch</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill bg-primary fs-6 px-3 py-2">
                        <i class="bi bi-building me-2"></i>{{ $branch->name }}
                    </span>
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
                                    <button id="btnRefresh" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                                    </button>
                                    <button id="btnExport" class="btn btn-success">
                                        <i class="bi bi-download me-2"></i>Export
                                    </button>
                                    <span class="badge rounded-pill bg-light text-dark border d-flex align-items-center px-3" data-bs-toggle="tooltip" data-bs-placement="top" title="This view shows all cashflow data for your branch.">
                                        <i class="bi bi-eye-fill text-info me-2"></i>
                                        Read-Only View
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
                                                                                                    <td class="text-end">{{ $cashflow->actual_amount ? number_format($cashflow->actual_amount, 2) : '0.00' }}</td>
                                                <td class="text-end">{{ $cashflow->projection_percentage ? number_format($cashflow->projection_percentage, 2) : '0.00' }}%</td>
                                                <td class="text-end">{{ $cashflow->projected_amount ? number_format($cashflow->projected_amount, 2) : '0.00' }}</td>
                                                <td class="text-end">{{ $cashflow->total ? number_format($cashflow->total, 2) : '0.00' }}</td>
                                                    <td class="text-end">
                                                        <button class="btn btn-sm btn-outline-primary btn-view" title="View Details" data-id="{{ $cashflow->id }}">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted py-4">
                                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                                        No cash flow entries found for your branch
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light">
                                                <th colspan="7" class="text-end">Total Operating</th>
                                                <th class="text-end" id="totalOperating">0.00</th>
                                                <th></th>
                                            </tr>
                                            <tr class="table-light">
                                                <th colspan="7" class="text-end">Total Investing</th>
                                                <th class="text-end" id="totalInvesting">0.00</th>
                                                <th></th>
                                            </tr>
                                            <tr class="table-light">
                                                <th colspan="7" class="text-end">Total Financing</th>
                                                <th class="text-end" id="totalFinancing">0.00</th>
                                                <th></th>
                                            </tr>
                                            <tr class="table-secondary">
                                                <th colspan="7" class="text-end">Net Cash Flow</th>
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

            // Modal instance
            const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));

            // Load initial data and calculate totals
            updateSummary();

            // View buttons
            document.querySelectorAll('.btn-view').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    loadCashflowForView(id);
                    viewModal.show();
                });
            });

            // Month filter
            document.getElementById('reporting_period').addEventListener('change', function() {
                loadCashflows();
            });

            // Refresh button
            document.getElementById('btnRefresh').addEventListener('click', function() {
                this.disabled = true;
                this.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Refreshing...';

                loadCashflows();
                updateSummary();

                setTimeout(() => {
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Refresh';
                }, 1000);
            });

            // Export button
            document.getElementById('btnExport').addEventListener('click', function() {
                exportCashflows();
            });

            function loadCashflows() {
                const monthInput = document.getElementById('reporting_period');

                const [year, month] = monthInput.value.split('-');
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                                  'July', 'August', 'September', 'October', 'November', 'December'];

                const params = new URLSearchParams({
                    year: year,
                    month: monthNames[parseInt(month) - 1]
                });

                fetch(`{{ route('branch.cashflows.index') }}?${params}`)
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
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                No cash flow entries found for your branch
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
                        <td class="text-end">${formatNumber(cashflow.actual_amount)}</td>
                        <td class="text-end">${formatNumber(cashflow.projection_percentage)}%</td>
                        <td class="text-end">${formatNumber(cashflow.projected_amount)}</td>
                        <td class="text-end">${formatNumber(cashflow.total)}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary btn-view" title="View Details" data-id="${cashflow.id}">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                // Re-attach event listeners
                attachEventListeners();
            }

            function attachEventListeners() {
                // View buttons
                document.querySelectorAll('.btn-view').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        loadCashflowForView(id);
                        viewModal.show();
                    });
                });
            }

            function loadCashflowForView(id) {
                fetch(`{{ route('branch.cashflows.index') }}/${id}`)
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

            function updateSummary() {
                const monthInput = document.getElementById('reporting_period');

                const [year, month] = monthInput.value.split('-');
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                                  'July', 'August', 'September', 'October', 'November', 'December'];

                const params = new URLSearchParams({
                    year: year,
                    month: monthNames[parseInt(month) - 1]
                });

                fetch(`{{ route('branch.cashflows.summary') }}?${params}`)
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

                const [year, month] = monthInput.value.split('-');
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                                  'July', 'August', 'September', 'October', 'November', 'December'];

                const params = new URLSearchParams({
                    year: year,
                    month: monthNames[parseInt(month) - 1]
                });

                fetch(`{{ route('branch.cashflows.export') }}?${params}`)
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
                // Simple alert for now
                alert(message);
            }
        });
    </script>
</body>

</html>

