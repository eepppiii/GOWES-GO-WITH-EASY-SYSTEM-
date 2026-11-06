/* GOWES — TAMBAH PETUGAS / ADMIN (superadmin/tambah-petugas-admin.html).
   Form [data-tambah="id-panel"]: isi wajib diisi, lalu baris baru ditambahkan ke tabel panel itu
   (hanya tampilan; belum disimpan ke server). Nilai kolom diambil urut dari input form,
   kecuali input password yang tidak pernah ditampilkan. */
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('form[data-tambah]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var inputs = Array.prototype.slice.call(form.querySelectorAll('input, select'));
      var kosong = inputs.filter(function (i) { return !i.value.trim(); });
      if (kosong.length) { kosong[0].focus(); return; }

      var tbody = document.querySelector('#' + form.dataset.tambah + ' tbody');
      var tr = document.createElement('tr');
      inputs.forEach(function (i) {
        if (i.type === 'password') return;        // password sengaja TIDAK ditampilkan di tabel
        var td = document.createElement('td');
        td.textContent = i.value.trim();          // textContent: aman dari HTML injection
        tr.appendChild(td);
      });
      tbody.appendChild(tr);

      form.reset();
      var modal = form.closest('.modal');
      if (modal) modal.classList.remove('open');
    });
  });
});