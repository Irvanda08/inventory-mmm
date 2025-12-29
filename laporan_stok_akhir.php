<?php
session_start();
// Inisialisasi Database & Template
include 'config/database.php';
include 'templates/header.php';
include 'templates/sidebar.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

// 1. Ambil Parameter Filter
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$kategori_filter = $_GET['kategori'] ?? '';

// 2. Query Daftar Kategori Unik (untuk isi dropdown filter)
$list_kategori = mysqli_query($conn, "SELECT DISTINCT kategori FROM barang WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC");

// 3. Query Stok Akhir dengan Perhitungan Agregat & Filter Kategori
$sql = "SELECT b.kode_barang, b.nama_barang, b.satuan, b.kategori,
            SUM(CASE WHEN t.jenis='masuk' THEN t.jumlah ELSE 0 END) -
            SUM(CASE WHEN t.jenis='keluar' THEN t.jumlah ELSE 0 END) AS sisa_stok
        FROM barang b
        LEFT JOIN transaksi_barang t ON b.id_barang = t.id_barang";

// Syarat filter tanggal diletakkan pada JOIN agar stok awal tetap terhitung hingga batas tanggal tersebut
if($tanggal){
    $sql .= " AND t.tanggal <= '".mysqli_real_escape_string($conn, $tanggal)."'";
}

// Filter berdasarkan kategori yang dipilih
if($kategori_filter){
    $sql .= " WHERE b.kategori = '".mysqli_real_escape_string($conn, $kategori_filter)."'";
}

$sql .= " GROUP BY b.id_barang, b.kode_barang, b.nama_barang, b.satuan, b.kategori
          ORDER BY b.nama_barang ASC";

$stok_query = mysqli_query($conn, $sql);
$total_items = mysqli_num_rows($stok_query);
?>

<style>
    /* Pengaturan Cetak Profesional */
    @media print {
        body * { visibility: hidden; }
        #printableArea, #printableArea * { visibility: visible; }
        #printableArea { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
        table { border: 1px solid #cbd5e1; width: 100%; border-collapse: collapse; }
        th { background-color: #f8fafc !important; color: #000 !important; border: 1px solid #cbd5e1 !important; }
        td { border: 1px solid #cbd5e1 !important; }
    }
    
    .main-content-full { width: 100%; max-width: 100%; }
</style>

<main class="flex-1 bg-slate-50 min-h-screen p-4 md:p-8 main-content-full">
    
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8 no-print">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Laporan Stok Akhir</h1>
            <p class="text-sm text-slate-500 mt-1">Rekapitulasi ketersediaan barang di gudang PT MMM</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-4">
            <form method="GET" class="flex flex-wrap items-center gap-3 bg-white p-2.5 rounded-2xl border border-slate-200 shadow-sm focus-within:ring-2 focus-within:ring-blue-500 transition-all">
                <div class="flex items-center gap-2 px-2 border-r border-slate-100">
                    <span class="text-slate-400 text-[10px] font-black uppercase">Periode</span>
                    <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal); ?>"
                        class="bg-transparent border-none text-sm focus:ring-0 px-1 py-1 font-bold text-slate-700" required>
                </div>

                <div class="flex items-center gap-2 px-2">
                    <span class="text-slate-400 text-[10px] font-black uppercase">Kategori</span>
                    <select name="kategori" class="bg-transparent border-none text-sm focus:ring-0 px-1 py-1 font-bold text-slate-700 min-w-[140px]">
                        <option value="">Semua Kategori</option>
                        <?php mysqli_data_seek($list_kategori, 0); while($kat = mysqli_fetch_assoc($list_kategori)): ?>
                            <option value="<?= htmlspecialchars($kat['kategori']); ?>" <?= $kategori_filter == $kat['kategori'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($kat['kategori']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-xl text-sm font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">
                    Tampilkan
                </button>
            </form>

            <button onclick="window.print()" 
                class="bg-emerald-600 text-white px-6 py-2.5 rounded-2xl shadow-lg shadow-emerald-200 hover:bg-emerald-700 transition-all flex items-center gap-2 font-bold text-sm">
                <span>🖨️</span> Cetak Laporan
            </button>
        </div>
    </div>

    <div id="printableArea" class="w-full">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 no-print">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-6">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-3xl">📦</div>
                <div>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mb-1">Total Jenis Barang</p>
                    <p class="text-3xl font-black text-slate-800"><?= $total_items; ?> <span class="text-base font-medium text-slate-400">Item</span></p>
                </div>
            </div>
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-6">
                <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-3xl">📅</div>
                <div>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mb-1">Data Hingga Tanggal</p>
                    <p class="text-3xl font-black text-slate-800"><?= date('d F Y', strtotime($tanggal)); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden w-full">
            <div class="hidden print:block text-center border-b-2 border-slate-800 p-10 mb-8">
                <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight">Laporan Stok Akhir</h1>
                <p class="text-lg uppercase mt-2">PT MMM - Pergudangan Terintegrasi</p>
                <?php if($kategori_filter): ?>
                    <p class="text-md font-bold text-blue-600 mt-2 uppercase tracking-widest">Kategori: <?= htmlspecialchars($kategori_filter); ?></p>
                <?php endif; ?>
                <p class="text-sm mt-2 italic font-serif">Posisi Data: <?= date('d/m/Y', strtotime($tanggal)); ?></p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left table-auto border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100">
                            <th class="px-6 py-6 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center w-16">No</th>
                            <th class="px-6 py-6 text-xs font-extrabold text-slate-500 uppercase tracking-widest w-40">Kode Barang</th>
                            <th class="px-6 py-6 text-xs font-extrabold text-slate-500 uppercase tracking-widest">Nama Barang</th>
                            <th class="px-6 py-6 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center">Kategori</th>
                            <th class="px-6 py-6 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center">Stok</th>
                            <th class="px-6 py-6 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center">Satuan</th>
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
                            <td class="px-6 py-5 text-sm font-medium text-slate-400 text-center"><?= $no++; ?></td>
                            <td class="px-6 py-5">
                                <span class="text-xs font-mono font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-md uppercase tracking-wider">
                                    <?= htmlspecialchars($row['kode_barang']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-sm font-bold text-slate-800 group-hover:text-blue-600 transition-colors capitalize">
                                    <?= htmlspecialchars($row['nama_barang']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-[10px] font-black uppercase tracking-widest">
                                    <?= htmlspecialchars($row['kategori'] ?? 'Umum'); ?>
                                </span>
                            </td>
                            <td class="px-6 py-5 text-lg font-black text-center <?= $stok <= 5 ? 'text-red-600' : 'text-slate-800' ?>">
                                <?= number_format($stok); ?>
                            </td>
                            <td class="px-6 py-5 text-xs font-bold text-slate-400 uppercase text-center"><?= htmlspecialchars($row['satuan']); ?></td>
                        </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' class='px-8 py-32 text-center text-slate-400 font-medium italic'>Data stok tidak ditemukan untuk filter ini.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-16 hidden print:grid grid-cols-2 text-center">
            <div></div>
            <div class="flex flex-col items-center">
                <p class="text-sm text-slate-600 mb-24">Dicetak pada: <?= date('d/m/Y') ?></p>
                <div class="w-48 border-t border-slate-900"></div>
                <p class="font-bold text-slate-800 mt-2">Kepala Gudang PT MMM</p>
            </div>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?>