@php
    $tableId = $id ?? 'datatable-' . Str::random(5);
@endphp

<div class="table-responsive">
    <table id="{{ $tableId }}" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
        <thead class="table-light">
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>

@if(isset($items) && method_exists($items, 'links'))
    <div class="mt-3">
        {{ $items->links() }}
    </div>
@endif

@push('scripts')
    <!-- DataTables JS for {{ $tableId }} -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof jQuery !== 'undefined' && $.fn.DataTable) {
                if (!$.fn.DataTable.isDataTable('#{{ $tableId }}')) {
                    $('#{{ $tableId }}').DataTable({
                        responsive: true,
                        // If items has links, we may want to disable client-side paging to avoid conflict
                        paging: {{ isset($items) && method_exists($items, 'links') ? 'false' : 'true' }},
                        searching: true,
                        ordering: true,
                        info: true,
                        language: {
                            search: "_INPUT_",
                            searchPlaceholder: "Search here..."
                        }
                    });
                }
            }
        });
    </script>
@endpush
