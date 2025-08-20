<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Management - Admin</title>

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
                    <h3>User Management</h3>
                    <p class="text-subtitle text-muted">Manage system users, roles, and permissions</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button id="btnAdd" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Add New User
                    </button>

                </div>
            </div>

               <!-- User Statistics Cards -->
               <section class="row mt-4">
                <div class="col-12 col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-primary me-3">
                                    <i class="bi bi-people-fill text-white"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0" id="totalUsers">{{ $users->count() ?? 0 }}</h4>
                                    <small class="text-muted">Total Users</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-danger me-3">
                                    <i class="bi bi-shield-fill text-white"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0" id="adminUsers">{{ $users->where('role', 'admin')->count() ?? 0 }}</h4>
                                    <small class="text-muted">Admin Users</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-primary me-3">
                                    <i class="bi bi-building-fill text-white"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0" id="headUsers">{{ $users->where('role', 'head')->count() ?? 0 }}</h4>
                                    <small class="text-muted">Head Office</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-success me-3">
                                    <i class="bi bi-geo-alt-fill text-white"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0" id="branchUsers">{{ $users->where('role', 'branch')->count() ?? 0 }}</h4>
                                    <small class="text-muted">Branch Users</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <h4 class="mb-0">System Users</h4>
                                <div class="d-flex align-items-center flex-wrap gap-2 justify-content-end">
                                    <div class="input-group" style="max-width: 200px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                        <input type="text" id="searchInput" class="form-control" placeholder="Search users...">
                                    </div>
                                    <div class="input-group" style="max-width: 150px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-funnel"></i></span>
                                        <select id="role_filter" class="form-select">
                                            <option value="">All Roles</option>
                                            <option value="admin">Admin</option>
                                            <option value="head">Head Office</option>
                                            <option value="branch">Branch</option>
                                        </select>
                                    </div>
                                    <div class="input-group" style="max-width: 150px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                                        <select id="branch_filter" class="form-select">
                                            <option value="">All Branches</option>
                                            @foreach($branches ?? [] as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="input-group" style="max-width: 150px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-check-circle"></i></span>
                                        <select id="status_filter" class="form-select">
                                            <option value="">All Status</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="table-users">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Contact Info</th>
                                                <th>Role & Branch</th>
                                                <th>Status</th>
                                                <th>Last Login</th>
                                                <th>Created</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($users ?? [] as $user)
                                                <tr data-id="{{ $user->id }}">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-md me-3">
                                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                    <i class="bi bi-person-fill text-muted"></i>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0">{{ $user->name }}</h6>
                                                                <small class="text-muted">{{ $user->email }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <div><i class="bi bi-envelope me-1"></i>{{ $user->email }}</div>
                                                            <div><i class="bi bi-telephone me-1"></i>{{ $user->phone ?? 'N/A' }}</div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : ($user->role === 'head' ? 'bg-primary' : 'bg-success') }} mb-1">
                                                                {{ ucfirst($user->role) }}
                                                            </span>
                                                            @if($user->branch)
                                                                <div><small class="text-muted">{{ $user->branch->name }}</small></div>
                                                            @else
                                                                <div><small class="text-muted">No branch assigned</small></div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($user->status === 'active')
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-check-circle me-1"></i>Active
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary">
                                                                <i class="bi bi-x-circle me-1"></i>Inactive
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Never' }}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                                                        </small>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="btn-group" role="group">
                                                            <button class="btn btn-sm btn-outline-primary btn-view" title="View Details" data-id="{{ $user->id }}">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-warning btn-edit" title="Edit User" data-id="{{ $user->id }}">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger btn-delete" title="Delete User" data-id="{{ $user->id }}">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-4">
                                                        <i class="bi bi-people fs-1 d-block mb-3"></i>
                                                        No users found
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

            <!-- Add/Edit User Modal -->
            <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="userModalLabel">Add New User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="userForm">
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="+63 912 345 6789">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="">Select Role</option>
                                            <option value="admin">Admin</option>
                                            <option value="head">Head Office</option>
                                            <option value="branch">Branch</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6" id="branchField" style="display: none;">
                                        <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                                        <select class="form-select" id="branch_id" name="branch_id">
                                            <option value="">Select Branch</option>
                                            @foreach($branches ?? [] as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- <div class="col-md-6">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div> --}}
                                    <div class="col-md-6">
                                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <small class="text-muted">Minimum 8 characters</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- View User Modal -->
            <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewModalLabel">User Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Full Name:</label>
                                    <p id="view_name"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email Address:</label>
                                    <p id="view_email"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone Number:</label>
                                    <p id="view_phone"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Role:</label>
                                    <p id="view_role"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Branch:</label>
                                    <p id="view_branch"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Status:</label>
                                    <p id="view_status"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Created:</label>
                                    <p id="view_created"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Last Updated:</label>
                                    <p id="view_updated"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Last Login:</label>
                                    <p id="view_last_login"></p>
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
                            <p>Are you sure you want to delete this user? This action cannot be undone.</p>
                            <p class="text-danger"><strong>User:</strong> <span id="deleteUserName"></span></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmDelete">Delete User</button>
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

            // Store the original form content at the very beginning
            const userModalElement = document.getElementById('userModal');
            const originalFormContent = userModalElement.querySelector('.modal-body').innerHTML;

            // Initialize DataTable
            const usersTable = document.querySelector('#table-users');
            if (usersTable && window.simpleDatatables) {
                const dataTable = new simpleDatatables.DataTable(usersTable);
            }

            // Modal instances
            const userModal = new bootstrap.Modal(document.getElementById('userModal'));
            const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

            // Form elements
            const userForm = document.getElementById('userForm');
            const roleSelect = document.getElementById('role');
            const branchField = document.getElementById('branchField');
            const branchSelect = document.getElementById('branch_id');

            // Show/hide branch field based on role
            roleSelect.addEventListener('change', function() {
                if (this.value === 'branch') {
                    branchField.style.display = 'block';
                    branchSelect.required = true;
                } else {
                    branchField.style.display = 'none';
                    branchSelect.required = false;
                    branchSelect.value = '';
                }
            });

            // Add user button
            document.getElementById('btnAdd').addEventListener('click', function() {
                resetForm();
                document.getElementById('userModalLabel').textContent = 'Add New User';
                userModal.show();
            });

            // Form submission
            userForm.addEventListener('submit', function(e) {
                e.preventDefault();
                saveUser();
            });

            // Use event delegation for dynamic buttons (works with pagination)
            const tableBody = document.querySelector('#table-users tbody');

            // Combined event delegation for all action buttons
            tableBody.addEventListener('click', function(e) {
                // Check if the clicked element or its parent has the button class
                const button = e.target.closest('.btn-view, .btn-edit, .btn-delete');
                if (!button) return;

                const id = button.getAttribute('data-id');

                if (button.classList.contains('btn-view')) {
                    loadUserForView(id);
                    viewModal.show();
                } else if (button.classList.contains('btn-edit')) {
                    loadUserForEdit(id);
                    userModal.show();
                } else if (button.classList.contains('btn-delete')) {
                    const userName = button.closest('tr').querySelector('h6').textContent;
                    document.getElementById('deleteUserName').textContent = userName;
                    document.getElementById('confirmDelete').setAttribute('data-id', id);
                    deleteModal.show();
                }
            });

            // Alternative approach: Use document-level event delegation as fallback
            document.addEventListener('click', function(e) {
                const button = e.target.closest('.btn-view, .btn-edit, .btn-delete');
                if (!button || !tableBody.contains(button)) return;

                const id = button.getAttribute('data-id');

                if (button.classList.contains('btn-view')) {
                    loadUserForView(id);
                    viewModal.show();
                } else if (button.classList.contains('btn-edit')) {
                    loadUserForEdit(id);
                    userModal.show();
                } else if (button.classList.contains('btn-delete')) {
                    const userName = button.closest('tr').querySelector('h6').textContent;
                    document.getElementById('deleteUserName').textContent = userName;
                    document.getElementById('confirmDelete').setAttribute('data-id', id);
                    deleteModal.show();
                }
            });

            // Confirm delete
            document.getElementById('confirmDelete').addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                deleteUser(id);
            });

            // Search functionality
            document.getElementById('searchInput').addEventListener('input', function() {
                filterUsers();
            });

            // Filter functionality
            document.getElementById('role_filter').addEventListener('change', filterUsers);
            document.getElementById('branch_filter').addEventListener('change', filterUsers);
            document.getElementById('status_filter').addEventListener('change', filterUsers);

            function resetForm() {
                userForm.reset();

                // Get fresh references to form elements
                const branchFieldEl = document.getElementById('branchField');
                const branchSelectEl = document.getElementById('branch_id');

                if (branchFieldEl) branchFieldEl.style.display = 'none';
                if (branchSelectEl) branchSelectEl.required = false;

                // Reset password fields
                const passwordField = document.getElementById('password');
                const passwordConfirmField = document.getElementById('password_confirmation');

                if (passwordField) {
                    passwordField.required = true;
                    passwordField.placeholder = 'Enter password';
                }
                if (passwordConfirmField) {
                    passwordConfirmField.required = true;
                    passwordConfirmField.placeholder = 'Confirm password';
                }
            }

            function saveUser() {
                const formData = new FormData(userForm);
                const userId = userForm.getAttribute('data-user-id');

                const url = userId ? `/admin/users/${userId}` : '/admin/users';
                const method = userId ? 'PUT' : 'POST';

                // Show loading state
                const submitBtn = userForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Saving...';

                fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        userModal.hide();
                        showAlert(data.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        let errorMessage = data.message;
                        if (data.errors) {
                            errorMessage = Object.values(data.errors).flat().join('\n');
                        }
                        showAlert(errorMessage, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error saving user:', error);
                    showAlert('Error saving user. Please try again.', 'error');
                })
                .finally(() => {
                    // Reset button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            }

            function loadUserForView(id) {
                // Show loading state
                const viewModal = document.getElementById('viewModal');
                const modalBody = viewModal.querySelector('.modal-body');
                modalBody.innerHTML = '<div class="text-center py-4"><i class="bi bi-hourglass-split fs-1"></i><p class="mt-2">Loading user data...</p></div>';

                fetch(`/admin/users/${id}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('User not found');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const user = data.data;
                            modalBody.innerHTML = `
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Full Name:</label>
                                        <p id="view_name">${user.name}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Email Address:</label>
                                        <p id="view_email">${user.email}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Phone Number:</label>
                                        <p id="view_phone">${user.phone || 'N/A'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Role:</label>
                                        <p id="view_role">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Branch:</label>
                                        <p id="view_branch">${user.branch ? user.branch.name : 'N/A'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Status:</label>
                                        <p id="view_status">${user.status.charAt(0).toUpperCase() + user.status.slice(1)}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Created:</label>
                                        <p id="view_created">${new Date(user.created_at).toLocaleDateString()}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Last Updated:</label>
                                        <p id="view_updated">${new Date(user.updated_at).toLocaleDateString()}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Last Login:</label>
                                        <p id="view_last_login">${user.last_login_at ? new Date(user.last_login_at).toLocaleString() : 'Never'}</p>
                                    </div>
                                </div>
                            `;
                        } else {
                            modalBody.innerHTML = '<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle fs-1"></i><p class="mt-2">Error loading user data</p></div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading user:', error);
                        modalBody.innerHTML = '<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle fs-1"></i><p class="mt-2">Error loading user data</p></div>';
                    });
            }

            function loadUserForEdit(id) {
                // Show loading state
                const modalBody = document.querySelector('#userModal .modal-body');

                // Use the original form content stored at startup
                const formTemplate = originalFormContent;

                modalBody.innerHTML = '<div class="text-center py-4"><i class="bi bi-hourglass-split fs-1"></i><p class="mt-2">Loading user data...</p></div>';


                fetch(`/admin/users/${id}`)
                    .then(response => {

                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(data => {

                        if (data.success) {
                            const user = data.data;
                            document.getElementById('userModalLabel').textContent = 'Edit User';
                            userForm.setAttribute('data-user-id', user.id);

                            // Restore original form content
                            modalBody.innerHTML = formTemplate;

                            // Wait a moment for DOM to be ready, then populate fields
                            setTimeout(() => {
                                populateFormFields(user);
                            }, 10);
                        } else {
                            modalBody.innerHTML = '<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle fs-1"></i><p class="mt-2">Error loading user data</p></div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading user:', error);
                        modalBody.innerHTML = `<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle fs-1"></i><p class="mt-2">Error loading user data</p><small class="text-muted">${error.message}</small></div>`;
                    });
            }

                        function populateFormFields(user) {
                // Get fresh references to all form elements
                const nameField = document.getElementById('name');
                const emailField = document.getElementById('email');
                const phoneField = document.getElementById('phone');
                const roleField = document.getElementById('role');
                const passwordField = document.getElementById('password');
                const passwordConfirmField = document.getElementById('password_confirmation');
                const branchFieldEl = document.getElementById('branchField');
                const branchSelectEl = document.getElementById('branch_id');

                // Check if all required elements exist
                if (!nameField || !emailField || !phoneField || !roleField || !passwordField || !passwordConfirmField) {
                    console.error('Required form elements not found');
                    const modalBody = document.querySelector('#userModal .modal-body');
                    modalBody.innerHTML = '<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle fs-1"></i><p class="mt-2">Error: Form elements not found</p></div>';
                    return;
                }

                // Populate form fields
                nameField.value = user.name;
                emailField.value = user.email;
                phoneField.value = user.phone || '';
                roleField.value = user.role;

                // Handle branch field visibility and value
                if (user.role === 'branch') {
                    if (branchFieldEl) branchFieldEl.style.display = 'block';
                    if (branchSelectEl) {
                        branchSelectEl.required = true;
                        branchSelectEl.value = user.branch_id || '';
                    }
                } else {
                    if (branchFieldEl) branchFieldEl.style.display = 'none';
                    if (branchSelectEl) branchSelectEl.required = false;
                }

                                // Configure password fields for editing
                passwordField.required = false;
                passwordConfirmField.required = false;
                passwordField.placeholder = 'Leave blank to keep current password';
                passwordConfirmField.placeholder = 'Leave blank to keep current password';
            }

            function deleteUser(id) {
                // Show loading state
                const confirmBtn = document.getElementById('confirmDelete');
                const originalText = confirmBtn.innerHTML;
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Deleting...';

                fetch(`/admin/users/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        deleteModal.hide();
                        showAlert(data.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error deleting user:', error);
                    showAlert('Error deleting user. Please try again.', 'error');
                })
                .finally(() => {
                    // Reset button state
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = originalText;
                });
            }

            function filterUsers() {
                const searchTerm = document.getElementById('searchInput').value.toLowerCase();
                const roleFilter = document.getElementById('role_filter').value;
                const branchFilter = document.getElementById('branch_filter').value;
                const statusFilter = document.getElementById('status_filter').value;

                const rows = document.querySelectorAll('#table-users tbody tr');

                rows.forEach(row => {
                    const name = row.querySelector('h6').textContent.toLowerCase();
                    const email = row.querySelector('small').textContent.toLowerCase();
                    const role = row.querySelector('.badge').textContent.toLowerCase();
                    const branch = row.querySelector('small.text-muted').textContent.toLowerCase();
                    const status = row.querySelector('.badge:last-child').textContent.toLowerCase();

                    const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
                    const matchesRole = !roleFilter || role.includes(roleFilter);
                    const matchesBranch = !branchFilter || branch.includes(branchFilter);
                    const matchesStatus = !statusFilter || status.includes(statusFilter);

                    row.style.display = matchesSearch && matchesRole && matchesBranch && matchesStatus ? '' : 'none';
                });
            }

            function showAlert(message, type = 'info') {
                // Create alert container if it doesn't exist
                let alertContainer = document.getElementById('alert-container');
                if (!alertContainer) {
                    alertContainer = document.createElement('div');
                    alertContainer.id = 'alert-container';
                    alertContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
                    document.body.appendChild(alertContainer);
                }

                // Create alert element
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
                alertDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                        <span>${message}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                // Add to container
                alertContainer.appendChild(alertDiv);

                // Auto-remove after 5 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);
            }
        });
    </script>
</body>

</html>


