{{-- Toast Alert Notifications --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if (session('success'))
            Toastify({
                text: "{{ session('success') }}",
                duration: 3500,
                close: true,
                gravity: "top",
                position: "right",
                backgroundColor: "#4fbe87",
            }).showToast();
        @endif

        @if (session('error'))
            Toastify({
                text: "{{ session('error') }}",
                duration: 4000,
                close: true,
                gravity: "top",
                position: "right",
                backgroundColor: "#f3616d",
            }).showToast();
        @endif

        @if (session('warning'))
            Toastify({
                text: "{{ session('warning') }}",
                duration: 4000,
                close: true,
                gravity: "top",
                position: "right",
                backgroundColor: "#ffc107",
            }).showToast();
        @endif

        @if (session('info'))
            Toastify({
                text: "{{ session('info') }}",
                duration: 3500,
                close: true,
                gravity: "top",
                position: "right",
                backgroundColor: "#17a2b8",
            }).showToast();
        @endif

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                Toastify({
                    text: "{{ $error }}",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#f3616d",
                }).showToast();
            @endforeach
        @endif
    });
</script>
