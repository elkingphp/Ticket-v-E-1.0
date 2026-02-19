@extends('core::layouts.master')

@section('title', __('ermo.mission_control'))

@section('content')
<div class="row" id="ermo-dashboard">
    <!-- Alerts Section -->
    <div class="col-12" id="global-alerts">
        @if(config('ermo.emergency_bypass'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 border-start border-4 border-danger mb-3" role="alert">
                <i class="ri-error-warning-line me-3 align-middle fs-16"></i>
                <strong>{{ __('ermo.emergency_bypass_active') }}</strong> - {{ __('ermo.emergency_bypass_desc') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div id="redis-degraded-alert" class="d-none">
            <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 border-start border-4 border-warning mb-3" role="alert">
                <i class="ri-broadcast-line me-3 align-middle fs-16"></i>
                <strong>{{ __('ermo.redis_connectivity_issues') }}</strong> - {{ __('ermo.redis_degraded_desc') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="col-12">
        <div class="row">
            <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">{{ __('ermo.total_requests') }}</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <div>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-4" id="stat-total-requests">0</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle rounded fs-3">
                                    <i class="ri-pulse-line text-info"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">{{ __('ermo.active_modules') }}</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <div>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-4" id="stat-active-modules">0</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle rounded fs-3">
                                    <i class="ri-checkbox-circle-line text-success"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">{{ __('ermo.cluster_health') }}</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <div>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-4" id="stat-health-pct">100%</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle rounded fs-3">
                                    <i class="ri-shield-check-line text-primary"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
             <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">{{ __('ermo.system_latency') }}</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <div>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-4" id="stat-avg-latency">-- ms</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-warning-subtle rounded fs-3">
                                    <i class="ri-timer-flash-line text-warning"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Module Control Section -->
    <div class="col-xl-9">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('ermo.operational_registry') }}</h4>
                <div class="flex-shrink-0">
                    <button type="button" class="btn btn-soft-primary btn-sm" onclick="loadMetrics()">
                        <i class="ri-refresh-line align-middle"></i> {{ __('ermo.manual_refresh') }}
                    </button>
                </div>
            </div><!-- end card header -->

            <div class="card-body">
                <div class="table-responsive table-card">
                    <table class="table table-nowrap align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">{{ __('ermo.module_name') }}</th>
                                <th scope="col">{{ __('ermo.status') }}</th>
                                <th scope="col">{{ __('ermo.through_put_active') }}</th>
                                <th scope="col">{{ __('ermo.last_failure') }}</th>
                                <th scope="col">{{ __('ermo.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="module-table-body">
                            <!-- JS Injection -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">{{ __('ermo.sla_tracking') }}</h4>
            </div>
            <div class="card-body" id="sla-section">
                <!-- JS Injection -->
            </div>
        </div>
    </div>

    <!-- Cluster Management Sidebar -->
    <div class="col-xl-3">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('ermo.cluster_topology') }}</h4>
            </div>
            <div class="card-body p-0">
                <div id="dependency-graph-container" style="height: 400px; background: #f9fbfd;" class="d-flex align-items-center justify-content-center">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <p class="text-muted mb-0">{{ __('ermo.logical_topology') }}...</p>
                    </div>
                </div>
            </div>
            <div class="card-footer text-muted small">
                <i class="ri-information-line me-1"></i> {{ __('ermo.topology_desc') }}
            </div>
        </div>

        <div class="card border-primary border-top border-4">
            <div class="card-body">
                <h5 class="card-title text-primary"><i class="ri-rocket-line me-1"></i> {{ __('ermo.quick_suggestions') }}</h5>
                <div id="suggestions-area">
                    <p class="text-muted small">{{ __('ermo.suggestions_desc') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="module-row-template">
    <tr>
        <td>
            <div class="d-flex align-items-center">
                <div class="avatar-xs flex-shrink-0 me-3">
                    <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                        <i class="ri-cpu-line"></i>
                    </div>
                </div>
                <div>
                    <h6 class="fs-14 mb-0"><a href="javascript:void(0)" class="text-reset fw-medium module-name">NAME</a></h6>
                    <small class="text-muted">State Version: <span class="module-version">0</span></small>
                </div>
            </div>
        </td>
        <td class="module-status-badge">
            <!-- Badge -->
        </td>
        <td>
            <div class="d-flex align-items-center gap-2">
                <span class="fw-medium module-active-requests">0</span>
                <div class="progress progress-sm flex-grow-1" style="width: 60px;">
                    <div class="progress-bar bg-primary module-request-bar" role="progressbar" style="width: 0%"></div>
                </div>
            </div>
        </td>
        <td>
            <span class="text-muted module-last-failure">Never</span>
        </td>
        <td>
            <div class="dropdown">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item transition-btn" href="javascript:void(0);" data-state="active"><i class="ri-play-circle-line align-bottom me-2 text-success"></i> Activate</a></li>
                    <li><a class="dropdown-item transition-btn" href="javascript:void(0);" data-state="maintenance"><i class="ri-tools-line align-bottom me-2 text-warning"></i> Maintenance</a></li>
                    <li><a class="dropdown-item transition-btn" href="javascript:void(0);" data-state="degraded"><i class="ri-error-warning-line align-bottom me-2 text-danger"></i> Degrade</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item transition-btn" href="javascript:void(0);" data-state="disabled"><i class="ri-indeterminate-circle-line align-bottom me-2 text-muted"></i> Disable</a></li>
                </ul>
            </div>
        </td>
    </tr>
</template>
@endsection

@push('styles')
<style>
    .avatar-title {
        transition: transform 0.3s ease;
    }
    tr:hover .avatar-title {
        transform: scale(1.1);
    }
    #dependency-graph-container svg {
        cursor: grab;
    }
    #dependency-graph-container svg:active {
        cursor: grabbing;
    }
    .topology-node rect {
        rx: 13; ry: 13;
        fill: #fff;
        stroke: #e2e8f0;
        filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.05));
    }
    .node-active rect { stroke: #0ab39c; fill: #f0fdfa; }
    .node-degraded rect { stroke: #f06548; fill: #fef2f2; }
    .node-maintenance rect { stroke: #f7b84b; fill: #fffbeb; }
    .node-disabled rect { stroke: #405189; fill: #eff6ff; }
    
    .link { stroke: #cbd5e1; stroke-width: 1.5px; opacity: 0.6; }
    .node-label { font-size: 10px; font-weight: 600; fill: #334155; pointer-events: none; }
</style>
@endpush

@push('scripts')
<!-- D3.js is great for graphs -->
<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
    const CONFIG = {
        POLL_INTERVAL: 3000,
        ENDPOINTS: {
            METRICS: '{{ route('admin.ermo.metrics') }}',
            TRANSITION: '{{ route('admin.ermo.transition') }}',
            GRAPH: '{{ route('admin.ermo.graph') }}',
            SUGGESTIONS: '/api/v1/ermo/suggestions' // Reusing API suggestions
        }
    };

    let modulesData = [];

    async function loadMetrics() {
        try {
            const response = await fetch(CONFIG.ENDPOINTS.METRICS);
            const result = await response.json();
            if (result.status === 'success') {
                updateUI(result.data, result.emergency);
                modulesData = result.data;
            }
        } catch (error) {
            console.error('Failed to load metrics:', error);
        }
    }

    function updateUI(data, emergency) {
        const tbody = document.getElementById('module-table-body');
        const template = document.getElementById('module-row-template');
        const slaSection = document.getElementById('sla-section');
        
        // Update stats
        let totalActiveRequests = 0;
        let activeCount = 0;
        let healthyCount = 0;
        let totalLatencies = 0;

        tbody.innerHTML = '';
        slaSection.innerHTML = '';

        data.forEach(module => {
            totalActiveRequests += module.active_requests;
            if (module.status === 'active') activeCount++;
            if (module.health_status === 'healthy') healthyCount++;
            totalLatencies += module.latency;

            // Update Table
            const clone = template.content.cloneNode(true);
            clone.querySelector('.module-name').textContent = module.name;
            clone.querySelector('.module-version').textContent = module.updated_at; // Using display time
            clone.querySelector('.module-active-requests').textContent = module.active_requests;
            clone.querySelector('.module-last-failure').textContent = module.last_failure;
            
            const badgeTd = clone.querySelector('.module-status-badge');
            const statusColor = getStatusColor(module.status);
            badgeTd.innerHTML = `<span class="badge bg-${statusColor}-subtle text-${statusColor} text-uppercase">${module.status}</span>`;

            const progressBar = clone.querySelector('.module-request-bar');
            const satPct = module.max_concurrent > 0 ? (module.active_requests / module.max_concurrent) * 100 : 0;
            progressBar.style.width = Math.min(satPct, 100) + '%';
            if (satPct > 80) progressBar.classList.replace('bg-primary', 'bg-danger');

            // Setup Transition Buttons
            clone.querySelectorAll('.transition-btn').forEach(btn => {
                btn.addEventListener('click', () => initiateTransition(module.slug, btn.dataset.state));
            });

            // Tooltip integration
            const nameLink = clone.querySelector('.module-name');
            nameLink.setAttribute('data-bs-toggle', 'tooltip');
            nameLink.setAttribute('data-bs-placement', 'top');
            nameLink.setAttribute('title', `Reason: ${module.last_reason}`);

            tbody.appendChild(clone);

            // Update SLA Bars
            const complianceColor = module.uptime > 99.5 ? 'success' : (module.uptime > 98 ? 'warning' : 'danger');
            slaSection.innerHTML += `
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-medium">${module.name}</span>
                        <span class="text-${complianceColor} fw-bold">${module.uptime.toFixed(2)}% Compliance</span>
                    </div>
                    <div class="progress progress-xl animated-progess custom-progress progress-label">
                        <div class="progress-bar bg-${complianceColor}" role="progressbar" style="width: ${module.uptime}%" aria-valuenow="${module.uptime}" aria-valuemin="0" aria-valuemax="100">
                            <div class="label">${module.uptime.toFixed(2)}%</div>
                        </div>
                    </div>
                </div>
            `;
        });

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Global Stats
        document.getElementById('stat-total-requests').textContent = totalActiveRequests;
        document.getElementById('stat-active-modules').textContent = activeCount;
        document.getElementById('stat-avg-latency').textContent = (totalLatencies / data.length).toFixed(1) + ' ms';
        const healthPct = Math.round((healthyCount / data.length) * 100);
        document.getElementById('stat-health-pct').textContent = healthPct + '%';

        // Emergency alerts
        const redisAlert = document.getElementById('redis-degraded-alert');
        if (emergency.redis_degraded) redisAlert.classList.remove('d-none');
        else redisAlert.classList.add('d-none');
    }

    async function initiateTransition(slug, newState) {
        const { value: reason } = await Swal.fire({
            title: `Transition ${slug.toUpperCase()} to ${newState.toUpperCase()}`,
            input: 'text',
            inputLabel: 'Reason for manual override',
            inputPlaceholder: 'e.g. Scheduled maintenance, Hotfix, Scaling...',
            showCancelButton: true,
            confirmButtonText: 'Perform Transition',
            confirmButtonColor: '#405189',
            cancelButtonColor: '#f06548',
        });

        if (reason === undefined) return; // Cancelled

        try {
            const response = await fetch(CONFIG.ENDPOINTS.TRANSITION, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ slug, status: newState, reason })
            });

            const result = await response.json();

            if (response.status === 403 && result.type === 'sudo_required') {
                Swal.fire({
                    title: 'Sudo Mode Required',
                    text: 'Redirecting to secure verification...',
                    icon: 'warning',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = '{{ route('profile.sudo') }}';
                });
                return;
            }

            if (result.status === 'success') {
                Swal.fire({
                    title: 'Success',
                    text: result.message,
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                loadMetrics();
                loadGraph();
            } else {
                Swal.fire('Operation Denied', result.message, 'error');
            }
        } catch (e) {
            Swal.fire('Network Error', 'Could not communicate with cluster manager.', 'error');
        }
    }

    async function loadGraph() {
        try {
            const response = await fetch(CONFIG.ENDPOINTS.GRAPH);
            const result = await response.json();
            if (result.status === 'success') renderGraph(result.data);
        } catch (e) {}
    }

    function renderGraph(data) {
        const container = document.getElementById('dependency-graph-container');
        const width = container.clientWidth;
        const height = container.clientHeight;
        container.innerHTML = '';

        const svg = d3.select("#dependency-graph-container").append("svg")
            .attr("width", width)
            .attr("height", height);

        // Define Arrowhead
        svg.append("defs").append("marker")
            .attr("id", "arrowhead")
            .attr("viewBox", "0 -5 10 10")
            .attr("refX", 25)
            .attr("refY", 0)
            .attr("markerWidth", 6)
            .attr("markerHeight", 6)
            .attr("orient", "auto")
            .append("path")
            .attr("d", "M0,-5L10,0L0,5")
            .attr("fill", "#999");

        const simulation = d3.forceSimulation(data.nodes)
            .force("link", d3.forceLink(data.edges).id(d => d.id).distance(100))
            .force("charge", d3.forceManyBody().strength(-300))
            .force("center", d3.forceCenter(width / 2, height / 2));

        const link = svg.selectAll(".link")
            .data(data.edges)
            .enter().append("line")
            .attr("stroke", "#dcdfe6")
            .attr("stroke-width", 2)
            .attr("marker-end", "url(#arrowhead)");

        const node = svg.selectAll(".node")
            .data(data.nodes)
            .enter().append("g")
            .attr("class", d => `topology-node node-${d.status}`)
            .call(d3.drag()
                .on("start", dragstarted)
                .on("drag", dragged)
                .on("end", dragended));

        node.append("rect")
            .attr("width", 80)
            .attr("height", 26)
            .attr("x", -40)
            .attr("y", -13)
            .attr("stroke-width", 2);

        node.append("text")
            .attr("text-anchor", "middle")
            .attr("dy", ".35em")
            .attr("class", "node-label")
            .text(d => d.label.length > 12 ? d.label.substring(0, 10) + '..' : d.label);

        simulation.on("tick", () => {
            link
                .attr("x1", d => d.source.x)
                .attr("y1", d => d.source.y)
                .attr("x2", d => d.target.x)
                .attr("y2", d => d.target.y);

            node
                .attr("transform", d => `translate(${d.x},${d.y})`);
        });

        function dragstarted(event, d) {
            if (!event.active) simulation.alphaTarget(0.3).restart();
            d.fx = d.x;
            d.fy = d.y;
        }
        function dragged(event, d) {
            d.fx = event.x;
            d.fy = event.y;
        }
        function dragended(event, d) {
            if (!event.active) simulation.alphaTarget(0);
            d.fx = null;
            d.fy = null;
        }
    }

    async function loadSuggestions() {
        try {
            const response = await fetch(CONFIG.ENDPOINTS.SUGGESTIONS);
            const result = await response.json();
            const area = document.getElementById('suggestions-area');
            if (result.status === 'success' && result.data.length > 0) {
                area.innerHTML = result.data.map(s => `
                    <div class="alert alert-${s.priority === 'high' ? 'danger' : 'warning'} p-2 small mb-2 shadow-sm">
                        <h6 class="fs-12 fw-bold text-uppercase mb-1"><i class="ri-lightbulb-line"></i> ${s.type.replace('_', ' ')}</h6>
                        <p class="mb-2">${s.message}</p>
                        <button class="btn btn-sm btn-light w-100 fs-10" onclick="initiateTransition('${s.module}', '${s.action === 'reactivate' ? 'active' : 'maintenance'}')">TAKE ACTION</button>
                    </div>
                `).join('');
            } else {
                area.innerHTML = '<div class="text-center py-4"><i class="ri-checkbox-circle-line fs-24 text-success mb-2 d-block"></i><p class="text-muted small">Cluster is operating at peak efficiency. No recommendations.</p></div>';
            }
        } catch (e) {}
    }

    function getStatusColor(status) {
        switch(status) {
            case 'active': return 'success';
            case 'maintenance': return 'warning';
            case 'degraded': return 'danger';
            case 'disabled': return 'dark';
            default: return 'secondary';
        }
    }

    // Initial Load
    loadMetrics();
    loadGraph();
    loadSuggestions();

    // WebSocket Real-time Sync
    if (typeof window.Echo !== 'undefined') {
        window.Echo.private('ermo.cluster')
            .listen('.cluster.updated', (e) => {
                console.log('ERMO Cluster Event:', e);
                
                // Intelligent Re-sync based on event type
                if (['state_transition', 'module_created', 'module_deleted'].includes(e.type)) {
                    loadMetrics();
                    loadGraph();
                    
                    // Show premium toast
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'info',
                            title: '{{ __("ermo.cluster_update") }}',
                            text: e.data.slug ? `${e.data.slug.toUpperCase()} ${e.type.replace("_", " ")}` : e.type,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                } else if (e.type === 'chaos_injected') {
                   loadMetrics();
                   // Highlight active alerts if metrics changed significantly
                } else {
                    // Fallback to full sync for unknown event types
                    loadMetrics();
                }
            });
    } else {
        // Fallback to polling if Echo is unavailable
        setInterval(loadMetrics, 5000);
        console.warn('Echo not found, falling back to polling.');
    }
    
    // Low-frequency polling for non-critical elements
    setInterval(loadSuggestions, 30000); 
</script>
@endpush
