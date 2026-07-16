<footer>
    <div class="footer clearfix mb-0 text-muted">
        <div class="float-start">
            <p>{{ date('Y') }} &copy; Nexora Digital</p>
        </div>
        <div class="float-end">
            <p>Masuk sebagai <span class="text-primary fw-bold">{{ auth('admin')->user()->name ?? '' }}</span></p>
        </div>
    </div>
</footer>
