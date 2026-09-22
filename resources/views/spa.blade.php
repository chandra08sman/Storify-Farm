@extends('layouts.base')

@section('content')
<div id="view-landing">

<div class="ambient-bg" aria-hidden="true">
  <span class="orb orb1"></span><span class="orb orb2"></span><span class="orb orb3"></span><span class="orb orb4"></span>
  <div class="scan-sweep"></div>
  <div class="grain-drift">
    <span style="left:5%;animation-duration:15s;animation-delay:0s"></span>
    <span style="left:14%;animation-duration:19s;animation-delay:2.4s"></span>
    <span style="left:23%;animation-duration:14s;animation-delay:5.1s"></span>
    <span style="left:33%;animation-duration:21s;animation-delay:1.2s"></span>
    <span style="left:44%;animation-duration:16s;animation-delay:4s"></span>
    <span style="left:55%;animation-duration:20s;animation-delay:0.6s"></span>
    <span style="left:65%;animation-duration:15s;animation-delay:6s"></span>
    <span style="left:75%;animation-duration:18s;animation-delay:2.8s"></span>
    <span style="left:85%;animation-duration:17s;animation-delay:4.6s"></span>
    <span style="left:94%;animation-duration:22s;animation-delay:1.8s"></span>
  </div>
  <span class="float-icon material-symbols-outlined" style="left:8%;top:18%;font-size:64px;animation-duration:12s">eco</span>
  <span class="float-icon material-symbols-outlined" style="left:88%;top:14%;font-size:52px;animation-duration:15s;animation-delay:-4s">barcode_scanner</span>
  <span class="float-icon material-symbols-outlined" style="left:78%;top:72%;font-size:58px;animation-duration:13s;animation-delay:-7s">inventory_2</span>
  <span class="float-icon material-symbols-outlined" style="left:12%;top:78%;font-size:46px;animation-duration:17s;animation-delay:-2s">warehouse</span>
</div>


<header class="sticky top-0 z-40 bg-bg/90 backdrop-blur border-b border-line">
  <div class="max-w-7xl mx-auto px-5 md:px-8 h-[72px] flex items-center gap-6">
    <a href="#top" class="flex items-center gap-2 text-primary font-bold text-xl shrink-0">
      <span class="material-symbols-outlined">eco</span> Storify Farm
    </a>
    <nav class="desktop-nav hidden md:flex items-center gap-7 ml-6">
      <a href="#dashboard-preview" class="navlink">Dashboard</a>
      <a href="#inventory-detail" class="navlink">Inventory</a>
      <a href="#planner-detail" class="navlink">Warehouse Planner</a>
      <a href="#storifyview-detail" class="navlink">Storify View</a>
      <a href="#reports-detail" class="navlink">Reports</a>
    </nav>
    <div class="ml-auto flex items-center gap-3">
      <button onclick="goDashboardOrLogin()" class="hidden sm:inline-flex items-center gap-2 bg-primary text-white px-5 h-11 rounded-lg font-semibold hover:bg-primary2 transition">
        <span class="material-symbols-outlined text-[19px]">login</span> Login
      </button>
      <button onclick="document.getElementById('mnav').classList.toggle('open')" class="mobile-menu p-2 rounded-lg hover:bg-sage2">
        <span class="material-symbols-outlined">menu</span>
      </button>
    </div>
  </div>
  <div id="mnav" class="md:hidden flex-col border-t border-line bg-white px-5 py-3 gap-1">
    <a href="#dashboard-preview" class="block py-2">Dashboard</a>
    <a href="#inventory-detail" class="block py-2">Inventory</a>
    <a href="#planner-detail" class="block py-2">Warehouse Planner</a>
    <a href="#storifyview-detail" class="block py-2">Storify View</a>
    <a href="#reports-detail" class="block py-2">Reports</a>
    <button onclick="goDashboardOrLogin()" class="mt-2 w-full bg-primary text-white h-11 rounded-lg font-semibold">Login</button>
  </div>
</header>

<section id="top" class="relative overflow-hidden">
  <div class="absolute inset-0 grain-bg pointer-events-none"></div>
  <div class="max-w-7xl mx-auto px-5 md:px-8 pt-14 pb-16 md:pt-20 md:pb-24 grid lg:grid-cols-2 gap-12 items-center relative">
    <div>
      <div class="inline-flex items-center gap-2 bg-sage2 border border-sage text-primary2 px-3 py-1.5 rounded-full eyebrow mb-5">
        <span class="material-symbols-outlined text-[16px]">warehouse</span> Smart Warehouse Management System
      </div>
      <h1 class="text-4xl md:text-5xl font-extrabold text-primary leading-[1.1]">
        Gudang hasil panen,<br>tercatat rapi <span class="relative inline-block">setiap saat<span class="absolute left-0 -bottom-1 w-full h-2 bg-amber/50 -z-10"></span></span>.
      </h1>
      <p class="text-muted text-lg mt-5 max-w-lg leading-relaxed">
        Storify Farm membantu Anda mengelola stok, lokasi, dan pergerakan hasil panen — mulai dari beras premium sampai beras ketan — dengan pencatatan real-time, Barcode, dan metode FIFO. Tidak perlu lagi mencatat manual di buku atau spreadsheet.
      </p>
      <div class="flex flex-wrap gap-3 mt-8">
        <button onclick="goDashboardOrLogin()" class="bg-primary text-white px-6 h-12 rounded-lg font-semibold flex items-center gap-2 hover:bg-primary2 transition">
          Masuk ke Dashboard <span class="material-symbols-outlined text-[19px]">arrow_forward</span>
        </button>
        <a href="#fitur" class="border border-line px-6 h-12 rounded-lg font-semibold flex items-center gap-2 hover:bg-white transition">
          Lihat Fitur
        </a>
      </div>
      <div class="flex flex-wrap gap-x-8 gap-y-2 mt-8 text-sm text-muted">
        <div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-primary text-[18px]">check_circle</span>Stok real-time</div>
        <div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-primary text-[18px]">check_circle</span>FIFO otomatis</div>
        <div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-primary text-[18px]">check_circle</span>Bisa di-install (PWA)</div>
      </div>
    </div>

    <div class="relative">
      <div class="card p-6 rotate-1">
        <div class="flex items-center justify-between mb-4">
          <span class="hero-batch">BATCH #BRS240730</span>
          <span class="badge inline-flex items-center gap-1 bg-sage text-primary px-3 py-1 rounded-full text-xs font-mono font-semibold">
            <span class="material-symbols-outlined text-[14px]">barcode_scanner</span>Premium
          </span>
        </div>
        <svg viewBox="0 0 460 300" class="w-full h-auto">
          <rect x="0" y="0" width="460" height="300" rx="12" fill="#eeece2"/>
          <g stroke="#bcc9b8" stroke-width="2">
            <line x1="30" y1="70" x2="430" y2="70"/>
            <line x1="30" y1="150" x2="430" y2="150"/>
            <line x1="30" y1="230" x2="430" y2="230"/>
          </g>
          <g stroke="#47624f" stroke-width="3" stroke-linecap="round">
            <line x1="40" y1="40" x2="40" y2="260"/>
            <line x1="420" y1="40" x2="420" y2="260"/>
          </g>
          <g>
            <rect x="60" y="35" width="46" height="34" rx="8" fill="#2c4a3b"/>
            <rect x="112" y="35" width="46" height="34" rx="8" fill="#47624f"/>
            <rect x="164" y="35" width="46" height="34" rx="8" fill="#2c4a3b"/>
            <rect x="216" y="35" width="46" height="34" rx="8" fill="#47624f"/>
            <rect x="268" y="35" width="46" height="34" rx="8" fill="#2c4a3b"/>
          </g>
          <g>
            <rect x="80" y="112" width="46" height="34" rx="8" fill="#47624f"/>
            <rect x="132" y="112" width="46" height="34" rx="8" fill="#2c4a3b"/>
            <rect x="184" y="112" width="46" height="34" rx="8" fill="#47624f"/>
            <rect x="236" y="112" width="46" height="34" rx="8" fill="#2c4a3b"/>
          </g>
          <g>
            <rect x="60" y="192" width="46" height="34" rx="8" fill="#2c4a3b"/>
            <rect x="112" y="192" width="46" height="34" rx="8" fill="#47624f"/>
            <rect x="164" y="192" width="46" height="34" rx="8" fill="#2c4a3b"/>
            <rect x="216" y="192" width="46" height="34" rx="8" fill="#47624f"/>
            <rect x="268" y="192" width="46" height="34" rx="8" fill="#2c4a3b"/>
          </g>
          <g transform="translate(330,95)">
            <rect x="0" y="0" width="76" height="76" rx="10" fill="#ffffff" stroke="#d7d9cc"/>
            <g fill="#2c4a3b">
              <rect x="10" y="10" width="16" height="16"/><rect x="50" y="10" width="16" height="16"/>
              <rect x="10" y="50" width="16" height="16"/>
              <rect x="32" y="10" width="6" height="6"/><rect x="32" y="24" width="6" height="10"/>
              <rect x="46" y="32" width="6" height="6"/><rect x="32" y="46" width="20" height="6"/>
              <rect x="50" y="54" width="16" height="6"/><rect x="56" y="34" width="10" height="6"/>
            </g>
          </g>
        </svg>
        <div class="flex justify-between items-center mt-4 pt-4 border-t border-line text-sm">
          <span class="text-muted">Zone A · Rack 2</span>
          <span class="font-mono font-semibold text-primary">12.000 kg</span>
        </div>
      </div>
      <div class="absolute -bottom-5 -left-5 card px-4 py-3 -rotate-2 hidden sm:block">
        <div class="flex items-center gap-2 text-sm">
          <span class="material-symbols-outlined text-amber">bolt</span>
          <div><b>FIFO aktif</b><div class="text-muted text-xs">Batch tertua diprioritaskan</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="dashboard-preview" class="max-w-7xl mx-auto px-5 md:px-8 py-14 md:py-16">
  <div class="max-w-xl mb-8">
    <div class="eyebrow text-primary2 mb-2">Ringkasan langsung</div>
    <h2 class="text-3xl md:text-4xl font-bold text-primary">Semua kondisi gudang, satu layar.</h2>
    <p class="text-muted mt-3">Contoh tampilan ringkas yang akan Anda lihat begitu masuk ke dashboard.</p>
    <p id="statusGudang" class="text-primary font-semibold mt-4">Status gudang belum dicek.</p>
    <button type="button" onclick="document.getElementById('statusGudang').innerHTML='Status gudang siap dikelola.'; this.style.setProperty('background-color','#b7d7b0','important'); this.style.setProperty('color','#285238','important')" class="mt-3 bg-primary text-white px-5 h-11 rounded-lg font-semibold hover:bg-primary2 transition">
      Cek Status Gudang
    </button>
  </div>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
    <div class="card p-6 tag-corner">
      <div class="eyebrow text-muted">Total Stok</div>
      <div class="text-3xl md:text-4xl font-bold text-primary mt-2">12.450</div>
      <div class="text-sm text-muted mt-1">kg di seluruh gudang</div>
    </div>
    <div class="card p-6 tag-corner">
      <div class="eyebrow text-muted">Barang Masuk</div>
      <div class="text-3xl md:text-4xl font-bold text-primary mt-2">5.000</div>
      <div class="text-sm text-muted mt-1">kg bulan ini</div>
    </div>
    <div class="card p-6 tag-corner">
      <div class="eyebrow text-muted">Barang Keluar</div>
      <div class="text-3xl md:text-4xl font-bold text-primary mt-2">3.250</div>
      <div class="text-sm text-muted mt-1">kg bulan ini</div>
    </div>
    <div class="card p-6 tag-corner">
      <div class="eyebrow text-muted">Kapasitas Gudang</div>
      <div class="text-3xl md:text-4xl font-bold text-primary mt-2">68%</div>
      <div class="text-sm text-muted mt-1">rata-rata terisi</div>
    </div>
  </div>
