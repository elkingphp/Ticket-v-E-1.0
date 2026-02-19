<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="/" class="logo logo-dark">
            <span class="logo-sm">
                @if(get_setting('logo_sm'))
                    <img src="{{ asset(get_setting('logo_sm')) }}" alt="" height="22">
                @elseif(get_setting('logo_dark'))
                    <img src="{{ asset(get_setting('logo_dark')) }}" alt="" height="22">
                @else
                    <span class="fs-22 fw-bold text-white">D</span>
                @endif
            </span>
            <span class="logo-lg">
                @if(get_setting('logo_dark'))
                    <img src="{{ asset(get_setting('logo_dark')) }}" alt="" height="22">
                @else
                    <span class="fs-22 fw-bold text-white">{{ get_setting('site_name', 'DIGILIANS') }}</span>
                @endif
            </span>
        </a>
        <!-- Light Logo-->
        <a href="/" class="logo logo-light">
            <span class="logo-sm">
                @if(get_setting('logo_sm'))
                    <img src="{{ asset(get_setting('logo_sm')) }}" alt="" height="22">
                @elseif(get_setting('logo_light'))
                    <img src="{{ asset(get_setting('logo_light')) }}" alt="" height="22">
                @else
                    <span class="fs-22 fw-bold text-white">D</span>
                @endif
            </span>
            <span class="logo-lg">
                @if(get_setting('logo_light'))
                    <img src="{{ asset(get_setting('logo_light')) }}" alt="" height="22">
                @else
                    <span class="fs-22 fw-bold text-white">{{ get_setting('site_name', 'DIGILIANS') }}</span>
                @endif
            </span>
        </a>


        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span data-key="t-menu">{{ __('sidebar.menu') }}</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">{{ __('sidebar.dashboards') }}</span>
                    </a>
                </li>
                
                <!-- Tickets Module -->
                <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">{{ __('tickets::messages.tickets') }}</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
                        <i class="ri-ticket-2-line"></i> <span data-key="t-tickets">{{ __('tickets::messages.my_tickets') }}</span>
                    </a>
                </li>

                @can('tickets.agent_desk')
                <li class="nav-item">
                     <a class="nav-link menu-link {{ request()->routeIs('agent.tickets.*') ? 'active' : '' }}" href="{{ route('agent.tickets.index') }}">
                        <i class="ri-customer-service-2-line"></i> <span data-key="t-agent-desk">{{ __('tickets::messages.support_desk') }}</span>
                    </a>
                </li>
                @endcan

                @can('tickets.settings')
                <li class="nav-item">
                    @php $ticketsAdminActive = request()->routeIs('admin.tickets.*'); @endphp
                    <a class="nav-link menu-link {{ $ticketsAdminActive ? 'active' : '' }}" href="#sidebarTicketsAdmin" data-bs-toggle="collapse" 
                        role="button" aria-expanded="{{ $ticketsAdminActive ? 'true' : 'false' }}" aria-controls="sidebarTicketsAdmin">
                        <i class="ri-settings-5-line"></i> <span data-key="t-ticket-settings">{{ __('tickets::messages.settings') }}</span>
                    </a>
                    <div class="collapse menu-dropdown {{ $ticketsAdminActive ? 'show' : '' }}" id="sidebarTicketsAdmin" style="">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('admin.tickets.settings') }}" class="nav-link {{ request()->routeIs('admin.tickets.settings') ? 'active' : '' }}" data-key="t-module-settings">{{ __('tickets::messages.settings') }}</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.tickets.stages.index') }}" class="nav-link {{ request()->routeIs('admin.tickets.stages.*') ? 'active' : '' }}" data-key="t-stages">{{ __('tickets::messages.lookups.stages') }}</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.tickets.categories.index') }}" class="nav-link {{ request()->routeIs('admin.tickets.categories.*') ? 'active' : '' }}" data-key="t-categories">{{ __('tickets::messages.lookups.categories') }}</a>
                            </li>
                             <li class="nav-item">
                                <a href="{{ route('admin.tickets.complaints.index') }}" class="nav-link {{ request()->routeIs('admin.tickets.complaints.*') ? 'active' : '' }}" data-key="t-complaints">{{ __('tickets::messages.lookups.complaints') }}</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.tickets.statuses.index') }}" class="nav-link {{ request()->routeIs('admin.tickets.statuses.*') ? 'active' : '' }}" data-key="t-statuses">{{ __('tickets::messages.lookups.statuses') }}</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.tickets.priorities.index') }}" class="nav-link {{ request()->routeIs('admin.tickets.priorities.*') ? 'active' : '' }}" data-key="t-priorities">{{ __('tickets::messages.lookups.priorities') }}</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.tickets.groups.index') }}" class="nav-link {{ request()->routeIs('admin.tickets.groups.*') ? 'active' : '' }}" data-key="t-groups">{{ __('tickets::messages.lookups.groups') }}</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.tickets.templates.index') }}" class="nav-link {{ request()->routeIs('admin.tickets.templates.*') ? 'active' : '' }}" data-key="t-templates">{{ __('tickets::messages.templates') }}</a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endcan

                <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">{{ __('sidebar.system') }}</span></li>

                @canany(['view users', 'view roles', 'view permissions'])
                <li class="nav-item">
                    @php $usersActive = request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*'); @endphp
                    <a class="nav-link menu-link {{ $usersActive ? 'active' : '' }}" href="#sidebarUsers" data-bs-toggle="collapse" role="button"
                        aria-expanded="{{ $usersActive ? 'true' : 'false' }}" aria-controls="sidebarUsers">
                        <i class="ri-user-line"></i> <span data-key="t-users">{{ __('sidebar.users_management') }}</span>
                    </a>
                    <div class="collapse menu-dropdown {{ $usersActive ? 'show' : '' }}" id="sidebarUsers">
                        <ul class="nav nav-sm flex-column">
                            @can('view users')
                            <li class="nav-item">
                                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" data-key="t-user-list"> {{ __('sidebar.user_list') }} </a>
                            </li>
                            @endcan
                            @canany(['view roles', 'manage roles'])
                            <li class="nav-item">
                                <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" data-key="t-roles"> {{ __('sidebar.roles') }} </a>
                            </li>
                            @endcan
                            @can('manage permissions')
                            <li class="nav-item">
                                <a href="{{ route('permissions.index') }}" class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}" data-key="t-permissions"> {{ __('sidebar.permissions') }} </a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcanany

                @can('view settings')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                        <i class="ri-settings-4-line"></i> <span data-key="t-settings">{{ __('sidebar.system_settings') }}</span>
                    </a>
                </li>
                @endcan

                @can('view audit logs')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}">
                        <i class="ri-history-line"></i> <span data-key="t-audit">{{ __('sidebar.audit_logs') }}</span>
                    </a>
                </li>
                @endcan

                <!-- Notifications (Admin Only) -->
                @role('super-admin')
                <li class="nav-item">
                    @php $notiActive = request()->routeIs('admin.notifications.*'); @endphp
                    <a class="nav-link menu-link {{ $notiActive ? 'active' : '' }}" href="#sidebarNotifications" data-bs-toggle="collapse" 
                       role="button" aria-expanded="{{ $notiActive ? 'true' : 'false' }}" aria-controls="sidebarNotifications">
                        <i class="ri-notification-3-line"></i> <span data-key="t-notifications">{{ __('sidebar.notifications') }}</span>
                    </a>
                    <div class="collapse menu-dropdown {{ $notiActive ? 'show' : '' }}" id="sidebarNotifications">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('admin.notifications.dashboard') }}" class="nav-link {{ request()->routeIs('admin.notifications.dashboard') ? 'active' : '' }}" data-key="t-noti-dashboard"> {{ __('sidebar.dashboards') }} </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.notifications.statistics') }}" class="nav-link {{ request()->routeIs('admin.notifications.statistics') ? 'active' : '' }}" data-key="t-noti-stats"> {{ __('sidebar.statistics') }} </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.notifications.thresholds.index') }}" class="nav-link {{ request()->routeIs('admin.notifications.thresholds.*') ? 'active' : '' }}" data-key="t-noti-thresholds"> {{ __('sidebar.thresholds') }} </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endrole

                <!-- ERMO Infrastructure (Admin Only) -->
                @role('super-admin')
                <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-infrastructure">{{ __('ermo.infrastructure') }}</span></li>
                <li class="nav-item">
                    @php $ermoActive = request()->routeIs('admin.ermo.*'); @endphp
                    <a class="nav-link menu-link {{ $ermoActive ? 'active' : '' }}" href="#sidebarERMO" data-bs-toggle="collapse" 
                       role="button" aria-expanded="{{ $ermoActive ? 'true' : 'false' }}" aria-controls="sidebarERMO">
                        <i class="ri-cpu-line"></i> <span data-key="t-ermo">{{ __('ermo.ermo_orchestrator') }}</span>
                    </a>
                    <div class="collapse menu-dropdown {{ $ermoActive ? 'show' : '' }}" id="sidebarERMO">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('admin.ermo.index') }}" class="nav-link {{ request()->routeIs('admin.ermo.index') ? 'active' : '' }}" data-key="t-ermo-control"> {{ __('ermo.mission_control') }} </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.ermo.modules.index') }}" class="nav-link {{ request()->routeIs('admin.ermo.modules.*') ? 'active' : '' }}" data-key="t-ermo-modules"> {{ __('ermo.module_management') }} </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endrole
            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>