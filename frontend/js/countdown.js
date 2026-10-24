/* GOWES — HITUNG MUNDUR (mahasiswa/peminjaman.html, kembali.html, waktu-habis.html).
   Dengan durasi 2 jam tampil 01:42:18 seperti di desain, lalu berjalan mundur. */
document.addEventListener('DOMContentLoaded', function () {
  var G = window.GOWES;
  var cd = document.getElementById('countdown');
  if (!cd) return;
  var sisa = (G.batasMenit(G.getDurasi()) - 13 * 60) * 60 - 1062;
  function tampil() {
    var s = Math.max(sisa, 0);
    cd.textContent = G.pad(Math.floor(s / 3600)) + ':' + G.pad(Math.floor((s % 3600) / 60)) + ':' + G.pad(s % 60);
  }
  tampil();
  setInterval(function () {
    sisa -= 1; tampil();
    /* waktu habis -> tampilkan dialog "Waktu Peminjaman Habis!" */
    if (sisa <= 0 && !/waktu-habis|kembali/.test(location.pathname)) location.href = 'waktu-habis.html';
  }, 1000);
});
