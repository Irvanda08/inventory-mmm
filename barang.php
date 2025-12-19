<?php
// Pastikan database di-include terlebih dahulu
include 'config/database.php';

// Cek jika session belum berjalan, baru jalankan session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi halaman: cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

// Ambil data dari database
$data = mysqli_query($conn, "SELECT * FROM barang ORDER BY nama_barang ASC");

// Include template setelah logika session aman
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<style>
    /* Utility untuk form */
    .input-field { 
        @apply w-full bg-white border-2 border-slate-200 rounded-lg px-4 py-2.5 text-slate-900 font-medium focus:outline-none focus:border-cyan-500 transition-all outline-none; 
    }
    
    .modal-overlay { 
        display: none; 
        @apply fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-[100] p-4; 
    }

    .modal-active {
        display: flex !important;
    }

    .fade-in-down {
        animation: fadeInDown 0.2s ease-out;
    }

    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<main class="flex-1 bg-slate-50 min-h-screen p-4 md:p-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Inventaris</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola daftar produk dan stok gudang PT MMM</p>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="relative hidden sm:block">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">🔍</span>
                <input type="text" id="searchInput" placeholder="Cari nama atau kode barang..." 
                       class="pl-10 pr-4 py-2 border-2 border-slate-200 rounded-xl text-sm focus:outline-none focus:border-cyan-500 bg-white w-64 transition-all">
            </div>

            <button onclick="toggleModal('modal', true)"
                class="bg-cyan-600 hover:bg-cyan-700 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-cyan-600/20 transition-all flex items-center gap-2 font-bold text-sm">
                <span class="text-lg">+</span> Tambah Barang
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-cyan-100 text-cyan-600 rounded-xl flex items-center justify-center text-xl">📦</div>
            <div>
                <p class="text-xs text-slate-500 font-black uppercase tracking-widest">Total Item</p>
                <p class="text-2xl font-bold text-slate-900"><?= mysqli_num_rows($data); ?></p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center text-xl">⚠️</div>
            <div>
                <p class="text-xs text-slate-500 font-black uppercase tracking-widest">Stok Rendah</p>
                <p class="text-2xl font-bold text-slate-900">3 Item</p> 
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center text-xl">📅</div>
            <div>
                <p class="text-xs text-slate-500 font-black uppercase tracking-widest">Update Terakhir</p>
                <p class="text-sm font-bold text-slate-900"><?= date('d M Y'); ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left" id="inventoryTable">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-widest">Info Barang</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-widest">Kuantitas</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-widest">Tanggal Masuk</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-widest">Keterangan</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-widest text-center">Aksi</th>
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
                                <span class="font-bold text-slate-900 text-base"><?= htmlspecialchars($b['nama_barang']); ?></span>
                                <span class="text-xs text-cyan-700 font-mono font-bold bg-cyan-50 px-2 py-0.5 rounded w-fit mt-1"><?= htmlspecialchars($b['kode_barang']); ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-bold text-slate-900"><?= number_format($b['stok_awal']); ?></span>
                                <span class="text-[10px] font-black text-slate-500 border-2 border-slate-100 px-1.5 py-0.5 rounded uppercase"><?= htmlspecialchars($b['satuan']); ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-slate-700 font-medium"><?= date('d M Y', strtotime($b['tanggal_masuk']));?></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500 italic max-w-xs truncate">
                            <?= $b['keterangan'] ? htmlspecialchars($b['keterangan']) : '-'; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center items-center gap-2">
                                <button onclick="openEditModal('<?= $b['id_barang']; ?>','<?= addslashes($b['kode_barang']); ?>','<?= addslashes($b['nama_barang']); ?>','<?= $b['stok_awal']; ?>','<?= addslashes($b['satuan']); ?>','<?= $b['tanggal_masuk']; ?>','<?= addslashes($b['keterangan']); ?>')"
                                    class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors">✏️</button>
                                <a href="barang_hapus.php?id=<?= $b['id_barang']; ?>" 
                                    onclick="return confirm('Hapus barang ini?')"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">🗑️</a>
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
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-slate-800 px-6 py-4 flex justify-between items-center">
            <h2 class="font-bold text-white tracking-widest uppercase text-sm">Tambah Inventaris Baru</h2>
            <button onclick="toggleModal('modal', false)" class="text-slate-300 hover:text-white text-2xl">&times;</button>
        </div>

        <form action="barang_simpan.php" method="POST" class="p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest">Kode Barang</label>
                    <input type="text" name="kode_barang" placeholder="BRG-001" required class="input-field">
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest">Nama Barang</label>
                    <input type="text" name="nama_barang" placeholder="Masukkan nama produk" required class="input-field">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest">Kuantitas Awal</label>
                    <input type="number" name="stok_awal" value="0" min="0" required class="input-field font-bold">
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest">Satuan</label>
                    <select id="selectSatuanTambah" name="satuan" onchange="handleSatuan(this, 'manualTambah')" required class="input-field appearance-none">
                        <option value="" disabled selected>Pilih Satuan</option>
                        <option value="Pcs">Pcs</option>
                        <option value="Box">Box</option>
                        <option value="Unit">Unit</option>
                        <option value="Kg">Kg</option>
                        <option value="Liter">Liter</option>
                        <option value="Lainnya">-- Lainnya (Input Manual) --</option>
                    </select>
                    <div id="manualTambah" class="hidden fade-in-down mt-2">
                        <input type="text" placeholder="Ketik satuan manual..." class="w-full bg-cyan-50 border-2 border-cyan-200 rounded-lg px-4 py-2 text-slate-900 font-bold focus:border-cyan-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest">Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" value="<?= date('Y-m-d') ?>" required class="input-field">
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest">Catatan Tambahan</label>
                    <textarea name="keterangan" rows="1" placeholder="Info detail..." class="input-field resize-none"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-slate-100">
                <button type="button" onclick="toggleModal('modal', false)" class="px-6 py-2.5 text-sm font-bold text-slate-400 hover:text-slate-600">BATAL</button>
                <button type="submit" class="px-8 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-black rounded-lg shadow-lg shadow-cyan-200 transition-all">SIMPAN BARANG</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" class="modal-overlay">
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-amber-500 px-6 py-4 flex justify-between items-center">
            <h2 class="font-bold text-white tracking-widest uppercase text-sm">Perbarui Data Barang</h2>
            <button onclick="toggleModal('modalEdit', false)" class="text-white text-2xl">&times;</button>
        </div>

        <form action="barang_update.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="id_barang" id="edit_id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest">Kode Barang</label>
                    <input type="text" name="kode_barang" id="edit_kode" required class="input-field font-bold bg-slate-50 border-amber-100">
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest">Nama Barang</label>
                    <input type="text" name="nama_barang" id="edit_nama" required class="input-field">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest">Stok Saat Ini</label>
                    <input type="number" name="stok_awal" id="edit_stok" required class="input-field font-bold">
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest">Satuan</label>
                    <select id="edit_satuan" name="satuan" onchange="handleSatuan(this, 'manualEdit')" required class="input-field appearance-none">
                        <option value="Pcs">Pcs</option>
                        <option value="Box">Box</option>
                        <option value="Unit">Unit</option>
                        <option value="Kg">Kg</option>
                        <option value="Liter">Liter</option>
                        <option value="Lainnya">-- Lainnya (Input Manual) --</option>
                    </select>
                    <div id="manualEdit" class="hidden fade-in-down mt-2">
                        <input type="text" id="edit_satuan_manual" placeholder="Ketik satuan baru..." class="w-full bg-amber-50 border-2 border-amber-200 rounded-lg px-4 py-2 text-slate-900 font-bold focus:border-amber-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest">Tanggal Update</label>
                    <input type="date" name="tanggal_masuk" id="edit_tanggal" required class="input-field">
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest">Keterangan</label>
                    <textarea name="keterangan" id="edit_keterangan" rows="1" class="input-field resize-none"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-slate-100">
                <button type="button" onclick="toggleModal('modalEdit', false)" class="px-6 py-2.5 text-sm font-bold text-slate-400 hover:text-slate-600 transition-all">BATAL</button>
                <button type="submit" class="px-8 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-black rounded-lg shadow-lg shadow-amber-200 transition-all">SIMPAN PERUBAHAN</button>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle Modal & Reset State
