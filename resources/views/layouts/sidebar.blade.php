<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header">
            <div class="d-flex justify-content-between">
                <div class="logo">
                    <a
                        href="@if (Auth::user()->role === 'admin') {{ route('admin.dashboard') }}
                                @elseif(Auth::user()->role === 'head')
                                    {{ route('head.dashboard') }}
                                @elseif(Auth::user()->role === 'branch')
                                    {{ route('branch.dashboard') }}
                                @else #
                                @endif">
                                <img class="text-center" src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo" srcset=""></a>
                </div>
                <div class="toggler">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div>
        <div class="sidebar-menu">
            <ul class="menu">
                <li class="sidebar-title">Menu</li>

                @if(Auth::user()->role === 'admin')
                    <li class="sidebar-item active">
                        <a href="{{ route('admin.dashboard') }}" class='sidebar-link'>
                            <i class="bi bi-grid-fill"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="sidebar-item has-sub">
                        <a href="#" class='sidebar-link'>
                            <i class="bi bi-stack"></i>
                            <span>Setup</span>
                        </a>
                        <ul class="submenu">
                            <li class="submenu-item">
                                <a href="component-alert.html">Chart of Accounts</a>
                            </li>
                        </ul>
                    </li>
                    <li class="sidebar-item has-sub">
                        <a href="#" class='sidebar-link'>
                            <i class="bi bi-people"></i>
                            <span>Users</span>
                        </a>
                        <ul class="submenu">
                            <li class="submenu-item">
                                <a href="component-alert.html">Users Datatable</a>
                            </li>
                        </ul>
                    </li>
                @elseif(Auth::user()->role === 'head')
                    <li class="sidebar-item active">
                        <a href="{{ route('head.dashboard') }}" class='sidebar-link'>
                            <i class="bi bi-grid-fill"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class='sidebar-link'>
                            <i class="bi bi-bar-chart"></i>
                            <span>File Upload</span>
                        </a>
                    </li>
                     <li class="sidebar-item">
                        <a href="#" class='sidebar-link'>
                            <i class="bi-cash-stack"></i>
                            <span>Cash Flow</span>
                        </a>
                    </li>
                @elseif(Auth::user()->role === 'branch')
                    <li class="sidebar-item active">
                        <a href="{{ route('branch.dashboard') }}" class='sidebar-link'>
                            <i class="bi bi-grid-fill"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class='sidebar-link'>
                            <i class="bi bi-cash-stack"></i>
                            <span>Cash Flow</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
        <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
    </div>
</div>
