<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Branch File Upload</title>
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
                    <h3>Cash Flow File Upload</h3>
                    <p class="text-subtitle text-muted">Upload cash flow Excel files for your branch</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button id="btnAdd" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Upload File
                    </button>
                </div>
            </div>

            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <h4 class="mb-0">Uploaded Files ({{ $branch->name ?? 'N/A' }})</h4>
                                <div class="d-flex align-items-center flex-wrap gap-2 justify-content-end">
                                    <div class="input-group" style="max-width: 150px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-calendar"></i></span>
                                        <select id="year_filter" class="form-select">
                                            <option value="">All Years</option>
                                            @for($y = now()->year; $y >= 2020; $y--)
                                                <option value="{{ $y }}">{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="input-group" style="max-width: 160px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-flag"></i></span>
                                        <select id="status_filter" class="form-select">
                                            <option value="">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="processed">Processed</option>
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
                                                        <span class="badge bg-{{ $file->status === 'processed' ? 'success' : 'secondary' }}">{{ ucfirst($file->status) }}</span>
                                                    </td>
                                                    <td>{{ $file->created_at ? $file->created_at->format('M d, Y H:i') : 'N/A' }}</td>
                                                    <td class="text-end">
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-primary btn-view" data-id="{{ $file->id }}" title="View Details"><i class="bi bi-eye"></i></button>
                                                            <button type="button" class="btn btn-sm btn-outline-success btn-process" data-id="{{ $file->id }}" title="Process"><i class="bi bi-cpu"></i></button>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-download" data-id="{{ $file->id }}" title="Download"><i class="bi bi-download"></i></button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $file->id }}" title="Delete"><i class="bi bi-trash"></i></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-4">
                                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                                        No files found
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
                            <h5 class="modal-title" id="uploadModalLabel">Upload Cashflow File</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="uploadForm">
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Branch</label>
                                        <input type="text" class="form-control" value="{{ $branch->name ?? 'N/A' }}" readonly>
                                        <small class="text-muted">This upload will be saved under your branch.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                                        <select class="form-select" id="year" name="year" required>
                                            <option value="">Select Year</option>
                                            @for($y = now()->year; $y >= 2020; $y--)
                                                <option value="{{ $y }}">{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="month" class="form-label">Month <span class="text-danger">*</span></label>
                                        <select class="form-select" id="month" name="month" required>
                                            <option value="">Select Month</option>
                                            @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                                                <option value="{{ $m }}">{{ $m }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label for="file" class="form-label">Excel File <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls" required>
                                        <small class="text-muted">Only Excel files (.xlsx, .xls) up to 10MB</small>
                                    </div>
                                    <div class="col-12">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="2" placeholder="Optional"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-2"></i>Upload</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- View Modal -->
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
                                    <label class="form-label fw-bold">Year:</label>
                                    <p id="view_year"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Month:</label>
                                    <p id="view_month"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Uploaded By:</label>
                                    <p id="view_uploaded_by"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Status:</label>
                                    <p id="view_status"></p>
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

        </div>
    </div>

    <script src="{{ asset('assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/simple-datatables/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/vendors/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));
            const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));

            document.getElementById('btnAdd').addEventListener('click', function() {
                document.getElementById('uploadForm').reset();
                uploadModal.show();
            });

            document.getElementById('uploadForm').addEventListener('submit', function(e) {
                e.preventDefault();
                uploadFile();
            });

            document.querySelectorAll('.btn-view').forEach(btn => btn.addEventListener('click', function(){
                loadFileForView(this.dataset.id);
                viewModal.show();
            }));

            document.querySelectorAll('.btn-process').forEach(btn => btn.addEventListener('click', function(){
                processFile(this.dataset.id);
            }));

            document.querySelectorAll('.btn-download').forEach(btn => btn.addEventListener('click', function(){
                downloadFile(this.dataset.id);
            }));

            document.querySelectorAll('.btn-delete').forEach(btn => btn.addEventListener('click', function(){
                deleteFile(this.dataset.id);
            }));

            document.getElementById('year_filter').addEventListener('change', filterFiles);
            document.getElementById('status_filter').addEventListener('change', filterFiles);

            function uploadFile() {
                const form = document.getElementById('uploadForm');
                const formData = new FormData(form);

                if (!form.year.value || !form.month.value || !form.file.files[0]) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Please complete all required fields' });
                    return;
                }

                Swal.fire({ title: 'Uploading...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch('/branch/files', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData,
                }).then(r => r.json()).then(data => {
                    Swal.close();
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Uploaded', text: data.message });
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Upload Failed', text: data.message || 'Error' });
                    }
                }).catch(() => {
                    Swal.close();
                    Swal.fire({ icon: 'error', title: 'Upload Failed', text: 'Network error' });
                });
            }

            function loadFileForView(id) {
                fetch(`/branch/files/${id}`).then(r => r.json()).then(data => {
                    if (data.success) {
                        const f = data.data;
                        document.getElementById('view_file_name').textContent = f.file_name;
                        document.getElementById('view_original_name').textContent = f.original_name;
                        document.getElementById('view_year').textContent = f.year;
                        document.getElementById('view_month').textContent = f.month;
                        document.getElementById('view_uploaded_by').textContent = f.uploaded_by || 'N/A';
                        document.getElementById('view_status').textContent = f.status;
                        document.getElementById('view_description').textContent = f.description || '—';
                    }
                });
            }

            function processFile(id) {
                Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                fetch(`/branch/files/${id}/process`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                }).then(r => r.json()).then(data => {
                    Swal.close();
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Processed', text: data.message });
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Processing Failed', text: data.message || 'Error' });
                    }
                }).catch(() => {
                    Swal.close();
                    Swal.fire({ icon: 'error', title: 'Processing Failed', text: 'Network error' });
                });
            }

            function downloadFile(id) {
                fetch(`/branch/files/${id}/download`).then(r => r.json()).then(data => {
                    if (data.success && data.url) {
                        window.open(data.url, '_blank');
                    }
                });
            }

            function deleteFile(id) {
                Swal.fire({ title: 'Delete file?', icon: 'warning', showCancelButton: true }).then(res => {
                    if (!res.isConfirmed) return;
                    fetch(`/branch/files/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } })
                        .then(r => r.json()).then(data => {
                            if (data.success) {
                                Swal.fire({ icon: 'success', title: 'Deleted' });
                                setTimeout(() => location.reload(), 800);
                            } else {
                                Swal.fire({ icon: 'error', title: 'Delete Failed', text: data.message || 'Error' });
                            }
                        });
                });
            }

            function filterFiles() {
                const year = document.getElementById('year_filter').value;
                const status = document.getElementById('status_filter').value;
                const rows = document.querySelectorAll('#table-files tbody tr');
                rows.forEach(row => {
                    let show = true;
                    if (year) {
                        const period = row.cells[2].textContent;
                        if (!period.includes(year)) show = false;
                    }
                    if (status) {
                        const statusText = row.cells[4].innerText.trim().toLowerCase();
                        if (!statusText.includes(status.toLowerCase())) show = false;
                    }
                    row.style.display = show ? '' : 'none';
                });
            }
        });
    </script>
</body>
</html>

