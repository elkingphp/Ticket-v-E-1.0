<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <script>
                    document.write(new Date().getFullYear())
                </script> © {{ config('app.name') }}.
            </div>
            <div class="col-sm-6">
                <div class="text-sm-end d-none d-sm-block">
                    {{ __('core::messages.developed_by') }} Digilians
                </div>
            </div>
        </div>
    </div>
</footer>
