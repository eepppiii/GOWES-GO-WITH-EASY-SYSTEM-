// ==========================================
// LOGIKA LOADING SCREEN (PRELOADER)
// ==========================================
(function() {
    // 1. Buat elemen HTML loader
    const loaderHTML = `
        <div id="gowes-loader">
            <img src="../img/logo.png" alt="GOWES Logo">
            <div class="loader-spinner"></div>
        </div>
    `;

    // 2. Suntikkan HTML loader ke dalam <body> segera setelah DOM siap
    document.addEventListener("DOMContentLoaded", function() {
        document.body.insertAdjacentHTML('beforeend', loaderHTML);
        
        const loader = document.getElementById('gowes-loader');

        // 3. Sembunyikan loader saat semua aset halaman selesai dimuat
        window.addEventListener('load', function() {
            if (loader) {
                loader.classList.add('hidden');
                // Hapus elemen dari DOM setelah animasi fade out selesai (opsional)
                setTimeout(() => { loader.remove(); }, 400);
            }
        });

        // 4. Tampilkan loader saat user mengklik link untuk pindah halaman
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                const target = this.getAttribute('target');
                
                // Abaikan link kosong, anchor (#), javascript, atau target="_blank"
                if (!href || href.startsWith('#') || href.startsWith('javascript') || target === '_blank') {
                    return;
                }

                // Cegah navigasi instan, tampilkan loader dulu
                e.preventDefault();
                
                if (loader) {
                    loader.classList.remove('hidden');
                    
                    // Beri jeda sedikit agar animasi loader terlihat sebelum pindah
                    setTimeout(() => {
                        window.location.href = href;
                    }, 300);
                } else {
                    window.location.href = href;
                }
            });
        });
    });
})();