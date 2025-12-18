<?php
session_start();
include 'config/database.php';
include 'templates/header.php';
include 'templates/sidebar.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

// Statistik
$totalBarang = mysqli_fetch_assoc(
  mysqli_query($conn, "SELECT COUNT(*) AS total FROM barang")
)['total'];

$totalMasuk = mysqli_fetch_assoc(
  mysqli_query($conn, "SELECT IFNULL(SUM(jumlah),0) AS total FROM transaksi_barang WHERE UPPER(jenis)='MASUK'")
)['total'];

$totalKeluar = mysqli_fetch_assoc(
  mysqli_query($conn, "SELECT IFNULL(SUM(jumlah),0) AS total FROM transaksi_barang WHERE UPPER(jenis)='KELUAR'")
)['total'];

// Transaksi terbaru
$transaksi = mysqli_query($conn, "
  SELECT t.*, b.nama_barang, b.satuan
  FROM transaksi_barang t
  JOIN barang b ON b.id_barang = t.id_barang
  ORDER BY t.tanggal DESC, t.id_transaksi DESC
  LIMIT 10
");
?>

<main class="flex-1 p-6 bg-gray-100 min-h-screen">

<h1 class="text-3xl font-bold mb-6 text-gray-800">📊 Dashboard Gudang</h1>

<!-- INFO BOX -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

  <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
    <div class="bg-blue-100 p-3 rounded-full">
      <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18"/>
      </svg>
    </div>
    <div>
      <p class="text-gray-500 text-sm">Total Jenis Barang</p>
      <h2 class="text-2xl font-bold text-blue-600"><?= $totalBarang; ?></h2>
    </div>
  </div>

  <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
    <div class="bg-green-100 p-3 rounded-full">
      <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 6h18M3 14h18M3 18h18"/>
      </svg>
    </div>
    <div>
      <p class="text-gray-500 text-sm">Total Barang Masuk</p>
      <h2 class="text-2xl font-bold text-green-600"><?= $totalMasuk; ?></h2>
    </div>
  </div>

  <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
    <div class="bg-red-100 p-3 rounded-full">
      <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M3 16h18M3 8h18"/>
      </svg>
    </div>
    <div>
      <p class="text-gray-500 text-sm">Total Barang Keluar</p>
      <h2 class="text-2xl font-bold text-red-600"><?= $totalKeluar; ?></h2>
    </div>
  </div>

</div>

<!-- TRANSAKSI TERBARU -->
<div class="bg-white rounded-xl shadow overflow-hidden">
  <div class="p-4 border-b font-semibold bg-gray-50">
    Transaksi Terbaru
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
      <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
        <tr>
          <th class="px-4 py-2 text-left">Tanggal</th>
          <th class="px-4 py-2 text-left">Barang</th>
          <th class="px-4 py-2 text-center">Jenis</th>
          <th class="px-4 py-2 text-center">Jumlah</th>
          <th class="px-4 py-2 text-left">Keterangan</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php if(mysqli_num_rows($transaksi) > 0): ?>
          <?php while ($t = mysqli_fetch_assoc($transaksi)): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-2"><?= date('d-m-Y', strtotime($t['tanggal'])); ?></td>
            <td class="px-4 py-2">
              <strong><?= htmlspecialchars($t['nama_barang']); ?></strong><br>
              <span class="text-xs text-gray-500"><?= htmlspecialchars($t['satuan']); ?></span>
            </td>
            <td class="px-4 py-2 text-center">
              <span class="px-2 py-1 rounded text-white <?= strtoupper($t['jenis'])=='MASUK' ? 'bg-green-600' : 'bg-red-600'; ?>">
                <?= strtoupper($t['jenis']); ?>
              </span>
            </td>
            <td class="px-4 py-2 text-center"><?= $t['jumlah']; ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($t['keterangan']); ?></td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="px-4 py-4 text-center text-gray-500">Tidak ada transaksi</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</main>

<?php include 'templates/footer.php'; ?>
