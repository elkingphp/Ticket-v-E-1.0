<!-- Toast Notifications -->
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal !== 'undefined') {
                window.Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                @if (session('success'))
                    Toast.fire({
                        icon: 'success',
                        title: {!! json_encode(session('success')) !!}
                    });
                @endif

                @if (session('error'))
                    Toast.fire({
                        icon: 'error',
                        title: {!! json_encode(session('error')) !!}
                    });
                @endif

                @if (session('warning'))
                    Toast.fire({
                        icon: 'warning',
                        title: {!! json_encode(session('warning')) !!}
                    });
                @endif

                @if ($errors->any())
                    Toast.fire({
                        icon: 'error',
                        title: {!! json_encode($errors->first()) !!}
                    });
                @endif

                @if (session('info'))
                    Toast.fire({
                        icon: 'info',
                        title: {!! json_encode(session('info')) !!}
                    });
                @endif

                @if (session('import_errors'))
                    console.log("Import errors detected in session");
                    Swal.fire({
                        title: 'نتائج الاستيراد - تنبيه',
                        html: `
                        <div class="text-start mt-2">
                            <p class="text-muted fw-bold mb-2">تم اكتشاف الأخطاء التالية في ملف البيانات:</p>
                            <div class="alert alert-warning border-0 p-3" style="max-height: 250px; overflow-y: auto;">
                                <ul class="list-unstyled mb-0 fs-13">
                                    @foreach (session('import_errors') as $error)
                                        <li class="mb-2 text-wrap"><i class="ri-error-warning-line me-1 text-danger"></i> {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    `,
                        icon: 'warning',
                        confirmButtonText: 'حسناً، فهمت',
                        customClass: {
                            confirmButton: 'btn btn-primary w-xs shadow-none'
                        },
                        buttonsStyling: false
                    });
                @endif
            }
        });
    </script>
@endpush
