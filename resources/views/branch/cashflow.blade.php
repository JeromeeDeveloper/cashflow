<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Cash Flow</title>

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
                    <p class="text-subtitle text-muted">Enter percent allocations and generate cash flow</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <input type="month" id="reporting_period" class="form-control" style="min-width: 220px;" value="{{ date('Y-m') }}">


                    <button id="btnGenerate" class="btn btn-primary"><i class="bi bi-lightning-charge me-2"></i>Generate</button>
                </div>
            </div>

            <div class="page-content">
                <section class="row">
                    <div class="col-12 col-xxl-9">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="mb-0">Cash Flow Input</h4>
                                <div class="input-group" style="max-width: 320px;">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search rows...">
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="table-cashflow">
                                        <thead>
                                            <tr>
                                                <th style="min-width:100px">GL Code</th>
                                                <th style="min-width:220px">GL Name</th>
                                                <th style="min-width:160px">Category</th>
                                                <th class="text-end" style="min-width:160px">Base Amount</th>
                                                <th class="text-end" style="min-width:140px">Percent %</th>
                                                <th class="text-end" style="min-width:180px">Cash Flow Amount</th>
                                                <th style="min-width:160px">Notes</th>
                                                <th class="text-end" style="min-width:90px">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cashflow-body">
                                            <tr>
                                                <td><input type="text" class="form-control form-control-sm" placeholder="1001"></td>
                                                <td><input type="text" class="form-control form-control-sm" placeholder="Cash on Hand"></td>
                                                <td>
                                                    <select class="form-select form-select-sm">
                                                        <option>Operating</option>
                                                        <option>Investing</option>
                                                        <option>Financing</option>
                                                    </select>
                                                </td>
                                                <td class="text-end"><input type="text" class="form-control form-control-sm text-end base-amount" value="0"></td>
                                                <td class="text-end"><input type="number" step="0.01" class="form-control form-control-sm text-end percent" value="0"></td>
                                                <td class="text-end"><input type="text" class="form-control form-control-sm text-end amount" value="0" readonly></td>
                                                <td><input type="text" class="form-control form-control-sm" placeholder=""></td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-danger btn-delete" title="Remove"><i class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light">
                                                <th colspan="5" class="text-end">Total Operating</th>
                                                <th class="text-end" id="totalOperating">0</th>
                                                <th colspan="2"></th>
                                            </tr>
                                            <tr class="table-light">
                                                <th colspan="5" class="text-end">Total Investing</th>
                                                <th class="text-end" id="totalInvesting">0</th>
                                                <th colspan="2"></th>
                                            </tr>
                                            <tr class="table-light">
                                                <th colspan="5" class="text-end">Total Financing</th>
                                                <th class="text-end" id="totalFinancing">0</th>
                                                <th colspan="2"></th>
                                            </tr>
                                            <tr class="table-secondary">
                                                <th colspan="5" class="text-end">Net Cash Flow</th>
                                                <th class="text-end" id="netCashflow">0</th>
                                                <th colspan="2"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xxl-3">
                        <div class="card">
                            <div class="card-header">
                                <h4>Summary</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Operating</span>
                                    <strong id="sumOperating">0</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Investing</span>
                                    <strong id="sumInvesting">0</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Financing</span>
                                    <strong id="sumFinancing">0</strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Net Cash Flow</span>
                                    <span class="fw-bold" id="sumNet">0</span>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4>Notes</h4>
                            </div>
                            <div class="card-body">
                                <textarea class="form-control" rows="6" placeholder="Optional notes for this period..."></textarea>
                                <div class="d-flex justify-content-end mt-3 gap-2">
                                    <button class="btn btn-light-secondary" id="btnClear"><i class="bi bi-eraser me-2"></i>Clear</button>
                                    <button class="btn btn-success" id="btnSave"><i class="bi bi-save me-2"></i>Save</button>
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
        function parseNumber(value) {
            if (!value) return 0;
            const cleaned = ('' + value).replace(/[,\s]/g, '');
            const num = parseFloat(cleaned);
            return isNaN(num) ? 0 : num;
        }

        function formatNumber(num) {
            const n = typeof num === 'number' ? num : parseNumber(num);
            return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function computeRow(row) {
            const baseInput = row.querySelector('.base-amount');
            const percentInput = row.querySelector('.percent');
            const amountInput = row.querySelector('.amount');
            const category = row.querySelector('select');

            const base = parseNumber(baseInput.value);
            const pct = parseNumber(percentInput.value);
            const amount = base * (pct / 100);
            amountInput.value = formatNumber(amount);

            return { category: category.value, amount };
        }

        function recomputeTotals() {
            const rows = Array.from(document.querySelectorAll('#cashflow-body tr'));
            let totals = { Operating: 0, Investing: 0, Financing: 0 };
            rows.forEach(row => {
                const result = computeRow(row);
                totals[result.category] += result.amount;
            });

            document.getElementById('totalOperating').textContent = formatNumber(totals.Operating);
            document.getElementById('totalInvesting').textContent = formatNumber(totals.Investing);
            document.getElementById('totalFinancing').textContent = formatNumber(totals.Financing);
            const net = totals.Operating + totals.Investing + totals.Financing;
            document.getElementById('netCashflow').textContent = formatNumber(net);

            document.getElementById('sumOperating').textContent = formatNumber(totals.Operating);
            document.getElementById('sumInvesting').textContent = formatNumber(totals.Investing);
            document.getElementById('sumFinancing').textContent = formatNumber(totals.Financing);
            document.getElementById('sumNet').textContent = formatNumber(net);
        }

        function addRow(prefill) {
            const body = document.getElementById('cashflow-body');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" class="form-control form-control-sm" placeholder="1001" value="${prefill?.code || ''}"></td>
                <td><input type="text" class="form-control form-control-sm" placeholder="Account name" value="${prefill?.name || ''}"></td>
                <td>
                    <select class="form-select form-select-sm">
                        <option ${prefill?.category === 'Operating' ? 'selected' : ''}>Operating</option>
                        <option ${prefill?.category === 'Investing' ? 'selected' : ''}>Investing</option>
                        <option ${prefill?.category === 'Financing' ? 'selected' : ''}>Financing</option>
                    </select>
                </td>
                <td class="text-end"><input type="text" class="form-control form-control-sm text-end base-amount" value="${prefill?.base || 0}"></td>
                <td class="text-end"><input type="number" step="0.01" class="form-control form-control-sm text-end percent" value="${prefill?.percent || 0}"></td>
                <td class="text-end"><input type="text" class="form-control form-control-sm text-end amount" value="0" readonly></td>
                <td><input type="text" class="form-control form-control-sm" value="${prefill?.notes || ''}"></td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-danger btn-delete" title="Remove"><i class="bi bi-trash"></i></button>
                </td>
            `;
            body.appendChild(tr);
            attachRowHandlers(tr);
            recomputeTotals();
        }

        function attachRowHandlers(row) {
            row.addEventListener('input', (e) => {
                if (e.target.classList.contains('base-amount') || e.target.classList.contains('percent')) {
                    recomputeTotals();
                }
            });
            row.querySelector('select').addEventListener('change', recomputeTotals);
            row.querySelector('.btn-delete').addEventListener('click', () => {
                row.remove();
                recomputeTotals();
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize existing rows
            document.querySelectorAll('#cashflow-body tr').forEach(attachRowHandlers);

            document.getElementById('btnGenerate').addEventListener('click', recomputeTotals);
            document.getElementById('btnAddRow').addEventListener('click', () => addRow());
            document.getElementById('btnClear').addEventListener('click', () => {
                document.getElementById('cashflow-body').innerHTML = '';
                addRow();
                recomputeTotals();
            });


            recomputeTotals();
        });
    </script>
</body>

</html>

