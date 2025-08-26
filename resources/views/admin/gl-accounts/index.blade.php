<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GL Accounts Management - Admin</title>

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
    <style>
        .account-hierarchy {
            margin-left: 20px;
            border-left: 2px solid #e9ecef;
            padding-left: 15px;
        }

        .parent-account {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            font-weight: 600;
        }

        .parent-account.has-children {
            background-color: #e3f2fd;
            border-left: 4px solid #1976d2;
        }





        .account-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .indicator-parent { background-color: #007bff; }
        .indicator-child { background-color: #28a745; }
        .indicator-single { background-color: #ffc107; }


        /* Relationship status indicators */
        .relationship-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
            padding: 2px 6px;
            border-radius: 12px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .relationship-status.has-parent {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }

                .relationship-status.has-children {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }

        /* Enhanced dropdown styling */
        .dropdown-header {
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
        }

        .dropdown-item:hover {
            background-color: #e9ecef;
        }

        .dropdown-item i {
            width: 16px;
            text-align: center;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.875rem;
            }

            .table th, .table td {
                padding: 0.5rem 0.25rem;
                vertical-align: middle;
            }

            .badge {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }

            .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }

            .btn-group {
                display: flex;
                flex-direction: column;
                width: 100%;
            }

            .btn-group .btn {
                margin-bottom: 0.25rem;
            }
        }

        /* Fix table alignment for parent-child structure */
        .table th, .table td {
            vertical-align: middle;
            padding: 0.75rem;
        }

        .child-account td:first-child {
            padding-left: 2rem;
        }

        .child-account {
            position: relative;
        }

        /* Simple, clean styling */
        .card {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

        .table {
            border: 1px solid #dee2e6;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }

        .btn {
            border-radius: 0.375rem;
        }

        .form-control, .form-select {
            border-radius: 0.375rem;
        }

        /* Drag and Drop Styles */
        .drag-handle {
            cursor: grab;
            user-select: none;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .selected-account-row {
            transition: all 0.2s ease;
        }

        .selected-account-row.dragging {
            opacity: 0.5;
            transform: rotate(2deg);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .selected-account-row.drag-over {
            border-top: 3px solid #007bff;
            border-bottom: 3px solid #007bff;
            background-color: #f8f9fa;
        }

        .cursor-grab {
            cursor: grab;
        }

        .cursor-grab:active {
            cursor: grabbing;
        }
    </style>
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
                    <h3>GL Accounts Management</h3>
                    <p class="text-subtitle text-muted">Manage which GL accounts are selected for cashflow display</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill bg-primary fs-6 px-3 py-2">
                        <i class="bi bi-gear me-2"></i>Admin Panel
                    </span>
                </div>
            </div>

            <div class="page-content">
                <!-- Statistics Cards -->
                <section class="row">
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card">
                            <div class="card-body py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                        <div class="stats-icon purple mb-2">
                                            <i class="bi bi-list-ul"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7 text-start">
                                        <h6 class="text-muted font-semibold">Total Accounts</h6>
                                        <h6 class="font-extrabold mb-0" id="totalAccounts">{{ $glAccounts->count() }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card">
                            <div class="card-body py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                        <div class="stats-icon blue mb-2">
                                            <i class="bi bi-check-circle"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7 text-start">
                                        <h6 class="text-muted font-semibold">Selected</h6>
                                        <h6 class="font-extrabold mb-0" id="selectedAccounts">{{ $selectedAccounts->count() }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card">
                            <div class="card-body py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                        <div class="stats-icon green mb-2">
                                            <i class="bi bi-percent"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7 text-start">
                                        <h6 class="text-muted font-semibold">Selection %</h6>
                                        <h6 class="font-extrabold mb-0" id="selectionPercentage">
                                            {{ $glAccounts->count() > 0 ? round(($selectedAccounts->count() / $glAccounts->count()) * 100, 2) : 0 }}%
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card">
                            <div class="card-body py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                        <div class="stats-icon orange mb-2">
                                            <i class="bi bi-folder"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7 text-start">
                                        <h6 class="text-muted font-semibold">Parent Accounts</h6>
                                        <h6 class="font-extrabold mb-0" id="parentAccounts">{{ $parentAccounts->count() }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                   <!-- All GL Accounts Management -->
                   <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <h4 class="mb-0">All GL Accounts</h4>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                                    <i class="bi bi-plus-circle me-2"></i>Add GL Account
                                </button>
                                <div class="d-flex align-items-center flex-wrap gap-2 justify-content-end">
                                    <!-- Import -->
                                    <form action="{{ route('admin.gl-accounts.import') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2" id="adminImportForm">
                                        @csrf
                                        <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control" style="max-width: 260px;" required id="adminImportFile">
                                        <button type="submit" class="btn btn-outline-primary" id="adminImportBtn">
                                            <span class="btn-text"><i class="bi bi-upload me-2"></i>Import Excel</span>
                                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true" id="adminImportSpinner"></span>
                                        </button>
                                    </form>
                                    <!-- Search -->
                                    <div class="input-group" style="max-width: 300px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                        <input type="text" id="searchInput" class="form-control" placeholder="Search accounts...">
                                    </div>

                                    <!-- Filter -->
                                    <select id="accountTypeFilter" class="form-select" style="max-width: 150px;">
                                        <option value="">All Types</option>
                                        <option value="parent">Parent</option>
                                        <option value="single">Single</option>
                                        <option value="child">Child</option>
                                    </select>

                                    <select id="cashflowTypeFilter" class="form-select" style="max-width: 150px;">
                                        <option value="">All Cashflow Types</option>
                                        <option value="receipts">Receipts</option>
                                        <option value="disbursements">Disbursements</option>
                                    </select>

                                    <select id="selectionFilter" class="form-select" style="max-width: 150px;">
                                        <option value="">All Status</option>
                                        <option value="1">Selected</option>
                                        <option value="0">Not Selected</option>
                                    </select>

                                    <!-- Bulk Actions -->
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-success" id="btnSelectAll">
                                            <i class="bi bi-check-all me-2"></i>Select all GL Accounts
                                        </button>
                                        <button type="button" class="btn btn-primary" id="btnMarkSelected">
                                            <i class="bi bi-check2-circle me-2"></i>Mark as Selected
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="btnMarkUnselected">
                                            <i class="bi bi-x-circle me-2"></i>Mark as Not Selected
                                        </button>
                                    </div>


                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="table-gl-accounts">
                                        <thead>
                                            <tr>
                                                <th class="text-center">
                                                    <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                                                </th>
                                                <th class="text-center">Account Code</th>
                                                <th class="text-center">Account Name</th>
                                                <th class="text-center">Account Type</th>
                                                <th class="text-center">Cashflow Type</th>
                                                <th class="text-center">Parent Account</th>
                                                <th class="text-center">Level</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Selection</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($glAccounts as $account)
                                                <tr class="{{ $account->parent_id ? 'child-account' : 'parent-account' }} {{ $account->children->count() > 0 ? 'has-children' : '' }}" data-id="{{ $account->id }}">
                                                    <td>
                                                        <input type="checkbox" class="form-check-input account-checkbox" value="{{ $account->id }}">
                                                    </td>
                                                    <td>
                                                        <span class="account-indicator indicator-{{ $account->account_type }}"></span>
                                                        <span class="fw-medium text-primary">{{ $account->account_code }}</span>
                                                    </td>
                                                    <td>
                                                        @if($account->parent_id)
                                                            <div class="account-hierarchy">
                                                                {{ $account->account_name }}
                                                            </div>
                                                        @else
                                                            <strong>{{ $account->account_name }}</strong>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @switch($account->account_type)
                                                            @case('parent')
                                                                <span class="badge bg-primary">{{ $account->account_type }}</span>
                                                                @break

                                                            @default
                                                                <span class="badge bg-secondary">{{ $account->account_type }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td>
                                                        @php
                                                            $cashflowType = $account->cashflow_type ?? null;
                                                            $actualType = $account->getMostCommonCashflowType();
                                                            $displayType = $cashflowType ?: ($actualType ?: 'disbursements');
                                                        @endphp
                                                        @if($displayType === 'receipts')
                                                            <span class="badge bg-success">Receipts</span>
                                                        @else
                                                            <span class="badge bg-danger">Disbursements</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($account->parent)
                                                            <span class="text-muted">{{ $account->parent->account_name }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">{{ $account->level }}</span>
                                                        @if($account->parent || $account->children->count() > 0)
                                                            <div class="mt-1">
                                                                @if($account->children->count() > 0)
                                                                    <span class="relationship-status has-children">
                                                                        <i class="bi bi-arrow-down"></i>{{ $account->children->count() }} Children
                                                                    </span>
                                                                @elseif($account->parent)
                                                                    <span class="relationship-status has-parent">
                                                                        <i class="bi bi-arrow-up"></i>Child of {{ $account->parent->account_name }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($account->is_active)
                                                            <span class="badge bg-success">Active</span>
                                                        @else
                                                            <span class="badge bg-danger">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input selection-toggle" type="checkbox"
                                                                   data-id="{{ $account->id }}"
                                                                   {{ $account->is_selected ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="selection-{{ $account->id }}">
                                                                {{ $account->is_selected ? 'Selected' : 'Not Selected' }}
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-outline-primary btn-view" title="View Details" data-id="{{ $account->id }}">
                                                            <i class="bi bi-eye"></i>
                                                            <span class="ms-1">view</span>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-warning btn-edit" title="Edit Account" data-id="{{ $account->id }}">
                                                            <i class="bi bi-pencil"></i>
                                                            <span class="ms-1">edit</span>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger merge-accounts-btn" title="Merge Accounts" data-id="{{ $account->id }}">
                                                            <i class="bi bi-arrow"></i>
                                                            <span class="ms-1">merge</span>
                                                        </button>
                                                        <div class="btn-group" role="group">
                                                            <button class="btn btn-sm btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" title="Parent-Child Actions">
                                                                <i class="bi bi-diagram-3"></i>
                                                                <span class="ms-1">Relationships</span>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><h6 class="dropdown-header">Parenting</h6></li>
                                                                <li>
                                                                    <a class="dropdown-item make-parent-btn" href="#" data-id="{{ $account->id }}">
                                                                        <i class="bi bi-plus-circle text-success me-2"></i>
                                                                        {{ $account->children->count() > 0 || $account->account_type === 'parent' ? 'Add Additional Children' : 'Make This Account a Parent' }}
                                                                    </a>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                @if($account->parent)
                                                                    <li><h6 class="dropdown-header">Current Parent</h6></li>
                                                                    <li><a class="dropdown-item remove-parent-btn" href="#" data-id="{{ $account->id }}">
                                                                        <i class="bi bi-dash-circle text-warning me-2"></i>Remove Parent ({{ $account->parent->account_name }})
                                                                    </a></li>
                                                                @endif
                                                                @if($account->children->count() > 0)
                                                                    <li><h6 class="dropdown-header">Current Children ({{ $account->children->count() }})</h6></li>
                                                                    <li><a class="dropdown-item remove-children-btn" href="#" data-id="{{ $account->id }}">
                                                                        <i class="bi bi-dash-circle text-warning me-2"></i>Remove All Children
                                                                    </a></li>
                                                                @endif
                                                                {{-- @if($account->parent || $account->children->count() > 0)
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li><a class="dropdown-item remove-all-relationships-btn" href="#" data-id="{{ $account->id }}">
                                                                        <i class="bi bi-trash text-danger me-2"></i>Remove All Relationships
                                                                    </a></li>
                                                                @endif --}}
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @if($account->children->count() > 0)
                                                    @foreach($account->children as $child)
                                                        <tr class="child-account" data-id="{{ $child->id }}">
                                                            <td>
                                                                <input type="checkbox" class="form-check-input account-checkbox" value="{{ $child->id }}">
                                                            </td>
                                                            <td>
                                                                <span class="account-indicator indicator-{{ $child->account_type }}"></span>
                                                                <span class="fw-medium text-primary">{{ $child->account_code }}</span>
                                                            </td>
                                                            <td>
                                                                <div class="account-hierarchy">
                                                                    {{ $child->account_name }}
                                                                </div>
                                                            </td>
                                                            <td>
                                                                @switch($child->account_type)
                                                                    @case('parent')
                                                                        <span class="badge bg-primary">{{ $child->account_type }}</span>
                                                                        @break
                                                                    @default
                                                                        <span class="badge bg-secondary">{{ $child->account_type }}</span>
                                                                @endswitch
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $childCashflowType = $child->cashflow_type ?? null;
                                                                    $childActualType = $child->getMostCommonCashflowType();
                                                                    $childDisplayType = $childCashflowType ?: ($childActualType ?: 'disbursements');
                                                                @endphp
                                                                @if($childDisplayType === 'receipts')
                                                                    <span class="badge bg-success">Receipts</span>
                                                                @else
                                                                    <span class="badge bg-danger">Disbursements</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="text-muted">{{ $account->account_name }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-light text-dark">{{ $child->level }}</span>
                                                            </td>
                                                            <td>
                                                                @if($child->is_active)
                                                                    <span class="badge bg-success">Active</span>
                                                                @else
                                                                    <span class="badge bg-danger">Inactive</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input selection-toggle" type="checkbox"
                                                                           data-id="{{ $child->id }}"
                                                                           {{ $child->is_selected ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="selection-{{ $child->id }}">
                                                                        {{ $child->is_selected ? 'Selected' : 'Not Selected' }}
                                                                    </label>
                                                                </div>
                                                            </td>
                                                                                                                         <td class="text-center">
                                                                 <button class="btn btn-sm btn-outline-primary btn-view" title="View Details" data-id="{{ $child->id }}">
                                                                     <i class="bi bi-eye"></i>
                                                                     <span class="ms-1">view</span>
                                                                 </button>
                                                                 <button class="btn btn-sm btn-outline-warning btn-edit" title="Edit Account" data-id="{{ $child->id }}">
                                                                     <i class="bi bi-pencil"></i>
                                                                     <span class="ms-1">edit</span>
                                                                 </button>
                                                             </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @empty
                                                <tr>
                                                    <td colspan="10" class="text-center text-muted py-4">
                                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                                        No GL accounts found
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

                <!-- Selected Accounts Table -->
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <h4 class="mb-0">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Selected Accounts ({{ $selectedAccounts->count() }})
                                </h4>
                                <div class="d-flex align-items-center flex-wrap gap-2 justify-content-end">
                                    <button type="button" class="btn btn-warning" id="btnDeselectAll">
                                        <i class="bi bi-x-circle me-2"></i>Deselect All
                                    </button>
                                    <span class="badge rounded-pill bg-success text-white d-flex align-items-center px-3">
                                        <i class="bi bi-check-circle me-2"></i>
                                        Available in Cashflow
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="table-selected-accounts">
                                        <thead class="text-center align-items-center">
                                            <tr>
                                                <th class="text-center" style="width: 50px;">
                                                    <i class="bi bi-grip-vertical text-muted"></i>
                                                </th>
                                                <th class="text-center">Account Code</th>
                                                <th class="text-center">Account Name</th>
                                                <th class="text-center">Account Type</th>
                                                <th class="text-center">Cashflow Type</th>
                                                <th class="text-center">Parent Account</th>
                                                <th class="text-center">Level</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Selection</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $selectedIds = $selectedAccounts->pluck('id')->toArray();
                                            @endphp
                                            @forelse($selectedAccounts as $account)
                                                @if($account->parent_id && in_array($account->parent_id, $selectedIds))
                                                    @continue
                                                @endif
                                                <tr class="selected-account-row {{ $account->parent_id ? 'child-account' : 'parent-account' }}"
                                                    data-id="{{ $account->id }}"
                                                    data-order="{{ $loop->index }}"
                                                    draggable="true">
                                                    <td class="drag-handle text-center">
                                                        <i class="bi bi-grip-vertical text-muted cursor-grab"></i>
                                                    </td>
                                                    <td>
                                                        <span class="account-indicator indicator-{{ $account->account_type }}"></span>
                                                        <span class="fw-medium text-primary">{{ $account->account_code }}</span>
                                                    </td>
                                                    <td>
                                                        @if($account->parent_id)
                                                            <div class="account-hierarchy">
                                                                {{ $account->account_name }}
                                                            </div>
                                                        @else
                                                            <strong>{{ $account->account_name }}</strong>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @switch($account->account_type)
                                                            @case('parent')
                                                                <span class="badge bg-primary">{{ $account->account_type }}</span>
                                                                @break
                                                            @case('single')
                                                                <span class="badge bg-success">{{ $account->account_type }}</span>
                                                                @break
                                                            @case('child')
                                                                <span class="badge bg-info">{{ $account->account_type }}</span>
                                                                @break
                                                            @default
                                                                <span class="badge bg-secondary">{{ $account->account_type }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td>
                                                        @php
                                                            $cashflowType = $account->cashflow_type ?? null;
                                                            $actualType = $account->getMostCommonCashflowType();
                                                            $displayType = $cashflowType ?: ($actualType ?: 'disbursements');
                                                        @endphp
                                                        @if($displayType === 'receipts')
                                                            <span class="badge bg-success">Receipts</span>
                                                        @else
                                                            <span class="badge bg-danger">Disbursements</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($account->parent)
                                                            <span class="text-muted">{{ $account->parent->account_name }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">{{ $account->level }}</span>
                                                        @if($account->parent || $account->children->count() > 0)
                                                            <div class="mt-1">
                                                                @if($account->children->count() > 0)
                                                                    <span class="relationship-status has-children">
                                                                        <i class="bi bi-arrow-down"></i>{{ $account->children->count() }} Children
                                                                    </span>
                                                                @elseif($account->parent)
                                                                    <span class="relationship-status has-parent">
                                                                        <i class="bi bi-arrow-up"></i>Child of {{ $account->parent->account_name }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($account->is_active)
                                                            <span class="badge bg-success">Active</span>
                                                        @else
                                                            <span class="badge bg-danger">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="form-check form-switch d-inline-block">
                                                            <input class="form-check-input selection-toggle" type="checkbox"
                                                                   data-id="{{ $account->id }}"
                                                                   {{ $account->is_selected ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @if($account->children->count() > 0)
                                                    @foreach($account->children->where('is_selected', true) as $child)
                                                        <tr class="child-account selected-account-row"
                                                            data-id="{{ $child->id }}"
                                                            data-order="{{ $loop->index }}"
                                                            draggable="true">
                                                            <td class="drag-handle text-center">
                                                                <i class="bi bi-grip-vertical text-muted cursor-grab"></i>
                                                            </td>
                                                            <td>
                                                                <span class="account-indicator indicator-{{ $child->account_type }}"></span>
                                                                <span class="fw-medium text-primary">{{ $child->account_code }}</span>
                                                            </td>
                                                            <td>
                                                                <div class="account-hierarchy">
                                                                    {{ $child->account_name }}
                                                                </div>
                                                            </td>
                                                            <td>
                                                                @switch($child->account_type)
                                                                    @case('parent')
                                                                        <span class="badge bg-primary">{{ $child->account_type }}</span>
                                                                        @break
                                                                    @case('child')
                                                                        <span class="badge bg-success">{{ $child->account_type }}</span>
                                                                        @break
                                                                    @case('single')
                                                                        <span class="badge bg-info">{{ $child->account_type }}</span>
                                                                        @break
                                                                    @default
                                                                        <span class="badge bg-secondary">{{ $child->account_type }}</span>
                                                                @endswitch
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $childCashflowType = $child->cashflow_type ?? 'disbursements';
                                                                    $childActualType = $child->getMostCommonCashflowType();
                                                                    $childDisplayType = $childActualType ?: $childCashflowType;
                                                                @endphp
                                                                @if($childDisplayType === 'receipts')
                                                                    <span class="badge bg-success">Receipts</span>
                                                                @else
                                                                    <span class="badge bg-danger">Disbursements</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="text-muted">{{ $account->account_name }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-light text-dark">{{ $child->level }}</span>
                                                            </td>
                                                            <td>
                                                                @if($child->is_active)
                                                                    <span class="badge bg-success">Active</span>
                                                                @else
                                                                    <span class="badge bg-danger">Inactive</span>
                                                                @endif
                                                            </td>
                                                                                                                         <td class="text-center">
                                                                 <div class="form-check form-switch d-inline-block">
                                                                     <input class="form-check-input selection-toggle" type="checkbox"
                                                                            data-id="{{ $child->id }}"
                                                                            {{ $child->is_selected ? 'checked' : '' }}>
                                                                 </div>
                                                             </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted py-4">
                                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                                        No selected accounts found
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

            <!-- Add Account Modal -->
            <div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addAccountModalLabel">Add GL Account</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="addAccountForm">
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="add_account_code" class="form-label">Account Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="add_account_code" name="account_code" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="add_account_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="add_account_name" name="account_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="add_cashflow_type" class="form-label">Cashflow Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="add_cashflow_type" name="cashflow_type" required>
                                            <option value="receipts">Receipts</option>
                                            <option value="disbursements">Disbursements</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="add_level" class="form-label">Level</label>
                                        <input type="text" class="form-control" id="add_level" name="level" value="1" placeholder="Enter level">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="add_is_active" name="is_active" checked>
                                            <label class="form-check-label" for="add_is_active">Active</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Selection Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="add_is_selected" name="is_selected">
                                            <label class="form-check-label" for="add_is_selected">Selected for Cashflow</label>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Create Account</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- View Account Details Modal -->
            <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewModalLabel">GL Account Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Account Code:</label>
                                    <p id="view_account_code"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Account Name:</label>
                                    <p id="view_account_name"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Account Type:</label>
                                    <p id="view_account_type"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Cashflow Type:</label>
                                    <p id="view_cashflow_type"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Parent Account:</label>
                                    <p id="view_parent"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Level:</label>
                                    <p id="view_level"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Status:</label>
                                    <p id="view_status"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Selection Status:</label>
                                    <p id="view_selection"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Children Count:</label>
                                    <p id="view_children_count"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Created:</label>
                                    <p id="view_created"></p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Account Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Edit GL Account</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="editAccountForm">
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="edit_account_code" class="form-label">Account Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_account_code" name="account_code" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_account_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_account_name" name="account_name" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="edit_cashflow_type" class="form-label">Cashflow Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="edit_cashflow_type" name="cashflow_type" required>
                                            <option value="receipts">Receipts</option>
                                            <option value="disbursements">Disbursements</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="edit_level" class="form-label">Level</label>
                                        <input type="text" class="form-control" id="edit_level" name="level" placeholder="Enter level">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_is_active" class="form-label">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                            <label class="form-check-label" for="edit_is_active">Active</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_is_selected" class="form-label">Selection Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="edit_is_selected" name="is_selected">
                                            <label class="form-check-label" for="edit_is_selected">Selected for Cashflow</label>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Update Account</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Make Parent Modal -->
            <div class="modal fade" id="makeParentModal" tabindex="-1" aria-labelledby="makeParentModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="makeParentModalLabel">
                                <i class="bi bi-diagram-3 me-2"></i>Make Account a Parent
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="makeParentForm">
                            <div class="modal-body">
                                <!-- Parent Account Info -->
                                <div class="alert alert-primary border-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-star-fill text-warning fs-4 me-3"></i>
                                        <div>
                                            <h6 class="mb-1">Parent Account</h6>
                                            <p class="mb-0 fw-bold" id="parentAccountName"></p>
                                            <small class="text-muted">This account will become a parent and can have multiple child accounts.</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Instructions -->
                                <div class="alert alert-info border-0">
                                    <h6 class="alert-heading">
                                        <i class="bi bi-lightbulb me-2"></i>How Parent-Child Relationships Work
                                    </h6>
                                    <ul class="mb-0">
                                        <li><strong>Parent Account:</strong> The main account that groups related accounts together</li>
                                        <li><strong>Child Accounts:</strong> Sub-accounts that belong to the parent account</li>
                                        <li><strong>Benefits:</strong> Better organization, easier reporting, and cleaner cashflow views</li>
                                    </ul>
                                </div>

                                <!-- Child Selection -->
                                <div class="mb-3">
                                    <label for="childAccounts" class="form-label fw-bold">
                                        <i class="bi bi-list-check me-2"></i>Select Child Accounts <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="childAccounts" name="child_ids[]" multiple required size="8">
                                    </select>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <strong>Tip:</strong> Hold <kbd>Ctrl</kbd> (Windows) or <kbd>Cmd</kbd> (Mac) to select multiple accounts.
                                        You can also hold <kbd>Shift</kbd> to select a range.
                                    </div>
                                </div>

                                <!-- Preview -->
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <i class="bi bi-eye me-2"></i>Relationship Preview
                                    </div>
                                    <div class="card-body">
                                        <div id="relationshipPreview">
                                            <p class="text-muted mb-0">Select child accounts above to see the relationship structure.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Create Parent-Child Relationship
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Bulk Cashflow Type Update Modal -->
            <div class="modal fade" id="bulkCashflowTypeModal" tabindex="-1" aria-labelledby="bulkCashflowTypeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="bulkCashflowTypeModalLabel">Update Cashflow Types</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="bulkCashflowTypeForm">
                            <div class="modal-body">
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Warning:</strong> This will update the cashflow type for all selected accounts.
                                </div>
                                <div class="mb-3">
                                    <label for="bulkCashflowType" class="form-label">Cashflow Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="bulkCashflowType" name="cashflow_type" required>
                                        <option value="">Select Type</option>
                                        <option value="receipts">Receipts</option>
                                        <option value="disbursements">Disbursements</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-warning">Update Types</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Merge Accounts Modal -->
            <div class="modal fade" id="mergeAccountsModal" tabindex="-1" aria-labelledby="mergeAccountsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="mergeAccountsModalLabel">Merge Accounts</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="mergeAccountsForm">
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Merge Information:</strong> This will combine multiple accounts into one main account. The merged accounts will be deactivated and their cashflows will be moved to the main account.
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mainAccountName" class="form-label">Main Account Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="mainAccountName" name="new_account_name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mainAccountCode" class="form-label">Main Account Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="mainAccountCode" name="new_account_code" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Select Accounts to Merge <span class="text-danger">*</span></label>
                                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                        <div id="mergeAccountsList">
                                            <!-- Accounts will be loaded here -->
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Merge Preview</label>
                                    <div id="mergePreview" class="border rounded p-3 bg-light">
                                        <p class="text-muted mb-0">Select accounts above to see the merge preview.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-info">
                                    <i class="bi bi-arrow-merge me-2"></i>Merge Accounts
                                </button>
                            </div>
                        </form>
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
            // Import loader (Admin)
            const adminImportForm = document.getElementById('adminImportForm');
            if (adminImportForm) {
                adminImportForm.addEventListener('submit', function(e) {
                    e.preventDefault(); // Prevent default form submission

                    const btn = document.getElementById('adminImportBtn');
                    const spinner = document.getElementById('adminImportSpinner');
                    const fileInput = document.getElementById('adminImportFile');

                    if (btn && spinner && fileInput) {
                        btn.disabled = true;
                        fileInput.disabled = true;
                        spinner.classList.remove('d-none');
                    }

                                        // Create FormData and submit via AJAX
                    const formData = new FormData();

                    // Explicitly add the file to FormData
                    if (fileInput.files[0]) {
                        formData.append('file', fileInput.files[0]);
                        console.log('File added to FormData:', fileInput.files[0].name);
                    }

                    // Add CSRF token
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                    // Debug: Log what's in the FormData
                    console.log('FormData contents:');
                    for (let [key, value] of formData.entries()) {
                        console.log(key, value);
                    }

                    // Debug: Check if file input has a file
                    console.log('File input value:', fileInput.files[0]);
                    console.log('File input name:', fileInput.name);

                    fetch(this.action, {
                        method: 'POST',
                        body: formData
                        // Don't set headers for FormData - CSRF token is already in the form
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Import Successful!',
                                text: data.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Import Failed',
                                text: data.message,
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Import Failed',
                            text: 'An error occurred during import. Please try again.',
                            confirmButtonText: 'OK'
                        });
                    })
                    .finally(() => {
                        // Reset button state
                        if (btn && spinner && fileInput) {
                            btn.disabled = false;
                            fileInput.disabled = false;
                            spinner.classList.add('d-none');
                        }
                    });
                });
            }
            // CSRF token for Laravel
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            // Enable Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize drag and drop for selected accounts table
            initializeDragAndDrop();

            // Initialize DataTables
            const glAccountsTable = document.querySelector('#table-gl-accounts');
            const selectedAccountsTable = document.querySelector('#table-selected-accounts');

            if (glAccountsTable && window.simpleDatatables) {
                new simpleDatatables.DataTable(glAccountsTable);
            }
            if (selectedAccountsTable && window.simpleDatatables) {
                new simpleDatatables.DataTable(selectedAccountsTable);
            }

            // Modal instances
            const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));

            // Search functionality
            let searchTimeout;
            const searchInput = document.getElementById('searchInput');
            if (searchInput) searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadAccounts();
                }, 500);
            });

            // Filter functionality
            const accountTypeFilter = document.getElementById('accountTypeFilter');
            const cashflowTypeFilter = document.getElementById('cashflowTypeFilter');
            const selectionFilter = document.getElementById('selectionFilter');
            if (accountTypeFilter) accountTypeFilter.addEventListener('change', loadAccounts);
            if (cashflowTypeFilter) cashflowTypeFilter.addEventListener('change', loadAccounts);
            if (selectionFilter) selectionFilter.addEventListener('change', loadAccounts);

            // Select all checkbox
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            if (selectAllCheckbox) selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.account-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            // Individual account checkboxes
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('account-checkbox')) {
                    updateSelectAllCheckbox();
                }
            });

            // Selection toggle
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('selection-toggle')) {
                    const accountId = e.target.getAttribute('data-id');
                    const isSelected = e.target.checked;
                    updateSelection(accountId, isSelected);
                }

                // Handle child selection to also select parent
                if (e.target.classList.contains('account-checkbox')) {
                    const accountId = e.target.value;
                    const isSelected = e.target.checked;
                    const row = e.target.closest('tr');

                    // If this is a child account, also select/deselect the parent
                    if (row.classList.contains('child-account')) {
                        const parentRow = row.previousElementSibling;
                        if (parentRow && parentRow.classList.contains('parent-account')) {
                            const parentCheckbox = parentRow.querySelector('.account-checkbox');
                            if (parentCheckbox) {
                                parentCheckbox.checked = isSelected;
                            }
                        }
                    }

                    // If this is a parent account, also select/deselect all children
                    if (row.classList.contains('parent-account')) {
                        const childRows = row.parentNode.querySelectorAll(`tr.child-account[data-parent-id="${accountId}"]`);
                        childRows.forEach(childRow => {
                            const childCheckbox = childRow.querySelector('.account-checkbox');
                            if (childCheckbox) {
                                childCheckbox.checked = isSelected;
                            }
                        });
                    }
                }
            });

            // Bulk actions
            const btnSelectAll = document.getElementById('btnSelectAll');
            if (btnSelectAll) btnSelectAll.addEventListener('click', function() { selectAll(); });

            const btnDeselectAll = document.getElementById('btnDeselectAll');
            if (btnDeselectAll) btnDeselectAll.addEventListener('click', function() { deselectAll(); });

            // New bulk selection actions for All GL Accounts table
            const btnMarkSelected = document.getElementById('btnMarkSelected');
            if (btnMarkSelected) btnMarkSelected.addEventListener('click', function() {
                const ids = Array.from(document.querySelectorAll('#table-gl-accounts .account-checkbox:checked')).map(cb => cb.value);
                if (ids.length === 0) return showAlert('Please check at least one account.', 'warning');
                updateBulkSelection(ids, true);
            });

            const btnMarkUnselected = document.getElementById('btnMarkUnselected');
            if (btnMarkUnselected) btnMarkUnselected.addEventListener('click', function() {
                const ids = Array.from(document.querySelectorAll('#table-gl-accounts .account-checkbox:checked')).map(cb => cb.value);
                if (ids.length === 0) return showAlert('Please check at least one account.', 'warning');
                updateBulkSelection(ids, false);
            });

            function updateBulkSelection(accountIds, isSelected) {
                fetch('{{ route('admin.gl-accounts.bulk-selection') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ account_ids: accountIds, is_selected: isSelected })
                })
                .then(async response => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const message = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Bulk update failed');
                        throw new Error(message);
                    }
                    return data;
                })
                .then(data => {
                    showAlert(data.message || 'Selection updated.', 'success');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch(err => {
                    console.error('Bulk selection error:', err);
                    showAlert(err.message || 'Bulk update failed', 'error');
                });
            }

            // View buttons
            document.querySelectorAll('.btn-view').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    loadAccountForView(id);
                    viewModal.show();
                });
            });

            function loadAccounts() {
                const search = document.getElementById('searchInput').value;
                const accountType = document.getElementById('accountTypeFilter').value;
                const cashflowType = document.getElementById('cashflowTypeFilter').value;
                const selectionStatus = document.getElementById('selectionFilter').value;

                const params = new URLSearchParams();
                if (search) params.append('search', search);
                if (accountType) params.append('account_type', accountType);
                if (cashflowType) params.append('cashflow_type', cashflowType);
                if (selectionStatus !== '') params.append('is_selected', selectionStatus);

                fetch(`{{ route('admin.gl-accounts.get-accounts') }}?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateTable(data.data);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading accounts:', error);
                        showAlert('Error loading accounts', 'error');
                    });
            }

            function updateTable(accounts) {
                const tbody = document.querySelector('#table-gl-accounts tbody');
                tbody.innerHTML = '';

                if (accounts.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                No GL accounts found
                            </td>
                        </tr>
                    `;
                    return;
                }

                accounts.forEach(account => {
                    const row = document.createElement('tr');
                    row.className = account.parent_id ? 'child-account' : 'parent-account';
                    row.setAttribute('data-id', account.id);
                    row.innerHTML = `
                        <td>
                            <input type="checkbox" class="form-check-input account-checkbox" value="${account.id}">
                        </td>
                        <td>
                            <span class="account-indicator indicator-${account.account_type}"></span>
                            <span class="fw-medium text-primary">${account.account_code}</span>
                        </td>
                        <td>
                            ${account.parent_id ? `<div class="account-hierarchy">${account.account_name}</div>` : `<strong>${account.account_name}</strong>`}
                        </td>
                        <td>${getAccountTypeBadge(account.account_type)}</td>
                        <td>${getCashflowTypeBadge(account.cashflow_type)}</td>
                        <td>
                            <span class="text-muted">${account.parent?.account_name || '-'}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">${account.level}</span>
                        </td>
                        <td>
                            ${account.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'}
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input selection-toggle" type="checkbox"
                                       data-id="${account.id}"
                                       ${account.is_selected ? 'checked' : ''}>
                                <label class="form-check-label" for="selection-${account.id}">
                                    ${account.is_selected ? 'Selected' : 'Not Selected'}
                                </label>
                            </div>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary btn-view" title="View Details" data-id="${account.id}">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                // Re-attach event listeners
                attachEventListeners();
            }

            function attachEventListeners() {
                // View buttons
                document.querySelectorAll('.btn-view').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        loadAccountForView(id);
                        viewModal.show();
                    });
                });

                // Selection toggles
                document.querySelectorAll('.selection-toggle').forEach(toggle => {
                    toggle.addEventListener('change', function() {
                        const accountId = this.getAttribute('data-id');
                        const isSelected = this.checked;
                        updateSelection(accountId, isSelected);
                    });
                });

                // Account checkboxes
                document.querySelectorAll('.account-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        updateSelectAllCheckbox();
                    });
                });
            }

            function updateSelection(accountId, isSelected) {
                fetch(`{{ route('admin.gl-accounts.update-selection', ['glAccount' => ':id']) }}`.replace(':id', accountId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        is_selected: isSelected
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        // Reload the page to update both tables
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showAlert(data.message || 'Failed to update selection', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error updating selection:', error);
                    showAlert('Error updating selection', 'error');
                });
            }

            function selectAll() {
                Swal.fire({
                    title: 'Select All Accounts',
                    text: 'Are you sure you want to select all GL accounts? This will mark all accounts for display in cash flow reports.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, select all!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('{{ route('admin.gl-accounts.select-all') }}', {
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
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            } else {
                                showAlert(data.message || 'Failed to select all', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error selecting all:', error);
                            showAlert('Error selecting all accounts', 'error');
                        });
                    }
                });
            }

            function deselectAll() {
                Swal.fire({
                    title: 'Deselect All Accounts',
                    text: 'Are you sure you want to deselect all GL accounts? This will remove all accounts from cash flow reports.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, deselect all!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('{{ route('admin.gl-accounts.deselect-all') }}', {
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
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            } else {
                                showAlert(data.message || 'Failed to deselect all', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error deselecting all:', error);
                            showAlert('Error deselecting all accounts', 'error');
                        });
                    }
                });
            }

            function loadAccountForView(id) {
                // For now, we'll use the existing data from the table
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    const cells = row.querySelectorAll('td');
                    document.getElementById('view_account_code').textContent = cells[1].textContent.trim();
                    document.getElementById('view_account_name').textContent = cells[2].textContent.trim();
                    document.getElementById('view_account_type').innerHTML = cells[3].innerHTML;
                    document.getElementById('view_cashflow_type').innerHTML = cells[4].innerHTML;
                    document.getElementById('view_parent').textContent = cells[5].textContent.trim();
                    document.getElementById('view_level').textContent = cells[6].textContent.trim();
                    document.getElementById('view_status').innerHTML = cells[7].innerHTML;
                    document.getElementById('view_selection').textContent = cells[8].querySelector('label').textContent.trim();
                    document.getElementById('view_children_count').textContent = 'N/A'; // Would need to fetch from server
                    document.getElementById('view_created').textContent = 'N/A'; // Would need to fetch from server
                }
            }

            function updateSelectAllCheckbox() {
                const checkboxes = document.querySelectorAll('.account-checkbox');
                const checkedBoxes = document.querySelectorAll('.account-checkbox:checked');
                const selectAllCheckbox = document.getElementById('selectAllCheckbox');

                selectAllCheckbox.checked = checkedBoxes.length === checkboxes.length;
                selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < checkboxes.length;
            }

            function getAccountTypeBadge(type) {
                const badges = {
                    'parent': '<span class="badge bg-primary">parent</span>',
                    'single': '<span class="badge bg-success">single</span>',
                    'child': '<span class="badge bg-info">child</span>'
                };
                return badges[type] || `<span class="badge bg-secondary">${type}</span>`;
            }

            function getCashflowTypeBadge(type) {
                if (type === 'receipts') {
                    return '<span class="badge bg-success">Receipts</span>';
                } else {
                    return '<span class="badge bg-danger">Disbursements</span>';
                }
            }

            function showAlert(message, type = 'info') {
                const iconMap = {
                    'success': 'success',
                    'error': 'error',
                    'warning': 'warning',
                    'info': 'info'
                };

                Swal.fire({
                    title: type.charAt(0).toUpperCase() + type.slice(1),
                    text: message,
                    icon: iconMap[type] || 'info',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6'
                });
            }

            // Edit Account functionality
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-edit')) {
                    const accountId = e.target.closest('.btn-edit').dataset.id;
                    loadAccountForEdit(accountId);
                }
            });

            function loadAccountForEdit(id) {
                fetch(`{{ route('admin.gl-accounts') }}/${id}/edit`, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.account) {
                        const account = data.account;
                        document.getElementById('edit_account_code').value = account.account_code;
                        document.getElementById('edit_account_name').value = account.account_name;
                        // Account type is managed via relationships and hidden in the edit form
                        document.getElementById('edit_cashflow_type').value = account.cashflow_type || 'disbursements';
                        // Parent is managed via relationship actions; no parent field in edit form
                        document.getElementById('edit_level').value = account.level || '1';
                        document.getElementById('edit_is_active').checked = account.is_active;
                        document.getElementById('edit_is_selected').checked = account.is_selected;

                        // Removed parent accounts dropdown population

                        // Store account ID for form submission
                        document.getElementById('editAccountForm').dataset.accountId = id;

                        // Show modal
                        const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                        editModal.show();
                    }
                })
                .catch(error => {
                    console.error('Error loading account for edit:', error);
                    showAlert('Error loading account details', 'error');
                });
            }

            // Parent-Child relationship functionality
            document.addEventListener('click', function(e) {
                // Check if the clicked element is the make-parent button or contains it
                const makeParentBtn = e.target.closest('.make-parent-btn');
                if (makeParentBtn) {
                    e.preventDefault(); // Prevent default link behavior
                    const accountId = makeParentBtn.dataset.id;
                    loadMakeParentModal(accountId);
                    return;
                }

                // Check other buttons
                const removeParentBtn = e.target.closest('.remove-parent-btn');
                if (removeParentBtn) {
                    e.preventDefault();
                    const accountId = removeParentBtn.dataset.id;
                    removeParentChild(accountId, 'remove_parent');
                    return;
                }

                const removeChildrenBtn = e.target.closest('.remove-children-btn');
                if (removeChildrenBtn) {
                    e.preventDefault();
                    const accountId = removeChildrenBtn.dataset.id;
                    removeParentChild(accountId, 'remove_children');
                    return;
                }

                const removeAllBtn = e.target.closest('.remove-all-relationships-btn');
                if (removeAllBtn) {
                    e.preventDefault();
                    const accountId = removeAllBtn.dataset.id;
                    removeParentChild(accountId, 'remove_all');
                    return;
                }

                // Merge accounts functionality
                const mergeAccountsBtn = e.target.closest('.merge-accounts-btn');
                if (mergeAccountsBtn) {
                    e.preventDefault();
                    const accountId = mergeAccountsBtn.dataset.id;
                    loadMergeAccountsModal(accountId);
                    return;
                }

                const unmergeAccountsBtn = e.target.closest('.unmerge-accounts-btn');
                if (unmergeAccountsBtn) {
                    e.preventDefault();
                    const accountId = unmergeAccountsBtn.dataset.id;
                    unmergeAccounts(accountId);
                    return;
                }
            });

            function loadMakeParentModal(accountId) {
                // Get all accounts that could be children
                fetch('{{ route('admin.gl-accounts.get-accounts') }}?context=make_parent', {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data) {
                        const childSelect = document.getElementById('childAccounts');
                        childSelect.innerHTML = '';

                        // Build options: eligible singles + existing children (pre-selected)
                        const existingChildIds = Array.from(document.querySelectorAll(`tr[data-id="${accountId}"] ~ tr.child-account`)).map(tr => tr.getAttribute('data-id'));

                        data.data.forEach(account => {
                            const isExistingChild = existingChildIds.includes(String(account.id));
                            const isEligibleSingle = account.account_type === 'single';
                            if (account.id != accountId && (isEligibleSingle || isExistingChild)) {
                                const option = document.createElement('option');
                                option.value = account.id;
                                option.textContent = `${account.account_code} - ${account.account_name}`;
                                if (isExistingChild) option.selected = true;
                                childSelect.appendChild(option);
                            }
                        });

                        // Get account name for display
                        const row = document.querySelector(`tr[data-id="${accountId}"]`);
                        if (row) {
                            const accountName = row.querySelector('td:nth-child(3)').textContent.trim();
                            document.getElementById('parentAccountName').textContent = accountName;
                        }

                        // Store account ID for form submission
                        document.getElementById('makeParentForm').dataset.accountId = accountId;

                        // Add event listener for relationship preview
                        childSelect.addEventListener('change', updateRelationshipPreview);

                        // Show modal
                        const makeParentModal = new bootstrap.Modal(document.getElementById('makeParentModal'));
                        makeParentModal.show();
                    }
                })
                .catch(error => {
                    console.error('Error loading accounts for parent selection:', error);
                    showAlert('Error loading accounts', 'error');
                });
            }

            function updateRelationshipPreview() {
                const childSelect = document.getElementById('childAccounts');
                const previewDiv = document.getElementById('relationshipPreview');
                const selectedOptions = Array.from(childSelect.selectedOptions);

                if (selectedOptions.length === 0) {
                    previewDiv.innerHTML = '<p class="text-muted mb-0">Select child accounts above to see the relationship structure.</p>';
                    return;
                }

                const parentName = document.getElementById('parentAccountName').textContent;
                let previewHTML = `
                    <div class="mb-2">
                        <strong class="text-primary">${parentName}</strong>
                        <i class="bi bi-arrow-down text-success ms-2"></i>
                    </div>
                    <ul class="list-unstyled ms-3">
                `;

                selectedOptions.forEach(option => {
                    previewHTML += `<li><i class="bi bi-arrow-right text-muted me-2"></i>${option.textContent}</li>`;
                });

                previewHTML += '</ul>';
                previewDiv.innerHTML = previewHTML;
            }

            function removeParentChild(accountId, action) {
                let message = '';
                let title = '';

                switch(action) {
                    case 'remove_parent':
                        title = 'Remove Parent Relationship';
                        message = 'Are you sure you want to remove the parent relationship? This will make this account a top-level account.';
                        break;
                    case 'remove_children':
                        title = 'Remove Child Relationships';
                        message = 'Are you sure you want to remove all child relationships? This will make all child accounts top-level accounts.';
                        break;
                    case 'remove_all':
                        title = 'Remove All Relationships';
                        message = 'Are you sure you want to remove all parent and child relationships? This will make all related accounts top-level accounts.';
                        break;
                }

                Swal.fire({
                    title: title,
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, remove it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`{{ route('admin.gl-accounts') }}/${accountId}/remove-parent-child`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ action: action })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showAlert(data.message, 'success');
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            } else {
                                showAlert(data.message || 'Failed to remove relationship', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error removing relationship:', error);
                            showAlert('Error removing relationship', 'error');
                        });
                    }
                });
            }

            // Bulk cashflow type update functionality
            const btnUpdateCashflowTypes = document.getElementById('btnUpdateCashflowTypes');
            if (btnUpdateCashflowTypes) {
                btnUpdateCashflowTypes.addEventListener('click', function() {
                    const checkedBoxes = document.querySelectorAll('.account-checkbox:checked');
                    if (checkedBoxes.length === 0) {
                        showAlert('Please select at least one account to update', 'warning');
                        return;
                    }

                    const bulkModal = new bootstrap.Modal(document.getElementById('bulkCashflowTypeModal'));
                    bulkModal.show();
                });
            }

            // Form submissions
            document.getElementById('editAccountForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const accountId = this.dataset.accountId;
                const formData = new FormData(this);
                // Convert to JSON to avoid empty required fields being sent unintentionally
                const payload = {
                    account_code: formData.get('account_code')?.toString().trim(),
                    account_name: formData.get('account_name')?.toString().trim(),
                    cashflow_type: formData.get('edit_cashflow_type') ? formData.get('edit_cashflow_type') : formData.get('cashflow_type'),
                    level: formData.get('level'),
                    is_active: document.getElementById('edit_is_active').checked,
                    is_selected: document.getElementById('edit_is_selected').checked,
                };
                // Remove undefined/empty keys
                Object.keys(payload).forEach(k => (payload[k] === undefined || payload[k] === null || payload[k] === '') && delete payload[k]);

                fetch(`{{ route('admin.gl-accounts') }}/${accountId}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload)
                })
                .then(async response => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const message = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Failed to update account');
                        throw new Error(message);
                    }
                    return data;
                })
                .then(data => {
                    showAlert(data.message || 'GL Account updated successfully.', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                })
                .catch(error => {
                    console.error('Error updating account:', error);
                    showAlert(error.message || 'Error updating account', 'error');
                });
            });

            // Add account submission
            const addAccountForm = document.getElementById('addAccountForm');
            if (addAccountForm) {
                addAccountForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    const payload = {
                        account_code: formData.get('account_code')?.toString().trim(),
                        account_name: formData.get('account_name')?.toString().trim(),
                        cashflow_type: formData.get('cashflow_type'),
                        level: formData.get('level'),
                        is_active: document.getElementById('add_is_active').checked,
                        is_selected: document.getElementById('add_is_selected').checked,
                    };

                    fetch('{{ route('admin.gl-accounts.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(async response => {
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            const message = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Failed to create account');
                            throw new Error(message);
                        }
                        return data;
                    })
                    .then(data => {
                        showAlert(data.message || 'GL Account created successfully.', 'success');
                        bootstrap.Modal.getInstance(document.getElementById('addAccountModal')).hide();
                        setTimeout(() => window.location.reload(), 800);
                    })
                    .catch(err => {
                        console.error('Create account error:', err);
                        showAlert(err.message || 'Failed to create account', 'error');
                    });
                });
            }

            document.getElementById('makeParentForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const accountId = this.dataset.accountId;
                const childSelect = document.getElementById('childAccounts');
                if (!childSelect) {
                    showAlert('Child selection list not found. Please reload the page.', 'error');
                    return;
                }
                const selectedChildIds = Array.from(childSelect.selectedOptions).map(o => o.value);

                if (selectedChildIds.length === 0) {
                    showAlert('Please select at least one child account.', 'warning');
                    return;
                }

                fetch(`{{ route('admin.gl-accounts') }}/${accountId}/make-parent`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ child_ids: selectedChildIds })
                })
                .then(async response => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const message = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Failed to make parent');
                        throw new Error(message);
                    }
                    return data;
                })
                .then(data => {
                    showAlert(data.message || 'Account updated successfully', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('makeParentModal')).hide();
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                })
                .catch(error => {
                    console.error('Error making parent:', error);
                    showAlert(error.message || 'Error making parent', 'error');
                });
            });

            document.getElementById('bulkCashflowTypeForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const checkedBoxes = document.querySelectorAll('.account-checkbox:checked');
                const accountIds = Array.from(checkedBoxes).map(cb => cb.value);

                formData.append('account_ids', JSON.stringify(accountIds));

                fetch('{{ route('admin.gl-accounts.update-cashflow-types') }}', {
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
                        bootstrap.Modal.getInstance(document.getElementById('bulkCashflowTypeModal')).hide();
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showAlert(data.message || 'Failed to update cashflow types', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error updating cashflow types:', error);
                    showAlert('Error updating cashflow types', 'error');
                });
            });

            // Merge accounts functionality
            function loadMergeAccountsModal(accountId) {
                // Get all accounts that could be merged
                fetch('{{ route('admin.gl-accounts.get-accounts') }}', {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data) {
                        const mergeAccountsList = document.getElementById('mergeAccountsList');
                        mergeAccountsList.innerHTML = '';

                        data.data.forEach(account => {
                            if (account.id != accountId && !account.merged_into) {
                                const div = document.createElement('div');
                                div.className = 'form-check mb-2';
                                div.innerHTML = `
                                    <input class="form-check-input merge-account-checkbox" type="checkbox" value="${account.id}" id="merge_${account.id}">
                                    <label class="form-check-label" for="merge_${account.id}">
                                        <strong>${account.account_code}</strong> - ${account.account_name}
                                        <span class="badge bg-secondary ms-2">${account.account_type}</span>
                                    </label>
                                `;
                                mergeAccountsList.appendChild(div);
                            }
                        });

                        // Get account name for display
                        const row = document.querySelector(`tr[data-id="${accountId}"]`);
                        if (row) {
                            const accountName = row.querySelector('td:nth-child(3)').textContent.trim();
                            const accountCode = row.querySelector('td:nth-child(2)').textContent.trim();
                            document.getElementById('mainAccountName').value = accountName;
                            document.getElementById('mainAccountCode').value = accountCode;
                        }

                        // Store account ID for form submission
                        document.getElementById('mergeAccountsForm').dataset.accountId = accountId;

                        // Add event listener for merge preview
                        const checkboxes = document.querySelectorAll('.merge-account-checkbox');
                        checkboxes.forEach(checkbox => {
                            checkbox.addEventListener('change', updateMergePreview);
                        });

                        // Show modal
                        const mergeModal = new bootstrap.Modal(document.getElementById('mergeAccountsModal'));
                        mergeModal.show();
                    }
                })
                .catch(error => {
                    console.error('Error loading accounts for merge:', error);
                    showAlert('Error loading accounts', 'error');
                });
            }

            function updateMergePreview() {
                const checkboxes = document.querySelectorAll('.merge-account-checkbox:checked');
                const previewDiv = document.getElementById('mergePreview');
                const mainAccountName = document.getElementById('mainAccountName').value;
                const mainAccountCode = document.getElementById('mainAccountCode').value;

                if (checkboxes.length === 0) {
                    previewDiv.innerHTML = '<p class="text-muted mb-0">Select accounts above to see the merge preview.</p>';
                    return;
                }

                let previewHTML = `
                    <div class="mb-2">
                        <strong class="text-primary">Main Account:</strong> ${mainAccountCode} - ${mainAccountName}
                    </div>
                    <div class="mb-2">
                        <strong class="text-info">Accounts to be merged:</strong>
                    </div>
                    <ul class="list-unstyled ms-3">
                `;

                checkboxes.forEach(checkbox => {
                    const label = checkbox.nextElementSibling.textContent.trim();
                    previewHTML += `<li><i class="bi bi-arrow-right text-success"></i> ${label}</li>`;
                });

                previewHTML += '</ul>';
                previewDiv.innerHTML = previewHTML;
            }

            function unmergeAccounts(accountId) {
                Swal.fire({
                    title: 'Unmerge Accounts',
                    text: 'Are you sure you want to unmerge accounts? This will restore all merged accounts to their original state.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, unmerge!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`{{ route('admin.gl-accounts') }}/${accountId}/unmerge`, {
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
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            } else {
                                showAlert(data.message || 'Failed to unmerge accounts', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error unmerging accounts:', error);
                            showAlert('Error unmerging accounts', 'error');
                        });
                    }
                });
            }

            // Merge accounts form submission
            document.getElementById('mergeAccountsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const accountId = this.dataset.accountId;
                const checkedBoxes = document.querySelectorAll('.merge-account-checkbox:checked');
                const accountIds = Array.from(checkedBoxes).map(cb => cb.value);

                if (accountIds.length === 0) {
                    showAlert('Please select at least one account to merge', 'warning');
                    return;
                }

                // Create the request data
                const requestData = {
                    account_ids: accountIds,
                    new_account_name: document.getElementById('mainAccountName').value,
                    new_account_code: document.getElementById('mainAccountCode').value
                };

                fetch(`{{ route('admin.gl-accounts') }}/${accountId}/merge`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('mergeAccountsModal')).hide();
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showAlert(data.message || 'Failed to merge accounts', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error merging accounts:', error);
                    showAlert('Error merging accounts', 'error');
                });
            });

            // Drag and Drop functionality for Selected Accounts table
            function initializeDragAndDrop() {
                const tbody = document.getElementById('selected-accounts-tbody');
                if (!tbody) return;

                let draggedRow = null;
                let draggedIndex = null;

                // Add drag event listeners to all rows
                function addDragListeners(row) {
                    row.addEventListener('dragstart', handleDragStart);
                    row.addEventListener('dragend', handleDragEnd);
                    row.addEventListener('dragover', handleDragOver);
                    row.addEventListener('drop', handleDrop);
                    row.addEventListener('dragenter', handleDragEnter);
                    row.addEventListener('dragleave', handleDragLeave);
                }

                // Initialize drag listeners for existing rows
                document.querySelectorAll('.selected-account-row').forEach(addDragListeners);

                function handleDragStart(e) {
                    draggedRow = this;
                    draggedIndex = Array.from(this.parentNode.children).indexOf(this);
                    this.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/html', this.outerHTML);
                }

                function handleDragEnd(e) {
                    this.classList.remove('dragging');
                    draggedRow = null;
                    draggedIndex = null;
                }

                function handleDragOver(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                }

                function handleDragEnter(e) {
                    e.preventDefault();
                    this.classList.add('drag-over');
                }

                function handleDragLeave(e) {
                    this.classList.remove('drag-over');
                }

                function handleDrop(e) {
                    e.preventDefault();
                    this.classList.remove('drag-over');

                    if (draggedRow && draggedRow !== this) {
                        const rows = Array.from(tbody.children);
                        const dropIndex = Array.from(this.parentNode.children).indexOf(this);

                        // Remove dragged row from its current position
                        draggedRow.remove();

                        // Insert at new position
                        if (dropIndex > draggedIndex) {
                            tbody.insertBefore(draggedRow, this.nextSibling);
                        } else {
                            tbody.insertBefore(draggedRow, this);
                        }

                        // Update order attributes
                        rows.forEach((row, index) => {
                            if (row.classList.contains('selected-account-row')) {
                                row.setAttribute('data-order', index);
                            }
                        });

                        // Show success message
                        showAlert('Account order updated successfully!', 'success');

                        // Save the new order to backend (optional)
                        saveAccountOrder();
                    }
                }

                // Function to save the new order to backend
                function saveAccountOrder() {
                    const rows = Array.from(tbody.querySelectorAll('.selected-account-row'));
                    const orderData = rows.map((row, index) => ({
                        id: row.dataset.id,
                        order: index
                    }));

                    // Send to backend
                    fetch('{{ route("admin.gl-accounts.update-order") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ order: orderData })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Order saved successfully:', data.message);
                        } else {
                            console.error('Failed to save order:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error saving order:', error);
                    });
                }
            }
        });
    </script>
</body>

</html>
