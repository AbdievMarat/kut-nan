@if (session('success'))
    <script>
        document.addEventListener("DOMContentLoaded", () => {
        const message = "{{ is_array(session('success')) ? session('success')['text'] : session('success') }}";
            Swal.fire({
                icon: 'success',
                title: message,
                showConfirmButton: false,
                timer: 1500
            });
        });
    </script>
@endif

@if (session('error'))
    <script type="text/javascript">
        const message = "{{ is_array(session('error')) ? session('error')['text'] : session('error') }}";
        document.addEventListener("DOMContentLoaded", () => {
            Swal.fire({
                icon: 'error',
                title: message,
                showConfirmButton: false,
                timer: 1500
            });
        });
    </script>
@endif
