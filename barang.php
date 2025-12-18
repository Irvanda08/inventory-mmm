<?php
include 'config/database.php';
include 'templates/header.php';
include 'templates/sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$data = mysqli_query($conn, "SELECT * FROM barang ORDER BY nama_barang ASC");
?>

<style>
    /* Utility untuk form */
    .input-field { 
        @apply w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all; 
    }
    
    /* Perbaikan: modal-overlay sekarang default display: none agar tidak muncul saat load */
    .modal-overlay { 
        display: none; 
        @apply fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-[100] p-4; 
    }

    /* Class tambahan untuk memunculkan modal via JS */
    .modal-active {
        display: flex !important;
    }
</style>

<main class="flex-1 bg-slate-50 min-h-screen p-4 md:p-8">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Manajemen Inventaris</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola daftar produk dan stok gudang PT MMM</p>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="relative hidden sm:block">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    🔍
                </span>
                <input type="text" id="searchInput" placeholder="Cari nama atau kode barang..." class="pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 bg-white w-64 transition-all">
            </div>

            <button onclick="toggleModal('modal', true)"
                class="bg-cyan-600 hover:bg-cyan-700 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-cyan-600/20 transition-all flex items-center gap-2 font-semibold text-sm">
                <span class="text-lg">+</span> Tambah Barang
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-cyan-100 text-cyan-600 rounded-xl flex items-center justify-center text-xl">📦</div>
            <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Item</p>
                <p class="text-xl font-bold text-slate-800"><?= mysqli_num_rows($data); ?></p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center text-xl">⚠️</div>
            <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Stok Rendah</p>
                <p class="text-xl font-bold text-slate-800">3 Item</p> 
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center text-xl">📅</div>
            <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Update Terakhir</p>
                <p class="text-sm font-bold text-slate-800"><?= date('d M Y'); ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left" id="inventoryTable">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Info Barang</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Kuantitas</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal Masuk</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php 
                    mysqli_data_seek($data, 0); 
                    while ($b = mysqli_fetch_assoc($data)) { 
                    ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-700"><?= htmlspecialchars($b['nama_barang']); ?></span>
                                <span class="text-xs text-cyan-600 font-mono bg-cyan-50 px-2 py-0.5 rounded w-fit mt-1"><?= htmlspecialchars($b['kode_barang']); ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-bold text-slate-800"><?= number_format($b['stok_awal']); ?></span>
                                <span class="text-[10px] font-bold text-slate-400 border border-slate-200 px-1.5 rounded uppercase"><?= htmlspecialchars($b['satuan']); ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-slate-600"><?= date('d M Y', strtotime($b['tanggal_masuk']));?></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500 italic max-w-xs truncate">
                            <?= $b['keterangan'] ? htmlspecialchars($b['keterangan']) : '-'; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center items-center gap-2">
                                <button onclick="openEditModal('<?= $b['id_barang']; ?>','<?= addslashes($b['kode_barang']); ?>','<?= addslashes($b['nama_barang']); ?>','<?= $b['stok_awal']; ?>','<?= addslashes($b['satuan']); ?>','<?= $b['tanggal_masuk']; ?>','<?= addslashes($b['keterangan']); ?>')"
                                    class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">
                                    ✏️
                                </button>
                                <a href="barang_hapus.php?id=<?= $b['id_barang']; ?>" 
                                    onclick="return confirm('Hapus barang ini dari database?')"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                    🗑️
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div id="modal" class="modal-overlay">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="font-bold text-slate-700">Tambah Inventaris Baru</h2>
            <button onclick="toggleModal('modal', false)" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
        </div>
        <form action="barang_simpan.php" method="POST" class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">Kode Barang</label>
                    <input type="text" name="kode_barang" placeholder="Contoh: BRG-001" required class="input-field">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">Nama Barang</label>
                    <input type="text" name="nama_barang" placeholder="Nama Produk" required class="input-field">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">Kuantitas Awal</label>
                    <input type="number" name="stok_awal" placeholder="0" required class="input-field">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">Satuan</label>
                    <input type="text" name="satuan" placeholder="Pcs, Box, dll" required class="input-field">
                </div>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase">Tanggal Masuk</label>
                <input type="date" name="tanggal_masuk" value="<?= date('Y-m-d') ?>" required class="input-field">
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase">Catatan Tambahan</label>
                <textarea name="keterangan" rows="3" placeholder="Informasi detail barang..." class="input-field"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="toggleModal('modal', false)" class="px-5 py-2 text-sm font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-all">Batal</button>
                <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-cyan-600 hover:bg-cyan-700 rounded-xl shadow-lg shadow-cyan-600/20 transition-all">Simpan Barang</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" class="modal-overlay">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="font-bold text-slate-700">Perbarui Data Barang</h2>
            <button onclick="toggleModal('modalEdit', false)" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
        </div>
        <form action="barang_update.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="id_barang" id="edit_id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">Kode Barang</label>
                    <input type="text" name="kode_barang" id="edit_kode" required class="input-field">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">Nama Barang</label>
                    <input type="text" name="nama_barang" id="edit_nama" required class="input-field">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">Stok</label>
                    <input type="number" name="stok_awal" id="edit_stok" required class="input-field">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">Satuan</label>
                    <input type="text" name="satuan" id="edit_satuan" required class="input-field">
                </div>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase">Tanggal Update</label>
                <input type="date" name="tanggal_masuk" id="edit_tanggal" required class="input-field">
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase">Keterangan</label>
                <textarea name="keterangan" id="edit_keterangan" rows="3" class="input-field"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="toggleModal('modalEdit', false)" class="px-5 py-2 text-sm font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-all">Batal</button>
                <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-lg shadow-amber-600/20 transition-all">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
// Fungsi General untuk Modal
function toggleModal(modalId, show) {
    const modal = document.getElementById(modalId);
    if (show) {
        modal.classList.add('modal-active');
    } else {
        modal.classList.remove('modal-active');
    }
}

// Fungsi membuka modal edit (diaktifkan via tombol ✏️)
function openEditModal(id, kode, nama, stok, satuan, tanggal, ket) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_kode').value = kode;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_stok').value = stok;
    document.getElementById('edit_satuan').value = satuan;
    document.getElementById('edit_tanggal').value = tanggal;
    document.getElementById('edit_keterangan').value = ket;
    toggleModal('modalEdit', true);
}

// Fitur Pencarian (Search) Real-time
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#inventoryTable tbody tr');

    rows.forEach(row => {
        // Cek nama dan kode barang di kolom pertama
        let content = row.cells[0].innerText.toLowerCase();
        row.style.display = content.includes(filter) ? "" : "none";
    });
});

// Tutup modal jika user klik di luar kotak putih (di area overlay)
window.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.classList.remove('modal-active');
    }
});
</script>

<?php include 'templates/footer.php'; ?>