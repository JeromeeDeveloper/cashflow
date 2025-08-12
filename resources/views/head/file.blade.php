<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Flow Upload - Head Office</title>

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

            <div class="page-heading">
                <h3>Cash Flow Upload</h3>
                <p class="text-subtitle text-muted">Upload a Cash Flow Excel file and review upload history</p>
            </div>

            <div class="page-content">
                <section class="row">
                    <div class="col-12 col-lg-4">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="mb-0">Upload Cash Flow</h4>
                                <button type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download me-1"></i>Template
                                </button>
                            </div>
                            <div class="card-body">
                                <form action="javascript:void(0)" method="post" enctype="multipart/form-data" id="uploadForm">
                                    <div class="mb-3">
                                        <label for="reporting_period" class="form-label">Reporting Period</label>
                                        <input type="month" class="form-control" id="reporting_period" value="{{ date('Y-m') }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="branch" class="form-label">Branch (optional)</label>
                                        <select class="form-select" id="branch">
                                            <option value="">All Branches</option>
                                            <option value="main">Main Office</option>
                                            <option value="branch1">Branch 1</option>
                                            <option value="branch2">Branch 2</option>
                                            <option value="branch3">Branch 3</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="excel_file" class="form-label">Cash Flow Excel File</label>
                                        <input type="file" class="form-control" id="excel_file" accept=".xlsx,.xls" required>
                                        <div class="form-text">Supported formats: .xlsx, .xls (Max size: 10MB)</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description (optional)</label>
                                        <textarea class="form-control" id="description" rows="3" placeholder="Brief description..."></textarea>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary" id="btnUpload">
                                            <i class="bi bi-upload me-2"></i>Upload Cash Flow
                                        </button>
                                        <button type="button" class="btn btn-light-secondary" id="btnClear">
                                            <i class="bi bi-eraser me-2"></i>Clear Form
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>


                    </div>

                    <div class="col-12 col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="mb-0">Uploaded Cash Flow Files</h4>

                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="table-files">
                                        <thead>
                                            <tr>
                                                <th>File Name</th>
                                                <th>Period</th>
                                                <th>Branch</th>
                                                <th>Upload Date</th>
                                                <th>Status</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-file-earmark-excel text-success me-2"></i>
                                                        <div>
                                                            <div class="fw-bold">cashflow_main_jan_2024.xlsx</div>
                                                            <small class="text-muted">2.3 MB</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>Jan 2024</td>
                                                <td>Main Office</td>
                                                <td>2024-01-15 09:30</td>
                                                <td><span class="badge bg-success">Processed</span></td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></button>
                                                    <button class="btn btn-sm btn-outline-secondary" title="Download"><i class="bi bi-download"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-file-earmark-excel text-success me-2"></i>
                                                        <div>
                                                            <div class="fw-bold">cashflow_branch1_jan_2024.xlsx</div>
                                                            <small class="text-muted">1.8 MB</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>Jan 2024</td>
                                                <td>Branch 1</td>
                                                <td>2024-01-14 14:20</td>
                                                <td><span class="badge bg-warning text-dark">Pending</span></td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></button>
                                                    <button class="btn btn-sm btn-outline-secondary" title="Download"><i class="bi bi-download"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-file-earmark-excel text-success me-2"></i>
                                                        <div>
                                                            <div class="fw-bold">cashflow_branch3_dec_2023.xlsx</div>
                                                            <small class="text-muted">2.1 MB</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>Dec 2023</td>
                                                <td>Branch 3</td>
                                                <td>2024-01-08 16:15</td>
                                                <td><span class="badge bg-danger">Error</span></td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></button>
                                                    <button class="btn btn-sm btn-outline-secondary" title="Download"><i class="bi bi-download"></i></button>
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

    <script src="{{ asset('assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/simple-datatables/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            const filesTable = document.querySelector('#table-files');
            if (filesTable && window.simpleDatatables) {
                new simpleDatatables.DataTable(filesTable);
            }

            // Form handling
            const uploadForm = document.getElementById('uploadForm');
            const btnUpload = document.getElementById('btnUpload');
            const btnClear = document.getElementById('btnClear');

            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Simulate upload process
                btnUpload.disabled = true;
                btnUpload.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Uploading...';

                setTimeout(() => {
                    btnUpload.disabled = false;
                    btnUpload.innerHTML = '<i class="bi bi-upload me-2"></i>Upload Cash Flow';

                    // Show success message (replace with actual upload logic)
                    alert('Cash Flow file uploaded successfully!');
                    uploadForm.reset();
                }, 2000);
            });

            btnClear.addEventListener('click', function() {
                uploadForm.reset();
            });

            // File size validation
            const fileInput = document.getElementById('excel_file');
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const maxSize = 10 * 1024 * 1024; // 10MB
                    if (file.size > maxSize) {
                        alert('File size exceeds 10MB limit. Please select a smaller file.');
                        this.value = '';
                    }
                }
            });
        });
    </script>
</body>

</html>
