/* GOWES — PENCARIAN & FILTER TANGGAL TABEL (panel petugas/admin/superadmin).
   #cari = kotak cari teks, #tgl = pilih tanggal (cocok dengan kolom "02 Okt 2026"). */
document.addEventListener('DOMContentLoaded', function () {
  var cari = document.getElementById('cari');
  var tgl = document.getElementById('tgl');
  if (!cari && !tgl) return;
  var BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

  function formatTanggal(v) {            // "2026-10-02" -> "02 Okt 2026"
    var p = (v || '').split('-');
    return p.length === 3 ? p[2] + ' ' + BULAN[parseInt(p[1], 10) - 1] + ' ' + p[0] : '';
  }
  function saring() {
    var q = cari ? cari.value.toLowerCase() : '';
    var t = tgl ? formatTanggal(tgl.value).toLowerCase() : '';
    document.querySelectorAll('table.tbl tbody tr').forEach(function (tr) {
      var teks = tr.textContent.toLowerCase();
      tr.style.display = (!q || teks.indexOf(q) > -1) && (!t || teks.indexOf(t) > -1) ? '' : 'none';
    });
  }
  if (cari) cari.addEventListener('input', saring);
  if (tgl) tgl.addEventListener('change', saring);
});
