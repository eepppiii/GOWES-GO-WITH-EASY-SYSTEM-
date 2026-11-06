// ==========================================
// LOGIKA UNTUK POP-UP LOGOUT (HALAMAN PROFIL)
// ==========================================

function openLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.classList.add('active');
    }
}

function closeLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

// Menutup modal jika area luar (overlay) diklik
document.addEventListener("DOMContentLoaded", function() {
    const modal = document.getElementById('logoutModal');
    
    if (modal) {
        modal.addEventListener('click', function(e) {
            // Jika yang diklik adalah elemen overlay itu sendiri (bukan isi kontennya)
            if (e.target === this) {
                closeLogoutModal();
            }
        });
    }
});