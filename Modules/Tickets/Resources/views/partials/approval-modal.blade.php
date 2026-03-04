<div class="modal fade" id="deleteRequestsModal" tabindex="-1" aria-labelledby="deleteRequestsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteRequestsModalLabel">{{ __('tickets::messages.delete_requests') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>{{ __('tickets::messages.requested_by') }}</th>
                            <th>{{ __('tickets::messages.item_to_delete') }}</th>
                            <th>{{ __('tickets::messages.page') }}</th>
                            <th>{{ __('tickets::messages.date') }}</th>
                            <th>{{ __('tickets::messages.action') }}</th>
                        </tr>
                    </thead>
                    <tbody id="deleteRequestsTableBody">
                        <!-- Loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function openDeleteRequests(type, id = null) {
        const tbody = document.getElementById('deleteRequestsTableBody');
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';

        let url = `/admin/tickets/delete-requests?type=${encodeURIComponent(type)}`;
        if (id) {
            url += `&id=${encodeURIComponent(id)}`;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';
                if (data.length === 0) {
                    tbody.innerHTML =
                        `<tr><td colspan="5" class="text-center">${"{{ __('tickets::messages.no_data_found') }}"}</td></tr>`;
                    return;
                }

                data.forEach(req => {
                    const row = `
                        <tr class="text-center">
                            <td>
                                <div class="fw-medium">${req.user.name}</div>
                                <div class="text-muted fs-11">${req.user.email}</div>
                            </td>
                            <td><span class="badge bg-light text-dark fs-12">${req.item_name}</span></td>
                            <td><span class="badge bg-soft-info text-info fs-11">${req.page}</span></td>
                            <td class="fs-12">${req.created_at}</td>
                            <td>
                                <div class="d-flex gap-2 justify-content-center">
                                    <form method="POST" action="/admin/tickets/delete-requests/${req.id}/approve">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="ri-check-line align-middle me-1"></i> {{ __('tickets::messages.approve') }}
                                        </button>
                                    </form>
                                    <form method="POST" action="/admin/tickets/delete-requests/${req.id}/reject">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="ri-close-line align-middle me-1"></i> {{ __('tickets::messages.reject') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML =
                    `<tr><td colspan="5" class="text-center text-danger">Error loading requests.</td></tr>`;
            });

        new bootstrap.Modal(document.getElementById('deleteRequestsModal')).show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const openApprovalType = urlParams.get('open_approval');
        const approvalId = urlParams.get('approval_id');

        if (openApprovalType) {
            // Give it a brief timeout to ensure everything is rendered
            setTimeout(() => {
                openDeleteRequests(openApprovalType, approvalId);
            }, 300);

            // Remove params from URL so refreshing won't trigger again
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
</script>
