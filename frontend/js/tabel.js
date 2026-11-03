document.addEventListener('DOMContentLoaded', function () {
  var BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

  function formatTanggal(v) {            // "2026-10-02" -> "02 Okt 2026"
    var p = (v || '').split('-');
    return p.length === 3 ? p[2] + ' ' + BULAN[parseInt(p[1], 10) - 1] + ' ' + p[0] : '';
  }

  document.querySelectorAll('input[type=search], input[type=date]').forEach(function (input) {
    var panel = input.closest('.panel');
    var cakupan = panel && panel.querySelector('table.tbl') ? panel : document;
    var cari = cakupan.querySelector('input[type=search]');
    var tgl = cakupan.querySelector('input[type=date]');

    function saring() {
      var q = cari ? cari.value.toLowerCase() : '';
      var t = tgl ? formatTanggal(tgl.value).toLowerCase() : '';
      cakupan.querySelectorAll('table.tbl tbody tr').forEach(function (tr) {
        var teks = tr.textContent.toLowerCase();
        tr.style.display = (!q || teks.indexOf(q) > -1) && (!t || teks.indexOf(t) > -1) ? '' : 'none';
      });
    }
    input.addEventListener(input.type === 'date' ? 'change' : 'input', saring);
  });
});