</section>

<section id="fitur" class="bg-white border-y border-line">
  <div class="max-w-7xl mx-auto px-5 md:px-8 py-14 md:py-16">
    <div class="max-w-xl mb-10">
      <div class="eyebrow text-primary2 mb-2">Fitur unggulan</div>
      <h2 class="text-3xl md:text-4xl font-bold text-primary">Dibangun khusus untuk gudang hasil panen.</h2>
      <p class="text-muted mt-3">Bukan sekadar pencatatan stok biasa — Storify Farm memahami cara kerja gudang beras.</p>
    </div>
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
      <a href="#inventory-detail" class="card p-6 block hover:shadow-lg hover:-translate-y-0.5 transition">
        <div class="w-12 h-12 rounded-xl bg-sage2 flex items-center justify-center text-primary mb-4">
          <span class="material-symbols-outlined">barcode_scanner</span>
        </div>
        <h3 class="font-semibold text-lg">Inventory &amp; Barcode</h3>
        <p class="text-muted text-sm mt-2 leading-relaxed">Setiap produk punya Barcode/SKU sendiri. Cukup cari atau pindai untuk mencatat barang masuk atau keluar tanpa input manual.</p>
        <span class="text-primary font-semibold text-sm mt-3 inline-flex items-center gap-1">Pelajari <span class="material-symbols-outlined text-[16px]">arrow_forward</span></span>
      </a>
      <a href="#planner-detail" class="card p-6 block hover:shadow-lg hover:-translate-y-0.5 transition">
        <div class="w-12 h-12 rounded-xl bg-sage2 flex items-center justify-center text-primary mb-4">
          <span class="material-symbols-outlined">view_quilt</span>
        </div>
        <h3 class="font-semibold text-lg">Warehouse Planner</h3>
        <p class="text-muted text-sm mt-2 leading-relaxed">Hitung kapasitas gudang, jumlah rak, dan tata letak sebelum gudang mulai digunakan — lengkap dengan estimasi biaya.</p>
        <span class="text-primary font-semibold text-sm mt-3 inline-flex items-center gap-1">Pelajari <span class="material-symbols-outlined text-[16px]">arrow_forward</span></span>
      </a>
      <a href="#storifyview-detail" class="card p-6 block hover:shadow-lg hover:-translate-y-0.5 transition">
        <div class="w-12 h-12 rounded-xl bg-sage2 flex items-center justify-center text-primary mb-4">
          <span class="material-symbols-outlined">streetview</span>
        </div>
        <h3 class="font-semibold text-lg">Storify View</h3>
        <p class="text-muted text-sm mt-2 leading-relaxed">Panorama virtual gudang ala Google Street View, membantu petugas menemukan lorong dan rak yang dituju dengan cepat.</p>
        <span class="text-primary font-semibold text-sm mt-3 inline-flex items-center gap-1">Pelajari <span class="material-symbols-outlined text-[16px]">arrow_forward</span></span>
      </a>
      <a href="#reports-detail" class="card p-6 block hover:shadow-lg hover:-translate-y-0.5 transition">
        <div class="w-12 h-12 rounded-xl bg-sage2 flex items-center justify-center text-primary mb-4">
          <span class="material-symbols-outlined">assessment</span>
        </div>
        <h3 class="font-semibold text-lg">Reporting</h3>
        <p class="text-muted text-sm mt-2 leading-relaxed">Laporan barang masuk, keluar, stok, dan kapasitas dibuat otomatis dan bisa diunduh dalam bentuk CSV kapan saja.</p>
        <span class="text-primary font-semibold text-sm mt-3 inline-flex items-center gap-1">Pelajari <span class="material-symbols-outlined text-[16px]">arrow_forward</span></span>
      </a>
    </div>
  </div>
</section>

<section id="inventory-detail" class="max-w-7xl mx-auto px-5 md:px-8 py-14 md:py-16 scroll-mt-20">
  <div class="grid lg:grid-cols-2 gap-12 items-center">
    <div>
      <div class="eyebrow text-primary2 mb-2">Inventory &amp; Barcode</div>
      <h2 class="text-3xl md:text-4xl font-bold text-primary">Setiap karung punya identitasnya sendiri.</h2>
      <p class="text-muted mt-3 leading-relaxed">Semua produk — dari beras Premium sampai Ketan — terdaftar dengan SKU/Barcode, lokasi rak, dan kapasitas maksimum masing-masing. Setiap kali barang masuk, sistem membuat <b>batch</b> baru lengkap dengan tanggal terima. Saat barang keluar, sistem otomatis mengambil batch <b>paling lama</b> lebih dulu (FIFO), jadi stok lama tidak pernah tertumpuk di belakang.</p>
      <ol class="mt-6 space-y-3">
        <li class="flex gap-3"><span class="w-7 h-7 shrink-0 rounded-full bg-sage flex items-center justify-center text-primary font-bold text-sm">1</span><span class="text-sm text-muted"><b class="text-text">Cari atau pindai Barcode/SKU</b> produk untuk entry cepat tanpa ketik manual.</span></li>
        <li class="flex gap-3"><span class="w-7 h-7 shrink-0 rounded-full bg-sage flex items-center justify-center text-primary font-bold text-sm">2</span><span class="text-sm text-muted"><b class="text-text">Catat barang masuk</b> — sistem membuat nomor batch baru dan menambah stok.</span></li>
        <li class="flex gap-3"><span class="w-7 h-7 shrink-0 rounded-full bg-sage flex items-center justify-center text-primary font-bold text-sm">3</span><span class="text-sm text-muted"><b class="text-text">Catat barang keluar</b> — batch tertua diambil otomatis (FIFO), stok berkurang sesuai jumlah.</span></li>
        <li class="flex gap-3"><span class="w-7 h-7 shrink-0 rounded-full bg-sage flex items-center justify-center text-primary font-bold text-sm">4</span><span class="text-sm text-muted"><b class="text-text">Status kapasitas</b> tiap produk dihitung otomatis: stok saat ini dibanding kapasitas maksimum rak.</span></li>
      </ol>
    </div>
    <div class="card p-6">
      <div class="eyebrow text-muted mb-3">Contoh: Data Produk</div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Produk</th><th>SKU</th><th>Lokasi</th><th>Stok</th><th>Status</th></tr></thead>
          <tbody>
            <tr><td><b>Premium</b></td><td class="font-mono">SF-00001</td><td>Rak A1–A5</td><td class="font-mono">312.000 kg</td><td><span class="badge bg-primary/10 text-primary">Aman</span></td></tr>
            <tr><td><b>Ketan</b></td><td class="font-mono">SF-00004</td><td>Rak D1–D2</td><td class="font-mono">92.000 kg</td><td><span class="badge bg-amber/30 text-[#6b4a1c]">Terbatas</span></td></tr>
            <tr><td><b>Beras Merah</b></td><td class="font-mono">SF-00005</td><td>Rak E1–E2</td><td class="font-mono">41.000 kg</td><td><span class="badge bg-primary/10 text-primary">Aman</span></td></tr>
          </tbody>
        </table>
      </div>
      <div class="mt-5 space-y-2">
        <div class="flex items-center gap-3 py-2 border-b border-line text-sm"><div class="w-8 h-8 rounded-full bg-sage flex items-center justify-center"><span class="material-symbols-outlined text-[18px]">south_west</span></div><div class="flex-1"><b>Ketan</b><div class="text-muted text-xs">Barang masuk · Batch BRS260814xx</div></div><strong class="text-primary">+5.000 kg</strong></div>
        <div class="flex items-center gap-3 py-2 text-sm"><div class="w-8 h-8 rounded-full bg-amber/30 flex items-center justify-center"><span class="material-symbols-outlined text-[18px]">north_east</span></div><div class="flex-1"><b>Ketan</b><div class="text-muted text-xs">FIFO otomatis · ambil batch tertua</div></div><strong>-2.000 kg</strong></div>
      </div>
    </div>
  </div>
