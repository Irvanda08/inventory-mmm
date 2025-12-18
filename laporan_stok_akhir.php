<?php
session_start();
include 'config/database.php';
include 'templates/header.php';
include 'templates/sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

// Ambil tanggal filter, default ke hari ini jika tidak ada
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

// Query stok akhir dengan perhitungan agregat
$sql = "SELECT b.kode_barang, b.nama_barang, b.satuan, 
            SUM(CASE WHEN t.jenis='masuk' THEN t.jumlah ELSE 0 END) -
            SUM(CASE WHEN t.jenis='keluar' THEN t.jumlah ELSE 0 END) AS sisa_stok
        FROM barang b
        LEFT JOIN transaksi_barang t ON b.id_barang = t.id_barang";

if($tanggal){
    $sql .= " AND t.tanggal <= '".mysqli_real_escape_string($conn, $tanggal)."'";
}

$sql .= " GROUP BY b.id_barang, b.kode_barang, b.nama_barang, b.satuan
          ORDER BY b.nama_barang ASC";

$stok_query = mysqli_query($conn, $sql);

// Menghitung total item untuk kartu statistik
$total_items = mysqli_num_rows($stok_query);
?>

<style>
    /* Pengaturan Cetak Profesional */
    @media print {
        body * { visibility: hidden; }
        #printableArea, #printableArea * { visibility: visible; }
        #printableArea { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
        table { border: 1px solid #e2e8f0; }
        th { background-color: #f8fafc !important; color: #000 !important; border-bottom: 2px solid #e2e8f0; }
    }
</style>

<main class="flex-1 bg-slate-50 min-h-screen p-4 md:p-8">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 no-print">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Laporan Stok Akhir</h1>
            <p class="text-sm text-slate-500 mt-1">Rekapitulasi ketersediaan barang di gudang PT MMM</p>
        </div>
        
        <div class="flex items-center gap-3">
            <button onclick="window.print()" 
                class="bg-white border border-slate-200 text-slate-700 px-5 py-2.5 rounded-xl shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2 font-semibold text-sm">
                <span>🖨️</span> Cetak Laporan
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-8 max-w-md no-print">
        <form method="GET" class="flex items-end gap-4">
            <div class="flex-1 space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase">Posisi Stok Per Tanggal</label>
                <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal); ?>"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" required>
            </div>
            <button type="submit" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl shadow-lg shadow-blue-600/20 transition-all font-semibold text-sm">
                Filter
            </button>
        </form>
    </div>

    <div id="printableArea">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 no-print">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl">📦</div>
                <div>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Jenis Barang</p>
                    <p class="text-xl font-bold text-slate-800"><?= $total_items; ?> Item</p>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center text-xl">📅</div>
                <div>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Posisi Tanggal</p>
                    <p class="text-xl font-bold text-slate-800"><?= date('d F Y', strtotime($tanggal)); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden p-8">
            <div class="hidden print:block text-center border-b-2 border-slate-800 pb-4 mb-6">
                <h2 class="text-2xl font-bold uppercase">Laporan Stok Akhir Barang</h2>
                <p class="text-sm uppercase">PT MMM - Pergudangan Terintegrasi</p>
                <p class="text-xs mt-1 italic">Per Tanggal: <?= date('d/m/Y', strtotime($tanggal)); ?></p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-16">No</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Barang</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Sisa Stok</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Satuan</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        if(mysqli_num_rows($stok_query) > 0){
                            $no = 1;
                            while($row = mysqli_fetch_assoc($stok_query)){
                                $stok = $row['sisa_stok'] ?? 0;
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-600"><?= $no++; ?></td>
                            <td class="px-6 py-4 text-sm font-mono text-blue-600"><?= htmlspecialchars($row['kode_barang']); ?></td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-700"><?= htmlspecialchars($row['nama_barang']); ?></td>
                            <td class="px-6 py-4 text-sm font-black text-right <?= $stok <= 5 ? 'text-red-600' : 'text-slate-800' ?>">
                                <?= number_format($stok); ?>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-slate-400 uppercase"><?= htmlspecialchars($row['satuan']); ?></td>
                            <td class="px-6 py-4">
                                <?php if($stok <= 0): ?>
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded bg-red-100 text-red-600">Habis</span>
                                <?php elseif($stok <= 5): ?>
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded bg-amber-100 text-amber-600">Menipis</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded bg-green-100 text-green-600">Tersedia</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' class='px-6 py-12 text-center text-slate-400 italic'>Tidak ditemukan data transaksi pada tanggal tersebut</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-12 hidden print:grid grid-cols-2 text-center">
                <div></div>
                <div>
                    <p class="text-sm text-slate-600 mb-20">Dicetak pada: <?= date('d/m/Y H:i') ?></p>
                    <p class="font-bold text-slate-800 border-t border-slate-800 inline-block px-8 pt-2">Admin Gudang</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?>