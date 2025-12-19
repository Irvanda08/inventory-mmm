<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/database.php'; 
include 'templates/header.php';
include 'templates/sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

// 1. Tangkap filter
$id_barang   = isset($_GET['id_barang']) ? intval($_GET['id_barang']) : 0;
$bulan_awal  = isset($_GET['bulan_awal']) ? intval($_GET['bulan_awal']) : 0;
$bulan_akhir = isset($_GET['bulan_akhir']) ? intval($_GET['bulan_akhir']) : 0;
$tahun       = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

// 2. Query Daftar Barang untuk Dropdown
$barang_query = mysqli_query($conn, "SELECT * FROM barang ORDER BY nama_barang");
?>

<main class="flex-1 bg-slate-50 min-h-screen p-4 md:p-8">
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 no-print">
    <div>
      <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Laporan Stok Antar Bulan</h1>
      <p class="text-sm text-slate-500 mt-1">Sinkronisasi data Master Barang dengan mutasi bulanan</p>
    </div>
    <button type="button" onclick="window.print()" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold shadow-lg hover:bg-emerald-700 transition-all no-print">
      <span>🖨️</span> Print Laporan
    </button>
  </div>

  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-8 no-print">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase">Bulan Awal</label>
        <select name="bulan_awal" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500" required>
          <?php for ($i=1;$i<=12;$i++): ?>
            <option value="<?= $i ?>" <?= ($bulan_awal==$i)?'selected':''; ?>><?= date('F', mktime(0,0,0,$i,1)) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase">Bulan Akhir</label>
        <select name="bulan_akhir" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500" required>
          <?php for ($i=1;$i<=12;$i++): ?>
            <option value="<?= $i ?>" <?= ($bulan_akhir==$i)?'selected':''; ?>><?= date('F', mktime(0,0,0,$i,1)) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase">Tahun</label>
        <select name="tahun" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">
          <?php for ($t=date('Y'); $t>=2020; $t--): ?>
            <option value="<?= $t ?>" <?= ($tahun==$t)?'selected':''; ?>><?= $t ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase">Produk</label>
        <select name="id_barang" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500" required>
          <option value="">-- Pilih Produk --</option>
          <?php mysqli_data_seek($barang_query, 0); while($b = mysqli_fetch_assoc($barang_query)): ?>
            <option value="<?= $b['id_barang'] ?>" <?= ($id_barang==$b['id_barang'])?'selected':''; ?>>
              <?= htmlspecialchars($b['nama_barang']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="md:col-span-4 flex justify-end">
        <button type="submit" name="preview" class="px-8 py-3 bg-blue-600 text-white rounded-xl text-sm font-bold shadow-lg hover:bg-blue-700 transition-all">
          Tampilkan Laporan
        </button>
      </div>
    </form>
  </div>

  <?php if(isset($_GET['preview']) && $id_barang): ?>
    <?php
        // LOGIKA KRUSIAL: Ambil stok_awal langsung dari tabel barang berdasarkan ID
        $q_master = mysqli_query($conn, "SELECT stok_awal, nama_barang, kode_barang FROM barang WHERE id_barang = $id_barang");
        $d_master = mysqli_fetch_assoc($q_master);
        $stok_master = $d_master['stok_awal'] ?? 0;

        // Query Transaksi
        $sql = "SELECT * FROM transaksi_barang 
                WHERE id_barang = $id_barang 
                AND MONTH(tanggal) >= $bulan_awal 
                AND MONTH(tanggal) <= $bulan_akhir 
                AND YEAR(tanggal) = $tahun 
                ORDER BY tanggal ASC, id_transaksi ASC";
        $res = mysqli_query($conn, $sql);

        $t_masuk = 0; $t_keluar = 0;
        $stok_berjalan = $stok_master; // Stok berjalan dimulai dari STOK AWAL MASTER
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
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Stok Akhir</p>
                    <p id="stat-akhir" class="text-xl font-bold text-slate-800"><?= number_format($stok_master) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
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
    <?php if($id_barang): ?>
    <tr class="bg-slate-50/80 italic font-bold text-slate-600">
        <td class="px-6 py-4 text-center">-</td>
        <td class="px-6 py-4">
            <div class="font-bold text-slate-700"><?= htmlspecialchars($d_master['nama_barang']) ?></div>
            <div class="text-[10px] font-mono text-amber-600 uppercase">Saldo Awal (Master)</div>
        </td>
        <td class="px-6 py-4 text-center">-</td>
        <td class="px-6 py-4 text-center">-</td>
        <td class="px-6 py-4 text-center font-bold text-blue-700 bg-slate-100/80"><?= number_format($stok_master) ?></td>
    </tr>
    <?php endif; ?>

    <?php
    if(mysqli_num_rows($res) > 0){
        while($row = mysqli_fetch_assoc($res)){
            $masuk = strtolower($row['jenis'])=='masuk' ? $row['jumlah'] : 0;
            $keluar = strtolower($row['jenis'])=='keluar' ? $row['jumlah'] : 0;
            $stok_berjalan += $masuk - $keluar;
            $t_masuk += $masuk; 
            $t_keluar += $keluar;
    ?>
    <tr class="hover:bg-slate-50/50 transition-colors">
        <td class="px-6 py-4 text-sm text-slate-600"><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
        <td class="px-6 py-4">
            <div class="font-bold text-slate-700"><?= htmlspecialchars($row['nama_barang']) ?></div>
            <div class="text-[10px] font-mono text-blue-600"><?= htmlspecialchars($row['kode_barang']) ?></div>
        </td>
        <td class="px-6 py-4 text-center font-semibold text-blue-600"><?= $masuk ? '+'.number_format($masuk) : '-' ?></td>
        <td class="px-6 py-4 text-center text-rose-600"><?= $keluar ? '-'.number_format($keluar) : '-' ?></td>
        <td class="px-6 py-4 text-center font-bold text-slate-800 bg-slate-50/30"><?= number_format($stok_berjalan) ?></td>
    </tr>
    <?php } ?>
    <script>
        document.getElementById('stat-masuk').innerText = '<?= number_format($t_masuk) ?>';
        document.getElementById('stat-keluar').innerText = '<?= number_format($t_keluar) ?>';
        document.getElementById('stat-akhir').innerText = '<?= number_format($stok_berjalan) ?>';
    </script>
    <?php } else { ?>
    <tr><td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">Tidak ada transaksi pada rentang bulan ini.</td></tr>
    <?php } ?>
</tbody>
                </table>
            </div>
        </div>
    </div>
  <?php endif; ?>
</main>

<?php include 'templates/footer.php'; ?>