</section>

<section id="planner-detail" class="bg-white border-y border-line scroll-mt-20">
  <div class="max-w-7xl mx-auto px-5 md:px-8 py-14 md:py-16 grid lg:grid-cols-2 gap-12 items-center">
    <div class="order-2 lg:order-1">
      <div class="eyebrow text-muted mb-3">Contoh: Hitung Layout Gudang</div>
      <div class="card p-6">
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div class="bg-sage2 rounded-lg p-3"><div class="text-muted text-xs">Panjang gudang</div><b>20 m</b></div>
          <div class="bg-sage2 rounded-lg p-3"><div class="text-muted text-xs">Lebar gudang</div><b>12 m</b></div>
          <div class="bg-sage2 rounded-lg p-3"><div class="text-muted text-xs">Ukuran rak</div><b>2 × 1 m</b></div>
          <div class="bg-sage2 rounded-lg p-3"><div class="text-muted text-xs">Lebar lorong</div><b>1,2 m</b></div>
        </div>
        <div class="grid grid-cols-2 gap-4 mt-5">
          <div class="card p-5"><div class="text-sm text-muted font-mono">LUAS GUDANG</div><div class="text-3xl font-bold text-primary mt-2">240</div><div class="text-sm text-muted">m²</div></div>
          <div class="card p-5"><div class="text-sm text-muted font-mono">ESTIMASI RAK</div><div class="text-3xl font-bold text-primary mt-2">48</div><div class="text-sm text-muted">unit</div></div>
          <div class="card p-5"><div class="text-sm text-muted font-mono">SUHU IDEAL</div><div class="text-2xl font-bold text-primary mt-2">20–25°C</div></div>
          <div class="card p-5"><div class="text-sm text-muted font-mono">KELEMBAPAN</div><div class="text-2xl font-bold text-primary mt-2">60–70%</div></div>
        </div>
        <div class="mt-4 p-4 rounded-lg bg-sage2 text-sm text-primary2"><b>Rekomendasi:</b> prioritaskan rak berdasarkan FIFO, sisakan jalur utama untuk akses petugas, dan kelompokkan komoditas dengan kondisi penyimpanan serupa dalam satu zona.</div>
      </div>
    </div>
    <div class="order-1 lg:order-2">
      <div class="eyebrow text-primary2 mb-2">Warehouse Planner</div>
      <h2 class="text-3xl md:text-4xl font-bold text-primary">Rancang gudang sebelum mulai dipakai.</h2>
      <p class="text-muted mt-3 leading-relaxed">Sebelum gudang beroperasi (atau saat mau menata ulang), masukkan ukuran gudang, ukuran rak, dan lebar lorong. Sistem langsung menghitung luas total, memperkirakan berapa unit rak yang muat, dan memberi rekomendasi suhu, kelembapan, serta tata letak yang paling efisien untuk komoditas beras.</p>
      <ol class="mt-6 space-y-3">
        <li class="flex gap-3"><span class="w-7 h-7 shrink-0 rounded-full bg-sage flex items-center justify-center text-primary font-bold text-sm">1</span><span class="text-sm text-muted">Isi <b class="text-text">dimensi gudang</b> (panjang × lebar).</span></li>
        <li class="flex gap-3"><span class="w-7 h-7 shrink-0 rounded-full bg-sage flex items-center justify-center text-primary font-bold text-sm">2</span><span class="text-sm text-muted">Isi <b class="text-text">ukuran rak</b> dan <b class="text-text">lebar lorong</b> antar rak.</span></li>
        <li class="flex gap-3"><span class="w-7 h-7 shrink-0 rounded-full bg-sage flex items-center justify-center text-primary font-bold text-sm">3</span><span class="text-sm text-muted">Klik <b class="text-text">Hitung &amp; Beri Saran</b> — sistem tampilkan luas, estimasi rak, dan kondisi ideal.</span></li>
      </ol>
    </div>
  </div>
</section>

<section id="storifyview-detail" class="max-w-7xl mx-auto px-5 md:px-8 py-14 md:py-16 scroll-mt-20">
  <div class="grid lg:grid-cols-2 gap-12 items-center">
    <div>
      <div class="eyebrow text-primary2 mb-2 flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">explore</span>Storify View</div>
      <h2 class="text-3xl md:text-4xl font-bold text-primary">Tidak perlu hafal denah gudang.</h2>
      <p class="text-muted mt-3 leading-relaxed">Storify View menampilkan panorama virtual gudang ala Google Street View. Petugas baru cukup mencari nama produk atau lokasi rak yang dituju, dan sistem menunjukkan jalur dari area bongkar muat sampai ke rak yang tepat — tanpa perlu bertanya atau menghafal lorong.</p>
      <ol class="mt-6 space-y-3">
        <li class="flex gap-3"><span class="w-7 h-7 shrink-0 rounded-full bg-sage flex items-center justify-center text-primary font-bold text-sm">1</span><span class="text-sm text-muted">Ketik nama produk atau nomor rak yang dicari.</span></li>
        <li class="flex gap-3"><span class="w-7 h-7 shrink-0 rounded-full bg-sage flex items-center justify-center text-primary font-bold text-sm">2</span><span class="text-sm text-muted">Sistem menampilkan lokasi persis: zona dan nomor rak.</span></li>
        <li class="flex gap-3"><span class="w-7 h-7 shrink-0 rounded-full bg-sage flex items-center justify-center text-primary font-bold text-sm">3</span><span class="text-sm text-muted">Ikuti jalur yang ditampilkan dari Loading Area menuju rak tujuan.</span></li>
      </ol>
    </div>
    <div class="card p-6 bg-sage2">
      <div class="eyebrow text-primary2 mb-3">Contoh: Cari "Beras Ketan"</div>
      <div class="card p-5 bg-white">
        <div class="font-mono text-sm text-primary2 text-center">LOADING AREA</div>
        <div class="text-muted text-center my-2 text-sm">↓ Lorong Utama ↓</div>
        <div class="grid grid-cols-3 gap-2 mt-3">
          <div class="rounded-lg bg-sage2 p-3 text-center text-xs text-muted">Zona A<br><b class="text-text">Premium</b></div>
          <div class="rounded-lg bg-sage2 p-3 text-center text-xs text-muted">Zona B<br><b class="text-text">Medium</b></div>
          <div class="rounded-lg bg-sage2 p-3 text-center text-xs text-muted">Zona C<br><b class="text-text">Organik</b></div>
          <div class="rounded-lg bg-primary text-white p-3 text-center text-xs ring-2 ring-amber"><span class="material-symbols-outlined text-[16px]">place</span><br><b>Zona D — Ketan</b></div>
          <div class="rounded-lg bg-sage2 p-3 text-center text-xs text-muted">Zona E<br><b class="text-text">B. Merah</b></div>
          <div class="rounded-lg bg-sage2 p-3 text-center text-xs text-muted">Zona F<br><b class="text-text">B. Hitam</b></div>
        </div>
        <div class="text-xs text-muted text-center mt-3">Ditemukan di <b class="text-text">Rak D1–D2</b> — jalur ditandai dari Loading Area.</div>
      </div>
    </div>
  </div>
</section>

