/* GOWES — DIALOG / MODAL PANEL (admin/unit.html, superadmin/tambah-petugas-admin.html).
   Tombol dengan data-open="id" membuka <div class="modal" id="id">.
   Tombol/klik latar/Esc menutup. Data baris (data-id, data-lokasi, data-status, data-kondisi)
   diisi ke dialog "Ubah Status Go-bike". Tanpa server: Konfirmasi hanya menutup dialog. */
document.addEventListener('DOMContentLoaded', function () {
  function tutup(m) { m.classList.remove('open'); }

  document.querySelectorAll('[data-open]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var m = document.getElementById(btn.dataset.open);
      if (!m) return;
      m.querySelectorAll('[data-modal]').forEach(function (el) {
        var v = btn.dataset[el.dataset.modal];
        if (v) el.textContent = v;
      });
      var st = m.querySelector('select[name=status]');
      if (st && btn.dataset.status) st.value = btn.dataset.status;
      var kd = m.querySelector('input[name=kondisi]');
      if (kd && btn.dataset.kondisi) kd.value = btn.dataset.kondisi;
      m.classList.add('open');
      var f = m.querySelector('input, select');
      if (f) f.focus();
    });
  });

  document.querySelectorAll('.modal').forEach(function (m) {
    m.addEventListener('click', function (e) { if (e.target === m) tutup(m); });
    m.querySelectorAll('[data-close]').forEach(function (b) { b.addEventListener('click', function () { tutup(m); }); });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') document.querySelectorAll('.modal.open').forEach(tutup);
  });
});
