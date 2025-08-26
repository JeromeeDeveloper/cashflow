<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cash Flow File Upload - {{ $branch->name ?? 'Branch' }}</title>

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
                    <h3>Cash Flow File Management</h3>
                    <p class="text-subtitle text-muted">Upload and manage cash flow Excel files for {{ $branch->name ?? 'your branch' }}</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill bg-primary fs-6 px-3 py-2">
                        <i class="bi bi-building me-2"></i>{{ $branch->name ?? 'Branch' }}
                    </span>
                    <button id="btnAdd" class="btn btn-primary">
                        <i class="bi bi-cloud-arrow-up me-2"></i>Upload Excel File
                    </button>
                </div>
            </div>

            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <h4 class="mb-0">Uploaded Cash Flow Files - {{ $branch->name ?? 'Branch' }}</h4>
                                <div class="d-flex align-items-center flex-wrap gap-2 justify-content-end">
                                    <div class="input-group" style="max-width: 200px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                        <input type="text" id="searchInput" class="form-control" placeholder="Search files...">
                                    </div>
                                    <div class="input-group" style="max-width: 150px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                                        <select id="year_filter" class="form-select">
                                            <option value="">All Years</option>
                                            @for($y = date('Y'); $y >= 2020; $y--)
                                                <option value="{{ $y }}">{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="input-group" style="max-width: 150px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-check-circle"></i></span>
                                        <select id="status_filter" class="form-select">
                                            <option value="">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="processing">Processing</option>
                                            <option value="processed">Processed</option>
                                            <option value="error">Error</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="table-files">
                                        <thead>
                                            <tr>
                                                <th>File Name</th>
                                                <th>Original Name</th>
                                                <th>Period</th>
                                                <th>Uploaded By</th>
                                                <th>Status</th>
                                                <th>Upload Date</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($cashflowFiles ?? [] as $file)
                                                <tr data-id="{{ $file->id }}">
                                                    <td><span class="fw-medium text-primary">{{ $file->file_name }}</span></td>
                                                    <td>{{ $file->original_name }}</td>
                                                    <td>{{ $file->month }} {{ $file->year }}</td>
                                                    <td>{{ $file->uploaded_by ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $file->status === 'processed' ? 'success' : ($file->status === 'processing' ? 'warning' : ($file->status === 'error' ? 'danger' : 'secondary')) }}">{{ ucfirst($file->status) }}</span>
                                                    </td>
                                                    <td>{{ $file->created_at ? $file->created_at->format('M d, Y H:i') : 'N/A' }}</td>
                                                    <td class="text-end">
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-primary btn-view" data-id="{{ $file->id }}" title="View Details"><i class="bi bi-eye"></i></button>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-download" data-id="{{ $file->id }}" title="Download"><i class="bi bi-download"></i></button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $file->id }}" title="Delete"><i class="bi bi-trash"></i></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                                        No files uploaded yet
                                                        <br>
                                                        <small>Upload your first cash flow Excel file to get started</small>
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

    <!-- Upload File Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Upload Cash Flow Excel File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="file" class="form-label">Excel File <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls" required>
                                    <div class="form-text">Upload Excel file (.xlsx or .xls) containing cash flow data</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                                    <select class="form-select" id="year" name="year" required>
                                        <option value="">Select Year</option>
                                        @for($y = date('Y'); $y >= 2020; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="month" class="form-label">Month <span class="text-danger">*</span></label>
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
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="period_type" class="form-label">Period Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="period_type" name="period_type" required>
                                        <option value="">Select Period Type</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="weekly">Weekly</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="2" placeholder="Brief description of the file content"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>File Format Requirements</h6>
                            <ul class="mb-0 small">
                                <li><strong>Column A:</strong> Account Code (e.g., 1001, 2001)</li>
                                <li><strong>Column B:</strong> Account Name (e.g., "Cash", "Accounts Receivable")</li>
                                <li><strong>Column C:</strong> Actual Amount (numeric values)</li>
                                <li><strong>Column D:</strong> Projection Percentage (optional, numeric values)</li>
                                <li><strong>Note:</strong> File will be processed automatically after upload</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-upload me-2"></i>Upload & Process
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View File Details Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">File Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="fileDetails">
                        <!-- File details will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        // Initialize DataTable
        const table = new simpleDatatables.DataTable("#table-files", {
            searchable: true,
            fixedHeight: true,
            perPage: 10
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            table.search(this.value);
        });

        // Year filter
        document.getElementById('year_filter').addEventListener('change', function() {
            const year = this.value;
            if (year) {
                table.search(year);
            } else {
                table.search('');
            }
        });

        // Status filter
        document.getElementById('status_filter').addEventListener('change', function() {
            const status = this.value;
            if (status) {
                table.search(status);
            } else {
                table.search('');
            }
        });



        // Upload modal
        document.getElementById('btnAdd').addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('uploadModal'));
            modal.show();
        });

        // Upload form submission
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();



            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Uploading...';

            // Show loading alert
            Swal.fire({
                title: 'Uploading File...',
                text: 'Please wait while we process your Excel file',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('{{ route('branch.files.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Upload Successful!',
                        text: data.message,
                        confirmButtonText: 'Great!'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    // Check if it's a validation error
                    if (data.type === 'validation_error') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'GL Account Validation Failed',
                            html: data.message.replace(/\n/g, '<br>'),
                            confirmButtonText: 'OK',
                            width: '600px'
                        });
                    } else {
                        throw new Error(data.message || 'Upload failed');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Failed',
                    text: error.message || 'An error occurred during upload',
                    confirmButtonText: 'Try Again'
                });
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });

        // View file details
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-view')) {
                const fileId = e.target.closest('.btn-view').getAttribute('data-id');
                loadFileDetails(fileId);
            }
        });

        function loadFileDetails(fileId) {
            fetch(`{{ url('branch/files') }}/${fileId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('fileDetails').innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>File Name:</strong> ${data.file.file_name}</p>
                                    <p><strong>Original Name:</strong> ${data.file.original_name}</p>
                                    <p><strong>Period:</strong> ${data.file.month} ${data.file.year}</p>
                                    <p><strong>Status:</strong> <span class="badge bg-${data.file.status === 'processed' ? 'success' : 'secondary'}">${data.file.status}</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Uploaded By:</strong> ${data.file.uploaded_by || 'N/A'}</p>
                                    <p><strong>Upload Date:</strong> ${data.file.created_at}</p>
                                    <p><strong>File Size:</strong> ${data.file.file_size || 'N/A'}</p>
                                    <p><strong>Description:</strong> ${data.file.description || 'N/A'}</p>
                                </div>
                            </div>
                        `;

                        const modal = new bootstrap.Modal(document.getElementById('viewModal'));
                        modal.show();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load file details',
                        confirmButtonText: 'OK'
                    });
                });
        }



        // Download file
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-download')) {
                const fileId = e.target.closest('.btn-download').getAttribute('data-id');
                window.open(`{{ url('branch/files') }}/${fileId}/download`, '_blank');
            }
        });

        // Delete file
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete')) {
                const fileId = e.target.closest('.btn-delete').getAttribute('data-id');
                deleteFile(fileId);
            }
        });

        function deleteFile(fileId) {
            Swal.fire({
                title: 'Delete File?',
                text: 'This action cannot be undone. All associated data will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteFileAction(fileId);
                }
            });
        }

        function deleteFileAction(fileId) {
            fetch(`{{ url('branch/files') }}/${fileId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'File Deleted!',
                        text: data.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Delete failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Delete Failed',
                    text: error.message || 'An error occurred during deletion',
                    confirmButtonText: 'Try Again'
                });
            });
        }
    </script>
</body>
</html>

