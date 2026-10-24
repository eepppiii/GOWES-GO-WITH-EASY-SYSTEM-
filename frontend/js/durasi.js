/* GOWES — PILIH DURASI (mahasiswa/durasi.html).
   Tombol 1/2/3 jam, hitung batas pengembalian (maks 15.30 WIB), simpan pilihan. */
document.addEventListener('DOMContentLoaded', function () {
  var G = window.GOWES;
  var pilih = document.getElementById('pilih-durasi');
  if (!pilih) return;
  var tombol = pilih.querySelectorAll('[data-durasi]');
  function atur(n) {
    tombol.forEach(function (b) {
      var on = parseInt(b.dataset.durasi, 10) === n;
      b.classList.toggle('on', on);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
    G.store(function () { localStorage.setItem(G.KEY_DURASI, n); });
    var el = document.getElementById('batas');
    if (el) el.textContent = G.batas(n) + ' WIB';
  }
  tombol.forEach(function (b) {
    b.addEventListener('click', function () { atur(parseInt(b.dataset.durasi, 10)); });
  });
  atur(G.getDurasi());
});
