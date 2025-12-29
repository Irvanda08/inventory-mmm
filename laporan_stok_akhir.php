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

// File: laporan_stok_akhir.php

// Query dengan filter tanggal yang benar
$sql = "SELECT b.kode_barang, b.nama_barang, b.satuan, b.kategori,
            SUM(CASE WHEN t.jenis='masuk' THEN t.jumlah ELSE 0 END) -
            SUM(CASE WHEN t.jenis='keluar' THEN t.jumlah ELSE 0 END) AS sisa_stok
        FROM barang b
        LEFT JOIN transaksi_barang t ON b.id_barang = t.id_barang";

// Syarat filter tanggal: Menghitung transaksi HANYA sampai tanggal tersebut
if($tanggal){
    $sql .= " AND t.tanggal <= '".mysqli_real_escape_string($conn, $tanggal)."'";
}

// Filter kategori jika dipilih
if($kategori_filter){
    $sql .= " WHERE b.kategori = '".mysqli_real_escape_string($conn, $kategori_filter)."'";
}

$sql .= " GROUP BY b.id_barang, b.kode_barang, b.nama_barang, b.satuan, b.kategori
          ORDER BY b.nama_barang ASC";

$stok_query = mysqli_query($conn, $sql);
$total_items = mysqli_num_rows($stok_query);
?>

<style>
    /* KONFIGURASI TAMPILAN LAYAR */
    .container-proportional {
        max-width: 1440px;
        margin: 0 auto;
    }

    .table-proportional {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    .table-proportional th { 
        padding: 1rem 0.75rem !important; 
        font-size: 0.75rem; 
        border-bottom: 1px solid #e2e8f0;
    }
    
    .table-proportional td { 
        padding: 0.75rem 0.75rem !important; 
        font-size: 0.875rem; 
        border-bottom: 1px solid #f1f5f9;
    }

    /* KONFIGURASI KHUSUS CETAK (PRINT) - DIOPTIMALKAN */
    @media print {
        aside, nav, header, .no-print, form, button { display: none !important; }
        
        main { 
            margin: 0 !important; 
            padding: 0 !important; 
            width: 100% !important; 
            background: white !important; 
        }
        
        #printableArea { 
            visibility: visible; 
            width: 100%; 
            position: absolute; 
            left: 0; 
            top: 0; 
        }
        
        .print-header { 
            display: block !important; 
            border-bottom: 2px solid #000; 
            margin-bottom: 10px; 
            text-align: center; 
        }
        
        /* EKSTREM RAPAT: Paksa border solid dan hilangkan padding berlebih */
        table { 
            width: 100%; 
            border-collapse: collapse !important; 
            border: 1px solid #000 !important;
            border-radius: 0 !important;
        }
        
        th, td { 
            border: 1px solid #000 !important; 
            padding: 2px 4px !important; /* Jarak baris sangat rapat */
            font-size: 9pt !important; 
            line-height: 1 !important; /* Menghilangkan spasi antar baris teks */
            color: black !important;
            visibility: visible !important;
        }

        /* Hilangkan elemen dekoratif agar hemat tinta dan space */
        .rounded-3xl, .rounded-2xl, .overflow-hidden, .bg-white { 
            border-radius: 0 !important; 
            overflow: visible !important; 
            box-shadow: none !important;
            border: none !important;
        }

        .mt-16 { margin-top: 1.5rem !important; } /* Perkecil jarak tanda tangan */
        .mb-20 { margin-bottom: 3rem !important; } /* Perkecil area tanda tangan */

        .text-blue-600 { color: black !important; font-weight: bold !important; }
        .bg-blue-50, .bg-slate-100 { background: transparent !important; }
    }
</style>