<section id="reports-detail" class="bg-white border-y border-line scroll-mt-20">
  <div class="max-w-7xl mx-auto px-5 md:px-8 py-14 md:py-16 grid lg:grid-cols-2 gap-12 items-center">
    <div class="order-2 lg:order-1">
      <div class="eyebrow text-muted mb-3">Contoh: Laporan 1–31 Agustus 2026</div>
      <div class="card p-6">
        <div class="grid grid-cols-2 gap-4">
          <div class="card p-4"><span class="text-muted text-sm">Barang Masuk</span><b class="block text-2xl text-primary mt-1">5.000 kg</b></div>
          <div class="card p-4"><span class="text-muted text-sm">Barang Keluar</span><b class="block text-2xl text-primary mt-1">3.250 kg</b></div>
          <div class="card p-4"><span class="text-muted text-sm">Stok Bersih</span><b class="block text-2xl text-primary mt-1">+1.750 kg</b></div>
          <div class="card p-4"><span class="text-muted text-sm">Transaksi</span><b class="block text-2xl text-primary mt-1">42</b></div>
        </div>
        <div class="table-wrap mt-5">
          <table>
            <thead><tr><th>Produk</th><th>Masuk</th><th>Keluar</th><th>Bersih</th></tr></thead>
            <tbody>
              <tr><td><b>Premium</b></td><td class="font-mono">+3.000 kg</td><td class="font-mono">-2.000 kg</td><td class="font-mono">+1.000 kg</td></tr>
              <tr><td><b>Ketan</b></td><td class="font-mono">+2.000 kg</td><td class="font-mono">-1.250 kg</td><td class="font-mono">+750 kg</td></tr>
            </tbody>
          </table>
        </div>
        <button class="mt-4 border border-primary text-primary px-5 h-10 rounded-lg font-semibold text-sm flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">download</span>Unduh Laporan (CSV)</button>
      </div>
    </div>
    <div class="order-1 lg:order-2">
      <div class="eyebrow text-primary2 mb-2">Reports</div>
      <h2 class="text-3xl md:text-4xl font-bold text-primary">Laporan otomatis, tanpa rekap manual.</h2>
      <p class="text-muted mt-3 leading-relaxed">Semua transaksi barang masuk dan keluar yang tercatat di sistem otomatis dirangkum jadi laporan. Pilih rentang tanggal, dan sistem menghitung total barang masuk, keluar, perubahan stok bersih, sampai rincian per produk — siap diunduh sebagai CSV kapan saja untuk diarsipkan atau dilaporkan ke atasan.</p>
      <ol class="mt-6 space-y-3">
        <li class="flex gap-3"><span class="w-7 h-7 shrink-0 rounded-full bg-sage flex items-center justify-center text-primary font-bold text-sm">1</span><span class="text-sm text-muted">Pilih <b class="text-text">rentang tanggal</b> laporan yang diinginkan.</span></li>
        <li class="flex gap-3"><span class="w-7 h-7 shrink-0 rounded-full bg-sage flex items-center justify-center text-primary font-bold text-sm">2</span><span class="text-sm text-muted">Klik <b class="text-text">Generate Report</b> — ringkasan dan rincian per produk langsung tampil.</span></li>
        <li class="flex gap-3"><span class="w-7 h-7 shrink-0 rounded-full bg-sage flex items-center justify-center text-primary font-bold text-sm">3</span><span class="text-sm text-muted">Klik <b class="text-text">Unduh Laporan (CSV)</b> untuk menyimpan atau membagikannya.</span></li>
      </ol>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-5 md:px-8 py-14 md:py-16">
  <div class="max-w-xl mx-auto text-center mb-10">
    <div class="eyebrow text-primary2 mb-2">Profil Team</div>
    <h2 class="text-3xl md:text-4xl font-bold text-primary">Tim di balik Storify Farm</h2>
    <p class="text-muted mt-3">Para developer dan kreator yang membangun sistem gudang cerdas ini agar lebih rapi, cepat, dan mudah digunakan.</p>
  </div>
  <div class="grid md:grid-cols-2 xl:grid-cols-5 gap-5">
    <div class="card p-4 text-center hover:-translate-y-1 transition">
      <img src="/images/team/chandra-azmi-khairunnisa.svg" alt="Chandra Azmi Khairunnisa" class="w-full h-56 object-cover rounded-2xl mb-4 bg-sage2">
      <h3 class="font-semibold text-lg text-primary">Chandra Azmi Khairunnisa</h3>
    </div>
    <div class="card p-4 text-center hover:-translate-y-1 transition">
      <img src="/images/team/elysia-khansa-azarina.svg" alt="Elysia Khansa Azarina" class="w-full h-56 object-cover rounded-2xl mb-4 bg-sage2">
      <h3 class="font-semibold text-lg text-primary">Elysia Khansa Azarina</h3>
    </div>
    <div class="card p-4 text-center hover:-translate-y-1 transition">
      <img src="/images/team/karina-sophia-wibowo.svg" alt="Karina Sophia Wibowo" class="w-full h-56 object-cover rounded-2xl mb-4 bg-sage2">
      <h3 class="font-semibold text-lg text-primary">Karina Sophia Wibowo</h3>
    </div>
    <div class="card p-4 text-center hover:-translate-y-1 transition">
      <img src="/images/team/pradhita-prameswari.svg" alt="Pradhita Prameswari" class="w-full h-56 object-cover rounded-2xl mb-4 bg-sage2">
      <h3 class="font-semibold text-lg text-primary">Pradhita Prameswari</h3>
    </div>
    <div class="card p-4 text-center hover:-translate-y-1 transition">
      <img src="/images/team/una-alinka-perdana.svg" alt="Una Alinka Perdana" class="w-full h-56 object-cover rounded-2xl mb-4 bg-sage2">
      <h3 class="font-semibold text-lg text-primary">Una Alinka Perdana</h3>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-5 md:px-8 py-14 md:py-16">
  <div class="card bg-primary !border-primary p-8 md:p-12 flex flex-col md:flex-row items-center justify-between gap-6">
    <div class="text-white">
      <h2 class="text-2xl md:text-3xl font-bold">Siap kelola gudang Anda dengan lebih cerdas?</h2>
      <p class="opacity-85 mt-2 max-w-md">Masuk ke dashboard dan lihat kondisi stok, kapasitas, dan aktivitas gudang secara real-time.</p>
    </div>
    <button onclick="goDashboardOrLogin()" class="shrink-0 bg-white text-primary px-7 h-12 rounded-lg font-semibold flex items-center gap-2 hover:bg-sage2 transition">
      Masuk ke Dashboard <span class="material-symbols-outlined text-[19px]">arrow_forward</span>
    </button>
  </div>
</section>

<footer class="bg-primary text-white/90 mt-6">
  <div class="max-w-7xl mx-auto px-5 md:px-8 py-12 grid md:grid-cols-4 gap-10">
    <div class="md:col-span-2">
      <div class="flex items-center gap-2 text-white font-bold text-xl">
        <span class="material-symbols-outlined">eco</span> Storify Farm
      </div>
      <p class="text-sm opacity-75 mt-3 max-w-sm leading-relaxed">Smart Warehouse Management System berbasis Progressive Web App untuk pengelolaan gudang hasil panen — mulai dari pencatatan stok, lokasi, hingga laporan otomatis.</p>
    </div>
    <div>
      <div class="eyebrow opacity-60 mb-3">Navigasi</div>
      <ul class="space-y-2 text-sm">
        <li><a href="#dashboard-preview" class="hover:underline">Dashboard</a></li>
        <li><a href="#inventory-detail" class="hover:underline">Inventory</a></li>
        <li><a href="#planner-detail" class="hover:underline">Warehouse Planner</a></li>
        <li><a href="#storifyview-detail" class="hover:underline">Storify View</a></li>
        <li><a href="#reports-detail" class="hover:underline">Reports</a></li>
      </ul>
    </div>
    <div>
      <div class="eyebrow opacity-60 mb-3">Kontak Pengembang</div>
      <ul class="space-y-2 text-sm">
        <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">mail</span>hello@storifyfarm.id</li>
        <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">call</span>+62 812-0000-0000</li>
        <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">location_on</span>Surabaya, Jawa Timur</li>
      </ul>
    </div>
  </div>
  <div class="border-t border-white/15 py-5">
    <div class="max-w-7xl mx-auto px-5 md:px-8 flex flex-col sm:flex-row justify-between gap-2 text-xs opacity-60">
      <span>© 2026 Storify Farm. Semua hak dilindungi.</span>
      <span>Dikembangkan sebagai proyek Smart Warehouse Management System.</span>
    </div>
  </div>
</footer>

</div>



<div id="view-login" style="display:none" class="min-h-screen grid lg:grid-cols-2">

<div class="ambient-bg" aria-hidden="true">
  <span class="orb orb-onDark orb1" style="left:-8%;top:-10%"></span>
  <span class="orb orb-onDark orb4" style="left:20%;top:65%"></span>
  <span class="orb orb2" style="right:-6%;bottom:-12%"></span>
  <span class="orb orb3" style="right:14%;top:20%"></span>
  <div class="grain-drift">
    <span style="left:10%;animation-duration:16s;animation-delay:0s"></span>
    <span style="left:24%;animation-duration:20s;animation-delay:3s"></span>
    <span style="left:40%;animation-duration:15s;animation-delay:5.5s"></span>
  </div>
  <div class="grain-drift on-dark">
    <span style="left:6%;animation-duration:18s;animation-delay:1s"></span>
    <span style="left:34%;animation-duration:14s;animation-delay:4.5s"></span>
  </div>
  <span class="float-icon material-symbols-outlined on-dark" style="left:10%;top:70%;font-size:56px;animation-duration:14s">grain</span>
  <span class="float-icon material-symbols-outlined" style="left:80%;top:16%;font-size:50px;animation-duration:16s;animation-delay:-5s">barcode_scanner</span>
