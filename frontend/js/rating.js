/* GOWES — RATING LAPORAN KONDISI (mahasiswa/laporan.html). Pilih nilai 1–5 per bagian sepeda. */
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-rating]').forEach(function (box) {
    var btns = box.querySelectorAll('[data-nilai]');
    btns.forEach(function (b) {
      b.addEventListener('click', function () {
        btns.forEach(function (x) { x.classList.remove('on'); });
        b.classList.add('on');
      });
    });
  });
});
