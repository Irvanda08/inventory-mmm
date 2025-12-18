<?php
include 'config/database.php'; 
include 'templates/header.php';
include 'templates/sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

// 1. Tangkap filter
$id_barang   = isset($_GET['id_barang']) ? intval($_GET['id_barang']) : '';
$bulan_awal  = isset($_GET['bulan_awal']) ? intval($_GET['bulan_awal']) : '';
$bulan_akhir = isset($_GET['bulan_akhir']) ? intval($_GET['bulan_akhir']) : '';
$tahun       = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

// 2. Query Daftar Barang
$barang_query = mysqli_query($conn, "SELECT * FROM barang ORDER BY nama_barang");
?>

<main class="flex-1 bg-slate-50 min-h-screen p-4 md:p-8">
  
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 no-print">
    <div>
      <h1 class="text-2xl font-bold text-slate-800">Laporan Stok Antar Bulan</h1>
      <p class="text-sm text-slate-500 mt-1">
        Analisis pergerakan stok berdasarkan rentang waktu dan jenis produk
      </p>
    </div>
    <div class="flex gap-3">
        <button type="button" onclick="window.print()" class="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 transition-all">
          <span>🖨️</span> Print Laporan
        </button>
    </div>
  </div>

  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-8 no-print">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Bulan Awal</label>
        <select name="bulan_awal" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
          <option value="">Pilih Bulan</option>
          <?php for ($i=1;$i<=12;$i++): ?>
            <option value="<?= $i ?>" <?= ($bulan_awal==$i)?'selected':''; ?>><?= date('F', mktime(0,0,0,$i,1)) ?></option>
          <?php endfor; ?>
        </select>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Bulan Akhir</label>
        <select name="bulan_akhir" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
          <option value="">Pilih Bulan</option>
          <?php for ($i=1;$i<=12;$i++): ?>
            <option value="<?= $i ?>" <?= ($bulan_akhir==$i)?'selected':''; ?>><?= date('F', mktime(0,0,0,$i,1)) ?></option>
          <?php endfor; ?>
        </select>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tahun</label>
        <select name="tahun" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
          <?php for ($t=date('Y'); $t>=2020; $t--): ?>
            <option value="<?= $t ?>" <?= ($tahun==$t)?'selected':''; ?>><?= $t ?></option>
          <?php endfor; ?>
        </select>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Produk</label>
        <select name="id_barang" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
          <option value="">Semua Produk</option>
          <?php mysqli_data_seek($barang_query, 0); while($b = mysqli_fetch_assoc($barang_query)): ?>
            <option value="<?= $b['id_barang'] ?>" <?= ($id_barang==$b['id_barang'])?'selected':''; ?>>
              <?= htmlspecialchars($b['nama_barang']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="md:col-span-4 flex justify-end">
        <button type="submit" name="preview" class="px-8 py-3 bg-blue-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-600/20 hover:bg-blue-700 transition-all">
          Tampilkan Laporan
        </button>
      </div>
    </form>
  </div>

  <?php if(isset($_GET['preview'])): ?>
    <?php
        // Persiapan Query
        $where = [];
        if($id_barang) $where[] = "t.id_barang = '$id_barang'";
        if($bulan_awal) $where[] = "MONTH(t.tanggal) >= '$bulan_awal'";
        if($bulan_akhir) $where[] = "MONTH(t.tanggal) <= '$bulan_akhir'";
        if($tahun) $where[] = "YEAR(t.tanggal) = '$tahun'";

        $sql = "SELECT t.*, b.kode_barang, b.nama_barang, b.satuan
                FROM transaksi_barang t
                JOIN barang b ON t.id_barang = b.id_barang";

        if(count($where)) $sql .= " WHERE ".implode(' AND ', $where);
        $sql .= " ORDER BY t.tanggal ASC";
        $res = mysqli_query($conn, $sql);

        // Hitung Statistik Ringkasan
        $t_masuk = 0; $t_keluar = 0;
    ?>

    <div id="preview-table">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 no-print">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl">📥</div>
                <div>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Masuk</p>
                    <p id="stat-masuk" class="text-xl font-bold text-slate-800">0</p>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center text-xl">📤</div>
                <div>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Keluar</p>
                    <p id="stat-keluar" class="text-xl font-bold text-slate-800">0</p>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-xl">📦</div>
                <div>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Stok Akhir Laporan</p>
                    <p id="stat-akhir" class="text-xl font-bold text-slate-800">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="hidden print:block p-8 text-center border-b-2 border-slate-800 mb-6">
                <h2 class="text-2xl font-bold uppercase">Laporan Mutasi Stok Antar Bulan</h2>
                <p class="text-slate-600">Periode: <?= $bulan_awal ? date('F', mktime(0,0,0,$bulan_awal,1)) : 'Awal' ?> - <?= $bulan_akhir ? date('F', mktime(0,0,0,$bulan_akhir,1)) : 'Akhir' ?> <?= $tahun ?></p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Tanggal</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Produk</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Masuk</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Keluar</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center bg-slate-100/50">Sisa Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        $stok = 0;
                        if(mysqli_num_rows($res) > 0){
                          while($row = mysqli_fetch_assoc($res)){
                            $masuk = strtolower($row['jenis'])=='masuk' ? $row['jumlah'] : 0;
                            $keluar = strtolower($row['jenis'])=='keluar' ? $row['jumlah'] : 0;
                            $stok += $masuk - $keluar;
                            $t_masuk += $masuk; $t_keluar += $keluar;
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                          <td class="px-6 py-4 text-sm font-medium text-slate-600">
                              <?= date('d M Y', strtotime($row['tanggal'])) ?>
                          </td>
                          <td class="px-6 py-4">
                              <div class="font-bold text-slate-700"><?= htmlspecialchars($row['nama_barang']) ?></div>
                              <div class="text-[10px] font-mono text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded w-fit uppercase"><?= htmlspecialchars($row['kode_barang']) ?></div>
                          </td>
                          <td class="px-6 py-4 text-center font-semibold text-blue-600 italic">
                              <?= $masuk ? '+'.number_format($masuk) : '-' ?>
                          </td>
                          <td class="px-6 py-4 text-center font-semibold text-rose-600 italic">
                              <?= $keluar ? '-'.number_format($keluar) : '-' ?>
                          </td>
                          <td class="px-6 py-4 text-center font-bold text-slate-800 bg-slate-50/30">
                              <?= number_format($stok) ?>
                          </td>
                        </tr>
                        <?php } ?>
                        <script>
                            document.getElementById('stat-masuk').innerText = '<?= number_format($t_masuk) ?>';
                            document.getElementById('stat-keluar').innerText = '<?= number_format($t_keluar) ?>';
                            document.getElementById('stat-akhir').innerText = '<?= number_format($stok) ?>';
                        </script>
                        <?php } else { ?>
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <img src="https://illustrations.popsy.co/slate/empty-folder.svg" class="w-32 h-32 mx-auto mb-4 opacity-20">
                                <p class="text-slate-400 font-medium">Data tidak ditemukan untuk periode ini</p>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <div class="hidden print:grid grid-cols-2 mt-12 p-8 text-center">
                <div></div>
                <div class="space-y-20">
                    <p class="text-sm font-medium">Dicetak pada: <?= date('d/m/Y H:i') ?></p>
                    <p class="font-bold border-t-2 border-slate-800 pt-2 inline-block px-12 uppercase">Manajer Gudang</p>
                </div>
            </div>
        </div>
    </div>
  <?php endif; ?>

</main>

<style>
/* 1. Perbaikan Utama: CSS Khusus Print */
@media print {
    /* Sembunyikan semua elemen navigasi dan tombol */
    .no-print, 
    #sidebar, 
    nav, 
    header, 
    footer, 
    button, 
    .logout-section,
    form { 
        display: none !important; 
    }

    /* Pastikan area laporan memenuhi halaman kertas */
    body { background: white !important; }
    main { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    .bg-slate-50 { background-color: white !important; }
    
    /* Tampilkan tabel laporan dengan border yang jelas */
    #preview-table { 
        visibility: visible !important; 
        position: absolute; 
        left: 0; 
        top: 0; 
        width: 100%; 
        box-shadow: none !important;
        border: none !important;
    }
    
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th { background-color: #f1f5f9 !important; color: black !important; border: 1px solid #cbd5e1 !important; }
    td { border: 1px solid #cbd5e1 !important; }
}

/* Style Form Preview (Layar) */
.input-field { @apply w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 transition-all; }
</style>

<?php include 'templates/footer.php'; ?>