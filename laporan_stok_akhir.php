<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

// Ambil tanggal filter, default ke hari ini
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$tgl_db = mysqli_real_escape_string($conn, $tanggal);

// Query ini memastikan setiap barang hanya dihitung satu kali
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

include 'templates/header.php';
include 'templates/sidebar.php';
?>

<style>
    /* Pengaturan Cetak Profesional */
    @media print {
        body * { visibility: hidden; }
        #printableArea, #printableArea * { visibility: visible; }
        #printableArea { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
        table { border: 1px solid #e2e8f0; width: 100%; }
        th { background-color: #f8fafc !important; color: #000 !important; border-bottom: 2px solid #e2e8f0; }
    }
    
    /* Memastikan konten mengisi ruang yang tersedia secara horizontal */
    .main-content-full {
        width: 100%;
        max-width: 100%;
    }
</style>

<main class="flex-1 bg-slate-50 min-h-screen p-4 md:p-8 main-content-full">
    
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8 no-print">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Laporan Stok Akhir</h1>
            <p class="text-sm text-slate-500 mt-1">Rekapitulasi ketersediaan barang di gudang PT MMM secara real-time</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-4">
            <form method="GET" class="flex items-center gap-2 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm focus-within:ring-2 focus-within:ring-blue-500 transition-all">
                <span class="pl-2 text-slate-400 text-xs font-bold uppercase">Periode:</span>
                <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal); ?>"
                    class="bg-transparent border-none text-sm focus:ring-0 px-2 py-1 font-semibold text-slate-700" required>
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-xl text-sm font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">
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
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-6 transition-transform hover:scale-[1.01]">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-3xl">📦</div>
                <div>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-[0.2em] mb-1">Total Jenis Barang</p>
                    <p class="text-3xl font-black text-slate-800"><?= $total_items; ?> <span class="text-base font-medium text-slate-400">Item Terdata</span></p>
                </div>
            </div>
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-6 transition-transform hover:scale-[1.01]">
                <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-3xl">📅</div>
                <div>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-[0.2em] mb-1">Posisi Data Per</p>
                    <p class="text-3xl font-black text-slate-800"><?= date('d F Y', strtotime($tanggal)); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden w-full">
            <div class="hidden print:block text-center border-b-2 border-slate-800 p-10 mb-8">
                <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight">Laporan Stok Akhir</h1>
                <p class="text-lg uppercase mt-2">PT MMM - Pergudangan Terintegrasi</p>
                <p class="text-sm mt-2 italic font-serif">Data Akumulasi Hingga Tanggal: <?= date('d/m/Y', strtotime($tanggal)); ?></p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left table-auto border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100">
                            <th class="px-8 py-6 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center w-24">No</th>
                            <th class="px-8 py-6 text-xs font-extrabold text-slate-500 uppercase tracking-widest">Identitas Barang</th>
                            <th class="px-8 py-6 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-right">Volume Stok</th>
                            <th class="px-8 py-6 text-xs font-extrabold text-slate-500 uppercase tracking-widest">Satuan</th>
                            <th class="px-8 py-6 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center">Status Analisis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php
                        if(mysqli_num_rows($stok_query) > 0){
                            $no = 1;
                            while($row = mysqli_fetch_assoc($stok_query)){
                                $stok = $row['sisa_stok'] ?? 0;
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-all group">
                            <td class="px-8 py-5 text-sm font-medium text-slate-400 text-center"><?= $no++; ?></td>
                            <td class="px-8 py-5">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-bold text-slate-800 group-hover:text-blue-600 transition-colors lowercase first-letter:uppercase"><?= htmlspecialchars($row['nama_barang']); ?></span>
                                    <span class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($row['kode_barang']); ?></span>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-lg font-black text-right <?= $stok <= 5 ? 'text-red-600' : 'text-slate-800' ?>">
                                <?= number_format($stok); ?>
                            </td>
                            <td class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-tighter"><?= htmlspecialchars($row['satuan']); ?></td>
                            <td class="px-8 py-5 text-center">
                                <?php if($stok <= 0): ?>
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase bg-red-100 text-red-700 ring-1 ring-inset ring-red-200">Stok Habis</span>
                                <?php elseif($stok <= 5): ?>
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-700 ring-1 ring-inset ring-amber-200">Stok Tersisa Sedikit</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-700 ring-1 ring-inset ring-emerald-200">Stok Aman</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' class='px-8 py-32 text-center'><div class='flex flex-col items-center gap-2'><span class='text-4xl'>📂</span><p class='text-slate-400 font-medium italic'>Data stok tidak ditemukan untuk periode ini.</p></div></td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="h-12 bg-slate-50/30 border-t border-slate-50"></div>
        </div>

        <div class="mt-16 hidden print:grid grid-cols-2 text-center">
            <div></div>
            <div class="flex flex-col items-center">
                <p class="text-sm text-slate-600 mb-24">Dicetak pada: <?= date('d/m/Y H:i') ?></p>
                <div class="w-48 border-t border-slate-900"></div>
                <p class="font-bold text-slate-800 mt-2">Kepala Gudang PT MMM</p>
            </div>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?>