<?php
include 'config/database.php';
include 'templates/header.php';
include 'templates/sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

// Ambil data transaksi dengan Join
$data = mysqli_query($conn, "
    SELECT t.*, b.nama_barang, b.satuan, b.kode_barang 
    FROM transaksi_barang t
    JOIN barang b ON t.id_barang = b.id_barang
    ORDER BY t.tanggal DESC, t.id_transaksi DESC
");

// Hitung Ringkasan untuk Statistik
$total_masuk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) as total FROM transaksi_barang WHERE jenis='masuk'"))['total'] ?? 0;
$total_keluar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) as total FROM transaksi_barang WHERE jenis='keluar'"))['total'] ?? 0;
?>

<main class="flex-1 bg-slate-50 min-h-screen p-4 md:p-8">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Riwayat Transaksi</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau arus masuk dan keluar barang gudang PT MMM</p>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="relative hidden sm:block">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">🔍</span>
                <input type="text" id="searchTransaksi" placeholder="Cari transaksi..." 
                    class="pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white w-64 transition-all">
            </div>

            <button onclick="document.getElementById('modal').classList.remove('hidden')"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-blue-600/20 transition-all flex items-center gap-2 font-semibold text-sm">
                <span class="text-lg">+</span> Transaksi Baru
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl">🔄</div>
            <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Transaksi</p>
                <p class="text-xl font-bold text-slate-800"><?= mysqli_num_rows($data); ?></p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center text-xl">📥</div>
            <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Barang Masuk</p>
                <p class="text-xl font-bold text-slate-800"><?= number_format($total_masuk); ?></p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center text-xl">📤</div>
            <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Barang Keluar</p>
                <p class="text-xl font-bold text-slate-800"><?= number_format($total_keluar); ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left" id="tableTransaksi">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Barang</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Jenis</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Jumlah</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php while($t = mysqli_fetch_assoc($data)) { 
                        $jenis_bersih = trim(strtolower($t['jenis'])); // Pembersihan data
                    ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-slate-600"><?= date('d M Y', strtotime($t['tanggal'])); ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-700"><?= htmlspecialchars($t['nama_barang']); ?></span>
                                <span class="text-[10px] text-slate-400 font-mono uppercase"><?= $t['kode_barang'] ?? 'CODE'; ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if($jenis_bersih == 'masuk'): ?>
                                <span class="px-3 py-1 text-[10px] font-bold uppercase rounded-full bg-green-100 text-green-600">Masuk</span>
                            <?php else: ?>
                                <span class="px-3 py-1 text-[10px] font-bold uppercase rounded-full bg-red-100 text-red-600">Keluar</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold <?= $jenis_bersih == 'masuk' ? 'text-green-600' : 'text-red-600'; ?>">
                                <?= $jenis_bersih == 'masuk' ? '+' : '-'; ?> <?= number_format($t['jumlah']); ?>
                            </span>
                            <span class="text-[10px] text-slate-400 uppercase ml-1"><?= htmlspecialchars($t['satuan']); ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-slate-500 italic truncate max-w-xs">
                                <?= !empty($t['keterangan']) ? htmlspecialchars($t['keterangan']) : '-'; ?>
                            </p>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div id="modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center z-[100] p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="font-bold text-slate-700 text-lg">Input Transaksi Baru</h2>
            <button onclick="document.getElementById('modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        </div>
        
        <form action="transaksi_simpan.php" method="POST" class="p-6 space-y-4">
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase">Pilih Barang</label>
                <select name="id_barang" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                    <option value="">-- Cari Nama Barang --</option>
                    <?php
                    $barang = mysqli_query($conn, "SELECT * FROM barang ORDER BY nama_barang");
                    while($b = mysqli_fetch_assoc($barang)) { ?>
                        <option value="<?= $b['id_barang']; ?>"><?= htmlspecialchars($b['nama_barang']); ?> (Stok: <?= $b['stok_awal']; ?>)</option>
                    <?php } ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">Jenis Transaksi</label>
                    <select name="jenis" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="masuk">📥 Barang Masuk</option>
                        <option value="keluar">📤 Barang Keluar</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">Jumlah</label>
                    <input type="number" name="jumlah" placeholder="0" min="1" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase">Tanggal Transaksi</label>
                <input type="date" name="tanggal" value="<?= date('Y-m-d'); ?>" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase">Keterangan / Tujuan</label>
                <textarea name="keterangan" rows="3" placeholder="Informasi tambahan..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="document.getElementById('modal').classList.add('hidden')"
                    class="px-5 py-2 text-sm font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-all">Batal</button>
                <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-lg shadow-blue-600/20 transition-all">Simpan Transaksi</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Fitur Search Real-time
    document.getElementById('searchTransaksi').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#tableTransaksi tbody tr');

        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    // Tutup modal klik luar
    window.onclick = function(event) {
        let modal = document.getElementById('modal');
        if (event.target == modal) modal.classList.add('hidden');
    }
</script>

<?php include 'templates/footer.php'; ?>