<main class="flex-1 bg-slate-50 min-h-screen p-4 md:p-8 w-full">
    <div class="container-proportional">
        
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8 no-print">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Stok Akhir</h1>
                <p class="text-sm text-slate-500 mt-1">Monitoring ketersediaan barang PT MMM</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <form method="GET" class="flex flex-wrap items-center gap-3 bg-white p-2.5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-2 px-2 border-r border-slate-100">
                        <span class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Hingga</span>
                        <input type="date" name="tanggal" value="<?= $tanggal; ?>" class="border-none text-sm font-bold text-slate-700 focus:ring-0">
                    </div>
                    <div class="flex items-center gap-2 px-2">
                        <span class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Kategori</span>
                        <select name="kategori" class="border-none text-sm font-bold text-slate-700 focus:ring-0">
                            <option value="">Semua</option>
                            <?php mysqli_data_seek($list_kategori, 0); while($kat = mysqli_fetch_assoc($list_kategori)): ?>
                                <option value="<?= $kat['kategori']; ?>" <?= ($kategori_filter == $kat['kategori']) ? 'selected' : ''; ?>><?= $kat['kategori']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="bg-slate-800 text-white px-5 py-2 rounded-xl text-sm font-bold hover:bg-black transition-all">Filter</button>
                </form>

                <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2.5 rounded-2xl shadow-lg hover:bg-blue-700 transition-all font-bold text-sm">
                    🖨️ Cetak
                </button>
            </div>
        </div>

        <div id="printableArea">
            <div class="hidden print-header pb-2">
                <h1 class="text-xl font-bold uppercase">Laporan Stok Akhir Barang</h1>
                <p class="text-sm font-bold uppercase">PT MUARA MITRA MANDIRI</p>
                <p class="text-xs">Posisi Data Hingga: <?= date('d/m/Y', strtotime($tanggal)); ?></p>
            </div>

            <div class="bg-white rounded-[1.5rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table-proportional" id="stokTable">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="text-center w-12 font-bold text-slate-500 uppercase">NO</th>
                                <th class="text-left w-32 font-bold text-slate-500 uppercase">KODE</th>
                                <th class="text-left font-bold text-slate-500 uppercase">NAMA BARANG</th>
                                <th class="text-center w-40 font-bold text-slate-500 uppercase">KATEGORI</th>
                                <th class="text-center w-32 font-bold text-slate-500 uppercase">SISA STOK</th>
                                <th class="text-center w-24 font-bold text-slate-500 uppercase">SATUAN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php 
                            $no = 1;
                            if(mysqli_num_rows($stok_query) > 0):
                                while($row = mysqli_fetch_assoc($stok_query)): 
                                    $stok = $row['sisa_stok'];
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="text-center text-slate-400 font-medium"><?= $no++; ?></td>
                                <td class="font-mono font-bold text-blue-600 uppercase"><?= $row['kode_barang']; ?></td>
                                <td class="font-bold text-slate-800 uppercase"><?= htmlspecialchars($row['nama_barang']); ?></td>
                                <td class="text-center text-slate-500 uppercase text-[10px] font-black">
                                    <?= htmlspecialchars($row['kategori'] ?: 'Umum'); ?>
                                </td>
                                <td class="text-center font-black text-lg <?= $stok <= 5 ? 'text-red-600' : 'text-slate-800' ?>">
                                    <?= number_format($stok); ?>
                                </td>
                                <td class="text-center text-[10px] font-bold text-slate-400 uppercase"><?= htmlspecialchars($row['satuan']); ?></td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="6" class="p-20 text-center text-slate-400 italic">Data stok tidak tersedia.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-16 hidden print:grid grid-cols-2">
                <div></div> <div class="flex flex-col items-center ml-auto w-64 text-center">
                    <p class="text-sm text-slate-600 mb-20">
                        Yogyakarta, <?= date('d F Y') ?>
                    </p>
                    <div class="w-full border-t border-black mt-1"></div>
                    <p class="font-bold text-slate-800 tracking-wide text-xs mt-1">
                        Kord. Workshop PT MMM
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?>