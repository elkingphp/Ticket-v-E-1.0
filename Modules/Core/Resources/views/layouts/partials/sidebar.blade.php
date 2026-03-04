<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="/" class="logo logo-dark">
            <span class="logo-sm">
                @if (get_setting('logo_sm'))
                    <img src="{{ asset(get_setting('logo_sm')) }}" alt="" height="auto"
                        style="object-fit: contain;">
                @elseif(get_setting('logo_dark'))
                    <img src="{{ asset(get_setting('logo_dark')) }}" alt="" height="auto"
                        style="object-fit: contain;">
                @else
                    <span class="fs-22 fw-bold text-white">D</span>
                @endif
            </span>
            <span class="logo-lg">
                @if (get_setting('logo_dark'))
                    <img src="{{ asset(get_setting('logo_dark')) }}" alt="" height="auto"
                        style="object-fit: contain;">
                @else
                    <span class="fs-22 fw-bold text-white">{{ get_setting('site_name', 'DIGILIANS') }}</span>
                @endif
            </span>
        </a>
        <!-- Light Logo-->
        <a href="/" class="logo logo-light">
            <span class="logo-sm">
                @if (get_setting('logo_sm'))
                    <img src="{{ asset(get_setting('logo_sm')) }}" alt="" height="auto"
                        style="object-fit: contain;">
                @elseif(get_setting('logo_light'))
                    <img src="{{ asset(get_setting('logo_light')) }}" alt="" height="auto"
                        style="object-fit: contain;">
                @else
                    <span class="fs-22 fw-bold text-white">D</span>
                @endif
            </span>
            <span class="logo-lg">
                @if (get_setting('logo_light'))
                    <img src="{{ asset(get_setting('logo_light')) }}" alt="" height="auto"
                        style="object-fit: contain;">
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
                    <a class="nav-link menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <i class="ri-dashboard-2-line"></i> <span
                            data-key="t-dashboards">{{ __('sidebar.dashboards') }}</span>
                    </a>
                </li>

                <!-- Educational Module -->
                @canany(['education.attendance.view', 'education.lectures.view', 'education.evaluations.manage',
                    'education.access'])
                    <li class="menu-title"><i class="ri-more-fill"></i> <span
                            data-key="t-educational">{{ __('educational::messages.educational') }}</span></li>
                    <li class="nav-item">
                        @php $educationalActive = request()->routeIs('educational.dashboard') || request()->routeIs('educational.overview') || request()->routeIs('educational.attendance.*') || request()->routeIs('educational.lectures.*') || request()->routeIs('educational.evaluations.*'); @endphp
                        <a class="nav-link menu-link {{ $educationalActive ? 'active' : '' }}" href="#sidebarEducational"
                            data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $educationalActive ? 'true' : 'false' }}" aria-controls="sidebarEducational">
                            <i class="ri-book-3-line"></i> <span
                                data-key="t-education">{{ __('educational::messages.educational') }}</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $educationalActive ? 'show' : '' }}" id="sidebarEducational">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('educational.dashboard') }}"
                                        class="nav-link {{ request()->routeIs('educational.dashboard') ? 'active' : '' }}"
                                        data-key="t-dashboard">{{ __('educational::messages.dashboard') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('educational.overview') }}"
                                        class="nav-link {{ request()->routeIs('educational.overview') ? 'active' : '' }}"
                                        data-key="t-overview">نظرة عامة قيادية</a>
                                </li>
                                @can('education.attendance.view')
                                    <li class="nav-item">
                                        <a href="{{ route('educational.attendance.dashboard') }}"
                                            class="nav-link {{ request()->routeIs('educational.attendance.dashboard') ? 'active' : '' }}"
                                            data-key="t-attendance">{{ __('educational::messages.attendance') }}</a>
                                    </li>
                                @endcan

                                @can('education.attendance.override')
                                    <li class="nav-item">
                                        <a href="{{ route('educational.attendance.override.list') }}"
                                            class="nav-link {{ request()->routeIs('educational.attendance.override.list') ? 'active' : '' }}"
                                            data-key="t-attendance-override">{{ __('educational::messages.overrides') }}</a>
                                    </li>
                                @endcan
                                @can('education.lectures.view')
                                    <li class="nav-item">
                                        <a href="{{ route('educational.lectures.index') }}"
                                            class="nav-link {{ request()->routeIs('educational.lectures.index') ? 'active' : '' }}"
                                            data-key="t-lectures">{{ __('educational::messages.lectures') }}</a>
                                    </li>
                                @endcan
                                @can('education.evaluations.manage')
                                    <li class="nav-item">
                                        <a href="{{ route('educational.evaluations.forms.index') }}"
                                            class="nav-link {{ request()->routeIs('educational.evaluations.forms.*') ? 'active' : '' }}"
                                            data-key="t-evaluations">{{ __('educational::messages.evaluations') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>

                    <!-- Management (Educational) -->
                    @can('education.access')
                        <li class="nav-item">
                            @php $mgmtActive = request()->routeIs('educational.companies.*') || request()->routeIs('educational.instructors.*') || request()->routeIs('educational.students.*') || request()->routeIs('educational.programs.*') || request()->routeIs('educational.groups.*') || request()->routeIs('educational.schedules.*') || request()->routeIs('educational.tracks.*') || request()->routeIs('educational.job_profiles.*') || request()->routeIs('educational.governorates.*') || request()->routeIs('educational.session_types.*'); @endphp
                            <a class="nav-link menu-link {{ $mgmtActive ? 'active' : '' }}" href="#sidebarEducationAdmin"
                                data-bs-toggle="collapse" role="button" aria-expanded="{{ $mgmtActive ? 'true' : 'false' }}"
                                aria-controls="sidebarEducationAdmin">
                                <i class="ri-shield-user-line"></i> <span
                                    data-key="t-education-admin">{{ __('educational::messages.management') }}</span>
                            </a>
                            <div class="collapse menu-dropdown {{ $mgmtActive ? 'show' : '' }}" id="sidebarEducationAdmin">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('educational.companies.index') }}"
                                            class="nav-link {{ request()->routeIs('educational.companies.*') ? 'active' : '' }}">{{ __('educational::messages.training_companies') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('educational.instructors.index') }}"
                                            class="nav-link {{ request()->routeIs('educational.instructors.*') ? 'active' : '' }}">{{ __('educational::messages.instructors') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('educational.students.index') }}"
                                            class="nav-link {{ request()->routeIs('educational.students.*') ? 'active' : '' }}">{{ __('educational::messages.students') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('educational.tracks.index') }}"
                                            class="nav-link {{ request()->routeIs('educational.tracks.*') ? 'active' : '' }}">{{ __('educational::messages.tracks') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('educational.job_profiles.index') }}"
                                            class="nav-link {{ request()->routeIs('educational.job_profiles.*') ? 'active' : '' }}">{{ __('educational::messages.job_profiles') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('educational.programs.index') }}"
                                            class="nav-link {{ request()->routeIs('educational.programs.*') ? 'active' : '' }}">{{ __('educational::messages.programs') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('educational.governorates.index') }}"
                                            class="nav-link {{ request()->routeIs('educational.governorates.*') ? 'active' : '' }}">{{ __('educational::messages.governorates') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('educational.session_types.index') }}"
                                            class="nav-link {{ request()->routeIs('educational.session_types.*') ? 'active' : '' }}">{{ __('educational::messages.session_types') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('educational.groups.index') }}"
                                            class="nav-link {{ request()->routeIs('educational.groups.*') ? 'active' : '' }}">{{ __('educational::messages.groups') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('educational.schedules.index') }}"
                                            class="nav-link {{ request()->routeIs('educational.schedules.*') ? 'active' : '' }}">{{ __('educational::messages.schedule_templates') }}</a>
                                    </li>

                                </ul>
                            </div>
                        </li>

                        <!-- Academic Resources -->
                        <li class="nav-item">
                            @php $resActive = request()->routeIs('educational.campuses.*') || request()->routeIs('educational.buildings.*') || request()->routeIs('educational.floors.*') || request()->routeIs('educational.rooms.*'); @endphp
                            <a class="nav-link menu-link {{ $resActive ? 'active' : '' }}" href="#sidebarEducationResources"
                                data-bs-toggle="collapse" role="button" aria-expanded="{{ $resActive ? 'true' : 'false' }}"
                                aria-controls="sidebarEducationResources">
                                <i class="ri-building-line"></i> <span
                                    data-key="t-education-resources">{{ __('educational::messages.academic_resources') }}</span>
                            </a>
                            <div class="collapse menu-dropdown {{ $resActive ? 'show' : '' }}"
                                id="sidebarEducationResources">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('educational.campuses.index') }}"
                                            class="nav-link {{ request()->routeIs('educational.campuses.*') ? 'active' : '' }}">{{ __('educational::messages.campuses') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('educational.buildings.index') }}"
                                            class="nav-link {{ request()->routeIs('educational.buildings.*') ? 'active' : '' }}">{{ __('educational::messages.buildings') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('educational.floors.index') }}"
                                            class="nav-link {{ request()->routeIs('educational.floors.*') ? 'active' : '' }}">{{ __('educational::messages.floors') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('educational.rooms.index') }}"
                                            class="nav-link {{ request()->routeIs('educational.rooms.*') ? 'active' : '' }}">{{ __('educational::messages.rooms') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endcan
                @endcanany

                <!-- Tickets Module -->
                <li class="menu-title"><i class="ri-more-fill"></i> <span
                        data-key="t-pages">{{ __('tickets::messages.tickets') }}</span></li>

                @can('tickets.view_dashboard')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('agent.tickets.dashboard') ? 'active' : '' }}"
                            href="{{ route('agent.tickets.dashboard') }}">
                            <i class="ri-dashboard-2-line"></i> <span
                                data-key="t-tickets-dashboard">{{ __('tickets::messages.dashboard') }}</span>
                        </a>
                    </li>
                @endcan

                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('tickets.index') ? 'active' : '' }}"
                        href="{{ route('tickets.index') }}">
                        <i class="ri-ticket-2-line"></i> <span
                            data-key="t-tickets">{{ __('tickets::messages.my_tickets') }}</span>
                    </a>
                </li>

                @canany(['tickets.view_desk', 'tickets.reply', 'tickets.distribute'])
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('agent.tickets.*') ? 'active' : '' }}"
                            href="{{ route('agent.tickets.index') }}">
                            <i class="ri-customer-service-2-line"></i> <span
                                data-key="t-agent-desk">{{ __('tickets::messages.support_desk') }}</span>
                        </a>
                    </li>
                @endcanany

                @canany(['tickets.templates.manage', 'tickets.stages.view', 'tickets.categories.view',
                    'tickets.complaints.view', 'tickets.statuses.view', 'tickets.priorities.view', 'tickets.groups.view'])
                    <li class="nav-item">
                        @php $ticketsAdminActive = request()->routeIs('admin.tickets.*'); @endphp
                        <a class="nav-link menu-link {{ $ticketsAdminActive ? 'active' : '' }}"
                            href="#sidebarTicketsAdmin" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $ticketsAdminActive ? 'true' : 'false' }}"
                            aria-controls="sidebarTicketsAdmin">
                            <i class="ri-settings-5-line"></i> <span
                                data-key="t-ticket-settings">{{ __('tickets::messages.manage_tickets') }}</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $ticketsAdminActive ? 'show' : '' }}"
                            id="sidebarTicketsAdmin" style="">
                            <ul class="nav nav-sm flex-column">

                                @can('tickets.stages.view')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.tickets.stages.index') }}"
                                            class="nav-link {{ request()->routeIs('admin.tickets.stages.*') ? 'active' : '' }}"
                                            data-key="t-stages">{{ __('tickets::messages.lookups.stages') }}</a>
                                    </li>
                                @endcan
                                @can('tickets.categories.view')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.tickets.categories.index') }}"
                                            class="nav-link {{ request()->routeIs('admin.tickets.categories.*') ? 'active' : '' }}"
                                            data-key="t-categories">{{ __('tickets::messages.lookups.categories') }}</a>
                                    </li>
                                @endcan
                                @can('tickets.complaints.view')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.tickets.complaints.index') }}"
                                            class="nav-link {{ request()->routeIs('admin.tickets.complaints.*') ? 'active' : '' }}"
                                            data-key="t-complaints">{{ __('tickets::messages.lookups.complaints') }}</a>
                                    </li>
                                @endcan
                                @can('tickets.statuses.view')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.tickets.statuses.index') }}"
                                            class="nav-link {{ request()->routeIs('admin.tickets.statuses.*') ? 'active' : '' }}"
                                            data-key="t-statuses">{{ __('tickets::messages.lookups.statuses') }}</a>
                                    </li>
                                @endcan
                                @can('tickets.priorities.view')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.tickets.priorities.index') }}"
                                            class="nav-link {{ request()->routeIs('admin.tickets.priorities.*') ? 'active' : '' }}"
                                            data-key="t-priorities">{{ __('tickets::messages.lookups.priorities') }}</a>
                                    </li>
                                @endcan
                                @can('tickets.groups.view')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.tickets.groups.index') }}"
                                            class="nav-link {{ request()->routeIs('admin.tickets.groups.*') ? 'active' : '' }}"
                                            data-key="t-groups">{{ __('tickets::messages.lookups.groups') }}</a>
                                    </li>
                                @endcan

                                @can('tickets.templates.manage')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.tickets.templates.index') }}"
                                            class="nav-link {{ request()->routeIs('admin.tickets.templates.*') ? 'active' : '' }}"
                                            data-key="t-templates">{{ __('tickets::messages.templates') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

                <li class="menu-title"><i class="ri-more-fill"></i> <span
                        data-key="t-pages">{{ __('sidebar.system') }}</span></li>

                @canany(['users.view', 'roles.view'])
                    <li class="nav-item">
                        @php $usersActive = request()->routeIs('users.*') || request()->routeIs('roles.*'); @endphp
                        <a class="nav-link menu-link {{ $usersActive ? 'active' : '' }}" href="#sidebarUsers"
                            data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $usersActive ? 'true' : 'false' }}" aria-controls="sidebarUsers">
                            <i class="ri-user-line"></i> <span
                                data-key="t-users">{{ __('sidebar.users_management') }}</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $usersActive ? 'show' : '' }}" id="sidebarUsers">
                            <ul class="nav nav-sm flex-column">
                                @can('users.view')
                                    <li class="nav-item">
                                        <a href="{{ route('users.index') }}"
                                            class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                                            data-key="t-user-list"> {{ __('sidebar.user_list') }} </a>
                                    </li>
                                @endcan
                                @canany(['roles.view', 'roles.manage'])
                                    <li class="nav-item">
                                        <a href="{{ route('roles.index') }}"
                                            class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                                            data-key="t-roles"> {{ __('sidebar.roles') }} </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

                @can('settings.view')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"
                            href="{{ route('settings.index') }}">
                            <i class="ri-settings-4-line"></i> <span
                                data-key="t-settings">{{ __('sidebar.system_settings') }}</span>
                        </a>
                    </li>
                @endcan

                @can('audit.view')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('audit.*') ? 'active' : '' }}"
                            href="{{ route('audit.index') }}">
                            <i class="ri-history-line"></i> <span
                                data-key="t-audit">{{ __('sidebar.audit_logs') }}</span>
                        </a>
                    </li>
                @endcan

                <!-- Notifications (Admin Only) -->
                @role('super-admin')
                    <li class="nav-item">
                        @php $notiActive = request()->routeIs('admin.notifications.*'); @endphp
                        <a class="nav-link menu-link {{ $notiActive ? 'active' : '' }}" href="#sidebarNotifications"
                            data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $notiActive ? 'true' : 'false' }}" aria-controls="sidebarNotifications">
                            <i class="ri-notification-3-line"></i> <span
                                data-key="t-notifications">{{ __('sidebar.notifications') }}</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $notiActive ? 'show' : '' }}" id="sidebarNotifications">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('admin.notifications.dashboard') }}"
                                        class="nav-link {{ request()->routeIs('admin.notifications.dashboard') ? 'active' : '' }}"
                                        data-key="t-noti-dashboard"> {{ __('sidebar.dashboards') }} </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.notifications.statistics') }}"
                                        class="nav-link {{ request()->routeIs('admin.notifications.statistics') ? 'active' : '' }}"
                                        data-key="t-noti-stats"> {{ __('sidebar.statistics') }} </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.notifications.thresholds.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.notifications.thresholds.*') ? 'active' : '' }}"
                                        data-key="t-noti-thresholds"> {{ __('sidebar.thresholds') }} </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endrole

                <!-- ERMO Infrastructure (Admin Only) -->
                @role('super-admin')
                    <li class="menu-title"><i class="ri-more-fill"></i> <span
                            data-key="t-infrastructure">{{ __('ermo.infrastructure') }}</span></li>
                    <li class="nav-item">
                        @php $ermoActive = request()->routeIs('admin.ermo.*'); @endphp
                        <a class="nav-link menu-link {{ $ermoActive ? 'active' : '' }}" href="#sidebarERMO"
                            data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $ermoActive ? 'true' : 'false' }}" aria-controls="sidebarERMO">
                            <i class="ri-cpu-line"></i> <span
                                data-key="t-ermo">{{ __('ermo.ermo_orchestrator') }}</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $ermoActive ? 'show' : '' }}" id="sidebarERMO">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('admin.ermo.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.ermo.index') ? 'active' : '' }}"
                                        data-key="t-ermo-control"> {{ __('ermo.mission_control') }} </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.ermo.modules.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.ermo.modules.*') ? 'active' : '' }}"
                                        data-key="t-ermo-modules"> {{ __('ermo.module_management') }} </a>
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
