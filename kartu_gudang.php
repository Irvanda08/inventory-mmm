<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
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
$nama_barang = ''; $kode_barang = ''; $satuan = ''; $stok_awal_master = 0;

if ($id_barang) {
    // Ambil data barang dan stok_awal (saldo awal Master)
    $barang_res = mysqli_query($conn, "SELECT nama_barang, kode_barang, satuan, stok_awal FROM barang WHERE id_barang=$id_barang");
    $barang_row = mysqli_fetch_assoc($barang_res);
    $nama_barang = $barang_row['nama_barang'] ?? '';
    $kode_barang = $barang_row['kode_barang'] ?? '';
    $satuan = $barang_row['satuan'] ?? '';
    $stok_awal_master = $barang_row['stok_awal'] ?? 0;

    $sql = "SELECT * FROM transaksi_barang WHERE id_barang=$id_barang";
    if ($tgl_awal) $sql .= " AND tanggal >= '".mysqli_real_escape_string($conn, $tgl_awal)."'";
    if ($tgl_akhir) $sql .= " AND tanggal <= '".mysqli_real_escape_string($conn, $tgl_akhir)."'";
    $sql .= " ORDER BY tanggal ASC, id_transaksi ASC";

    $transaksi_result = mysqli_query($conn, $sql);
    $total_masuk = 0; $total_keluar = 0;
    while ($t = mysqli_fetch_assoc($transaksi_result)) {
        $transaksi[] = $t;
        if(strtolower($t['jenis']) == 'masuk') $total_masuk += $t['jumlah'];
        else $total_keluar += $t['jumlah'];
    }
}
?>

<main class="flex-1 bg-slate-50 min-h-screen p-4 md:p-8">
    <?php if($id_barang): ?>
    <div id="printable" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-xs font-bold uppercase">
                    <th class="p-4 border-b text-left">Tanggal</th>
                    <th class="p-4 border-b text-left">Keterangan</th>
                    <th class="p-4 border-b text-center">Masuk (+)</th>
                    <th class="p-4 border-b text-center">Keluar (-)</th>
                    <th class="p-4 border-b text-center bg-slate-100">Sisa Stok</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-slate-50/50 font-bold italic">
                    <td class="p-4 border-b text-center">-</td>
                    <td class="p-4 border-b">Saldo Awal (Master Barang)</td>
                    <td class="p-4 border-b text-center">-</td>
                    <td class="p-4 border-b text-center">-</td>
                    <td class="p-4 border-b text-center text-blue-600 bg-slate-100"><?= number_format($stok_awal_master); ?></td>
                </tr>

                <?php
                $sisa = $stok_awal_master; 
                foreach($transaksi as $t):
                    $masuk = (strtolower($t['jenis'])=='masuk') ? $t['jumlah'] : 0;
                    $keluar = (strtolower($t['jenis'])=='keluar') ? $t['jumlah'] : 0;
                    $sisa += $masuk - $keluar;
                ?>
                <tr>
                    <td class="p-4 border-b text-xs"><?= date('d/m/Y', strtotime($t['tanggal'])); ?></td>
                    <td class="p-4 border-b"><?= htmlspecialchars($t['keterangan']); ?></td>
                    <td class="p-4 border-b text-center text-emerald-600"><?= $masuk ?: '-' ?></td>
                    <td class="p-4 border-b text-center text-rose-600"><?= $keluar ?: '-' ?></td>
                    <td class="p-4 border-b text-center font-bold bg-slate-50"><?= number_format($sisa) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main>
<?php include 'templates/footer.php'; ?>