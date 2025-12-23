<?php
session_start();
// Inisialisasi Database & Template sesuai struktur Anda
include 'config/database.php';
include 'templates/header.php';
include 'templates/sidebar.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

/** * Ambil data dari tabel transaksi_barang di-JOIN dengan tabel barang
 * Difilter hanya jenis 'keluar'
 */
$query = "SELECT t.*, b.nama_barang, b.satuan, b.kode_barang 
          FROM transaksi_barang t
          JOIN barang b ON t.id_barang = b.id_barang
          WHERE LOWER(TRIM(t.jenis)) = 'keluar' 
          ORDER BY t.tanggal DESC, t.id_transaksi DESC";
          
$result = mysqli_query($conn, $query);
?>

<main class="flex-1 bg-slate-50 min-h-screen p-4 md:p-8 w-full">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 w-full no-print">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Laporan Barang Keluar</h1>
            <p class="text-sm text-slate-500 mt-1">Rekapitulasi distribusi barang keluar PT Muara Mitra Mandiri</p>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="relative hidden sm:block">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">🔍</span>
                <input type="text" id="searchInput" placeholder="Cari kode, nama, atau ket..." 
                    class="pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white w-64 md:w-80 transition-all shadow-sm">
            </div>

            <button onclick="window.print()" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-blue-600/20 transition-all flex items-center gap-2 font-semibold text-sm">
                <span>🖨️</span> Cetak Laporan
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden w-full print:border-none print:shadow-none">
        <div class="overflow-x-auto">
            <table class="w-full text-left table-auto" id="reportTable">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 print:bg-slate-100">
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Kode Barang</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Barang</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Jumlah</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Keterangan / Tujuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (mysqli_num_rows($result) > 0) : ?>
                        <?php while($row = mysqli_fetch_assoc($result)) : ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-slate-600"><?= date('d M Y', strtotime($row['tanggal'])); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-mono font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded uppercase tracking-wider">
                                    <?= htmlspecialchars($row['kode_barang']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-slate-700">
                                    <?= htmlspecialchars($row['nama_barang']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex flex-col items-end">
                                    <span class="font-black text-base text-rose-600">
                                        - <?= number_format($row['jumlah']); ?>
                                    </span>
                                    <span class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter"><?= htmlspecialchars($row['satuan']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-slate-500 italic">
                                    <?= !empty($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '<span class="text-slate-300">Tanpa keterangan</span>'; ?>
                                </p>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr id="noDataRow">
                            <td colspan="5" class="p-10 text-center text-slate-400 italic">Data pengeluaran barang tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="hidden print:block mt-20">
        <div class="flex justify-between items-start px-10">
            <div class="text-center">
                <p class="text-xs text-slate-500 mb-20">Mengetahui,</p>
                <p class="text-sm font-bold border-t border-slate-800 pt-1 px-4">Kepala Gudang</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-slate-500 mb-2">Padang, <?= date('d F Y') ?></p>
                <p class="text-xs text-slate-500 mb-16">Petugas Administrasi,</p>
                <p class="text-sm font-bold border-t border-slate-800 pt-1 px-4"><?= $_SESSION['nama'] ?? 'Administrator' ?></p>
            </div>
        </div>
    </div>
</main>

<style>
    /* Mengatur Layout agar rapi saat dicetak */
    @media print {
        aside, #sidebarToggle, nav, header, .no-print, #modal { 
            display: none !important; 
        }
        main { 
            margin-left: 0 !important; 
            padding: 0 !important; 
            background: white !important;
            width: 100% !important;
        }
        body { background: white !important; }
        table { border: 1px solid #e2e8f0; width: 100%; }
        th { background-color: #f8fafc !important; color: #475569 !important; }
    }
</style>

<script>
    /**
     * Logika Pencarian Real-time (Auto Update)
     */
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const table = document.getElementById('reportTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            // Abaikan baris "Data tidak ditemukan" jika ada
            if (rows[i].id === 'noDataRow') continue;

            let visible = false;
            const cells = rows[i].getElementsByTagName('td');
            
            for (let j = 0; j < cells.length; j++) {
                if (cells[j]) {
                    const text = cells[j].textContent || cells[j].innerText;
                    if (text.toLowerCase().indexOf(filter) > -1) {
                        visible = true;
                        break;
                    }
                }
            }
            rows[i].style.display = visible ? "" : "none";
        }
    });
</script>

<?php include 'templates/footer.php'; ?>