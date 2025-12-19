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

// Ambil tanggal filter, default ke hari ini
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$tgl_db = mysqli_real_escape_string($conn, $tanggal);

/**
 * PERBAIKAN LOGIKA:
 * Menggunakan Subquery (t) untuk menghitung transaksi secara terpisah.
 * Ini mencegah b.stok_awal terduplikasi saat JOIN.
 */
$sql = "SELECT 
            b.kode_barang, 
            b.nama_barang, 
            b.satuan, 
            b.stok_awal,
            (b.stok_awal + IFNULL(t.total_masuk, 0) - IFNULL(t.total_keluar, 0)) AS sisa_stok
        FROM barang b
        LEFT JOIN (
            SELECT 
                id_barang,
                SUM(CASE WHEN jenis = 'masuk' THEN jumlah ELSE 0 END) AS total_masuk,
                SUM(CASE WHEN jenis = 'keluar' THEN jumlah ELSE 0 END) AS total_keluar
            FROM transaksi_barang
            WHERE tanggal <= '$tgl_db'
            GROUP BY id_barang
        ) t ON b.id_barang = t.id_barang
        ORDER BY b.nama_barang ASC";

$stok_query = mysqli_query($conn, $sql);
$total_items = mysqli_num_rows($stok_query);
?>

<style>
    @media print {
        body * { visibility: hidden; }
        #printableArea, #printableArea * { visibility: visible; }
        #printableArea { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
    }
    .main-content-full { width: 100%; max-width: 100%; }
</style>

<main class="flex-1 bg-slate-50 min-h-screen p-4 md:p-8 main-content-full">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8 no-print">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Laporan Stok Akhir</h1>
            <p class="text-sm text-slate-500 mt-1">Rekapitulasi ketersediaan barang di gudang PT MMM secara real-time</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-4">
            <form method="GET" class="flex items-center gap-2 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm transition-all">
                <span class="pl-2 text-slate-400 text-xs font-bold uppercase">Periode:</span>
                <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal); ?>"
                    class="bg-transparent border-none text-sm focus:ring-0 px-2 py-1 font-semibold text-slate-700" required>
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-xl text-sm font-bold hover:bg-blue-700 transition-all">
                    Terapkan
                </button>
            </form>

            <button onclick="window.print()" 
                class="bg-white border border-slate-200 text-slate-700 px-6 py-2.5 rounded-2xl shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2 font-bold text-sm">
                <span>🖨️</span> Cetak PDF
            </button>
        </div>
    </div>

    <div id="printableArea" class="w-full">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 no-print">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-6">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-3xl">📦</div>
                <div>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-[0.2em] mb-1">Total Jenis Barang</p>
                    <p class="text-3xl font-black text-slate-800"><?= $total_items; ?> <span class="text-base font-medium text-slate-400">Item</span></p>
                </div>
            </div>
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-6">
                <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-3xl">📅</div>
                <div>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-[0.2em] mb-1">Posisi Data Per</p>
                    <p class="text-3xl font-black text-slate-800"><?= date('d F Y', strtotime($tanggal)); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden w-full">
            <div class="overflow-x-auto">
                <table class="w-full text-left table-auto border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100">
                            <th class="px-8 py-6 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center w-24">No</th>
                            <th class="px-8 py-6 text-xs font-extrabold text-slate-500 uppercase tracking-widest">Identitas Barang</th>
                            <th class="px-8 py-6 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-right">Volume Stok</th>
                            <th class="px-8 py-6 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center">Satuan</th>
                            <th class="px-8 py-6 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php
                        $no = 1;
                        while($row = mysqli_fetch_assoc($stok_query)){
                            $stok = $row['sisa_stok'] ?? 0;
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-all group">
                            <td class="px-8 py-5 text-sm font-medium text-slate-400 text-center"><?= $no++; ?></td>
                            <td class="px-8 py-5">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-bold text-slate-800"><?= htmlspecialchars($row['nama_barang']); ?></span>
                                    <span class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($row['kode_barang']); ?></span>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-lg font-black text-right <?= $stok <= 5 ? 'text-red-600' : 'text-slate-800' ?>">
                                <?= number_format($stok); ?>
                            </td>
                            <td class="px-8 py-5 text-center text-xs font-bold text-slate-400 uppercase"><?= htmlspecialchars($row['satuan']); ?></td>
                            <td class="px-8 py-5 text-center">
                                <?php if($stok <= 0): ?>
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase bg-red-100 text-red-700 ring-1 ring-red-200">Habis</span>
                                <?php elseif($stok <= 5): ?>
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-700 ring-1 ring-amber-200">Tipis</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200">Aman</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<?php include 'templates/footer.php'; ?>