</div>


  <div class="hidden lg:flex flex-col justify-between bg-primary text-white p-12 relative overflow-hidden">
    <div class="absolute inset-0 grain-bg pointer-events-none opacity-[.15]"></div>
    <a href="#/" class="flex items-center gap-2 font-bold text-xl relative"><span class="material-symbols-outlined">eco</span>Storify Farm</a>
    <div class="relative">
      <h2 class="text-3xl xl:text-4xl font-extrabold leading-tight">Kelola gudang panen<br>Anda, kapan saja.</h2>
      <ul class="mt-6 space-y-3 text-white/85">
        <li class="flex items-center gap-3"><span class="material-symbols-outlined text-amber">check_circle</span>Pencatatan stok real-time</li>
        <li class="flex items-center gap-3"><span class="material-symbols-outlined text-amber">check_circle</span>Pelacakan lokasi per rak &amp; batch</li>
        <li class="flex items-center gap-3"><span class="material-symbols-outlined text-amber">check_circle</span>FIFO otomatis &amp; laporan siap unduh</li>
      </ul>
    </div>
    <div class="relative grid grid-cols-3 gap-4 border-t border-white/15 pt-6">
      <div><div class="text-2xl font-bold">12.450</div><div class="text-xs opacity-70 mt-1">kg stok aktif</div></div>
      <div><div class="text-2xl font-bold">24</div><div class="text-xs opacity-70 mt-1">produk terdaftar</div></div>
      <div><div class="text-2xl font-bold">3</div><div class="text-xs opacity-70 mt-1">gudang terhubung</div></div>
    </div>
  </div>

  <div class="flex items-center justify-center p-6 md:p-12 bg-bg min-h-screen">
    <div class="w-full max-w-sm">
      <a href="#/" class="lg:hidden flex items-center gap-2 text-primary font-bold text-xl mb-8"><span class="material-symbols-outlined">eco</span>Storify Farm</a>
      <h1 class="text-2xl font-bold text-primary">Masuk ke akun Anda</h1>
      <p class="text-muted text-sm mt-1 mb-6">Kelola stok dan gudang hasil panen Anda.</p>

      <form id="loginForm" onsubmit="return handleLogin(event)" class="space-y-4" autocomplete="off">
        <label class="block text-sm font-medium">Email
          <input id="loginEmail" type="email" class="input mt-1" placeholder="nama@perusahaan.id" autocomplete="username" required>
        </label>
        <label class="block text-sm font-medium">Password
          <div class="relative mt-1">
            <input id="loginPassword" type="password" class="input pr-11" placeholder="••••••••" autocomplete="off" required>
            <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted" aria-label="Tampilkan password">
              <span id="pwIcon" class="material-symbols-outlined text-[20px]">visibility</span>
            </button>
          </div>
        </label>
        <div class="flex items-center justify-between text-sm">
          <label class="flex items-center gap-2"><input type="checkbox" class="w-4 h-4 accent-primary" checked>Ingat saya</label>
          <a href="#" onclick="showToast('Fitur reset password segera hadir');return false" class="text-primary font-medium">Lupa password?</a>
        </div>
        <p id="loginError" class="hidden text-sm text-danger">Email atau password salah.</p>
        <button type="submit" class="w-full bg-primary text-white h-12 rounded-lg font-semibold hover:bg-primary2 transition">Masuk</button>
      </form>

      <p class="text-sm text-muted text-center mt-6">Belum punya akun? <a href="#register" class="text-primary font-semibold">Daftar</a></p>
      <a href="#/" class="block text-center text-sm text-muted mt-4 hover:text-primary">← Kembali ke beranda</a>
    </div>
  </div>
</div>


<div id="view-register" style="display:none" class="min-h-screen grid lg:grid-cols-2">

  <div class="hidden lg:flex flex-col justify-between bg-primary text-white p-12 relative overflow-hidden">
    <div class="absolute inset-0 grain-bg pointer-events-none opacity-[.15]"></div>
    <a href="#/" class="flex items-center gap-2 font-bold text-xl relative"><span class="material-symbols-outlined">eco</span>Storify Farm</a>
    <div class="relative">
      <h2 class="text-3xl xl:text-4xl font-extrabold leading-tight">Mulai kelola gudang<br>panen Anda sendiri.</h2>
      <ul class="mt-6 space-y-3 text-white/85">
        <li class="flex items-center gap-3"><span class="material-symbols-outlined text-amber">check_circle</span>Akun baru langsung jadi Admin</li>
        <li class="flex items-center gap-3"><span class="material-symbols-outlined text-amber">check_circle</span>Data gudang dimulai dari kosong</li>
        <li class="flex items-center gap-3"><span class="material-symbols-outlined text-amber">check_circle</span>Tambahkan produk &amp; gudang Anda sendiri</li>
      </ul>
    </div>
    <div class="relative"></div>
  </div>

  <div class="flex items-center justify-center p-6 md:p-12 bg-bg min-h-screen">
    <div class="w-full max-w-sm">
      <a href="#/" class="lg:hidden flex items-center gap-2 text-primary font-bold text-xl mb-8"><span class="material-symbols-outlined">eco</span>Storify Farm</a>
      <h1 class="text-2xl font-bold text-primary">Buat akun baru</h1>
      <p class="text-muted text-sm mt-1 mb-6">Akun pertama Anda otomatis menjadi Admin dengan data gudang kosong.</p>

      <form id="registerForm" onsubmit="return handleRegister(event)" class="space-y-4">
        <label class="block text-sm font-medium">Nama Lengkap
          <input id="regName" type="text" class="input mt-1" placeholder="Nama Anda" required>
        </label>
        <label class="block text-sm font-medium">Email
          <input id="regEmail" type="email" class="input mt-1" placeholder="nama@perusahaan.id" required>
        </label>
        <label class="block text-sm font-medium">Password
          <div class="relative mt-1">
            <input id="regPassword" type="password" class="input pr-11" placeholder="Minimal 6 karakter" autocomplete="new-password" required>
            <button type="button" onclick="toggleRegisterPassword('regPassword','regPasswordIcon')" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted" aria-label="Tampilkan password">
              <span id="regPasswordIcon" class="material-symbols-outlined text-[20px]">visibility</span>
            </button>
          </div>
        </label>
        <label class="block text-sm font-medium">Konfirmasi Password
          <div class="relative mt-1">
            <input id="regPasswordConfirm" type="password" class="input pr-11" placeholder="Ulangi password" autocomplete="new-password" required>
            <button type="button" onclick="toggleRegisterPassword('regPasswordConfirm','regPasswordConfirmIcon')" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted" aria-label="Tampilkan konfirmasi password">
              <span id="regPasswordConfirmIcon" class="material-symbols-outlined text-[20px]">visibility</span>
            </button>
          </div>
        </label>
        <p id="registerError" class="hidden text-sm text-danger"></p>
        <button type="submit" class="w-full bg-primary text-white h-12 rounded-lg font-semibold hover:bg-primary2 transition">Daftar</button>
      </form>

      <p class="text-sm text-muted text-center mt-6">Sudah punya akun? <a href="#login" class="text-primary font-semibold">Masuk</a></p>
      <a href="#/" class="block text-center text-sm text-muted mt-4 hover:text-primary">← Kembali ke beranda</a>
    </div>
  </div>
</div>



<div id="view-app" style="display:none">

<div id="drawerOverlay" class="drawer-overlay" onclick="toggleMobile()"></div>

<aside class="fixed left-0 top-0 h-screen w-64 bg-white border-r border-line p-5 flex flex-col z-40">
  <div class="mb-7">
    <div class="flex items-center gap-2 text-primary font-bold text-2xl">
      <span class="material-symbols-outlined">eco</span> Storify Farm
    </div>
  </div>

  <div class="relative mb-6">
    <button onclick="toggleUserMenu(event)" class="flex items-center gap-3 w-full p-3 rounded-lg bg-sage2 hover:bg-sage transition text-left">
      <div class="w-10 h-10 rounded-full bg-primary2 text-white flex items-center justify-center shrink-0 overflow-hidden">
        <img id="sidebarAvatarImg" class="w-full h-full object-cover hidden" alt="Foto profil">
        <span id="sidebarAvatarIcon" class="material-symbols-outlined">person</span>
      </div>
      <div class="flex-1 min-w-0">
        <b class="userNameSlot block truncate">Admin User</b>
        <div class="text-xs text-muted font-mono userRoleSlot truncate">Warehouse Manager</div>
      </div>
      <span class="material-symbols-outlined text-muted text-[18px]">expand_more</span>
    </button>
    <div id="userMenu" class="hidden absolute left-0 right-0 mt-2 bg-white border border-line rounded-lg shadow-lg overflow-hidden z-50">
      <a href="#settings" class="flex items-center gap-2 px-4 py-3 hover:bg-sage2 text-sm"><span class="material-symbols-outlined text-[18px]">settings</span>Pengaturan</a>
      <button onclick="logout()" class="w-full flex items-center gap-2 px-4 py-3 hover:bg-sage2 text-sm text-danger text-left"><span class="material-symbols-outlined text-[18px]">logout</span>Keluar</button>
    </div>
  </div>

  <nav id="nav" class="flex flex-col gap-1 flex-1 min-h-0 overflow-y-auto pr-1">
    <a href="#dashboard" data-page="dashboard" class="nav-item active"><span class="material-symbols-outlined">dashboard</span>Dashboard</a>
    <a href="#produk" data-page="produk" class="nav-item"><span class="material-symbols-outlined">inventory_2</span>Data Produk</a>
    <a href="#transaksi" data-page="transaksi" class="nav-item"><span class="material-symbols-outlined">swap_horiz</span>Barang Masuk/Keluar</a>
    <a href="#barcode" data-page="barcode" data-roles="admin,petugas" class="nav-item"><span class="material-symbols-outlined">barcode_scanner</span>Barcode</a>
    <a href="#warehouse" data-page="warehouse" class="nav-item"><span class="material-symbols-outlined">streetview</span>Storify View</a>
    <a href="#fifo" data-page="fifo" class="nav-item"><span class="material-symbols-outlined">timer</span>FIFO</a>
    <a href="#kapasitas" data-page="kapasitas" class="nav-item"><span class="material-symbols-outlined">view_quilt</span>Kapasitas Gudang</a>
    <a href="#planner" data-page="planner" data-roles="admin,supervisor" class="nav-item"><span class="material-symbols-outlined">architecture</span>Warehouse Planner</a>
    <a href="#reports" data-page="reports" data-roles="admin,supervisor" class="nav-item"><span class="material-symbols-outlined">assessment</span>Reports</a>
    <a href="#users" data-page="users" data-roles="admin" class="nav-item"><span class="material-symbols-outlined">group</span>User Management</a>
  </nav>
  <div class="border-t border-line pt-3 text-center text-xs text-muted font-mono">Storify Farm v1.0</div>
</aside>

