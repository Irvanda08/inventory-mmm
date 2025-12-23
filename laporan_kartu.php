<?php
session_start();
include 'config/database.php';
include 'templates/header.php';
include 'templates/sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$barang_query = mysqli_query($conn, "SELECT * FROM barang ORDER BY nama_barang");

$id_barang = isset($_GET['id_barang']) ? intval($_GET['id_barang']) : 0;
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

$transaksi = [];
$nama_barang = '';
$kode_barang = '';
$satuan = '';
$total_masuk = 0;
$total_keluar = 0;

if ($id_barang) {
    $barang_res = mysqli_query($conn, "SELECT nama_barang, kode_barang, satuan FROM barang WHERE id_barang=$id_barang");
    $barang_row = mysqli_fetch_assoc($barang_res);
    $nama_barang = $barang_row['nama_barang'] ?? '';
    $kode_barang = $barang_row['kode_barang'] ?? '';
    $satuan = $barang_row['satuan'] ?? '';

    $sql = "SELECT * FROM transaksi_barang WHERE id_barang=$id_barang";
    if ($tgl_awal) $sql .= " AND tanggal >= '".mysqli_real_escape_string($conn, $tgl_awal)."'";
    if ($tgl_akhir) $sql .= " AND tanggal <= '".mysqli_real_escape_string($conn, $tgl_akhir)."'";
    $sql .= " ORDER BY tanggal ASC, id_transaksi ASC";

    $transaksi_result = mysqli_query($conn, $sql);
    while ($t = mysqli_fetch_assoc($transaksi_result)) {
        $transaksi[] = $t;
        if(strtolower($t['jenis']) == 'masuk') $total_masuk += $t['jumlah'];
        else $total_keluar += $t['jumlah'];
    }
}
?>

<style>
/* Tabel standar untuk tampilan layar */
.table-custom { width: 100%; border-collapse: collapse; }
.table-custom th { @apply bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider p-4 border-b border-slate-100; }
.table-custom td { @apply p-4 border-b border-slate-50 text-sm text-slate-600; }

/* Pengaturan Print */
@media print {
    body * { visibility: hidden; }
    #printable, #printable * { visibility: visible; }
    #printable { position: absolute; top: 0; left: 0; width: 100%; padding: 0; box-shadow: none; }
    .no-print { display: none !important; }
    table { border: 1px solid #e2e8f0; }
    th { background-color: #f8fafc !important; color: #000 !important; border: 1px solid #e2e8f0; }
    td { border: 1px solid #e2e8f0; }
}
</style>

<main class="flex-1 bg-slate-50 min-h-screen p-4 md:p-8">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 no-print">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Kartu Gudang</h1>
            <p class="text-sm text-slate-500 mt-1">Laporan mutasi stok barang secara mendetail</p>
        </div>
        
        <div class="flex gap-2">
            <?php if($id_barang): ?>
            <button onclick="window.print()" class="bg-white border border-slate-200 text-slate-700 px-5 py-2.5 rounded-xl shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2 font-semibold text-sm">
                <span>🖨️</span> Cetak Laporan
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-8 no-print">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-1">
                <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Pilih Barang</label>
                <select name="id_barang" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">-- Cari Barang --</option>
                    <?php mysqli_data_seek($barang_query, 0); ?>
                    <?php while($b = mysqli_fetch_assoc($barang_query)) { ?>
                        <option value="<?= $b['id_barang']; ?>" <?= ($id_barang==$b['id_barang'])?'selected':''; ?>>
                            <?= htmlspecialchars($b['nama_barang']); ?> (<?= htmlspecialchars($b['kode_barang']); ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Tanggal Awal</label>
                <input type="date" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Tanggal Akhir</label>
                <input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl shadow-lg shadow-blue-600/20 transition-all font-semibold text-sm">
                Tampilkan Preview
            </button>
        </form>
    </div>

    <?php if($id_barang): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 no-print">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl">📥</div>
            <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Masuk</p>
                <p class="text-xl font-bold text-slate-800"><?= number_format($total_masuk); ?> <span class="text-xs text-slate-400 font-normal"><?= $satuan ?></span></p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center text-xl">📤</div>
            <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Keluar</p>
                <p class="text-xl font-bold text-slate-800"><?= number_format($total_keluar); ?> <span class="text-xs text-slate-400 font-normal"><?= $satuan ?></span></p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-xl">📦</div>
            <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Stok Akhir</p>
                <?php $stok_akhir = $total_masuk - $total_keluar; ?>
                <p class="text-xl font-bold text-slate-800"><?= number_format($stok_akhir); ?> <span class="text-xs text-slate-400 font-normal"><?= $satuan ?></span></p>
            </div>
        </div>
    </div>

    <div id="printable" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <div class="flex justify-between items-start border-b-2 border-slate-800 pb-6 mb-8">
            <div>
                <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tighter">Kartu Gudang</h2>
                <p class="text-sm text-slate-500">PT MMM - Manajemen Inventaris Terpadu</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($nama_barang); ?></p>
                <p class="text-xs text-slate-500"><?= htmlspecialchars($kode_barang); ?></p>
                <p class="text-xs text-slate-500 mt-2 italic">Periode: <?= $tgl_awal ?: 'Awal' ?> — <?= $tgl_akhir ?: date('d/m/Y') ?></p>
            </div>
        </div>

        <table class="table-custom">
            <thead>
                <tr>
                    <th class="text-left w-32">Tanggal</th>
                    <th class="text-left">Keterangan / Referensi</th>
                    <th class="text-center w-28">Masuk (+)</th>
                    <th class="text-center w-28">Keluar (-)</th>
                    <th class="text-center w-28 bg-slate-100">Sisa Stok</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-slate-50/50 font-bold">
                    <td class="text-slate-800">-</td>
                    <td class="text-slate-800">-</td>
                    <td class="text-center">-</td>
                    <td class="text-center">-</td>
                    <td class="text-center text-blue-600 bg-slate-100">0</td>
                </tr>

                <?php
                $sisa = 0;
                if(count($transaksi) > 0):
                    foreach($transaksi as $t):
                        $jenis = strtolower($t['jenis']);
                        $masuk = ($jenis=='masuk') ? $t['jumlah'] : 0;
                        $keluar = ($jenis=='keluar') ? $t['jumlah'] : 0;
                        $sisa += $masuk - $keluar;
                ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="font-mono text-xs"><?= date('d/m/Y', strtotime($t['tanggal'])); ?></td>
                    <td><?= htmlspecialchars($t['keterangan']); ?></td>
                    <td class="text-center text-emerald-600 font-semibold"><?= $masuk ?: '-' ?></td>
                    <td class="text-center text-rose-600 font-semibold"><?= $keluar ?: '-' ?></td>
                    <td class="text-center font-bold text-slate-800 bg-slate-50/50"><?= number_format($sisa) ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="5" class="text-center py-12 text-slate-400">Tidak ada riwayat transaksi pada periode ini.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="mt-12 hidden print:grid grid-cols-2 text-center">
            <div></div>
            <div>
                <p class="text-sm text-slate-600 mb-20">Dicetak pada: <?= date('d/m/Y H:i') ?></p>
                <p class="font-bold text-slate-800 border-t border-slate-800 inline-block px-8 pt-2">Kepala Gudang / Admin</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

</main>

<?php include 'templates/footer.php'; ?>