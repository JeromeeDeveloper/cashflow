<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <div class="d-flex align-items-center gap-2">
                    <input type="month" id="reporting_period" class="form-control" style="min-width: 220px;" value="{{ date('Y-m') }}">
                    <select id="branch_filter" class="form-select" style="max-width: 150px;">
                        <option value="">All Branches</option>
                        <option value="main">Main Office</option>
                        <option value="branch1">Branch 1</option>
                        <option value="branch2">Branch 2</option>
                        <option value="branch3">Branch 3</option>
                    </select>
                    <button id="btnRefresh" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise me-2"></i>Refresh</button>
                    <button id="btnExport" class="btn btn-success"><i class="bi bi-download me-2"></i>Export</button>
                </div>
            </div>

            <div class="page-content">
                <section class="row">
                    <div class="col-12 col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="mb-0">Cash Flow Summary</h4>
                                <div class="d-flex gap-2">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" placeholder="Search accounts...">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="table-cashflow">
                                        <thead>
                                            <tr>
                                                <th>GL Code</th>
                                                <th>GL Name</th>
                                                <th>Category</th>
                                                <th>Branch</th>
                                                <th class="text-end">Base Amount</th>
                                                <th class="text-end">Percent %</th>
                                                <th class="text-end">Cash Flow Amount</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1001</td>
                                                <td>Cash on Hand</td>
                                                <td><span class="badge bg-primary">Operating</span></td>
                                                <td>Main Office</td>
                                                <td class="text-end">500,000.00</td>
                                                <td class="text-end">100.00</td>
                                                <td class="text-end">500,000.00</td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-primary" title="View Details"><i class="bi bi-eye"></i></button>
                                                    <button class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>1001</td>
                                                <td>Cash on Hand</td>
                                                <td><span class="badge bg-primary">Operating</span></td>
                                                <td>Branch 1</td>
                                                <td class="text-end">250,000.00</td>
                                                <td class="text-end">85.50</td>
                                                <td class="text-end">213,750.00</td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-primary" title="View Details"><i class="bi bi-eye"></i></button>
                                                    <button class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>1205</td>
                                                <td>Short-term Investments</td>
                                                <td><span class="badge bg-info text-dark">Investing</span></td>
                                                <td>Main Office</td>
                                                <td class="text-end">1,000,000.00</td>
                                                <td class="text-end">75.00</td>
                                                <td class="text-end">750,000.00</td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-primary" title="View Details"><i class="bi bi-eye"></i></button>
                                                    <button class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>2101</td>
                                                <td>Accounts Payable</td>
                                                <td><span class="badge bg-primary">Operating</span></td>
                                                <td>Branch 2</td>
                                                <td class="text-end">300,000.00</td>
                                                <td class="text-end">90.00</td>
                                                <td class="text-end">270,000.00</td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-primary" title="View Details"><i class="bi bi-eye"></i></button>
                                                    <button class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>5101</td>
                                                <td>Office Supplies Expense</td>
                                                <td><span class="badge bg-primary">Operating</span></td>
                                                <td>Branch 3</td>
                                                <td class="text-end">50,000.00</td>
                                                <td class="text-end">95.00</td>
                                                <td class="text-end">47,500.00</td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-primary" title="View Details"><i class="bi bi-eye"></i></button>
                                                    <button class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light">
                                                <th colspan="6" class="text-end">Total Operating</th>
                                                <th class="text-end" id="totalOperating">1,031,250.00</th>
                                                <th></th>
                                            </tr>
                                            <tr class="table-light">
                                                <th colspan="6" class="text-end">Total Investing</th>
                                                <th class="text-end" id="totalInvesting">750,000.00</th>
                                                <th></th>
                                            </tr>
                                            <tr class="table-light">
                                                <th colspan="6" class="text-end">Total Financing</th>
                                                <th class="text-end" id="totalFinancing">0.00</th>
                                                <th></th>
                                            </tr>
                                            <tr class="table-secondary">
                                                <th colspan="6" class="text-end">Net Cash Flow</th>
                                                <th class="text-end" id="netCashflow">1,781,250.00</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Summary by Branch</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Main Office</label>
                                    <div class="d-flex justify-content-between">
                                        <span>Operating: <span class="text-primary">1,250,000.00</span></span>
                                        <span>Investing: <span class="text-info">750,000.00</span></span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span>Total: <strong>2,000,000.00</strong></span>
                                    </div>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <label class="form-label">Branch 1</label>
                                    <div class="d-flex justify-content-between">
                                        <span>Operating: <span class="text-primary">213,750.00</span></span>
                                        <span>Investing: <span class="text-info">0.00</span></span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span>Total: <strong>213,750.00</strong></span>
                                    </div>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <label class="form-label">Branch 2</label>
                                    <div class="d-flex justify-content-between">
                                        <span>Operating: <span class="text-primary">270,000.00</span></span>
                                        <span>Investing: <span class="text-info">0.00</span></span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span>Total: <strong>270,000.00</strong></span>
                                    </div>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <label class="form-label">Branch 3</label>
                                    <div class="d-flex justify-content-between">
                                        <span>Operating: <span class="text-primary">47,500.00</span></span>
                                        <span>Investing: <span class="text-info">0.00</span></span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span>Total: <strong>47,500.00</strong></span>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Grand Total</span>
                                    <span class="fw-bold" id="grandTotal">2,531,250.00</span>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4>Quick Actions</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary" id="btnAddEntry">
                                        <i class="bi bi-plus-circle me-2"></i>Add Entry
                                    </button>
                                    <button class="btn btn-outline-info" id="btnBulkEdit">
                                        <i class="bi bi-pencil-square me-2"></i>Bulk Edit
                                    </button>
                                    <button class="btn btn-outline-warning" id="btnValidate">
                                        <i class="bi bi-check2-circle me-2"></i>Validate Data
                                    </button>
                                    <button class="btn btn-outline-success" id="btnApprove">
                                        <i class="bi bi-check-lg me-2"></i>Approve Period
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4>Notes & Comments</h4>
                            </div>
                            <div class="card-body">
                                <textarea class="form-control" rows="4" placeholder="Add notes or comments for this period..."></textarea>
                                <div class="d-flex justify-content-end mt-3">
                                    <button class="btn btn-primary" id="btnSaveNotes">
                                        <i class="bi bi-save me-2"></i>Save Notes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>


        </div>
    </div>

    <script src="{{ asset('assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/simple-datatables/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            const cashflowTable = document.querySelector('#table-cashflow');
            if (cashflowTable && window.simpleDatatables) {
                new simpleDatatables.DataTable(cashflowTable);
            }

            // Branch filter functionality
            const branchFilter = document.getElementById('branch_filter');
            branchFilter.addEventListener('change', function() {
                const selectedBranch = this.value;
                // Filter table rows based on selected branch
                const rows = document.querySelectorAll('#table-cashflow tbody tr');
                rows.forEach(row => {
                    const branchCell = row.cells[3]; // Branch column
                    if (!selectedBranch || branchCell.textContent.trim() === selectedBranch ||
                        (selectedBranch === 'main' && branchCell.textContent.trim() === 'Main Office')) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
                // Recalculate totals for filtered data
                recalculateTotals();
            });

            // Refresh button
            document.getElementById('btnRefresh').addEventListener('click', function() {
                this.disabled = true;
                this.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Refreshing...';

                setTimeout(() => {
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Refresh';
                    alert('Data refreshed successfully!');
                }, 1500);
            });

            // Export button
            document.getElementById('btnExport').addEventListener('click', function() {
                alert('Exporting cash flow data...');
                // Add export logic here
            });

            // Quick action buttons
            document.getElementById('btnAddEntry').addEventListener('click', function() {
                alert('Add Entry functionality - implement modal or redirect');
            });

            document.getElementById('btnBulkEdit').addEventListener('click', function() {
                alert('Bulk Edit functionality - implement multi-select and edit');
            });

            document.getElementById('btnValidate').addEventListener('click', function() {
                alert('Data validation in progress...');
                // Add validation logic here
            });

            document.getElementById('btnApprove').addEventListener('click', function() {
                if (confirm('Are you sure you want to approve this period? This action cannot be undone.')) {
                    alert('Period approved successfully!');
                }
            });

            document.getElementById('btnSaveNotes').addEventListener('click', function() {
                alert('Notes saved successfully!');
            });

            function recalculateTotals() {
                // This function would recalculate totals based on visible/filtered rows
                // For now, it's a placeholder
                console.log('Recalculating totals...');
            }
        });
    </script>
</body>

</html>