<main class="main ml-64 min-h-screen">
<header class="sticky top-0 z-30 bg-bg/95 backdrop-blur border-b border-line px-4 md:px-8 py-3 flex items-center gap-4">
  <button onclick="toggleMobile()" class="mobile-menu p-2 rounded-lg hover:bg-sage2"><span class="material-symbols-outlined">menu</span></button>
  <a href="#/" class="mobile-menu items-center gap-1 font-bold text-primary text-lg"><span class="material-symbols-outlined">eco</span>Storify Farm</a>
  <div class="desktop-search flex-1 max-w-md relative">
    <span class="material-symbols-outlined absolute left-3 top-2.5 text-muted">search</span>
    <input id="globalSearch" class="input pl-10 rounded-full" placeholder="Search inventory..." oninput="globalSearch(this.value)">
  </div>
  <div class="ml-auto flex items-center gap-2 relative">
    <button onclick="toggleNotifPanel(event)" class="p-2 rounded-full hover:bg-sage2 text-primary relative">
      <span class="material-symbols-outlined">notifications</span>
      <span id="notifBadge" class="hidden absolute top-0.5 right-0.5 min-w-[16px] h-4 px-1 bg-danger rounded-full text-[10px] leading-4 text-white font-bold text-center font-mono"></span>
    </button>
    <div id="notifPanel" class="hidden absolute right-0 top-12 w-80 bg-white border border-line rounded-lg shadow-lg z-50 max-h-96 overflow-auto">
      <div class="p-3 border-b border-line font-semibold text-sm">Notifikasi</div>
      <div id="notifList"><div class="notification-empty p-6 text-center text-muted text-sm"><span class="material-symbols-outlined block mb-2 text-[28px]">notifications_none</span>Tidak ada notifikasi baru</div></div>
    </div>
  </div>
</header>

<div class="content p-4 md:p-8 max-w-[1500px] mx-auto">

<section id="page-dashboard" class="page active">
  <div class="mb-8"><h1 class="text-4xl font-bold text-primary">Dashboard</h1><p class="text-muted text-lg mt-1">Overview inventory dan aktivitas warehouse.</p></div>
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
    <div class="card p-6"><div class="text-sm text-muted font-mono">TOTAL PRODUK</div><div id="statProducts" class="text-4xl font-bold text-primary mt-2">24</div><div class="text-sm mt-2">Produk terdaftar</div></div>
    <div class="card p-6"><div class="text-sm text-muted font-mono">STOK TERSEDIA</div><div id="statTotalStock" class="text-4xl font-bold text-primary mt-2">0</div><div class="text-sm mt-2">kg di seluruh gudang</div></div>
    <div class="card p-6"><div class="text-sm text-muted font-mono">BARANG MASUK HARI INI</div><div id="statTodayIn" class="text-4xl font-bold text-primary mt-2">0</div><div class="text-sm mt-2">kg hari ini</div></div>
    <div class="card p-6"><div class="text-sm text-muted font-mono">BARANG KELUAR HARI INI</div><div id="statTodayOut" class="text-4xl font-bold text-primary mt-2">0</div><div class="text-sm mt-2">kg hari ini</div></div>
  </div>
  <div class="grid lg:grid-cols-3 gap-5">
    <div class="card p-6 lg:col-span-2"><div class="flex justify-between items-center mb-4"><h2 id="myH2" class="text-xl font-semibold">Aktivitas Terbaru</h2><button onclick="go('transaksi')" class="text-primary font-semibold">Lihat semua →</button></div><div id="activityList"></div></div>
    <div class="card p-6"><h2 class="text-xl font-semibold mb-4">Kapasitas Gudang</h2><div id="dashboardCapacity" class="space-y-5"></div></div>
  </div>
</section>

<section id="page-produk" class="page">
  <div class="mb-8"><h1 class="text-4xl font-bold text-primary">Data Produk</h1><p class="text-muted text-lg mt-1">Kelola data produk dan stok.</p></div>
  <div class="card p-5 mb-5"><div class="flex flex-wrap gap-3"><input id="productSearch" class="input max-w-sm" placeholder="Cari produk..." oninput="renderProducts()"><button onclick="openProductForm()" class="bg-primary text-white px-5 rounded-lg font-semibold flex items-center gap-2"><span class="material-symbols-outlined">add</span>Tambah Produk</button></div></div>
  <div class="card p-5"><div class="table-wrap"><table><thead><tr><th>Produk</th><th>SKU</th><th>Lokasi</th><th>Stok</th><th>Kapasitas</th><th>Status Kapasitas</th><th>Suhu / Kelembapan</th><th>Aksi</th></tr></thead><tbody id="productTable"></tbody></table></div></div>
</section>

<section id="page-transaksi" class="page">
  <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
    <div><h1 class="text-4xl font-bold text-primary">Barang Masuk / Keluar</h1><p class="text-muted text-lg mt-1">Catat pergerakan stok secara akurat.</p></div>
    <button id="trxHistoryBtn" onclick="openHistoryModal()" class="border border-primary text-primary px-5 h-11 rounded-lg font-semibold flex items-center gap-2 shrink-0"><span class="material-symbols-outlined text-[19px]">history</span>History Keluar Masuk</button>
  </div>
  <div id="trxViewOnlyWrap" class="hidden">
    <div class="card p-6">
      <h2 class="text-2xl font-semibold text-primary mb-5">Riwayat Transaksi</h2>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Tanggal &amp; Waktu</th><th>Barang</th><th>Jenis</th><th>Jumlah</th><th>Keterangan</th><th>Pengguna</th></tr></thead>
          <tbody id="historyTableInline"></tbody>
        </table>
      </div>
    </div>
  </div>
  <div id="trxEditView" class="grid lg:grid-cols-3 gap-6">
    <div class="card p-6 lg:col-span-2"><h2 class="text-2xl font-semibold text-primary mb-5">Transaction Details</h2>
      <div class="grid md:grid-cols-2 gap-5">
        <label><span>Jenis Transaksi</span><select id="trxType" class="input mt-1" onchange="onTrxTypeChange()"><option value="in">Barang Masuk</option><option value="out">Barang Keluar (FIFO otomatis)</option></select></label>
        <label><span class="label">Produk</span><select id="trxProduct" class="input mt-1" onchange="onTrxProductChange()"></select></label>
        <label id="trxBatchWrap"><span>Nomor Batch</span><input id="trxBatch" class="input mt-1" placeholder="BRS260814xx"></label>
        <label id="trxDateWrap"><span>Tanggal Diterima</span><input id="trxDate" class="input mt-1" type="date"></label>
        <label><span>Quantity (kg)</span><input id="trxQty" class="input mt-1" type="number" min="1" placeholder="0"></label>
        <label><span>Lokasi</span><input id="trxLocation" class="input mt-1" readonly></label>
      </div>
      <p id="trxHint" class="text-sm text-muted mt-3">Barang masuk akan membuat batch baru. Barang keluar otomatis mengambil batch tertua terlebih dahulu (FIFO).</p>
      <div class="flex justify-end gap-3 mt-6"><button onclick="resetTrx()" class="px-5 h-11 rounded-lg border border-line">Cancel</button><button onclick="saveTransaction()" class="px-6 h-11 rounded-lg bg-primary text-white font-semibold">Confirm Transaction</button></div>
    </div>
    <div class="space-y-5">
      <div class="bg-primary text-white rounded-2xl p-6"><span class="material-symbols-outlined">lightbulb</span><h3 class="text-xl font-semibold mt-2">FIFO Recommendation</h3><p class="mt-2 opacity-90">Batch dengan tanggal masuk paling awal akan diprioritaskan keluar terlebih dahulu, otomatis oleh sistem.</p></div>
      <div class="card p-6"><div class="text-sm text-muted font-mono">CURRENT AVAILABLE STOCK</div><div id="trxCurrentStock" class="text-4xl font-bold text-primary mt-2">0</div><div id="trxCurrentLoc" class="text-muted">kg</div></div>
      <button onclick="openCameraScan('transaksi')" class="card p-6 w-full text-center border-dashed border-2 hover:border-primary">
  <span class="material-symbols-outlined text-5xl text-primary">barcode_scanner</span>
  <h3 class="font-semibold mt-2">Scan Barcode</h3>
  <p class="text-sm text-muted">Arahkan kamera HP ke barcode produk untuk isi form otomatis.</p>
</button>
    </div>
  </div>
</section>

<section id="page-barcode" class="page">
  <div class="mb-8"><h1 class="text-4xl font-bold text-primary">Barcode</h1><p class="text-muted text-lg mt-1">Generate, cetak, dan cari produk berdasarkan Barcode/SKU.</p></div>
  <div class="grid lg:grid-cols-2 gap-6">
    <div class="card p-6">
      <h2 class="text-xl font-semibold mb-5">Generate Barcode</h2>
      <select id="barcodeProduct" class="input mb-4" onchange="generateBarcode()"></select>
      <label class="block text-sm mb-4"><span>Cari berdasarkan Barcode / SKU</span><input id="barcodeSearch" class="input mt-1" placeholder="Contoh: SF-00001" oninput="searchByBarcode(this.value)"></label>
      <button onclick="openCameraScan()" class="w-full mb-3 border-2 border-primary text-primary h-11 rounded-lg font-semibold flex items-center justify-center gap-2">
        <span class="material-symbols-outlined text-[20px]">photo_camera</span>Scan dengan Kamera HP
      </button>
      <p class="text-xs text-muted mb-4 leading-relaxed">Buka halaman ini dari HP, lalu arahkan kamera ke label barcode produk untuk mencari produk otomatis.</p>
      <button onclick="generateBarcode()" class="w-full bg-primary text-white h-11 rounded-lg font-semibold">Generate Barcode</button>
    </div>
    <div class="card p-6 flex flex-col items-center justify-center min-h-[280px]">
      <div id="barcodeBox" class="w-full bg-sage2 rounded-xl flex items-center justify-center text-center text-muted p-5 min-h-[140px]">Barcode akan muncul di sini</div>
      <p id="barcodeLabel" class="font-mono mt-4 text-center"></p>
      <button id="barcodePrintBtn" onclick="printBarcode()" class="hidden mt-3 border border-line px-5 h-10 rounded-lg font-semibold text-sm flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">print</span>Cetak Barcode</button>
    </div>
  </div>
