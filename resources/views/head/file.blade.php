<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cash Flow File Upload - Head Office</title>

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
                    <p class="text-subtitle text-muted">Upload and manage cash flow Excel files for specific branches</p>
                </div>
                <div class="d-flex align-items-center gap-2">
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
                                <h4 class="mb-0">Uploaded Cash Flow Files</h4>
                                <div class="d-flex align-items-center flex-wrap gap-2 justify-content-end">
                                    <div class="input-group" style="max-width: 200px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                        <input type="text" id="searchInput" class="form-control" placeholder="Search files...">
                                    </div>
                                    <div class="input-group" style="max-width: 150px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                                        <select id="branch_filter" class="form-select">
                                            <option value="">All Branches</option>
                                            @foreach($branches ?? [] as $branch)
                                                <option value="{{ $branch->name }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
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
                                                <th>Branch</th>
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
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-file-earmark-excel text-success me-2 fs-5"></i>
                                                            <span class="fw-medium">{{ $file->file_name }}</span>
                                                        </div>
                                                    </td>
                                                    <td>{{ $file->original_name }}</td>
                                                    <td>{{ $file->branch->name ?? 'All Branches' }}</td>
                                                    <td>{{ $file->year }} {{ $file->month ?? 'N/A' }}</td>
                                                    <td>{{ $file->uploaded_by ?? 'N/A' }}</td>
                                                    <td>
                                                        @switch($file->status)
                                                            @case('pending')
                                                                <span class="badge bg-warning text-dark">Pending</span>
                                                                @break
                                                            @case('processed')
                                                                <span class="badge bg-success">Processed</span>
                                                                @break
                                                            @case('error')
                                                                <span class="badge bg-danger">Error</span>
                                                                @break
                                                            @default
                                                                <span class="badge bg-secondary">{{ $file->status }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td>{{ $file->created_at ? $file->created_at->format('M d, Y H:i') : 'N/A' }}</td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button class="btn btn-sm btn-outline-primary btn-view" title="View Details" data-id="{{ $file->id }}">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-success btn-process" title="Process File" data-id="{{ $file->id }}" {{ $file->status === 'processed' ? 'disabled' : '' }}>
                                                                <i class="bi bi-play-circle"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-info btn-download" title="Download File" data-id="{{ $file->id }}">
                                                                <i class="bi bi-download"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger btn-delete" title="Delete File" data-id="{{ $file->id }}">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted py-4">
                                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                                        No cash flow files uploaded yet
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

            <!-- Upload Modal -->
            <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="uploadModalLabel">Upload Cash Flow Excel File</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="uploadForm" enctype="multipart/form-data">
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                                        <select class="form-select" id="branch_id" name="branch_id" required>
                                            <option value="">Select Branch</option>
                                            @foreach($branches ?? [] as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                                        <select class="form-select" id="year" name="year" required>
                                            <option value="">Select Year</option>
                                            @for($y = date('Y'); $y >= 2020; $y--)
                                                <option value="{{ $y }}">{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-6">
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
                                    <div class="col-md-6">
                                        <label for="file" class="form-label">Excel File <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls" required>
                                        <small class="text-muted">Only Excel files (.xlsx, .xls) up to 10MB</small>
                                    </div>
                                    <div class="col-12">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Optional description of the cash flow data..."></textarea>
                                    </div>
                                </div>

                                <!-- File Format Instructions -->
                                <div class="alert alert-info mt-3">
                                    <h6><i class="bi bi-info-circle me-2"></i>Excel File Format Requirements:</h6>
                                    <ul class="mb-0 small">
                                        <li><strong>Row A1:</strong> Cooperative name</li>
                                        <li><strong>Row A2:</strong> "CASH FLOW MONITORING REPORT"</li>
                                        <li><strong>Column A:</strong> Headers (CASH BEGINNING BALANCE, TOTAL CASH AVAILABLE, etc.)</li>
                                        <li><strong>Column B:</strong> Product names and header values</li>
                                        <li><strong>Column C:</strong> Product amounts</li>
                                        <li><strong>Key Headers:</strong> CASH BEGINNING BALANCE, TOTAL CASH AVAILABLE, TOTAL DISBURSEMENTS, CASH ENDING BALANCE</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload me-2"></i>Upload File
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- View File Modal -->
            <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewModalLabel">File Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">File Name:</label>
                                    <p id="view_file_name"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Original Name:</label>
                                    <p id="view_original_name"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Branch:</label>
                                    <p id="view_branch"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Year:</label>
                                    <p id="view_year"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Month:</label>
                                    <p id="view_month"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">File Type:</label>
                                    <p id="view_file_type"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Status:</label>
                                    <p id="view_status"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Uploaded By:</label>
                                    <p id="view_uploaded_by"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Upload Date:</label>
                                    <p id="view_upload_date"></p>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Description:</label>
                                    <p id="view_description"></p>
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
            <!-- Removed - Using SweetAlert2 for confirmation instead -->


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

            // Enable Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize DataTable
            const filesTable = document.querySelector('#table-files');
            if (filesTable && window.simpleDatatables) {
                new simpleDatatables.DataTable(filesTable);
            }

            // Modal instances
            const uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));
            const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
            // const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal')); // Removed

            // Current file ID for operations
            let currentFileId = null;

            // Upload button
            document.getElementById('btnAdd').addEventListener('click', function() {
                document.getElementById('uploadForm').reset();
                uploadModal.show();
            });

            // View buttons
            document.querySelectorAll('.btn-view').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    loadFileForView(id);
                    viewModal.show();
                });
            });

            // Process button
            document.querySelectorAll('.btn-process').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const fileName = this.closest('tr').querySelector('td:nth-child(2)').textContent;
                    if (confirm(`Are you sure you want to process "${fileName}"? This will import the cashflow data into the system.`)) {
                        processFile(id);
                    }
                });
            });

            // Download button
            document.querySelectorAll('.btn-download').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    downloadFile(id);
                });
            });

            // Delete buttons
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    currentFileId = id;
                    // deleteModal.show(); // Removed
                    Swal.fire({
                        title: 'Delete File?',
                        text: 'Are you sure you want to delete this file? This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading
                            Swal.fire({
                                title: 'Deleting File...',
                                text: 'Please wait while we delete the file',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            fetch(`/head/files/${currentFileId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                // Close loading
                                Swal.close();

                                if (data.success) {
                                    // Show success message
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'File Deleted!',
                                        text: data.message,
                                        confirmButtonText: 'OK'
                                    });

                                    // Close modal and reload page
                                    // deleteModal.hide(); // Removed
                                    setTimeout(() => {
                                        location.reload();
                                    }, 1500);
                                } else {
                                    // Show error message
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Delete Failed',
                                        text: data.message || 'An error occurred while deleting the file',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            })
                            .catch(error => {
                                // Close loading
                                Swal.close();

                                console.error('Error deleting file:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Delete Failed',
                                    text: 'Network error occurred. Please try again.',
                                    confirmButtonText: 'OK'
                                });
                            });
                        }
                    });
                });
            });

            // Save upload button
            document.getElementById('uploadForm').addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent default form submission
                if (validateUploadForm()) {
                    uploadFile();
                }
            });

            // Confirm delete button
            // document.getElementById('btnConfirmDelete').addEventListener('click', function() { // Removed
            //     deleteFile(); // Removed
            // });

            // Filter change events
            document.getElementById('year_filter').addEventListener('change', function() {
                filterFiles();
            });

            document.getElementById('branch_filter').addEventListener('change', function() {
                filterFiles();
            });

            document.getElementById('status_filter').addEventListener('change', function() {
                filterFiles();
            });

            // Refresh button
            document.getElementById('btnRefresh').addEventListener('click', function() {
                this.disabled = true;
                this.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Refreshing...';

                setTimeout(() => {
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Refresh';
                    location.reload();
                }, 1000);
            });

            function validateUploadForm() {
                const form = document.getElementById('uploadForm');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return false;
                }

                const fileInput = document.getElementById('file');
                const file = fileInput.files[0];

                if (!file) {
                    showAlert('Please select a file to upload', 'error');
                    return false;
                }

                // Check file size (10MB limit)
                if (file.size > 10 * 1024 * 1024) {
                    showAlert('File size must be less than 10MB', 'error');
                    return false;
                }

                // Check file type
                const allowedTypes = ['.xlsx', '.xls'];
                const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
                if (!allowedTypes.includes(fileExtension)) {
                    showAlert('Please select a valid Excel file (.xlsx, .xls)', 'error');
                    return false;
                }

                return true;
            }

            function uploadFile() {
                const formData = new FormData();
                const fileInput = document.getElementById('file');
                const branchInput = document.getElementById('branch_id');
                const yearInput = document.getElementById('year');
                const monthInput = document.getElementById('month');
                const descriptionInput = document.getElementById('description');

                // Validate required fields
                if (!fileInput.files[0]) {
                    showAlert('Please select an Excel file', 'error');
                    return;
                }
                if (!branchInput.value) {
                    showAlert('Please select a branch', 'error');
                    return;
                }
                if (!yearInput.value) {
                    showAlert('Please select a year', 'error');
                    return;
                }
                if (!monthInput.value) {
                    showAlert('Please select a month', 'error');
                    return;
                }

                // Show loading with SweetAlert2
                Swal.fire({
                    title: 'Uploading File...',
                    text: 'Please wait while we upload your Excel file',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Prepare form data
                formData.append('file', fileInput.files[0]);
                formData.append('branch_id', branchInput.value);
                formData.append('year', yearInput.value);
                formData.append('month', monthInput.value);
                formData.append('description', descriptionInput.value);
                formData.append('_token', csrfToken);

                // Disable upload button
                const uploadBtn = document.querySelector('#uploadForm button[type="submit"]');
                uploadBtn.disabled = true;
                uploadBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Uploading...';

                fetch('/head/files', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Close loading
                    Swal.close();

                    if (data.success) {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'File Uploaded Successfully!',
                            text: data.message,
                            confirmButtonText: 'OK'
                        });

                        // Reset form and close modal
                        document.getElementById('uploadForm').reset();
                        uploadModal.hide();

                        // Reload page to show new file
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Upload Failed',
                            text: data.message || 'An error occurred while uploading the file',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    // Close loading
                    Swal.close();

                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Failed',
                        text: 'Network error occurred. Please try again.',
                        confirmButtonText: 'OK'
                    });
                })
                .finally(() => {
                    // Re-enable upload button
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="bi bi-upload me-2"></i>Upload File';
                });
            }

            function loadFileForView(id) {
                fetch(`{{ route("head.files.index") }}/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const file = data.data;
                            document.getElementById('view_file_name').textContent = file.file_name;
                            document.getElementById('view_original_name').textContent = file.original_name;
                            document.getElementById('view_branch').textContent = file.branch ? file.branch.name : 'All Branches';
                            document.getElementById('view_year').textContent = file.year;
                            document.getElementById('view_month').textContent = file.month || 'N/A';
                            document.getElementById('view_file_type').textContent = file.file_type;
                            document.getElementById('view_status').textContent = file.status;
                            document.getElementById('view_uploaded_by').textContent = file.uploaded_by || 'N/A';
                            document.getElementById('view_upload_date').textContent = file.created_at;
                            document.getElementById('view_description').textContent = file.description || 'No description provided';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading file:', error);
                        showAlert('Error loading file data', 'error');
                    });
            }

            function processFile(id) {
                // Show loading with SweetAlert2
                Swal.fire({
                    title: 'Processing File...',
                    text: 'Please wait while we process your Excel file and import data for all branches',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(`/head/files/${id}/process`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Close loading
                    Swal.close();

                    if (data.success) {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'File Processed Successfully!',
                            text: data.message,
                            confirmButtonText: 'OK'
                        });

                        // Reload page to show updated status
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Processing Failed',
                            text: data.message || 'An error occurred while processing the file',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    // Close loading
                    Swal.close();

                    console.error('Error processing file:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Processing Failed',
                        text: 'Network error occurred. Please try again.',
                        confirmButtonText: 'OK'
                    });
                });
            }

            function downloadFile(id) {
                fetch(`/head/files/${id}/download`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Create a temporary link to download the file
                            const link = document.createElement('a');
                            link.href = data.data.download_url;
                            link.download = data.data.file_name;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            showAlert('Download started', 'success');
                        } else {
                            showAlert(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error downloading file:', error);
                        showAlert('Error downloading file. Please try again.', 'error');
                    });
            }

            function filterFiles() {
                const yearFilter = document.getElementById('year_filter').value;
                const statusFilter = document.getElementById('status_filter').value;
                const branchFilter = document.getElementById('branch_filter').value;

                const rows = document.querySelectorAll('#table-files tbody tr');

                rows.forEach(row => {
                    let show = true;

                    // Filter by year
                    if (yearFilter && row.cells[3].textContent !== yearFilter) {
                        show = false;
                    }

                    // Filter by status
                    if (statusFilter) {
                        const statusCell = row.cells[6].textContent;
                        if (!statusCell.includes(statusFilter)) {
                            show = false;
                        }
                    }

                    // Filter by branch
                    if (branchFilter) {
                        const branchCell = row.cells[2].textContent;
                        if (!branchCell.includes(branchFilter)) {
                            show = false;
                        }
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
