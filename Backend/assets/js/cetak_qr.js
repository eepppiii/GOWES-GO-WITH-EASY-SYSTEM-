// ==========================================
// LOGIKA MODAL QR CODE & TAMBAH UNIT
// ==========================================

// ---------- Modal QR ----------
function openQrModal() {
    const modal = document.getElementById('qrModal');
    if (modal) {
        modal.classList.add('active');
        // Scroll ke QR unit yang baru ditambahkan (jika ada)
        const baru = modal.querySelector('.qr-card-new');
        if (baru) {
            baru.scrollIntoView({ block: 'center' });
        }
    }
}

function closeQrModal() {
    const modal = document.getElementById('qrModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function printQrCodes() {
    window.print();
}

// ---------- Modal Tambah Unit ----------
function openTambahModal() {
    const modal = document.getElementById('tambahModal');
    if (modal) {
        modal.classList.add('active');
        const sel = document.getElementById('id_shelter');
        if (sel) sel.focus();
    }
}

function closeTambahModal() {
    const modal = document.getElementById('tambahModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

// ---------- Modal Ubah Unit ----------
function openUbahModal(btn) {
    const modal = document.getElementById('ubahModal');
    if (!modal) return;

    const status = btn.dataset.status;
    document.getElementById('ubah_id_sepeda').value = btn.dataset.id;
    document.getElementById('ubah_kode').value = btn.dataset.kode;
    document.getElementById('ubah_id_shelter').value = btn.dataset.shelter;

    const sel  = document.getElementById('ubah_status');
    const note = document.getElementById('ubah_note');
    sel.value = status;

    // Sepeda yang sedang dipinjam: status dikunci
    const dipinjam = (status === 'Dipinjam');
    sel.disabled = dipinjam;
    note.style.display = dipinjam ? 'block' : 'none';

    modal.classList.add('active');
}

function closeUbahModal() {
    const modal = document.getElementById('ubahModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

// ---------- Pesan sukses / error ----------
function closeFlash() {
    const el = document.getElementById('flashMsg');
    if (el) {
        el.classList.add('hide');
        setTimeout(function () { el.remove(); }, 300);
    }
}

document.addEventListener("DOMContentLoaded", function () {
    // Pesan otomatis hilang setelah 6 detik
    if (document.getElementById('flashMsg')) {
        setTimeout(closeFlash, 6000);
    }

    // Tutup modal jika klik di luar kotak modal
    ['qrModal', 'tambahModal', 'ubahModal'].forEach(function (id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === this) {
                    modal.classList.remove('active');
                }
            });
        }
    });

    // Tutup modal dengan tombol Esc
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeQrModal();
            closeTambahModal();
            closeUbahModal();
        }
    });

    // Setelah unit baru disimpan, otomatis tampilkan QR-nya
    if (document.body.dataset.openQr === '1') {
        openQrModal();
        // Bersihkan ?added= dari URL agar tidak muncul lagi saat refresh
        if (window.history.replaceState) {
            window.history.replaceState({}, '', window.location.pathname);
        }
    }
});