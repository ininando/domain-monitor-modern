{{-- Dipasang sebelum CSS agar tema dan status sidebar tidak berkedip saat halaman dimuat. --}}
<script>
    (function () {
        try {
            var tema = localStorage.getItem('theme');
            var gelap = tema === 'dark' || (!tema && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', gelap);
            document.documentElement.dataset.sidebar = localStorage.getItem('sidebar-collapsed') === '1' ? 'collapsed' : 'expanded';
        } catch (e) {}
    })();
</script>
