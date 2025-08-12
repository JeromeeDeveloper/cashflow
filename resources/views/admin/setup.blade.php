<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - GL Accounts & Cash Flow Mapping</title>

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
                <h3>Setup</h3>
                <p class="text-subtitle text-muted">Definition of GL Accounts and mapping Coop Chart of Accounts to Cash Flow Accounts</p>
            </div>

            <div class="page-content">
                <section class="row">
                    <div class="col-12 col-lg-7">
                        <div class="card">
                            <div class="card-header">
                                <h4>Define GL Accounts</h4>
                            </div>
                            <div class="card-body">
                                <form class="row g-3" action="javascript:void(0)" method="post">
                                    <div class="col-md-4">
                                        <label for="gl_code" class="form-label">Account Code</label>
                                        <input type="text" class="form-control" id="gl_code" placeholder="e.g. 1001" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label for="gl_name" class="form-label">Account Name</label>
                                        <input type="text" class="form-control" id="gl_name" placeholder="e.g. Cash on Hand" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="gl_type" class="form-label">Account Type</label>
                                        <select id="gl_type" class="form-select">
                                            <option>Asset</option>
                                            <option>Liability</option>
                                            <option>Equity</option>
                                            <option>Income</option>
                                            <option>Expense</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="parent_account" class="form-label">Parent Account</label>
                                        <select id="parent_account" class="form-select">
                                            <option value="">None</option>
                                            <option>1000 - Assets</option>
                                            <option>2000 - Liabilities</option>
                                            <option>3000 - Equity</option>
                                            <option>4000 - Income</option>
                                            <option>5000 - Expenses</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="cashflow_category" class="form-label">Cash Flow Category</label>
                                        <select id="cashflow_category" class="form-select">
                                            <option>Operating</option>
                                            <option>Investing</option>
                                            <option>Financing</option>
                                            <option>Not Applicable</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cashflow_account" class="form-label">Cash Flow Account</label>
                                        <select id="cashflow_account" class="form-select">
                                            <option value="">Select mapping...</option>
                                            <option>Cash Receipts</option>
                                            <option>Cash Payments</option>
                                            <option>Investments</option>
                                            <option>Financing Activities</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label for="gl_desc" class="form-label">Description</label>
                                        <textarea id="gl_desc" class="form-control" rows="2" placeholder="Optional"></textarea>
                                    </div>

                                    <div class="col-12 d-flex justify-content-end gap-2">
                                        <button class="btn btn-light-secondary" type="reset"><i class="bi bi-arrow-counterclockwise me-2"></i>Reset</button>
                                        <button class="btn btn-primary" type="submit"><i class="bi bi-save me-2"></i>Save Account</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="mb-0">GL Accounts</h4>
                                <div class="d-flex gap-2">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" placeholder="Search accounts...">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="table-gl-accounts">
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Name</th>
                                                <th>Type</th>
                                                <th>Cash Flow Category</th>
                                                <th>Cash Flow Account</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1001</td>
                                                <td>Cash on Hand</td>
                                                <td>Asset</td>
                                                <td>Operating</td>
                                                <td>Cash Receipts</td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>2101</td>
                                                <td>Accounts Payable</td>
                                                <td>Liability</td>
                                                <td>Operating</td>
                                                <td>Cash Payments</td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>5101</td>
                                                <td>Office Supplies Expense</td>
                                                <td>Expense</td>
                                                <td>Operating</td>
                                                <td>Cash Payments</td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                <h4>Cash Flow Mapping</h4>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">Map Coop Chart of Accounts to Cash Flow Accounts.</p>
                                <div class="table-responsive">
                                    <table class="table" id="table-mapping">
                                        <thead>
                                            <tr>
                                                <th>Coop Code</th>
                                                <th>Coop Account</th>
                                                <th>Cash Flow Account</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1001</td>
                                                <td>Cash on Hand</td>
                                                <td>
                                                    <select class="form-select form-select-sm">
                                                        <option>Cash Receipts</option>
                                                        <option>Cash Payments</option>
                                                        <option>Investments</option>
                                                        <option>Financing Activities</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>1205</td>
                                                <td>Short-term Investments</td>
                                                <td>
                                                    <select class="form-select form-select-sm">
                                                        <option>Cash Receipts</option>
                                                        <option selected>Investments</option>
                                                        <option>Financing Activities</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>2101</td>
                                                <td>Accounts Payable</td>
                                                <td>
                                                    <select class="form-select form-select-sm">
                                                        <option selected>Cash Payments</option>
                                                        <option>Cash Receipts</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button class="btn btn-primary"><i class="bi bi-check2 me-2"></i>Save Mapping</button>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4>Import Chart of Accounts (CSV)</h4>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 align-items-end">
                                    <div class="col-12">
                                        <label for="coa_file" class="form-label">Select file</label>
                                        <input class="form-control" type="file" id="coa_file" accept=".csv">
                                    </div>
                                    <div class="col-12 d-flex justify-content-end gap-2">
                                        <button class="btn btn-light-secondary" type="button"><i class="bi bi-download me-2"></i>Download Template</button>
                                        <button class="btn btn-success" type="button"><i class="bi bi-upload me-2"></i>Upload</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        <p>2021 &copy; Mazer</p>
                    </div>
                    <div class="float-end">
                        <p>Crafted with <span class="text-danger"><i class="bi bi-heart"></i></span> by <a href="http://ahmadsaugi.com">A. Saugi</a></p>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="{{ asset('assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/simple-datatables/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script>
        (function() {
            const glTable = document.querySelector('#table-gl-accounts');
            const mapTable = document.querySelector('#table-mapping');
            if (glTable && window.simpleDatatables) {
                new simpleDatatables.DataTable(glTable);
            }
            if (mapTable && window.simpleDatatables) {
                new simpleDatatables.DataTable(mapTable, { searchable: false });
            }
        })();
    </script>
</body>

</html>


