/* GOWES — JS BERSAMA (dimuat di semua halaman).
   Isi: penyimpanan sederhana (localStorage), pengisian teks otomatis [data-fill],
   navigasi formulir tanpa server [data-go], dan reset status [data-reset].
   Fungsi yang dipakai file lain disimpan di window.GOWES. */
(function () {
  'use strict';

  var KEY_DURASI = 'gowes_durasi';
  var KEY_USER = 'gowes_user';
  var KEY_ROLE = 'gowes_role';
  var DUMMY = { nim: '244107060001', nama: 'Marry Antoine' };

  function store(fn) { try { return fn(); } catch (e) { return null; } }
  function pad(n) { return String(n).padStart(2, '0'); }

  function getDurasi() {
    var d = parseInt(store(function () { return localStorage.getItem(KEY_DURASI); }), 10);
    return d >= 1 && d <= 3 ? d : 2;
  }
  function getUser() {
    var saved = store(function () { return JSON.parse(localStorage.getItem(KEY_USER)); });
    return Object.assign({}, DUMMY, saved || {});
  }
  /* Mulai 13.00; batas pengembalian tidak boleh melewati 15.30 WIB */
  function batasMenit(d) { return Math.min(13 * 60 + d * 60, 15 * 60 + 30); }
  function batas(d) { var m = batasMenit(d); return pad(Math.floor(m / 60)) + '.' + pad(m % 60); }

  window.GOWES = {
    KEY_DURASI: KEY_DURASI, store: store, pad: pad,
    getDurasi: getDurasi, getUser: getUser, batasMenit: batasMenit, batas: batas
  };

  document.addEventListener('DOMContentLoaded', function () {
    var durasi = getDurasi();
    var user = getUser();

    /* Isi teks dinamis: <span data-fill="nama"> */
    document.querySelectorAll('[data-fill]').forEach(function (el) {
      switch (el.dataset.fill) {
        case 'nama': el.textContent = user.nama; break;
        case 'nim': el.textContent = user.nim; break;
        case 'inisial': el.textContent = user.nama.charAt(0).toUpperCase(); break;
        case 'durasi': el.textContent = durasi + ' jam'; break;
        case 'cek-durasi': el.textContent = 'Durasi ' + durasi + ' jam, tidak melewati 15.30 WIB'; break;
        case 'batas': el.textContent = batas(durasi) + ' WIB'; break;
        case 'batas-pendek': el.textContent = batas(durasi); break;
      }
    });

    /* Formulir tanpa server: <form data-go="halaman.html">
       data-save="user" -> simpan NIM/nama; <select name="peran"> -> pilih folder tujuan */
    document.querySelectorAll('form[data-go]').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (form.dataset.save === 'user') {
          var nim = (form.querySelector('[name=nim]') || {}).value;
          var nama = (form.querySelector('[name=nama]') || {}).value;
          var data = {};
          if (nim && nim.trim()) data.nim = nim.trim();
          if (nama && nama.trim()) data.nama = nama.trim();
          store(function () { localStorage.setItem(KEY_USER, JSON.stringify(data)); });
        }
        var peran = form.querySelector('[name=peran]');
        location.href = peran ? peran.value : form.dataset.go;
      });
    });

    /* Reset status: <a data-reset="durasi|all"> */
    document.querySelectorAll('[data-reset]').forEach(function (a) {
      a.addEventListener('click', function () {
        store(function () {
          localStorage.removeItem(KEY_DURASI);
          if (a.dataset.reset === 'all') localStorage.removeItem(KEY_USER);
        });
      });
    });
  });
})();
