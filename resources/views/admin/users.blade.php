<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Management</title>

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

            <div class="page-heading d-flex align-items-center justify-content-between">
                <div>
                    <h3>Users</h3>
                    <p class="text-subtitle text-muted">User management table</p>
                </div>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateUser">
                        <i class="bi bi-person-plus me-2"></i>New User
                    </button>
                </div>
            </div>

            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="mb-0">Users</h4>
                                <div class="d-flex gap-2">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" placeholder="Search users...">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="table-users">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Jane Doe</td>
                                                <td>jane@example.com</td>
                                                <td><span class="badge bg-primary">admin</span></td>
                                                <td><span class="badge bg-success">Active</span></td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-secondary" title="Reset Password"><i class="bi bi-key"></i></button>
                                                    <button class="btn btn-sm btn-outline-primary" title="Edit" data-bs-toggle="modal" data-bs-target="#modalEditUser"><i class="bi bi-pencil"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>John Smith</td>
                                                <td>john@example.com</td>
                                                <td><span class="badge bg-info text-dark">head</span></td>
                                                <td><span class="badge bg-warning text-dark">Pending</span></td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-secondary" title="Reset Password"><i class="bi bi-key"></i></button>
                                                    <button class="btn btn-sm btn-outline-primary" title="Edit" data-bs-toggle="modal" data-bs-target="#modalEditUser"><i class="bi bi-pencil"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Amanda Lee</td>
                                                <td>amanda@example.com</td>
                                                <td><span class="badge bg-secondary">branch</span></td>
                                                <td><span class="badge bg-success">Active</span></td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-secondary" title="Reset Password"><i class="bi bi-key"></i></button>
                                                    <button class="btn btn-sm btn-outline-primary" title="Edit" data-bs-toggle="modal" data-bs-target="#modalEditUser"><i class="bi bi-pencil"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
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

    <!-- Create User Modal -->
    <div class="modal fade" id="modalCreateUser" tabindex="-1" aria-labelledby="modalCreateUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCreateUserLabel">Create User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" action="javascript:void(0)" method="post">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" placeholder="Full name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="name@example.com" required>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" class="form-select">
                                <option value="admin">admin</option>
                                <option value="head">head</option>
                                <option value="branch">branch</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="disabled">Disabled</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Create</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="modalEditUser" tabindex="-1" aria-labelledby="modalEditUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditUserLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" action="javascript:void(0)" method="post">
                        <div class="col-md-6">
                            <label for="edit_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_name" value="Jane Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" value="jane@example.com" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_role" class="form-label">Role</label>
                            <select id="edit_role" class="form-select">
                                <option value="admin" selected>admin</option>
                                <option value="head">head</option>
                                <option value="branch">branch</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_status" class="form-label">Status</label>
                            <select id="edit_status" class="form-select">
                                <option value="active" selected>Active</option>
                                <option value="pending">Pending</option>
                                <option value="disabled">Disabled</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/simple-datatables/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script>
        (function() {
            const usersTable = document.querySelector('#table-users');
            if (usersTable && window.simpleDatatables) {
                new simpleDatatables.DataTable(usersTable);
            }
        })();
    </script>
</body>

</html>


