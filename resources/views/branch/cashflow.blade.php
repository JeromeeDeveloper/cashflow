<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cash Flow - {{ $branch->name ?? 'Branch' }}</title>

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
                    <h3>Cash Flow - {{ $branch->name ?? 'Branch' }}</h3>
                    <p class="text-subtitle text-muted">View cash flow data for your branch</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill bg-primary fs-6 px-3 py-2">
                        <i class="bi bi-building me-2"></i>{{ $branch->name ?? 'Branch' }}
                    </span>
                </div>
            </div>

            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <h4 class="mb-0">Cash Flow Summary</h4>
                                <div class="d-flex align-items-center flex-wrap gap-2 justify-content-end">
                                    <!-- Table Filters -->
                                    <div class="input-group" style="max-width: 280px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                                        <input type="month" id="table_start_period" class="form-control" value="{{ date('Y-m') }}">
                                    </div>
                                    <select id="table_period" class="form-select" style="max-width: 150px;">
                                        <option value="3">3 Months</option>
                                        <option value="6">6 Months</option>
                                        <option value="12">12 Months</option>
                                        <option value="36">3 Years</option>
                                        <option value="60">5 Years</option>
                                    </select>
                                    <button id="btnExport" class="btn btn-success"><i class="bi bi-download me-2"></i>Export Cashflow Planning Report</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-2 small text-muted" id="selectionSummary"></div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="table-cashflow">
                                        <thead id="cf-thead">
                                            <!-- Dynamic header will be built by JS to mirror Excel export -->
                                        </thead>
                                        <tbody>
                                            <!-- Table content will be populated dynamically -->
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
    <script src="{{ asset('assets/vendors/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Pass initial data from controller to JavaScript -->
    <script>
        const initialCashflows = @json($cashflows ?? []);
        console.log('Initial cashflows from controller:', initialCashflows);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // CSRF token for Laravel
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            // Enable Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Build header and load initial data
            buildTableHeader();
            let lastLoadedCashflows = Array.isArray(initialCashflows) ? initialCashflows : [];
            if (lastLoadedCashflows.length > 0) {
                updateTable(lastLoadedCashflows);
            } else {
                loadCashflows();
            }

            // Table filters
            const tableStartPeriod = document.getElementById('table_start_period');
            const tablePeriod = document.getElementById('table_period');
            const btnExport = document.getElementById('btnExport');

            if (tableStartPeriod) {
                tableStartPeriod.addEventListener('change', function() {
                    buildTableHeader();
                    loadCashflows();
                });
            }

            if (tablePeriod) {
                tablePeriod.addEventListener('change', function() {
                    buildTableHeader();
                    if (lastLoadedCashflows.length > 0) updateTable(lastLoadedCashflows);
                });
            }

            // Export button with SweetAlert
            if (btnExport) {
                btnExport.addEventListener('click', function() {
                    showExportConfirmation();
                });
            }

            function showExportConfirmation() {
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June','July', 'August', 'September', 'October', 'November', 'December'];
                const tableStartPeriod = document.getElementById('table_start_period');
                const tablePeriod = document.getElementById('table_period');

                if (!tableStartPeriod || !tablePeriod) {
                    console.error('Required form elements not found');
                    showAlert('Error: Required form elements not found', 'error');
                    return;
                }

                const currentMonthValue = tableStartPeriod.value || '{{ date('Y-m') }}';
                const [curYear, curMonth] = currentMonthValue.split('-');
                const currentPeriod = tablePeriod.value || '3';

                Swal.fire({
                    title: 'Export Cash Flow Report',
                    html: `
                        <div class="text-start">
                            <div class="mb-2">Start Period</div>
                            <input type="month" id="export_start_period" class="form-control" value="${curYear}-${curMonth}">
                            <div class="mt-3 mb-2">Period</div>
                            <select id="export_period" class="form-select">
                                <option value="3" ${currentPeriod==='3'?'selected':''}>3 Months</option>
                                <option value="6" ${currentPeriod==='6'?'selected':''}>6 Months</option>
                                <option value="12" ${currentPeriod==='12'?'selected':''}>12 Months</option>
                                <option value="36" ${currentPeriod==='36'?'selected':''}>3 Years</option>
                                <option value="60" ${currentPeriod==='60'?'selected':''}>5 Years</option>
                            </select>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-download me-2"></i>Export',
                    cancelButtonText: 'Cancel',
                    showLoaderOnConfirm: true,
                    preConfirm: () => exportCashflowsFromModal()
                });
            }

            function loadCashflows() {
                const monthInput = document.getElementById('table_start_period');
                if (!monthInput) {
                    console.error('table_start_period element not found');
                    return;
                }
                const [year, month] = monthInput.value.split('-');
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                                  'July', 'August', 'September', 'October', 'November', 'December'];

                const params = new URLSearchParams({
                    year: year,
                    month: monthNames[parseInt(month) - 1]
                });

                fetch(`{{ route('branch.cashflows.index') }}?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            lastLoadedCashflows = Array.isArray(data.data) ? data.data : [];
                            updateTable(lastLoadedCashflows);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading cashflows:', error);
                        showAlert('Error loading cashflows', 'error');
                    });
            }

            function updateTable(cashflows) {
                const tbody = document.querySelector('#table-cashflow tbody');
                const tablePeriod = document.getElementById('table_period');

                if (!tbody || !tablePeriod) {
                    console.error('Required elements not found in updateTable');
                    return;
                }

                tbody.innerHTML = '';

                const period = parseInt(tablePeriod.value || '3', 10);
                const { labels } = getPeriodLabels();

                if (!Array.isArray(cashflows) || cashflows.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="${4 + period}" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                No cash flow entries found
                            </td>
                        </tr>
                    `;
                    return;
                }

                // Split by type
                const receipts = cashflows.filter(c => (c.cashflow_type || '').toLowerCase() === 'receipts');
                const disbursements = cashflows.filter(c => (c.cashflow_type || '').toLowerCase() === 'disbursements');

                // Beginning balance = sum of all receipts actual_amount
                const beginningBalance = receipts.reduce((sum, c) => sum + (parseFloat(c.actual_amount) || 0), 0);

                // Helper to build a row
                const makeRow = (cells) => `<tr>${cells.join('')}</tr>`;
                const th = (content, extra='') => `<th ${extra}>${content}</th>`;
                const td = (content, cls='') => `<td class="${cls}">${content}</td>`;

                // Row: CASH BEGINNING BALANCE
                const beginningCells = [
                    td('CASH BEGINNING BALANCE'),
                    td(formatNumber(beginningBalance), 'text-end'),
                    td('-', 'text-end')
                ];
                for (let i = 0; i < period; i++) beginningCells.push(td(formatNumber(beginningBalance), 'text-end'));
                beginningCells.push(td(formatNumber(beginningBalance * period), 'text-end'));
                tbody.insertAdjacentHTML('beforeend', makeRow(beginningCells));

                // Section: ADD: RECEIPTS
                const sectionEmpty = new Array(period + 3).fill(td('-', 'text-end')).join('');
                tbody.insertAdjacentHTML('beforeend', makeRow([
                    td('ADD: RECEIPTS'),
                    td('-', 'text-end'),
                    td('-', 'text-end'),
                    ...Array.from({length: period}, () => td('-', 'text-end')),
                    td('-', 'text-end')
                ]));

                // Totals per period
                const receiptsTotals = Array.from({length: period}, () => 0);
                let receiptsGrand = 0;

                // Receipt rows
                receipts.forEach(c => {
                    const name = (c.gl_account && c.gl_account.parent_id) ? `&gt; ${c.gl_account.account_name}` : (c.gl_account?.account_name || c.account_name || 'N/A');
                    const actual = parseFloat(c.actual_amount) || 0;
                    const proj = parseFloat(c.projection_percentage) || 0;
                    const inputsCell = `<input type="number" class="form-control form-control-sm text-end projection-input" value="${proj}" min="0" max="100" step="0.01" data-id="${c.id}" style="width: 80px;">`;

                    const projections = [];
                    let current = actual;
                    for (let i = 0; i < period; i++) {
                        current = current * (1 + (proj / 100));
                        projections.push(current);
                        receiptsTotals[i] += current;
                    }
                    const sumProj = projections.reduce((a,b) => a+b, 0);
                    receiptsGrand += sumProj;

                    const cells = [
                        td(name),
                        td(formatNumber(actual), 'text-end'),
                        td(inputsCell, 'text-end')
                    ];
                    projections.forEach(v => cells.push(td(formatNumber(v), 'text-end')));
                    cells.push(td(formatNumber(sumProj), 'text-end'));
                    tbody.insertAdjacentHTML('beforeend', makeRow(cells));
                });

                // TOTAL CASH AVAILABLE
                const tcaCells = [ td('TOTAL CASH AVAILABLE'), td(formatNumber(beginningBalance), 'text-end'), td('-', 'text-end') ];
                for (let i = 0; i < period; i++) tcaCells.push(td(formatNumber(beginningBalance + receiptsTotals[i]), 'text-end'));
                tcaCells.push(td(formatNumber(beginningBalance * period + receiptsGrand), 'text-end'));
                tbody.insertAdjacentHTML('beforeend', makeRow(tcaCells));

                // Section: LESS: DISBURSEMENTS
                tbody.insertAdjacentHTML('beforeend', makeRow([
                    td('LESS: DISBURSEMENTS'),
                    td('-', 'text-end'),
                    td('-', 'text-end'),
                    ...Array.from({length: period}, () => td('-', 'text-end')),
                    td('-', 'text-end')
                ]));

                const disbTotals = Array.from({length: period}, () => 0);
                let disbGrand = 0;

                disbursements.forEach(c => {
                    const name = (c.gl_account && c.gl_account.parent_id) ? `&gt; ${c.gl_account.account_name}` : (c.gl_account?.account_name || c.account_name || 'N/A');
                    const actual = parseFloat(c.actual_amount) || 0;
                    const proj = parseFloat(c.projection_percentage) || 0;
                    const inputsCell = `<input type=\"number\" class=\"form-control form-control-sm text-end projection-input\" value=\"${proj}\" min=\"0\" max=\"100\" step=\"0.01\" data-id=\"${c.id}\" style=\"width: 80px;\">`;

                    const projections = [];
                    let current = actual;
                    for (let i = 0; i < period; i++) {
                        current = current * (1 + (proj / 100));
                        projections.push(current);
                        disbTotals[i] += current;
                    }
                    const sumProj = projections.reduce((a,b) => a+b, 0);
                    disbGrand += sumProj;

                    const cells = [
                        td(name),
                        td(formatNumber(actual), 'text-end'),
                        td(inputsCell, 'text-end')
                    ];
                    projections.forEach(v => cells.push(td(formatNumber(v), 'text-end')));
                    cells.push(td(formatNumber(sumProj), 'text-end'));
                    tbody.insertAdjacentHTML('beforeend', makeRow(cells));
                });

                // TOTAL DISBURSEMENTS
                const tdCells = [ td('TOTAL DISBURSEMENTS'), td(formatNumber(disbursements.reduce((s, c) => s + (parseFloat(c.actual_amount)||0), 0)), 'text-end'), td('-', 'text-end') ];
                for (let i = 0; i < period; i++) tdCells.push(td(formatNumber(disbTotals[i]), 'text-end'));
                tdCells.push(td(formatNumber(disbGrand), 'text-end'));
                tbody.insertAdjacentHTML('beforeend', makeRow(tdCells));

                // CASH ENDING BALANCE (mirror export formula)
                const cebCells = [ td('CASH ENDING BALANCE'), td(formatNumber(beginningBalance), 'text-end'), td('-', 'text-end') ];
                for (let i = 0; i < period; i++) {
                    const value = beginningBalance + (beginningBalance + receiptsTotals[i]) - disbTotals[i];
                    cebCells.push(td(formatNumber(value), 'text-end'));
                }
                const cebTotal = (beginningBalance * period) + ((beginningBalance * period) + receiptsGrand) - disbGrand;
                cebCells.push(td(formatNumber(cebTotal), 'text-end'));
                tbody.insertAdjacentHTML('beforeend', makeRow(cebCells));

                // Attach listeners for dynamic elements
                attachEventListeners();
            }

            function getPeriodLabels() {
                const monthInput = document.getElementById('table_start_period');
                const exportPeriod = document.getElementById('table_period');

                if (!monthInput || !exportPeriod) {
                    console.error('Required form elements not found in getPeriodLabels');
                    return { labels: ['January', 'February', 'March'], selectedLabel: 'January' };
                }

                const [yearStr, monthStr] = (monthInput.value || '').split('-');
                const period = parseInt(exportPeriod.value || '3', 10);
                const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                const startIndex = Math.max(0, (parseInt(monthStr, 10) || 1) - 1);
                const labels = [];
                for (let i = 0; i < period; i++) labels.push(months[(startIndex + i) % 12]);
                const selectedLabel = months[startIndex];
                return { labels, selectedLabel };
            }

            function buildTableHeader() {
                const thead = document.getElementById('cf-thead');
                const tablePeriod = document.getElementById('table_period');

                if (!thead || !tablePeriod) {
                    console.error('Required elements not found in buildTableHeader');
                    return;
                }

                const exportPeriod = parseInt(tablePeriod.value || '3', 10);
                const { labels, selectedLabel } = getPeriodLabels();

                const topRow = document.createElement('tr');
                topRow.innerHTML = `
                    <th rowspan="2">PARTICULARS</th>
                    <th rowspan="2" class="text-end">ACTUAL</th>
                    <th rowspan="2" class="text-end">PROJECTION %</th>
                    <th colspan="${exportPeriod}" class="text-center">CASH PROJECTION/PLAN</th>
                    <th rowspan="2" class="text-end">TOTAL</th>
                `;

                const secondRow = document.createElement('tr');
                secondRow.innerHTML = labels.map(l => `<th class="text-end">${l}</th>`).join('');

                thead.innerHTML = '';
                thead.appendChild(topRow);
                thead.appendChild(secondRow);
            }

            function attachEventListeners() {
                // Projection input changes
                document.querySelectorAll('.projection-input').forEach(input => {
                    input.addEventListener('change', function() {
                        const id = this.getAttribute('data-id');
                        const newValue = parseFloat(this.value) || 0;
                        updateProjectionPercentage(id, newValue);
                    });
                });
            }

            function updateProjectionPercentage(id, newValue) {
                fetch(`{{ url('branch/cashflows') }}/${id}/projection`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        projection_percentage: newValue
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        showAlert(data.message || 'Failed to update projection percentage', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error updating projection percentage:', error);
                    showAlert('Error updating projection percentage', 'error');
                });
            }

            function exportCashflowsFromModal() {
                const monthStr = (document.getElementById('export_start_period').value || '{{ date('Y-m') }}');
                const [year, month] = monthStr.split('-');
                const periodValue = (document.getElementById('export_period').value || '3');
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June','July', 'August', 'September', 'October', 'November', 'December'];

                const params = new URLSearchParams({
                    year: year,
                    month: monthNames[parseInt(month) - 1],
                    period: periodValue
                });

                const downloadUrl = `{{ route('branch.cashflows.export') }}?${params}`;

                return fetch(downloadUrl)
                    .then(response => {
                        if (response.ok) {
                            return response.blob();
                        }
                        throw new Error('Export failed');
                    })
                    .then(blob => {
                        // Create blob URL and download
                        const url = window.URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;

                        const pVal = parseInt(periodValue);
                        const periodText = pVal <= 12 ? `${pVal}months` : `${pVal}years`;
                        link.download = `cashflow_${monthNames[parseInt(month) - 1]}_${year}_${periodText}.xlsx`;

                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        // Clean up blob URL
                        window.URL.revokeObjectURL(url);

                        // Show success message
                        return Swal.fire({
                            title: 'Export Successful!',
                            text: 'Your cash flow report has been downloaded.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    })
                    .catch(error => {
                        console.error('Export error:', error);
                        return Swal.fire({
                            title: 'Export Failed',
                            text: 'There was an error generating the export. Please try again.',
                            icon: 'error'
                        });
                    });
            }

            function formatNumber(number) {
                return parseFloat(number || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function showAlert(message, type = 'info') {
                Swal.fire({
                    title: type.charAt(0).toUpperCase() + type.slice(1),
                    text: message,
                    icon: type,
                    timer: type === 'success' ? 2000 : undefined,
                    showConfirmButton: type !== 'success'
                });
            }
        });
    </script>
</body>

</html>

