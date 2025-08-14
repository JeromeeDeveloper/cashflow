<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cash Flow - {{ $branch->name ?? 'Branch' }}</title>

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
                    <h3>Cash Flow - {{ $branch->name ?? 'Branch' }}</h3>
                    <p class="text-subtitle text-muted">View cash flow data for your branch</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill bg-primary fs-6 px-3 py-2">
                        <i class="bi bi-building me-2"></i>{{ $branch->name ?? 'Branch' }}
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
                                                <th class="text-end">Actual Amount</th>
                                                <th class="text-end">Total</th>
                                                <th>Period</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($cashflows ?? [] as $cashflow)
                                                <tr data-id="{{ $cashflow->id }}">
                                                    <td>{{ $cashflow->glAccount->account_code ?? 'N/A' }}</td>
                                                    <td>{{ $cashflow->glAccount->account_name ?? $cashflow->account_name ?? 'N/A' }}</td>
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
                                                                <span class="badge bg-warning">{{ $cashflow->account_type }}</span>
                                                                @break
                                                            @default
                                                                <span class="badge bg-secondary">{{ $cashflow->account_type ?? 'N/A' }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td>
                                                        @switch($cashflow->category)
                                                            @case('Receipt')
                                                                <span class="badge bg-success">{{ $cashflow->category }}</span>
                                                                @break
                                                            @case('Disbursement')
                                                                <span class="badge bg-danger">{{ $cashflow->category }}</span>
                                                                @break
                                                            @default
                                                                <span class="badge bg-secondary">{{ $cashflow->category ?? 'N/A' }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="fw-medium text-primary">
                                                            {{ $cashflow->actual_amount ? number_format($cashflow->actual_amount, 2) : '0.00' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="text-muted">
                                                            {{ $cashflow->projection_percentage ? number_format($cashflow->projection_percentage, 2) . '%' : '0.00%' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="fw-medium text-success">
                                                            {{ $cashflow->projected_amount ? number_format($cashflow->projected_amount, 2) : '0.00' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="fw-bold text-dark">
                                                            {{ $cashflow->total ? number_format($cashflow->total, 2) : '0.00' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-primary btn-view" data-id="{{ $cashflow->id }}" title="View Details">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-warning btn-edit" data-id="{{ $cashflow->id }}" title="Edit Entry">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $cashflow->id }}" title="Delete Entry">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted py-4">
                                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                                        No cash flow data found
                                                        <br>
                                                        <small>Upload cash flow files to see data here</small>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- View Cash Flow Details Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">Cash Flow Entry Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="cashflowDetails">
                        <!-- Cash flow details will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Cash Flow Entry Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Cash Flow Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_account_code" class="form-label">Account Code</label>
                                    <input type="text" class="form-control" id="edit_account_code" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_account_name" class="form-label">Account Name</label>
                                    <input type="text" class="form-control" id="edit_account_name" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_actual_amount" class="form-label">Actual Amount <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_actual_amount" name="actual_amount" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_total" class="form-label">Total</label>
                                    <input type="number" class="form-control" id="edit_total" name="total" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Update Entry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/simple-datatables/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>

    <script>
        // Initialize DataTable
        const table = new simpleDatatables.DataTable("#table-cashflow", {
            searchable: true,
            fixedHeight: true,
            perPage: 25
        });

        // Load cash flows on page load - DISABLED to prevent flash
        // document.addEventListener('DOMContentLoaded', function() {
        //     loadCashflows();
        // });

        // Refresh button - simple page reload like head cashflow
        document.getElementById('btnRefresh').addEventListener('click', function() {
            location.reload();
        });

        // Export button
        document.getElementById('btnExport').addEventListener('click', function() {
            exportCashflows();
        });

        // Period change - simple page reload like head cashflow
        document.getElementById('reporting_period').addEventListener('change', function() {
            // For now, just update the display without reloading
            // This prevents the flash issue
        });

        // Load cash flows
        function loadCashflows() {
            const period = document.getElementById('reporting_period').value;
            const [year, month] = period.split('-');

            // Show loading state
            const tbody = document.querySelector('#table-cashflow tbody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <br>
                        <small class="mt-2 d-block">Loading cash flow data...</small>
                    </td>
                </tr>
            `;

            fetch(`/branch/cashflows?year=${year}&month=${month}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayCashflows(data.cashflows);
                    } else {
                        throw new Error(data.message || 'Failed to load data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-exclamation-triangle fs-1 text-warning d-block mb-3"></i>
                                Failed to load cash flow data
                                <br>
                                <small>${error.message}</small>
                            </td>
                        </tr>
                    `;
                });
        }

                // Display cash flows
        function displayCashflows(cashflows) {
            const tbody = document.querySelector('#table-cashflow tbody');

            if (!cashflows || cashflows.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            No cash flow data found for the selected period
                            <br>
                            <small>Try selecting a different period or upload cash flow files</small>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = cashflows.map(cashflow => `
                <tr data-id="${cashflow.id}">
                    <td>${cashflow.gl_account?.account_code || 'N/A'}</td>
                    <td>${cashflow.gl_account?.account_name || 'N/A'}</td>
                    <td>
                        ${getAccountTypeBadge(cashflow.gl_account?.account_type || 'single')}
                    </td>
                    <td class="text-end">
                        <span class="fw-medium text-primary">
                            ${cashflow.actual_amount ? parseFloat(cashflow.actual_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'}
                        </span>
                    </td>
                    <td class="text-end">
                        <span class="fw-bold text-dark">
                            ${cashflow.total ? parseFloat(cashflow.total).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'}
                        </span>
                    </td>
                    <td>${cashflow.period || 'N/A'}</td>
                    <td class="text-end">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-view" data-id="${cashflow.id}" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning btn-edit" data-id="${cashflow.id}" title="Edit Entry">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="${cashflow.id}" title="Delete Entry">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // Get account type badge
        function getAccountTypeBadge(accountType) {
            const badges = {
                'parent': '<span class="badge bg-primary">Parent</span>',
                'child': '<span class="badge bg-success">Child</span>',
                'single': '<span class="badge bg-info">Single</span>'
            };
            return badges[accountType] || '<span class="badge bg-secondary">' + (accountType || 'N/A') + '</span>';
        }



        // View cash flow details
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-view')) {
                const cashflowId = e.target.closest('.btn-view').getAttribute('data-id');
                loadCashflowDetails(cashflowId);
            }
        });

        function loadCashflowDetails(cashflowId) {
            fetch(`/branch/cashflows/${cashflowId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cashflow = data.cashflow;
                        document.getElementById('cashflowDetails').innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Account Code:</strong> ${cashflow.gl_account?.account_code || 'N/A'}</p>
                                    <p><strong>Account Name:</strong> ${cashflow.gl_account?.account_name || 'N/A'}</p>
                                    <p><strong>Account Type:</strong> ${getAccountTypeBadge(cashflow.gl_account?.account_type || 'single')}</p>
                                    <p><strong>Period:</strong> ${cashflow.period || 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Actual Amount:</strong> <span class="fw-medium text-primary">${cashflow.actual_amount ? parseFloat(cashflow.actual_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'}</span></p>
                                    <p><strong>Total:</strong> <span class="fw-bold text-dark">${cashflow.total ? parseFloat(cashflow.total).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'}</span></p>
                                    <p><strong>Year:</strong> ${cashflow.year || 'N/A'}</p>
                                    <p><strong>Month:</strong> ${cashflow.month || 'N/A'}</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Created:</strong> ${cashflow.created_at ? new Date(cashflow.created_at).toLocaleDateString() : 'N/A'}</p>
                                    <p><strong>Updated:</strong> ${cashflow.updated_at ? new Date(cashflow.updated_at).toLocaleDateString() : 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>File Source:</strong> ${cashflow.cashflow_file?.original_name || 'N/A'}</p>
                                    <p><strong>Branch:</strong> ${cashflow.branch?.name || 'N/A'}</p>
                                </div>
                            </div>
                        `;

                        const modal = new bootstrap.Modal(document.getElementById('viewModal'));
                        modal.show();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load cash flow details');
                });
        }

        // Edit cash flow entry
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-edit')) {
                const cashflowId = e.target.closest('.btn-edit').getAttribute('data-id');
                loadCashflowForEdit(cashflowId);
            }
        });

        function loadCashflowForEdit(cashflowId) {
            fetch(`/branch/cashflows/${cashflowId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cashflow = data.cashflow;

                        // Populate form fields
                        document.getElementById('edit_account_code').value = cashflow.gl_account?.account_code || '';
                        document.getElementById('edit_account_name').value = cashflow.gl_account?.account_name || '';
                        document.getElementById('edit_actual_amount').value = cashflow.actual_amount || '';
                        document.getElementById('edit_total').value = cashflow.total || '';

                        // Set form action
                        document.getElementById('editForm').setAttribute('data-id', cashflowId);

                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('editModal'));
                        modal.show();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load cash flow data for editing');
                });
        }

        // Update cash flow entry
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const cashflowId = this.getAttribute('data-id');
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Updating...';

            fetch(`/branch/cashflows/${cashflowId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(Object.fromEntries(formData))
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                    modal.hide();

                    // Show success message
                    alert('Cash flow entry updated successfully!');

                    // Reload data
                    loadCashflows();
                } else {
                    throw new Error(data.message || 'Update failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update cash flow entry: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });

        // Delete cash flow entry
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete')) {
                const cashflowId = e.target.closest('.btn-delete').getAttribute('data-id');
                deleteCashflow(cashflowId);
            }
        });

        function deleteCashflow(cashflowId) {
            if (confirm('Are you sure you want to delete this cash flow entry? This action cannot be undone.')) {
                fetch(`/branch/cashflows/${cashflowId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Cash flow entry deleted successfully!');
                        loadCashflows();
                    } else {
                        throw new Error(data.message || 'Delete failed');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete cash flow entry: ' + error.message);
                });
            }
        }

        // Export cash flows
        function exportCashflows() {
            const period = document.getElementById('reporting_period').value;
            const [year, month] = period.split('-');

            // Create download link
            const link = document.createElement('a');
            link.href = `/branch/cashflows/export?year=${year}&month=${month}`;
            link.download = `cashflow_${month}_${year}.xlsx`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>

