/* GOWES — DROPDOWN PILIHAN DALAM DIALOG (admin/unit.html, superadmin/tambah-petugas-admin.html).
   Mengganti tampilan <select> bawaan browser (daftarnya muncul "keluar" dari kotak dialog)
   dengan daftar buatan yang terbuka DI DALAM dialog dan mendorong tombol Konfirmasi ke bawah.
   <select> aslinya tetap ada (disembunyikan) sebagai penyimpan nilai, jadi js/modal.js dan
   js/tambah-petugas.js tetap bekerja tanpa diubah. Muat file ini SETELAH modal.js.
   Gaya (CSS) disuntik dari file ini sendiri, jadi tidak perlu mengubah css/panel.css. */
(function () {
  'use strict';

  var CSS = [
    '.dd{position:relative;width:100%}',
    '.dd-btn{display:flex;align-items:center;justify-content:space-between;width:100%;height:40px;padding:0 14px;',
    'border:1px solid #d9d6f5;border-radius:10px;background:var(--tint,#e9e8fa);color:var(--ink,#1a1a2e);',
    "font:400 13px 'Albert Sans',sans-serif;text-align:left;cursor:pointer}",
    '.dd-btn.is-placeholder{color:var(--muted,#7a7a90)}',
    '.dd-btn:focus-visible,.dd.open .dd-btn{outline:0;box-shadow:0 0 0 2px var(--primary,#3a32a5)}',
    '.dd-btn svg{width:14px;height:14px;flex:none;transition:transform .15s}',
    '.dd.open .dd-btn svg{transform:rotate(180deg)}',
    '.dd-list{list-style:none;margin:6px 0 0;padding:4px;border:1px solid #d9d6f5;border-radius:10px;background:#fff}',
    '.dd-list[hidden]{display:none}',
    ".dd-list li{padding:10px 12px;border-radius:8px;font:400 13px 'Albert Sans',sans-serif;color:var(--ink,#1a1a2e);cursor:pointer}",
    '.dd-list li:hover,.dd-list li:focus{outline:0;background:var(--tint,#e9e8fa)}',
    '.dd-list li[aria-selected=true]{background:var(--primary,#3a32a5);color:#fff;font-weight:600}',
    '.dd-native{position:absolute!important;opacity:0;pointer-events:none;width:1px;height:1px;left:0;bottom:0}'
  ].join('');

  var CHEVRON = '<svg viewBox="0 0 12 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m1 1.5 5 5 5-5"/></svg>';
  var semua = [];

  function suntikCSS() {
    var st = document.createElement('style');
    st.textContent = CSS;
    document.head.appendChild(st);
  }

  function enhance(sel) {
    var opts = Array.prototype.slice.call(sel.options);
    var placeholder = (opts.filter(function (o) { return o.value === ''; })[0] || {}).textContent || 'Pilih';
    var pilihan = opts.filter(function (o) { return o.value !== ''; });

    var wrap = document.createElement('div'); wrap.className = 'dd';
    var btn = document.createElement('button'); btn.type = 'button'; btn.className = 'dd-btn';
    btn.setAttribute('aria-haspopup', 'listbox'); btn.setAttribute('aria-expanded', 'false');
    var teks = document.createElement('span'); btn.appendChild(teks);
    btn.insertAdjacentHTML('beforeend', CHEVRON);
    var list = document.createElement('ul'); list.className = 'dd-list'; list.setAttribute('role', 'listbox'); list.hidden = true;

    pilihan.forEach(function (o) {
      var li = document.createElement('li'); li.setAttribute('role', 'option'); li.tabIndex = -1;
      li.dataset.value = o.value; li.textContent = o.textContent;
      list.appendChild(li);
    });
    wrap.appendChild(btn); wrap.appendChild(list);
    sel.classList.add('dd-native'); sel.tabIndex = -1; sel.setAttribute('aria-hidden', 'true');
    sel.parentNode.insertBefore(wrap, sel.nextSibling);

    function sync() {
      var ada = sel.value !== '' && pilihan.some(function (o) { return o.value === sel.value; });
      teks.textContent = ada ? sel.value : placeholder;
      btn.classList.toggle('is-placeholder', !ada);
      Array.prototype.forEach.call(list.children, function (li) {
        li.setAttribute('aria-selected', ada && li.dataset.value === sel.value ? 'true' : 'false');
      });
    }
    function buka(b) {
      wrap.classList.toggle('open', b); list.hidden = !b; btn.setAttribute('aria-expanded', b ? 'true' : 'false');
    }
    function pilih(li) {
      sel.value = li.dataset.value;
      sel.dispatchEvent(new Event('change', { bubbles: true }));
      sync(); buka(false); btn.focus();
    }

    btn.addEventListener('click', function () {
      var mau = list.hidden;
      semua.forEach(function (d) { d.tutup(); });
      buka(mau);
    });
    btn.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown') { e.preventDefault(); buka(true); if (list.firstElementChild) list.firstElementChild.focus(); }
    });
    list.addEventListener('click', function (e) { var li = e.target.closest('li'); if (li) pilih(li); });
    list.addEventListener('keydown', function (e) {
      var li = e.target.closest('li'); if (!li) return;
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); pilih(li); }
      else if (e.key === 'ArrowDown' && li.nextElementSibling) { e.preventDefault(); li.nextElementSibling.focus(); }
      else if (e.key === 'ArrowUp' && li.previousElementSibling) { e.preventDefault(); li.previousElementSibling.focus(); }
    });
    /* Esc menutup daftar dulu (bukan langsung menutup dialog) */
    wrap.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !list.hidden) { e.stopPropagation(); buka(false); btn.focus(); }
    });

    semua.push({ sync: sync, tutup: function () { buka(false); } });
    sync();
    if (sel.form) sel.form.addEventListener('reset', function () { setTimeout(sync, 0); });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var daftar = document.querySelectorAll('.modal select');
    if (!daftar.length) return;
    suntikCSS();
    Array.prototype.forEach.call(daftar, enhance);

    /* modal.js mengisi nilai select saat dialog dibuka -> sinkronkan tampilan sesudahnya */
    document.querySelectorAll('[data-open]').forEach(function (b) {
      b.addEventListener('click', function () { semua.forEach(function (d) { d.tutup(); d.sync(); }); });
    });
    document.addEventListener('click', function (e) {
      if (!e.target.closest('.dd')) semua.forEach(function (d) { d.tutup(); });
    });
  });
})();