</section>

<section id="page-warehouse" class="page">
  <div class="mb-8"><h1 class="text-4xl font-bold text-primary flex items-center gap-2"><span class="material-symbols-outlined text-[32px]">explore</span>Storify View</h1><p class="text-muted text-lg mt-1">Cari lokasi barang dan lihat jalur menuju rak.</p></div>
  <div class="card p-8 warehouse-layout">
    <div class="loading-area-fixed"><div class="font-mono text-sm">LOADING AREA</div><div class="text-muted mt-2">Posisi awal gudang</div><div class="text-muted mt-1">↓ Lorong Utama ↓</div></div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4" id="storifyRacks" ondragover="dragRackOver(event)" ondrop="dropRack(event)"></div>
  </div>
</section>

<section id="page-fifo" class="page">
  <div class="mb-8"><h1 class="text-4xl font-bold text-primary">FIFO</h1><p class="text-muted text-lg mt-1">Prioritas pengeluaran berdasarkan batch tertua (First In, First Out).</p></div>
  <div class="card p-5"><div class="table-wrap"><table><thead><tr><th>Prioritas</th><th>Produk</th><th>Batch</th><th>Tanggal Masuk</th><th>Sisa Stok</th><th>Rekomendasi</th></tr></thead><tbody id="fifoTable"></tbody></table></div></div>
</section>

<section id="page-kapasitas" class="page">
  <div class="mb-8"><h1 class="text-4xl font-bold text-primary">Kapasitas Gudang</h1><p class="text-muted text-lg mt-1">Status kapasitas dihitung otomatis dari stok dibanding kapasitas maksimum tiap produk.</p></div>
  <div class="grid md:grid-cols-3 gap-5" id="capacityCards"></div>
</section>

<section id="page-planner" class="page">
  <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
    <div><h1 class="text-4xl font-bold text-primary flex items-center gap-2"><span class="material-symbols-outlined text-[32px]">psychology</span>Warehouse Planner</h1><p class="text-muted text-lg mt-1 flex items-center gap-1 flex-wrap">Perancangan gudang: kapasitas, jumlah rak, suhu, kelembapan, dan rekomendasi layout. Butuh saran lebih detail? Konsultasi dengan AI ada di bagian bawah halaman ini.<span class="material-symbols-outlined text-[18px]">arrow_downward</span></p></div>
  </div>
  <div class="card p-6"><div class="grid md:grid-cols-2 lg:grid-cols-5 gap-4">
    <label><span class="text-sm">Panjang gudang (m)</span><input id="plLength" type="number" step=".1" class="input mt-1" placeholder="20" oninput="plannerInputsChanged()"></label>
    <label><span class="text-sm">Lebar gudang (m)</span><input id="plWidth" type="number" step=".1" class="input mt-1" placeholder="12" oninput="plannerInputsChanged()"></label>
    <label><span class="text-sm">Panjang rak (m)</span><input id="plRackLength" type="number" step=".1" class="input mt-1" placeholder="2" oninput="plannerInputsChanged()"></label>
    <label><span class="text-sm">Lebar rak (m)</span><input id="plRackWidth" type="number" step=".1" class="input mt-1" placeholder="1" oninput="plannerInputsChanged()"></label>
    <label><span class="text-sm">Lebar lorong (m)</span><input id="plAisle" type="number" step=".1" class="input mt-1" placeholder="1.2" oninput="plannerInputsChanged()"></label>
  </div>
  <button onclick="calculatePlanner()" class="mt-5 bg-primary text-white px-6 h-11 rounded-lg font-semibold">Hitung & Beri Saran</button></div>
  <div id="plannerResult" class="mt-5 hidden">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
      <div class="card p-6"><div class="text-sm text-muted font-mono">LUAS GUDANG</div><div id="plArea" class="text-3xl font-bold text-primary mt-2">0</div><div class="text-sm text-muted">m²</div></div>
      <div class="card p-6"><div class="text-sm text-muted font-mono">ESTIMASI RAK</div><div id="plRacks" class="text-3xl font-bold text-primary mt-2">0</div><div class="text-sm text-muted">unit</div></div>
      <div class="card p-6"><div class="text-sm text-muted font-mono">SUHU IDEAL</div><div class="text-3xl font-bold text-primary mt-2">20–25°C</div></div>
      <div class="card p-6"><div class="text-sm text-muted font-mono">KELEMBAPAN</div><div class="text-3xl font-bold text-primary mt-2">60–70%</div></div>
    </div>
    <div class="card p-6 mt-5"><h3 class="font-semibold text-lg text-primary">AI Planner Recommendation</h3><p class="text-muted mt-2">Prioritaskan rak berdasarkan FIFO, sisakan jalur utama untuk akses petugas, dan tempatkan komoditas dengan kondisi penyimpanan serupa dalam zona yang sama.</p>
    </div>
  </div>
  <div class="mt-8 card p-6 md:p-7 bg-sage2 border border-line flex flex-col md:flex-row items-center gap-5 justify-between">
    <div class="flex items-center gap-4 text-center md:text-left">
      <div class="w-14 h-14 rounded-2xl bg-primary text-white flex items-center justify-center shrink-0 mx-auto md:mx-0"><svg viewBox="0 0 48 48" width="28" height="28" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M24 4c3 2 3 7 0 9-3-2-3-7 0-9Z"></path><circle cx="24" cy="8" r="1.3" fill="currentColor" stroke="none"></circle><ellipse cx="24" cy="27" rx="16" ry="14.5"></ellipse><circle cx="18" cy="26" r="2.6"></circle><circle cx="30" cy="26" r="2.6"></circle><path d="M18 34c3 3 9 3 12 0"></path></svg></div>
      <div>
        <h3 class="font-semibold text-lg text-primary">Masih ada yang dibingungkan atau mau ditanyakan?</h3>
        <p class="text-muted text-sm mt-0.5">Konsultasi dengan Storify AI saja.</p>
      </div>
    </div>
    <button id="plannerConsultBtn" onclick="consultAIAboutPlanner()" class="shrink-0 bg-primary text-white px-6 h-12 rounded-lg font-semibold flex items-center gap-2 hover:bg-primary2 transition"><svg viewBox="0 0 48 48" width="19" height="19" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M24 4c3 2 3 7 0 9-3-2 3-7 0-9Z"></path><circle cx="24" cy="8" r="1.3" fill="currentColor" stroke="none"></circle><ellipse cx="24" cy="27" rx="16" ry="14.5"></ellipse><circle cx="18" cy="26" r="2.6"></circle><circle cx="30" cy="26" r="2.6"></circle><path d="M18 34c3 3 9 3 12 0"></path></svg>Konsultasi dengan Storify AI</button>
  </div>
</section>

<section id="page-ai" class="page">
  <div class="mb-6 flex items-center justify-between gap-3">
    <div class="flex items-center gap-3">
      <button onclick="backFromAI()" class="w-11 h-11 rounded-lg border border-line flex items-center justify-center shrink-0 hover:bg-sage2 transition" title="Kembali" aria-label="Kembali"><span class="material-symbols-outlined">arrow_back</span></button>
      <div><h1 class="text-4xl font-bold text-primary flex items-center gap-2"><svg viewBox="0 0 48 48" width="34" height="34" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M24 4c3 2 3 7 0 9-3-2-3-7 0-9Z"></path><circle cx="24" cy="8" r="1.3" fill="currentColor" stroke="none"></circle><ellipse cx="24" cy="27" rx="16" ry="14.5"></ellipse><circle cx="18" cy="26" r="2.6"></circle><circle cx="30" cy="26" r="2.6"></circle><path d="M18 34c3 3 9 3 12 0"></path></svg>Storify AI</h1><p class="text-muted text-lg mt-1">Tanya apa saja — stok, lokasi barang, FIFO, kapasitas gudang, rencana Warehouse Planner, sampai pertanyaan umum lainnya.</p></div>
    </div>
  </div>
  <div class="card p-0 overflow-hidden flex flex-col" style="height:min(640px,75vh)">
    <div id="aiAppMessages" class="ai-messages flex-1"></div>
    <form id="aiAppForm" class="ai-chat-form" onsubmit="return sendAppAIMessage(event)">
      <div class="ai-chat-input-row">
        <input id="aiAppInput" class="input" placeholder="Tulis pertanyaan apa saja..." autocomplete="off">
        <button type="submit" class="ai-send-btn" aria-label="Kirim"><span class="material-symbols-outlined">send</span></button>
      </div>
    </form>
  </div>
</section>

