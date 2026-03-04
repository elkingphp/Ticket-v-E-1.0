<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    function deleteItem(url, isRequest = false) {
        let title = "{{ __('tickets::messages.confirm_delete') }}";
        let text = "{{ __('tickets::messages.delete_warning') }}";
        let confirmText = "{{ __('tickets::messages.delete_it') }}";

        if (isRequest) {
            title = "{{ __('tickets::messages.confirm_delete_request') }}";
            text = "{{ __('tickets::messages.delete_request_warning') }}";
            confirmText = "{{ __('tickets::messages.request_delete') }}";
        }

        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            iconColor: '#f06548',
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: "{{ __('tickets::messages.cancel') }}",
            customClass: {
                confirmButton: 'btn btn-danger w-xs me-2',
                cancelButton: 'btn btn-light w-xs',
            },
            buttonsStyling: false,
            showCloseButton: true,
            background: '#fff',
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        }).then(function (result) {
            if (result.value) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    @if(session('success'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            icon: 'success',
            title: "{{ session('success') }}"
        });
    @endif

    @if(session('error'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            icon: 'error',
            title: "{{ session('error') }}"
        });
    @endif
</script>
<link rel="stylesheet" href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}">
