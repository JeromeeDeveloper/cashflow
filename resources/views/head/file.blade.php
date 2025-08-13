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
                    <h3>Cash Flow File Upload</h3>
                    <p class="text-subtitle text-muted">Upload and manage Excel files for cash flow processing</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button id="btnUpload" class="btn btn-primary">
                        <i class="bi bi-cloud-arrow-up me-2"></i>Upload New File
                    </button>
                    <button id="btnRefresh" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                    </button>
                </div>
            </div>

            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <h4 class="mb-0">Uploaded Cash Flow Files</h4>
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <div class="input-group" style="max-width: 200px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                                        <select id="year_filter" class="form-select">
                                            <option value="">All Years</option>
                                            @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                                                <option value="{{ $year }}">{{ $year }}</option>
                                            @endfor
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
                                                <th>Year</th>
                                                <th>Month</th>
                                                <th>Uploaded By</th>
                                                <th>Status</th>
                                                <th>Upload Date</th>
                                                <th>Actions</th>
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
                                                    <td>
                                                        <span class="badge bg-primary">{{ $file->branch->name ?? 'N/A' }}</span>
                                                    </td>
                                                    <td>{{ $file->year }}</td>
                                                    <td>{{ $file->month ?? 'N/A' }}</td>
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
                                                    <td>{{ $file->created_at->format('M d, Y H:i') }}</td>
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
                                                    <td colspan="9" class="text-center text-muted py-4">
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
                            <h5 class="modal-title" id="uploadModalLabel">Upload Cash Flow File</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="uploadForm" enctype="multipart/form-data">
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
                                            @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                                                <option value="{{ $year }}">{{ $year }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="month" class="form-label">Month</label>
                                        <select class="form-select" id="month" name="month">
                                            <option value="">Select Month (Optional)</option>
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
                                        <label for="file_type" class="form-label">File Type</label>
                                        <select class="form-select" id="file_type" name="file_type">
                                            <option value="cashflow" selected>Cash Flow</option>
                                            <option value="gl_accounts">GL Accounts</option>
                                            <option value="budget">Budget</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label for="cashflow_file" class="form-label">Excel File <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="cashflow_file" name="cashflow_file" accept=".xlsx,.xls,.csv" required>
                                        <div class="form-text">
                                            Supported formats: .xlsx, .xls, .csv. Maximum size: 10MB
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Optional description of the file contents..."></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="btnSaveUpload">
                                <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                                Upload File
                            </button>
                        </div>
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
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this cash flow file? This action cannot be undone and will also remove all associated cashflow data.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="btnConfirmDelete">Delete File</button>
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
            const filesTable = document.querySelector('#table-files');
            if (filesTable && window.simpleDatatables) {
                new simpleDatatables.DataTable(filesTable);
            }

            // Modal instances
            const uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));
            const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

            // Current file ID for operations
            let currentFileId = null;

            // Upload button
            document.getElementById('btnUpload').addEventListener('click', function() {
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

            // Process buttons
            document.querySelectorAll('.btn-process').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    processFile(id);
                });
            });

            // Download buttons
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
                    deleteModal.show();
                });
            });

            // Save upload button
            document.getElementById('btnSaveUpload').addEventListener('click', function() {
                if (validateUploadForm()) {
                    uploadFile();
                }
            });

            // Confirm delete button
            document.getElementById('btnConfirmDelete').addEventListener('click', function() {
                deleteFile();
            });

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

                const fileInput = document.getElementById('cashflow_file');
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
                const allowedTypes = ['.xlsx', '.xls', '.csv'];
                const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
                if (!allowedTypes.includes(fileExtension)) {
                    showAlert('Please select a valid Excel or CSV file', 'error');
                    return false;
                }

                return true;
            }

            function uploadFile() {
                const formData = new FormData(document.getElementById('uploadForm'));
                const btnSave = document.getElementById('btnSaveUpload');
                const spinner = btnSave.querySelector('.spinner-border');

                // Show loading state
                btnSave.disabled = true;
                spinner.classList.remove('d-none');
                btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Uploading...';

                fetch('{{ route("head.files.store") }}', {
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
                        uploadModal.hide();
                        location.reload(); // Refresh to show new file
                    } else {
                        showAlert(data.message || 'Failed to upload file', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error uploading file:', error);
                    showAlert('Error uploading file', 'error');
                })
                .finally(() => {
                    // Reset loading state
                    btnSave.disabled = false;
                    spinner.classList.add('d-none');
                    btnSave.innerHTML = 'Upload File';
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
                            document.getElementById('view_branch').textContent = file.branch?.name || 'N/A';
                            document.getElementById('view_year').textContent = file.year;
                            document.getElementById('view_month').textContent = file.month || 'N/A';
                            document.getElementById('view_file_type').textContent = file.file_type;
                            document.getElementById('view_status').textContent = file.status;
                            document.getElementById('view_uploaded_by').textContent = file.uploaded_by || 'N/A';
                            document.getElementById('view_upload_date').textContent = new Date(file.created_at).toLocaleString();
                            document.getElementById('view_description').textContent = file.description || 'No description provided';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading file:', error);
                        showAlert('Error loading file data', 'error');
                    });
            }

            function processFile(id) {
                if (!confirm('Are you sure you want to process this file? This will extract cashflow data and may take a few moments.')) {
                    return;
                }

                fetch(`{{ route("head.files.index") }}/${id}/process`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        location.reload(); // Refresh to show updated status
                    } else {
                        showAlert(data.message || 'Failed to process file', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error processing file:', error);
                    showAlert('Error processing file', 'error');
                });
            }

            function downloadFile(id) {
                // Create a temporary link to download the file
                const link = document.createElement('a');
                link.href = `{{ route("head.files.index") }}/${id}/download`;
                link.download = '';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            function deleteFile() {
                fetch(`{{ route("head.files.index") }}/${currentFileId}`, {
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
                        location.reload(); // Refresh to remove deleted file
                    } else {
                        showAlert(data.message || 'Failed to delete file', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error deleting file:', error);
                    showAlert('Error deleting file', 'error');
                });
            }

            function filterFiles() {
                const yearFilter = document.getElementById('year_filter').value;
                const branchFilter = document.getElementById('branch_filter').value;
                const statusFilter = document.getElementById('status_filter').value;

                const rows = document.querySelectorAll('#table-files tbody tr');

                rows.forEach(row => {
                    let show = true;

                    // Filter by year
                    if (yearFilter && row.cells[3].textContent !== yearFilter) {
                        show = false;
                    }

                    // Filter by branch
                    if (branchFilter) {
                        const branchCell = row.cells[2].textContent;
                        if (!branchCell.includes(branchFilter)) {
                            show = false;
                        }
                    }

                    // Filter by status
                    if (statusFilter) {
                        const statusCell = row.cells[6].textContent;
                        if (!statusCell.includes(statusFilter)) {
                            show = false;
                        }
                    }

                    row.style.display = show ? '' : 'none';
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