<section id="page-reports" class="page">
  <div class="mb-8"><h1 class="text-4xl font-bold text-primary">Reports</h1><p class="text-muted text-lg mt-1">Laporan transaksi dan inventory, otomatis dari data yang tercatat.</p></div>

  <div class="card p-6 mb-5">
    <div class="flex flex-wrap items-end gap-3">
      <label class="block"><span class="text-sm text-muted">Dari tanggal</span><input id="repFrom" type="date" class="input mt-1"></label>
      <label class="block"><span class="text-sm text-muted">Sampai tanggal</span><input id="repTo" type="date" class="input mt-1"></label>
      <button onclick="renderReports()" class="bg-primary text-white px-5 h-11 rounded-lg font-semibold">Generate Report</button>
      <button onclick="downloadReport()" class="border border-primary text-primary px-5 h-11 rounded-lg font-semibold flex items-center gap-2"><span class="material-symbols-outlined text-[19px]">download</span>Unduh Laporan (CSV)</button>
    </div>
  </div>

  <div class="grid md:grid-cols-4 gap-5 mb-5">
    <div class="card p-6"><span class="text-muted">Barang Masuk</span><b id="repIn" class="block text-3xl text-primary mt-2">0 kg</b></div>
    <div class="card p-6"><span class="text-muted">Barang Keluar</span><b id="repOut" class="block text-3xl text-primary mt-2">0 kg</b></div>
    <div class="card p-6"><span class="text-muted">Perubahan Stok Bersih</span><b id="repNet" class="block text-3xl text-primary mt-2">0 kg</b></div>
    <div class="card p-6"><span class="text-muted">Jumlah Transaksi</span><b id="repCount" class="block text-3xl text-primary mt-2">0</b></div>
  </div>

  <div class="card p-5 mb-5">
    <h2 class="text-xl font-semibold mb-4">Perubahan Stok per Produk</h2>
    <div class="table-wrap"><table><thead><tr><th>Produk</th><th>Barang Masuk</th><th>Barang Keluar</th><th>Perubahan Bersih</th><th>Stok Saat Ini</th></tr></thead><tbody id="repProductTable"></tbody></table></div>
  </div>

  <div class="card p-5">
    <h2 class="text-xl font-semibold mb-4">Data Transaksi</h2>
    <div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Jenis</th><th>Produk</th><th>Batch</th><th>Jumlah</th><th>Lokasi</th><th>Catatan</th></tr></thead><tbody id="repTrxTable"></tbody></table></div>
  </div>
</section>

<section id="page-users" class="page">
  <div class="mb-8"><h1 class="text-4xl font-bold text-primary">User Management</h1><p class="text-muted text-lg mt-1">Kelola akun admin, supervisor, dan petugas gudang.</p></div>
  <div class="card p-6 mb-5"><div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
    <input id="userName" class="input" placeholder="Nama">
    <input id="userEmail" class="input" type="email" placeholder="Email">
    <input id="userPassword" class="input" type="password" placeholder="Password (min. 6 karakter)">
    <select id="userRole" class="input"><option value="admin">Admin</option><option value="supervisor">Supervisor</option><option value="petugas" selected>Petugas</option></select>
  </div><button onclick="addUser()" class="mt-4 bg-primary text-white px-6 h-11 rounded-lg font-semibold">Tambah User</button></div>
  <div class="card p-5"><div class="table-wrap"><table><thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th></tr></thead><tbody id="usersTable"></tbody></table></div></div>
</section>

<section id="page-settings" class="page">
  <div class="mb-8"><h1 class="text-4xl font-bold text-primary">Settings</h1><p class="text-muted text-lg mt-1">Pengaturan profil, tema, dan warna aksen.</p></div>
  <div class="card p-6 max-w-2xl space-y-5">
    <label class="block"><span>Nama</span><input id="setName" class="input mt-1" value="Admin User"></label>
    <label class="block"><span>Foto Profil</span>
      <div class="flex items-center gap-4 mt-1">
        <div class="w-16 h-16 rounded-full bg-primary2 text-white flex items-center justify-center shrink-0 overflow-hidden border border-line">
          <img id="avatarPreview" class="w-full h-full object-cover hidden" alt="Preview foto profil">
          <span id="avatarPreviewIcon" class="material-symbols-outlined text-3xl">person</span>
        </div>
        <input id="setAvatar" type="file" accept="image/*" class="input" onchange="previewAvatar()">
      </div>
    </label>
    <label class="block"><span>Tema</span><select id="setTheme" class="input mt-1"><option value="light">Terang</option><option value="dark">Gelap</option><option value="system">Sistem</option></select></label>
    <label class="block"><span>Warna Aksen</span><select id="setAccent" class="input mt-1" onchange="toggleCustomAccentInput()"><option value="green">Hijau</option><option value="blue">Biru</option><option value="orange">Oranye</option><option value="purple">Ungu</option><option value="custom">Warna kustom</option></select></label>
    <label id="customAccentWrap" class="block hidden"><span>Warna kustom</span><div class="flex items-center gap-2 mt-1"><input id="setAccentCustom" type="color" class="h-11 w-20 input p-1" value="#3d4b3c"><span class="text-sm text-muted">Pilih warna yang diinginkan.</span></div></label>
    <div class="border-t border-line pt-5">
      <button type="button" id="passwordChangeToggle" onclick="togglePasswordChange()" class="w-full flex items-center justify-between text-left font-semibold text-primary hover:text-primary2 transition">
        <span>Ganti Password</span><span id="passwordChangeArrow" class="material-symbols-outlined">chevron_right</span>
      </button>
      <div id="passwordChangeFields" class="hidden space-y-4 mt-4">
        <label class="block"><span>Password saat ini</span><div class="relative mt-1"><input id="setCurrentPassword" type="password" class="input pr-11" autocomplete="current-password"><button type="button" onclick="toggleRegisterPassword('setCurrentPassword','setCurrentPasswordIcon')" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted" aria-label="Tampilkan password saat ini"><span id="setCurrentPasswordIcon" class="material-symbols-outlined text-[20px]">visibility</span></button></div></label>
        <label class="block"><span>Password baru</span><div class="relative mt-1"><input id="setPassword" type="password" class="input pr-11" minlength="6" autocomplete="new-password"><button type="button" onclick="toggleRegisterPassword('setPassword','setPasswordIcon')" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted" aria-label="Tampilkan password baru"><span id="setPasswordIcon" class="material-symbols-outlined text-[20px]">visibility</span></button></div></label>
        <label class="block"><span>Konfirmasi password baru</span><div class="relative mt-1"><input id="setPasswordConfirm" type="password" class="input pr-11" minlength="6" autocomplete="new-password"><button type="button" onclick="toggleRegisterPassword('setPasswordConfirm','setPasswordConfirmIcon')" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted" aria-label="Tampilkan konfirmasi password"><span id="setPasswordConfirmIcon" class="material-symbols-outlined text-[20px]">visibility</span></button></div></label>
        <p class="text-xs text-muted">Perubahan password akan dilaporkan ke admin workspace.</p>
      </div>
    </div>
    <label class="flex items-center justify-between p-4 bg-sage2 rounded-lg"><span>Notifikasi stok rendah</span><input type="checkbox" checked class="w-5 h-5 accent-primary"></label>
    <button onclick="saveSettings()" class="bg-primary text-white px-6 h-11 rounded-lg font-semibold">Simpan Pengaturan</button>
    <div class="border-t border-danger/30 pt-5 mt-2">
      <h2 class="font-semibold text-danger">Hapus akun</h2>
      <p class="text-sm text-muted mt-1 mb-3">Akun dan seluruh data workspace akan dihapus permanen.</p>
      <div class="flex flex-col sm:flex-row gap-3">
        <input id="deleteAccountPassword" type="password" class="input flex-1" placeholder="Password saat ini" autocomplete="current-password">
        <button type="button" onclick="deleteAccount()" class="border border-danger text-danger px-5 h-11 rounded-lg font-semibold">Hapus Akun</button>
      </div>
    </div>
  </div>
</section>

<div id="historyModal" class="hidden fixed inset-0 bg-black/40 z-[70] flex items-center justify-center p-4" onclick="if(event.target===this)closeHistoryModal()">
  <div class="card w-full max-w-4xl max-h-[85vh] flex flex-col">
    <div class="flex items-center justify-between p-5 border-b border-line shrink-0">
      <div><h2 class="text-xl font-semibold text-primary">History Keluar Masuk</h2><p class="text-sm text-muted mt-0.5">Seluruh riwayat pergerakan barang — terbaru di paling atas.</p></div>
      <button onclick="closeHistoryModal()" class="p-2 rounded-lg hover:bg-sage2 shrink-0"><span class="material-symbols-outlined">close</span></button>
    </div>
    <div class="p-5 overflow-auto">
      <div class="table-wrap">
        <table>
          <thead><tr><th>Tanggal &amp; Waktu</th><th>Barang</th><th>Jenis</th><th>Jumlah</th><th>Keterangan</th><th>Pengguna</th></tr></thead>
          <tbody id="historyTable"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

</div>
</main>
</div>
<div id="cameraScanModal" class="fixed inset-0 bg-black/70 z-[80] hidden items-center justify-center p-4">
  <div class="bg-white rounded-2xl overflow-hidden w-full max-w-sm">
    <div class="flex items-center justify-between p-4 border-b border-line">
      <h3 class="font-semibold flex items-center gap-2"><span class="material-symbols-outlined text-primary">photo_camera</span>Scan Barcode</h3>
      <button onclick="closeCameraScan()" class="text-muted hover:text-text" title="Tutup"><span class="material-symbols-outlined">close</span></button>
    </div>
    <div class="relative bg-black" style="aspect-ratio:1/1">
      <video id="cameraScanVideo" class="w-full h-full object-cover" playsinline muted autoplay></video>
      <div class="absolute inset-8 border-2 border-amber rounded-xl pointer-events-none"></div>
    </div>
    <div id="cameraScanStatus" class="p-4 text-sm text-muted text-center">Meminta izin kamera...</div>
  </div>
</div>

@endsection