function toggleModal(modalId, show) {
    const modal = document.getElementById(modalId);
    if (show) {
        modal.classList.add('modal-active');
    } else {
        modal.classList.remove('modal-active');
        if(modalId === 'modal') resetManualInput('selectSatuanTambah', 'manualTambah');
        if(modalId === 'modalEdit') resetManualInput('edit_satuan', 'manualEdit');
    }
}

// Logika Satuan Manual (Hybrid Dropdown)
function handleSatuan(selectElem, containerId) {
    const container = document.getElementById(containerId);
    const manualInput = container.querySelector('input');
    
    if (selectElem.value === 'Lainnya') {
        container.classList.remove('hidden');
        manualInput.setAttribute('required', 'true');
        manualInput.setAttribute('name', 'satuan');
        selectElem.removeAttribute('name');
        manualInput.focus();
    } else {
        container.classList.add('hidden');
        manualInput.removeAttribute('required');
        manualInput.removeAttribute('name');
        selectElem.setAttribute('name', 'satuan');
    }
}

function resetManualInput(selectId, containerId) {
    const sel = document.getElementById(selectId);
    const con = document.getElementById(containerId);
    con.classList.add('hidden');
    sel.setAttribute('name', 'satuan');
    con.querySelector('input').removeAttribute('name');
}

// Open Edit Modal & Sinkronisasi Satuan
function openEditModal(id, kode, nama, stok, satuan, tanggal, ket) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_kode').value = kode;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_stok').value = stok;
    document.getElementById('edit_tanggal').value = tanggal;
    document.getElementById('edit_keterangan').value = ket;

    const selectEdit = document.getElementById('edit_satuan');
    const inputManualEdit = document.getElementById('edit_satuan_manual');

    // Cek apakah satuan sudah ada di opsi dropdown
    let exists = false;
    for (let i = 0; i < selectEdit.options.length; i++) {
        if (selectEdit.options[i].value === satuan) {
            exists = true;
            break;
        }
    }

    if (exists) {
        selectEdit.value = satuan;
        handleSatuan(selectEdit, 'manualEdit');
    } else {
        selectEdit.value = 'Lainnya';
        handleSatuan(selectEdit, 'manualEdit');
        inputManualEdit.value = satuan;
    }
    
    toggleModal('modalEdit', true);
}

// Search Function
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#inventoryTable tbody tr');
    rows.forEach(row => {
        let content = row.cells[0].innerText.toLowerCase();
        row.style.display = content.includes(filter) ? "" : "none";
    });
});

// Close on Overlay Click
window.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.classList.remove('modal-active');
    }
});
</script>

<?php include 'templates/footer.php'; ?>