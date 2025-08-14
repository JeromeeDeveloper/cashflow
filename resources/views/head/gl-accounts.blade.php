<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GL Accounts Management - Head Office</title>

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

    <style>
        .parent-row {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }

        .child-row {
            background-color: #ffffff;
            border-left: 4px solid #28a745;
        }

        .single-row {
            background-color: #ffffff;
            border-left: 4px solid #17a2b8;
        }



        .toggle-children {
            transition: all 0.3s ease;
        }

        .toggle-children:hover {
            transform: scale(1.1);
        }

        .table-hover tbody tr:hover {
            background-color: #e3f2fd !important;
        }

        .badge {
            font-size: 0.75rem !important;
        }

        .table-dark th {
            background-color: #343a40 !important;
            border-color: #454d55 !important;
        }

        .account-structure-icon {
            font-size: 1.2rem;
        }
    </style>
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
                    <h3>GL Accounts Management</h3>
                    <p class="text-subtitle text-muted">Manage account codes and account names for cash flow mapping</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button id="btnAdd" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Add New Account
                    </button>
                </div>
            </div>

            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <h4 class="mb-0">Chart of Accounts</h4>
                                <div class="d-flex align-items-center flex-wrap gap-2 justify-content-end">
                                    <div class="input-group" style="max-width: 200px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                        <input type="text" id="searchInput" class="form-control" placeholder="Search accounts...">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Account Type Filter -->
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Filter by Type:</label>
                                        <select id="typeFilter" class="form-select">
                                            <option value="">All Types</option>
                                            <option value="parent">Parent Accounts</option>
                                            <option value="child">Child Accounts</option>
                                            <option value="single">Single Accounts</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Show Structure:</label>
                                        <select id="structureFilter" class="form-select">
                                            <option value="all">All Accounts</option>
                                            <option value="hierarchical">Hierarchical View</option>
                                            <option value="flat">Flat List</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Quick Stats:</label>
                                        <div class="d-flex gap-3">
                                            <span class="badge bg-primary fs-6">{{ $glAccounts->where('account_type', 'parent')->count() }} Parent</span>
                                            <span class="badge bg-success fs-6">{{ $glAccounts->where('account_type', 'child')->count() }} Children</span>
                                            <span class="badge bg-info fs-6">{{ $glAccounts->where('account_type', 'single')->count() }} Single</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover" id="table-accounts">
                                        <thead class="table-dark">
                                            <tr>
                                                <th style="width: 50px;"></th>
                                                <th style="width: 120px;">Account Code</th>
                                                <th>Account Name & Structure</th>
                                                <th style="width: 100px;">Type</th>
                                                <th style="width: 80px;">Level</th>
                                                <th style="width: 120px;">Cash Flow Entries</th>
                                                <th style="width: 100px;">Created Date</th>
                                                <th style="width: 150px;" class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($glAccounts->where('parent_id', null) as $account)
                                                <!-- Parent Account Row -->
                                                <tr class="parent-row" data-id="{{ $account->id }}" data-type="{{ $account->account_type }}">
                                                    <td>
                                                        @if($account->children->count() > 0)
                                                            <button class="btn btn-sm btn-outline-primary toggle-children" data-parent-id="{{ $account->id }}" title="Toggle Children">
                                                                <i class="bi bi-chevron-down"></i>
                                                            </button>
                                                        @else
                                                            <span class="text-muted"><i class="bi bi-dash"></i></span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="fw-bold text-primary">{{ $account->account_code }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-folder-fill text-warning me-2"></i>
                                                            <strong class="text-dark">{{ $account->account_name }}</strong>
                                                            @if($account->children->count() > 0)
                                                                <span class="badge bg-light text-dark ms-2">{{ $account->children->count() }} children</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary fs-6">{{ $account->account_type }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-warning text-dark">Level {{ $account->level }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info fs-6">{{ $account->cashflows_count ?? 0 }} entries</span>
                                                    </td>
                                                    <td>{{ $account->created_at ? $account->created_at->format('M d, Y') : 'N/A' }}</td>
                                                    <td class="text-end">
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-primary btn-view" data-id="{{ $account->id }}" title="View Details">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-warning btn-edit" data-id="{{ $account->id }}" title="Edit Account">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $account->id }}" title="Delete Account">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Child Account Rows (Hidden by default) -->
                                                @foreach($account->children as $childAccount)
                                                    <tr class="child-row" data-parent-id="{{ $account->id }}" data-id="{{ $childAccount->id }}" data-type="child" style="display: none;">
                                                        <td>
                                                            <span class="text-muted ms-3"><i class="bi bi-arrow-right"></i></span>
                                                        </td>
                                                        <td>
                                                            <span class="text-muted ms-3">{{ $childAccount->account_code }}</span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <span class="text-muted ms-4">└─</span>
                                                                <i class="bi bi-file-earmark-text text-success me-2"></i>
                                                                <span class="text-muted">{{ $childAccount->account_name }}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success fs-6">Child</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info text-dark">Level {{ $childAccount->level }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info fs-6">{{ $childAccount->cashflows_count ?? 0 }} entries</span>
                                                        </td>
                                                        <td>{{ $childAccount->created_at ? $childAccount->created_at->format('M d, Y') : 'N/A' }}</td>
                                                        <td class="text-end">
                                                            <div class="btn-group" role="group">
                                                                <button type="button" class="btn btn-sm btn-outline-primary btn-view" data-id="{{ $childAccount->id }}" title="View Details">
                                                                    <i class="bi bi-eye"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-outline-warning btn-edit" data-id="{{ $childAccount->id }}" title="Edit Account">
                                                                    <i class="bi bi-pencil"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $childAccount->id }}" title="Delete Account">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach



                                            @if($glAccounts->count() === 0)
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted py-4">
                                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                                        No GL accounts found
                                                        <br>
                                                        <small>GL accounts will be created automatically when you import cash flow files</small>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Add/Edit Account Modal -->
            <div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="accountModalLabel">Add New Account</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="accountForm">
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="account_code" class="form-label">Account Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="account_code" name="account_code" required placeholder="e.g., 1000, 2000, 3000">
                                        <small class="text-muted">Unique identifier for the account</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="account_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="account_name" name="account_name" required placeholder="e.g., Cash, Accounts Receivable">
                                        <small class="text-muted">Descriptive name for the account</small>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Save Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- View Account Modal -->
            <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewModalLabel">Account Details</h5>
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
                                    <label class="form-label fw-bold">Cash Flow Entries:</label>
                                    <p id="view_cashflows_count"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Created Date:</label>
                                    <p id="view_created_date"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Last Updated:</label>
                                    <p id="view_updated_date"></p>
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
    <script src="{{ asset('assets/vendors/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // CSRF token for Laravel
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            // Initialize DataTable
            const dataTable = new simpleDatatables.DataTable('#table-accounts', {
                searchable: false,
                fixedHeight: true,
                perPage: 10,
                perPageSelect: [10, 25, 50, 100],
                labels: {
                    placeholder: "Search accounts...",
                    perPage: "accounts per page",
                    noRows: "No accounts found",
                    info: "Showing {start} to {end} of {rows} accounts",
                }
            });

            // Modal instances
            const accountModal = new bootstrap.Modal(document.getElementById('accountModal'));
            const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));

            // Current account ID for operations
            let currentAccountId = null;
            let isEditMode = false;

            // Add new account button
            document.getElementById('btnAdd').addEventListener('click', function() {
                isEditMode = false;
                document.getElementById('accountModalLabel').textContent = 'Add New Account';
                document.getElementById('accountForm').reset();
                currentAccountId = null;
                accountModal.show();
            });

            // Account form submission
            document.getElementById('accountForm').addEventListener('submit', function(e) {
                e.preventDefault();
                saveAccount();
            });

            // View buttons
            document.querySelectorAll('.btn-view').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    loadAccountForView(id);
                    viewModal.show();
                });
            });

            // Edit buttons
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    loadAccountForEdit(id);
                    accountModal.show();
                });
            });

            // Delete buttons
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    currentAccountId = id;
                    deleteAccount();
                });
            });

            // Filter change events
            document.getElementById('searchInput').addEventListener('input', function() {
                filterAccounts();
            });

            // Type filter change event
            document.getElementById('typeFilter').addEventListener('change', function() {
                filterAccounts();
            });

            // Structure filter change event
            document.getElementById('structureFilter').addEventListener('change', function() {
                filterAccounts();
            });

            // Toggle children rows
            document.addEventListener('click', function(e) {
                if (e.target.closest('.toggle-children')) {
                    const button = e.target.closest('.toggle-children');
                    const parentId = button.getAttribute('data-parent-id');
                    const icon = button.querySelector('i');
                    const childRows = document.querySelectorAll(`.child-row[data-parent-id="${parentId}"]`);

                    if (childRows.length > 0 && childRows[0].style.display === 'none') {
                        // Show children
                        childRows.forEach(row => row.style.display = '');
                        icon.className = 'bi bi-chevron-up';
                        button.classList.remove('btn-outline-primary');
                        button.classList.add('btn-primary');
                    } else if (childRows.length > 0) {
                        // Hide children
                        childRows.forEach(row => row.style.display = 'none');
                        icon.className = 'bi bi-chevron-down';
                        button.classList.remove('btn-primary');
                        button.classList.add('btn-outline-primary');
                    }
                }
            });

            function saveAccount() {
                const formData = new FormData(document.getElementById('accountForm'));
                const url = isEditMode ? `/head/gl-accounts/${currentAccountId}` : '/head/gl-accounts';
                const method = isEditMode ? 'PUT' : 'POST';

                // Show loading
                Swal.fire({
                    title: isEditMode ? 'Updating Account...' : 'Saving Account...',
                    text: 'Please wait while we save the account',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(url, {
                    method: method,
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: isEditMode ? 'Account Updated!' : 'Account Created!',
                            text: data.message,
                            confirmButtonText: 'OK'
                        });

                        accountModal.hide();
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'An error occurred while saving the account',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    Swal.close();
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Network error occurred. Please try again.',
                        confirmButtonText: 'OK'
                    });
                });
            }

            function loadAccountForView(id) {
                fetch(`/head/gl-accounts/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const account = data.data;
                            document.getElementById('view_account_code').textContent = account.account_code;
                            document.getElementById('view_account_name').textContent = account.account_name;
                            document.getElementById('view_cashflows_count').textContent = account.cashflows_count ?? 0;
                            document.getElementById('view_created_date').textContent = account.created_at;
                            document.getElementById('view_updated_date').textContent = account.updated_at;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading account:', error);
                        showAlert('Error loading account data', 'error');
                    });
            }

            function loadAccountForEdit(id) {
                isEditMode = true;
                currentAccountId = id;
                document.getElementById('accountModalLabel').textContent = 'Edit Account';

                fetch(`/head/gl-accounts/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const account = data.data;
                            document.getElementById('account_code').value = account.account_code;
                            document.getElementById('account_name').value = account.account_name;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading account:', error);
                        showAlert('Error loading account data', 'error');
                    });
            }

            function deleteAccount() {
                Swal.fire({
                    title: 'Delete Account?',
                    text: 'Are you sure you want to delete this account? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Deleting Account...',
                            text: 'Please wait while we delete the account',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        fetch(`/head/gl-accounts/${currentAccountId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.close();

                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Account Deleted!',
                                    text: data.message,
                                    confirmButtonText: 'OK'
                                });

                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Delete Failed',
                                    text: data.message || 'An error occurred while deleting the account',
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            console.error('Error deleting account:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Delete Failed',
                                text: 'Network error occurred. Please try again.',
                                confirmButtonText: 'OK'
                            });
                        });
                    }
                });
            }

            function filterAccounts() {
                const searchTerm = document.getElementById('searchInput').value.toLowerCase();
                const typeFilter = document.getElementById('typeFilter').value;
                const structureFilter = document.getElementById('structureFilter').value;
                const rows = document.querySelectorAll('#table-accounts tbody tr');

                rows.forEach(row => {
                    let show = true;

                    // Search filter
                    if (searchTerm) {
                        const accountName = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                        const accountCode = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                        if (!accountName.includes(searchTerm) && !accountCode.includes(searchTerm)) {
                            show = false;
                        }
                    }

                    // Type filter
                    if (typeFilter && show) {
                        const rowType = row.getAttribute('data-type');
                        if (rowType !== typeFilter) {
                            show = false;
                        }
                    }

                    // Structure filter
                    if (structureFilter === 'hierarchical' && show) {
                        // Show only parent rows and their children
                        if (row.classList.contains('child-row')) {
                            const parentRow = document.querySelector(`.parent-row[data-id="${row.getAttribute('data-parent-id')}"]`);
                            if (parentRow && parentRow.style.display !== 'none') {
                                show = true;
                            } else {
                                show = false;
                            }
                        }
                    } else if (structureFilter === 'flat' && show) {
                        // Show all rows in flat structure
                        show = true;
                    }

                    row.style.display = show ? '' : 'none';
                });
            }

            function showAlert(message, type = 'info') {
                const alertType = type === 'error' ? 'error' : type === 'success' ? 'success' : 'info';

                Swal.fire({
                    icon: alertType,
                    title: type === 'error' ? 'Error' : type === 'success' ? 'Success' : 'Info',
                    text: message,
                    confirmButtonText: 'OK'
                });
            }
        });
    </script>
</body>
</html>
