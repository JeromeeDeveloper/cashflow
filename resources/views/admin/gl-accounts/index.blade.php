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

        .child-account {
            background-color: #ffffff;
            border-left: 2px solid #28a745;
        }

        .child-account::before {
            content: "└─";
            color: #28a745;
            font-weight: bold;
            margin-right: 8px;
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
        .indicator-detail { background-color: #ffc107; }
        .indicator-summary { background-color: #17a2b8; }

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

                <!-- Information Section -->
                <section class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info border-0">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle fs-4 me-3 mt-1"></i>
                                <div>
                                    <h6 class="alert-heading mb-2">Understanding Parent-Child Relationships</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>👑 Parent Account:</strong>
                                            <ul class="mb-0 mt-1">
                                                <li>Groups related accounts together</li>
                                                <li>Can have multiple child accounts</li>
                                                <li>Shows "X Children" badge</li>
                                                <li>Blue background color</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>👶 Child Account:</strong>
                                            <ul class="mb-0 mt-1">
                                                <li>Belongs to a parent account</li>
                                                <li>Shows "Child of [Parent Name]" badge</li>
                                                <li>Indented with "└─" symbol</li>
                                                <li>White background color</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>🎯 Benefits:</strong>
                                            <ul class="mb-0 mt-1">
                                                <li>Better organization</li>
                                                <li>Cleaner cashflow views</li>
                                                <li>Easier reporting</li>
                                                <li>Clear relationship indicators</li>
                                            </ul>
                                        </div>
                                    </div>
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
                                        <thead>
                                            <tr>
                                                <th>Account Code</th>
                                                <th>Account Name</th>
                                                <th>Account Type</th>
                                                <th>Cashflow Type</th>
                                                <th>Parent Account</th>
                                                <th>Level</th>
                                                <th>Status</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($selectedAccounts as $account)
                                                <tr class="{{ $account->parent_id ? 'child-account' : 'parent-account' }}" data-id="{{ $account->id }}">
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
                                                            @case('detail')
                                                                <span class="badge bg-success">{{ $account->account_type }}</span>
                                                                @break
                                                            @case('summary')
                                                                <span class="badge bg-info">{{ $account->account_type }}</span>
                                                                @break
                                                            @default
                                                                <span class="badge bg-secondary">{{ $account->account_type }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td>
                                                        @php
                                                            $cashflowType = $account->cashflow_type ?? 'disbursements';
                                                            $actualType = $account->getMostCommonCashflowType();
                                                            $displayType = $actualType ?: $cashflowType;
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
                                                    <td class="text-end">
                                                        <div class="form-check form-switch d-inline-block">
                                                            <input class="form-check-input selection-toggle" type="checkbox"
                                                                   data-id="{{ $account->id }}"
                                                                   {{ $account->is_selected ? 'checked' : '' }}>
                                                        </div>
                                                        <button class="btn btn-sm btn-outline-primary btn-view" title="View Details" data-id="{{ $account->id }}">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-warning btn-edit" title="Edit Account" data-id="{{ $account->id }}">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <div class="btn-group" role="group">
                                                            <button class="btn btn-sm btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" title="Parent-Child Actions">
                                                                <i class="bi bi-diagram-3"></i>
                                                                <span class="ms-1">Relationships</span>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><h6 class="dropdown-header">Make Parent</h6></li>
                                                                <li><a class="dropdown-item make-parent-btn" href="#" data-id="{{ $account->id }}">
                                                                    <i class="bi bi-plus-circle text-success me-2"></i>Make This Account a Parent
                                                                </a></li>
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
                                                                @if($account->parent || $account->children->count() > 0)
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li><a class="dropdown-item remove-all-relationships-btn" href="#" data-id="{{ $account->id }}">
                                                                        <i class="bi bi-trash text-danger me-2"></i>Remove All Relationships
                                                                    </a></li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @if($account->children->count() > 0)
                                                    @foreach($account->children->where('is_selected', true) as $child)
                                                        <tr class="child-account" data-id="{{ $child->id }}">
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
                                                                    @case('detail')
                                                                        <span class="badge bg-success">{{ $child->account_type }}</span>
                                                                        @break
                                                                    @case('summary')
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
                                                            <td class="text-end">
                                                                <div class="form-check form-switch d-inline-block">
                                                                    <input class="form-check-input selection-toggle" type="checkbox"
                                                                           data-id="{{ $child->id }}"
                                                                           {{ $child->is_selected ? 'checked' : '' }}>
                                                                </div>
                                                                <button class="btn btn-sm btn-outline-primary btn-view" title="View Details" data-id="{{ $child->id }}">
                                                                    <i class="bi bi-eye"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted py-4">
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

                <!-- All GL Accounts Management -->
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <h4 class="mb-0">All GL Accounts</h4>
                                <div class="d-flex align-items-center flex-wrap gap-2 justify-content-end">
                                    <!-- Search -->
                                    <div class="input-group" style="max-width: 300px;">
                                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                        <input type="text" id="searchInput" class="form-control" placeholder="Search accounts...">
                                    </div>

                                    <!-- Filter -->
                                    <select id="accountTypeFilter" class="form-select" style="max-width: 150px;">
                                        <option value="">All Types</option>
                                        <option value="parent">Parent</option>
                                        <option value="detail">Detail</option>
                                        <option value="summary">Summary</option>
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
                                            <i class="bi bi-check-all me-2"></i>Select All
                                        </button>
                                        <button type="button" class="btn btn-warning" id="btnUpdateCashflowTypes">
                                            <i class="bi bi-arrow-repeat me-2"></i>Update Cashflow Types
                                        </button>
                                    </div>

                                    <span class="badge rounded-pill bg-light text-dark border d-flex align-items-center px-3" data-bs-toggle="tooltip" data-bs-placement="top" title="Selected accounts will be available in cashflow views">
                                        <i class="bi bi-info-circle text-info me-2"></i>
                                        Selection affects cashflow
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="table-gl-accounts">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                                                </th>
                                                <th>Account Code</th>
                                                <th>Account Name</th>
                                                <th>Account Type</th>
                                                <th>Cashflow Type</th>
                                                <th>Parent Account</th>
                                                <th>Level</th>
                                                <th>Status</th>
                                                <th>Selection</th>
                                                <th class="text-end">Actions</th>
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
                                                            @case('detail')
                                                                <span class="badge bg-success">{{ $account->account_type }}</span>
                                                                @break
                                                            @case('summary')
                                                                <span class="badge bg-info">{{ $account->account_type }}</span>
                                                                @break
                                                            @default
                                                                <span class="badge bg-secondary">{{ $account->account_type }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td>
                                                        @php
                                                            $cashflowType = $account->cashflow_type ?? 'disbursements';
                                                            $actualType = $account->getMostCommonCashflowType();
                                                            $displayType = $actualType ?: $cashflowType;
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
                                                    <td class="text-end">
                                                        <button class="btn btn-sm btn-outline-primary btn-view" title="View Details" data-id="{{ $account->id }}">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-warning btn-edit" title="Edit Account" data-id="{{ $account->id }}">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <div class="btn-group" role="group">
                                                            <button class="btn btn-sm btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" title="Parent-Child Actions">
                                                                <i class="bi bi-diagram-3"></i>
                                                                <span class="ms-1">Relationships</span>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><h6 class="dropdown-header">Make Parent</h6></li>
                                                                <li><a class="dropdown-item make-parent-btn" href="#" data-id="{{ $account->id }}">
                                                                    <i class="bi bi-plus-circle text-success me-2"></i>Make This Account a Parent
                                                                </a></li>
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
                                                                @if($account->parent || $account->children->count() > 0)
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li><a class="dropdown-item remove-all-relationships-btn" href="#" data-id="{{ $account->id }}">
                                                                        <i class="bi bi-trash text-danger me-2"></i>Remove All Relationships
                                                                    </a></li>
                                                                @endif
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
                                                                    @case('detail')
                                                                        <span class="badge bg-success">{{ $child->account_type }}</span>
                                                                        @break
                                                                    @case('summary')
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
                                                            <td class="text-end">
                                                                <button class="btn btn-sm btn-outline-primary btn-view" title="View Details" data-id="{{ $child->id }}">
                                                                    <i class="bi bi-eye"></i>
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
                                        <label for="edit_account_type" class="form-label">Account Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="edit_account_type" name="account_type" required>
                                            <option value="parent">Parent</option>
                                            <option value="detail">Detail</option>
                                            <option value="summary">Summary</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_cashflow_type" class="form-label">Cashflow Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="edit_cashflow_type" name="cashflow_type" required>
                                            <option value="receipts">Receipts</option>
                                            <option value="disbursements">Disbursements</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_parent_id" class="form-label">Parent Account</label>
                                        <select class="form-select" id="edit_parent_id" name="parent_id">
                                            <option value="">No Parent</option>
                                        </select>
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
            document.getElementById('searchInput').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadAccounts();
                }, 500);
            });

            // Filter functionality
            document.getElementById('accountTypeFilter').addEventListener('change', loadAccounts);
            document.getElementById('cashflowTypeFilter').addEventListener('change', loadAccounts);
            document.getElementById('selectionFilter').addEventListener('change', loadAccounts);

            // Select all checkbox
            document.getElementById('selectAllCheckbox').addEventListener('change', function() {
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
            });

            // Bulk actions
            document.getElementById('btnSelectAll').addEventListener('click', function() {
                selectAll();
            });

            document.getElementById('btnDeselectAll').addEventListener('click', function() {
                deselectAll();
            });

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
                    'detail': '<span class="badge bg-success">detail</span>',
                    'summary': '<span class="badge bg-info">summary</span>'
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
                        document.getElementById('edit_account_type').value = account.account_type;
                        document.getElementById('edit_cashflow_type').value = account.cashflow_type || 'disbursements';
                        document.getElementById('edit_parent_id').value = account.parent_id || '';
                        document.getElementById('edit_is_active').checked = account.is_active;
                        document.getElementById('edit_is_selected').checked = account.is_selected;

                        // Populate parent accounts dropdown
                        const parentSelect = document.getElementById('edit_parent_id');
                        parentSelect.innerHTML = '<option value="">No Parent</option>';
                        data.parentAccounts.forEach(parent => {
                            const option = document.createElement('option');
                            option.value = parent.id;
                            option.textContent = `${parent.account_code} - ${parent.account_name}`;
                            parentSelect.appendChild(option);
                        });

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
            });

            function loadMakeParentModal(accountId) {
                // Get all accounts that could be children
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
                        const childSelect = document.getElementById('childAccounts');
                        childSelect.innerHTML = '';

                        data.data.forEach(account => {
                            if (account.id != accountId) {
                                const option = document.createElement('option');
                                option.value = account.id;
                                option.textContent = `${account.account_code} - ${account.account_name}`;
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
            document.getElementById('btnUpdateCashflowTypes').addEventListener('click', function() {
                const checkedBoxes = document.querySelectorAll('.account-checkbox:checked');
                if (checkedBoxes.length === 0) {
                    showAlert('Please select at least one account to update', 'warning');
                    return;
                }

                const bulkModal = new bootstrap.Modal(document.getElementById('bulkCashflowTypeModal'));
                bulkModal.show();
            });

            // Form submissions
            document.getElementById('editAccountForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const accountId = this.dataset.accountId;
                const formData = new FormData(this);

                fetch(`{{ route('admin.gl-accounts') }}/${accountId}`, {
                    method: 'PUT',
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
                        bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showAlert(data.message || 'Failed to update account', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error updating account:', error);
                    showAlert('Error updating account', 'error');
                });
            });

            document.getElementById('makeParentForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const accountId = this.dataset.accountId;
                const formData = new FormData(this);

                fetch(`{{ route('admin.gl-accounts') }}/${accountId}/make-parent`, {
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
                        bootstrap.Modal.getInstance(document.getElementById('makeParentModal')).hide();
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showAlert(data.message || 'Failed to make parent', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error making parent:', error);
                    showAlert('Error making parent', 'error');
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
        });
    </script>
</body>

</html>
