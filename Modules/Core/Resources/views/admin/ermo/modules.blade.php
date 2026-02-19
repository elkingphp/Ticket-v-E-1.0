@extends('core::layouts.master')

@section('title', __('ermo.module_management'))

@section('content')
<div class="row">
    <!-- Global Actions Section -->
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-4 bg-primary-subtle">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="ri-settings-5-line me-2"></i>{{ __('ermo.cluster_administration') }}</h4>
                        <p class="text-muted mb-0">{{ __('ermo.manage_registry_desc') }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModuleModal">
                            <i class="ri-add-line align-bottom me-1"></i> {{ __('ermo.add_plugin') }}
                        </button>
                        <button class="btn btn-danger" id="runChaosBtn">
                            <i class="ri-fire-line align-bottom me-1"></i> {{ __('ermo.simulate_chaos') }}
                        </button>
                         <button class="btn btn-soft-secondary" onclick="loadModules()">
                            <i class="ri-refresh-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content: Table & Graph -->
    <div class="col-xl-8">
        <div class="card" id="modulesList">
            <div class="card-header border-0 align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('ermo.operational_registry') }}</h4>
                <div class="flex-shrink-0">
                    <div id="connection-status" class="badge bg-success-subtle text-success">
                        <span class="pulse-dot-small bg-success"></span> {{ __('ermo.live_sync_active') }}
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-nowrap align-middle mb-0">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th scope="col">{{ __('ermo.identification') }}</th>
                                <th scope="col">{{ __('ermo.status') }}</th>
                                <th scope="col">{{ __('ermo.throughput') }}</th>
                                <th scope="col">{{ __('ermo.sla_compliance') }}</th>
                                <th scope="col" class="text-end">{{ __('ermo.management') }}</th>
                            </tr>
                        </thead>
                        <tbody id="module-management-body">
                            <!-- Injected by AJAX -->
                        </tbody>
                    </table>
                </div>
                <div id="no-modules" class="text-center py-5 d-none">
                    <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#405189,secondary:#0ab39c" style="width:75px;height:75px"></lord-icon>
                    <h5 class="mt-2 text-muted">{{ __('ermo.no_modules_found') }}</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Graph & Feed Section -->
    <div class="col-xl-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('ermo.logical_topology') }}</h5>
            </div>
            <div class="card-body p-0">
                <div id="topology-graph" style="height: 350px; background: #ffffff;"></div>
            </div>
            <div class="card-footer bg-light p-2">
                <small class="text-muted"><i class="ri-information-line me-1"></i> {{ __('ermo.topology_desc') }}</small>
            </div>
        </div>

        <div id="chaos-results-card" class="card shadow-sm d-none border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0 text-white">{{ __('ermo.latest_chaos_result') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-sm flex-shrink-0 me-3">
                        <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-20">
                            <i class="ri-alert-line"></i>
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-1" id="chaos-outcome">Outcome: N/A</h6>
                        <small class="text-muted" id="chaos-time"></small>
                    </div>
                </div>
                <p class="mb-0 small" id="chaos-summary"></p>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->

<!-- Add Module Modal -->
<div class="modal fade" id="addModuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary p-3">
                <h5 class="modal-title text-white" id="exampleModalLabel">{{ __('ermo.register_new_module') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addModuleForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('ermo.module_name') }}</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Image Optimizer" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('ermo.slug') }}</label>
                        <input type="text" name="slug" class="form-control" placeholder="e.g. image-optimizer" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('ermo.version') }}</label>
                                <input type="text" name="version" class="form-control" placeholder="1.0.0" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('ermo.concurrency_limit') }}</label>
                                <input type="number" name="max_concurrent_requests" class="form-control" value="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('ermo.module_type') }}</label>
                        <select name="is_core" class="form-select">
                            <option value="0">{{ __('ermo.standard_plugin') }}</option>
                            <option value="1">{{ __('ermo.core_infrastructure') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('ermo.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('ermo.establish_registry') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Module Modal -->
<div class="modal fade" id="editModuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-info p-3">
                <h5 class="modal-title text-white">{{ __('ermo.modify_module_settings') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editModuleForm">
                <input type="hidden" name="id" id="edit-module-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('ermo.module_name') }}</label>
                        <input type="text" name="name" id="edit-name" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('ermo.version') }}</label>
                                <input type="text" name="version" id="edit-version" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('ermo.concurrency_limit') }}</label>
                                <input type="number" name="max_concurrent_requests" id="edit-max-requests" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('ermo.dependencies') }}</label>
                        <select name="dependencies[]" id="edit-dependencies" class="form-select select2" multiple>
                            @foreach($availableModules as $m)
                                <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->slug }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ __('ermo.circular_dep_warning') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('ermo.discard') }}</button>
                    <button type="submit" class="btn btn-info">{{ __('ermo.update_cluster_sync') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .pulse-dot-small {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
        animation: pulse-small 1.5s infinite;
    }
    @keyframes pulse-small {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(10, 179, 156, 0.7); }
        70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(10, 179, 156, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(10, 179, 156, 0); }
    }
    .topology-node rect {
        rx: 15; ry: 15;
        fill: #fff;
        stroke: #e2e8f0;
        filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.05));
    }
    .node-active rect { stroke: #0ab39c; fill: #f0fdfa; }
    .node-degraded rect { stroke: #f06548; fill: #fef2f2; }
    .node-maintenance rect { stroke: #f7b84b; fill: #fffbeb; }
    .node-disabled rect { stroke: #405189; fill: #eff6ff; }
    
    .link { stroke: #cbd5e1; stroke-width: 1.5px; opacity: 0.6; }
    .node-label { font-size: 11px; font-weight: 600; fill: #334155; }
</style>
@endpush

@push('scripts')
<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
    const API = {
        METRICS: '{{ route('admin.ermo.metrics') }}',
        TRANSITION: '{{ route('admin.ermo.transition') }}',
        GRAPH: '{{ route('admin.ermo.graph') }}',
        MODULES: '{{ route('admin.ermo.modules.index') }}',
        CHAOS: '{{ route('admin.ermo.chaos.simulate') }}'
    };

    const LANG = {
        configure: '{{ __('ermo.configure') }}',
        activate: '{{ __('ermo.activate') }}',
        maintenance: '{{ __('ermo.maintenance') }}',
        degrade: '{{ __('ermo.degrade') }}',
        scrap: '{{ __('ermo.scrap_module') }}',
        transition_title: '{{ __('ermo.transition_title') }}',
        transition_confirm: '{{ __('ermo.transition_confirm', ['state' => '${newState.toUpperCase()}']) }}', // Handle in JS
        reason_placeholder: '{{ __('ermo.reason_placeholder') }}',
        orchestration_complete: '{{ __('ermo.orchestration_complete') }}',
        policy_violation: '{{ __('ermo.policy_violation') }}',
        cluster_error: '{{ __('ermo.cluster_error') }}',
        injecting: '{{ __('ermo.injecting') }}',
        injection_confirmation: '{{ __('ermo.injection_confirmation') }}',
        chaos_desc: '{{ __('ermo.chaos_desc') }}',
        engage_chaos: '{{ __('ermo.engage_chaos') }}',
        outcome: 'Outcome',
    };

    let modulesData = [];

    async function loadModules() {
        try {
            const response = await fetch(API.METRICS);
            const result = await response.json();
            if (result.status === 'success') {
                modulesData = result.data;
                renderModulesTable(result.data);
                renderTopology(true); // Don't reload graph every time
            }
        } catch (e) {
            console.error('Fetch error:', e);
        }
    }

    function renderModulesTable(data) {
        const tbody = document.getElementById('module-management-body');
        const emptyState = document.getElementById('no-modules');
        
        if (data.length === 0) {
            tbody.innerHTML = '';
            emptyState.classList.remove('d-none');
            return;
        }

        emptyState.classList.add('d-none');
        tbody.innerHTML = data.map(m => `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-light rounded p-1 me-2">
                            <i class="ri-instance-line fs-18 text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">${m.name}</h6>
                            <small class="text-muted"><span class="badge ${m.is_core ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary'} ms-1 small">v${m.version}</span></small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-${getStatusColor(m.status)}-subtle text-${getStatusColor(m.status)} text-uppercase">${m.status}</span>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-medium">${m.active_requests}</span>
                        <div class="progress progress-sm" style="width: 50px;">
                            <div class="progress-bar bg-primary" style="width: ${Math.min((m.active_requests / (m.max_concurrent || 1)) * 100, 100)}%"></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="text-${m.uptime > 99.9 ? 'success' : 'warning'} fw-bold me-2">${m.uptime.toFixed(2)}%</span>
                        <div class="vr mx-2" style="height: 12px;"></div>
                        <small class="text-muted">${Math.round(m.latency)}ms</small>
                    </div>
                </td>
                <td class="text-end">
                    <div class="dropdown">
                        <button class="btn btn-soft-secondary btn-sm" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="openEditModal('${m.id}')"><i class="ri-edit-2-line me-2 align-bottom"></i> ${LANG.configure}</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="initTransition('${m.slug}', 'active')"><i class="ri-play-line me-2 align-bottom text-success"></i> ${LANG.activate}</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="initTransition('${m.slug}', 'maintenance')"><i class="ri-settings-line me-2 align-bottom text-warning"></i> ${LANG.maintenance}</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="initTransition('${m.slug}', 'degraded')"><i class="ri-error-warning-line me-2 align-bottom text-danger"></i> ${LANG.degrade}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger ${m.is_core ? 'disabled' : ''}" href="javascript:void(0)" onclick="deleteModule('${m.id}', '${m.name}')"><i class="ri-delete-bin-line me-2 align-bottom"></i> ${LANG.scrap}</a></li>
                        </ul>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async function initTransition(slug, newState) {
        const { value: reason } = await Swal.fire({
            title: `${LANG.transition_title}: ${slug.toUpperCase()}`,
            text: `Confirm transition to ${newState.toUpperCase()}? This will be audited.`, // Dynamic template workaround
            input: 'text',
            inputPlaceholder: LANG.reason_placeholder,
            showCancelButton: true,
            confirmButtonColor: '#405189',
            cancelButtonColor: '#f06548',
        });

        if (reason === undefined) return;

        try {
            const resp = await fetch(API.TRANSITION, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ slug, status: newState, reason })
            });
            const res = await resp.json();
             if (resp.status === 403 && res.type === 'sudo_required') {
                window.location.href = '{{ route('profile.sudo') }}';
                return;
            }
            if (res.status === 'success') {
                Swal.fire({ title: LANG.orchestration_complete, text: res.message, icon: 'success', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                loadModules();
                renderTopology();
            } else {
                Swal.fire(LANG.policy_violation, res.message, 'error');
            }
        } catch (e) { Swal.fire(LANG.cluster_error, 'Critical communication failure.', 'error'); }
    }

    // CRUD Ops
    document.getElementById('addModuleForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const resp = await fetch(API.MODULES, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(data)
            });
            const res = await resp.json();
            if (res.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('addModuleModal')).hide();
                Swal.fire('Registry Updated', res.message, 'success');
                loadModules();
                e.target.reset();
            } else { Swal.fire('Registration Failed', res.message, 'error'); }
        } catch (e) {}
    });

    window.openEditModal = (id) => {
        const m = modulesData.find(m => m.id === id);
        if (!m) return;
        document.getElementById('edit-module-id').value = m.id;
        document.getElementById('edit-name').value = m.name;
        document.getElementById('edit-version').value = m.version;
        document.getElementById('edit-max-requests').value = m.max_concurrent;
        
        // Multi-select handling would need more care with select2, but for simplicity:
        // Set values and trigger change if select2 is used
        const editModal = new bootstrap.Modal(document.getElementById('editModuleModal'));
        editModal.show();
    };

    document.getElementById('editModuleForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('edit-module-id').value;
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        // Handle multi-select dependencies
        data.dependencies = Array.from(e.target.elements['dependencies[]'].selectedOptions).map(opt => opt.value);

        try {
            const resp = await fetch(`${API.MODULES}/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(data)
            });
            const res = await resp.json();
             if (resp.status === 403) { window.location.href = '{{ route('profile.sudo') }}'; return; }
            if (res.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('editModuleModal')).hide();
                Swal.fire('Configuration Sync', res.message, 'success');
                loadModules();
                renderTopology();
            }
        } catch (e) {}
    });

    window.deleteModule = async (id, name) => {
        const { isConfirmed } = await Swal.fire({
            title: `Decommission ${name}?`,
            text: "This operation will permanently scrap the module from the runtime registry and filesystem links.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'SCRAP MODULE',
            confirmButtonColor: '#f06548'
        });

        if (!isConfirmed) return;

        try {
            const resp = await fetch(`${API.MODULES}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const res = await resp.json();
            if (resp.status === 403) { window.location.href = '{{ route('profile.sudo') }}'; return; }
            if (res.status === 'success') {
                Swal.fire('Scrapped', res.message, 'success');
                loadModules();
                renderTopology();
            } else { Swal.fire('Protection Triggered', res.message, 'error'); }
        } catch (e) {}
    };

    // Chaos Simulation
    document.getElementById('runChaosBtn').addEventListener('click', async () => {
        const { isConfirmed } = await Swal.fire({
            title: LANG.injection_confirmation,
            text: LANG.chaos_desc,
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: LANG.engage_chaos,
            confirmButtonColor: '#f06548'
        });

        if (!isConfirmed) return;

        const btn = document.getElementById('runChaosBtn');
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> ${LANG.injecting}`;

        try {
            const resp = await fetch(API.CHAOS, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const res = await resp.json();
            
            if (res.status === 'success') {
                showChaosResult(res.data);
            }
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-fire-line align-bottom me-1"></i> Simulate Chaos';
            loadModules();
        }
    });

    function showChaosResult(report) {
        const card = document.getElementById('chaos-results-card');
        card.classList.remove('d-none');
        document.getElementById('chaos-outcome').textContent = `${LANG.outcome}: ${report.outcome.toUpperCase()}`;
        document.getElementById('chaos-time').textContent = report.timestamp;
        document.getElementById('chaos-summary').textContent = report.report;
        
        setTimeout(() => { card.classList.add('d-none'); }, 15000);
    }

    // Topology Visualization
    let simulation, svg;
    async function renderTopology(forceBuild = false) {
        try {
            const response = await fetch(API.GRAPH);
            const result = await response.json();
            if (result.status !== 'success') return;

            const container = document.getElementById('topology-graph');
            const width = container.clientWidth;
            const height = container.clientHeight;
            
            if (forceBuild || !svg) {
                container.innerHTML = '';
                svg = d3.select("#topology-graph").append("svg")
                    .attr("width", width)
                    .attr("height", height);
            }

            const data = result.data;
            if (simulation) simulation.stop();

            simulation = d3.forceSimulation(data.nodes)
                .force("link", d3.forceLink(data.edges).id(d => d.id).distance(120))
                .force("charge", d3.forceManyBody().strength(-300))
                .force("center", d3.forceCenter(width / 2, height / 2));

            svg.selectAll("*").remove();

            svg.append("defs").append("marker")
                .attr("id", "arrowhead")
                .attr("viewBox", "0 -5 10 10")
                .attr("refX", 30)
                .attr("markerWidth", 5)
                .attr("markerHeight", 5)
                .attr("orient", "auto")
                .append("path").attr("d", "M0,-5L10,0L0,5").attr("fill", "#cbd5e1");

            const link = svg.selectAll(".link")
                .data(data.edges).enter().append("line")
                .attr("class", "link")
                .attr("marker-end", "url(#arrowhead)");

            const node = svg.selectAll(".node")
                .data(data.nodes).enter().append("g")
                .attr("class", d => `topology-node node-${d.status}`)
                .call(d3.drag()
                    .on("start", (e, d) => { if (!e.active) simulation.alphaTarget(0.3).restart(); d.fx = d.x; d.fy = d.y; })
                    .on("drag", (e, d) => { d.fx = e.x; d.fy = e.y; })
                    .on("end", (e, d) => { if (!e.active) simulation.alphaTarget(0); d.fx = null; d.fy = null; }));

            node.append("rect")
                .attr("width", 100)
                .attr("height", 30)
                .attr("x", -50)
                .attr("y", -15)
                .attr("stroke-width", 2);

            node.append("text")
                .attr("text-anchor", "middle")
                .attr("dy", ".35em")
                .attr("class", "node-label")
                .text(d => d.label.length > 12 ? d.label.substring(0, 10) + '..' : d.label);

            simulation.on("tick", () => {
                link.attr("x1", d => d.source.x).attr("y1", d => d.source.y).attr("x2", d => d.target.x).attr("y2", d => d.target.y);
                node.attr("transform", d => `translate(${d.x},${d.y})`);
            });

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
    loadModules();
    renderTopology(true);

    // WebSocket Real-time Sync
    if (typeof window.Echo !== 'undefined') {
        window.Echo.private('ermo.cluster')
            .listen('.cluster.updated', (e) => {
                console.log('ERMO Module Event:', e);
                
                if (['state_transition', 'module_created', 'module_deleted', 'module_updated'].includes(e.type)) {
                    loadModules();
                    renderTopology(true);
                    
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
                   loadModules();
                   showChaosResult(e.data);
                }
            });
    } else {
        // Fallback to polling
        setInterval(loadModules, 5000);
    }
</script>
@endpush
