<?php
session_start();
include 'config/database.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

// Menangani Filter Tanggal
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');

include 'templates/header.php';
include 'templates/sidebar.php';

// Query mengambil data barang keluar dengan filter tanggal
$query = "SELECT t.*, b.nama_barang, b.satuan, b.kode_barang, b.kategori 
          FROM transaksi_barang t
          JOIN barang b ON t.id_barang = b.id_barang
          WHERE LOWER(TRIM(t.jenis)) = 'keluar' 
          AND t.tanggal BETWEEN '$tgl_mulai' AND '$tgl_sampai'
          ORDER BY t.tanggal DESC, t.id_transaksi DESC";
          
$result = mysqli_query($conn, $query);
?>

<style>
    /* 1. TAMPILAN UI DI LAYAR (DASHBOARD) */
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
        padding: 0.75rem 0.5rem !important; 
        font-size: 0.75rem; 
        border-bottom: 1px solid #e2e8f0;
    }
    
    .table-proportional td { 
        padding: 0.5rem 0.5rem !important; 
        font-size: 0.813rem; 
        border-bottom: 1px solid #f1f5f9;
        line-height: 1.25;
    }

    /* 2. KHUSUS HASIL CETAK / PRINT (SANGAT RAPAT) */
    @media print {
        /* Sembunyikan elemen non-cetak */
        aside, nav, header, .no-print, form, button { 
            display: none !important; 
        }
        
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
            margin-bottom: 8px; 
            text-align: center; 
        }
        
        /* EKSTREM RAPAT: Border solid & padding minimal */
        table { 
            width: 100%; 
            border-collapse: collapse !important; 
            border: 1px solid #000 !important;
            border-radius: 0 !important;
        }
        
        th, td { 
            border: 1px solid #000 !important; 
            padding: 2px 4px !important; 
            font-size: 8.5pt !important; 
            line-height: 1 !important;
            color: black !important;
            visibility: visible !important;
        }

        /* Matikan rounded & shadow agar garis tidak hilang saat print */
        .rounded-3xl, .rounded-2xl, .overflow-hidden, .bg-white { 
            border-radius: 0 !important; 
            overflow: visible !important; 
            box-shadow: none !important;
            border: none !important;
        }

        /* Penyesuaian jarak tanda tangan */
        .mt-6 { margin-top: 1rem !important; }
        .mb-14 { margin-bottom: 3rem !important; }

        .text-blue-600 { color: black !important; font-weight: bold !important; }
        .bg-blue-50, .bg-slate-100 { background: transparent !important; }
    }
</style>

<main class="flex-1 bg-slate-50 min-h-screen p-4 md:p-8 w-full">
    <div class="container-proportional">
        
        <div class="hidden print-header pb-2">
            <h1 class="text-xl font-bold uppercase leading-tight">Laporan Pengeluaran Barang</h1>
            <p class="text-sm font-bold">PT MUARA MITRA MANDIRI</p>
            <p class="text-xs">Periode: <?= date('d/m/Y', strtotime($tgl_mulai)) ?> s/d <?= date('d/m/Y', strtotime($tgl_sampai)) ?></p>
        </div>

        <div class="no-print flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Barang Keluar</h1>
                <p class="text-slate-500 mt-1 font-medium">Rekapitulasi distribusi pengeluaran gudang</p>
            </div>
            <button onclick="window.print()" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold text-sm shadow-xl shadow-blue-200 transition-all active:scale-95">
                <span>🖨️</span> Cetak Laporan
            </button>
        </div>

        <div class="no-print bg-white p-5 rounded-3xl shadow-sm border border-slate-200/60 mb-6">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[150px] space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Dari</label>
                    <input type="date" name="tgl_mulai" value="<?= $tgl_mulai ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold outline-none focus:border-blue-500 transition-all">
                </div>
                <div class="flex-1 min-w-[150px] space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sampai</label>
                    <input type="date" name="tgl_sampai" value="<?= $tgl_sampai ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold outline-none focus:border-blue-500 transition-all">
                </div>
                <button type="submit" class="px-6 py-2.5 bg-slate-800 text-white rounded-xl text-sm font-bold hover:bg-slate-900 transition-all">Filter</button>
                <div class="flex-[2] min-w-[250px] relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">🔍</span>
                    <input type="text" id="searchInput" placeholder="Cari kode, nama barang, atau kategori..." 
                        class="w-full pl-11 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 bg-white transition-all">
                </div>
            </form>
        </div>

        <div class="bg-white rounded-[1.5rem] shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left table-auto table-proportional" id="reportTable">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="font-bold text-slate-500 uppercase tracking-wider text-center">Tanggal</th>
                            <th class="font-bold text-slate-500 uppercase tracking-wider text-center">Kode Barang</th>
                            <th class="font-bold text-slate-500 uppercase tracking-wider text-center">Nama Barang</th>
                            <th class="font-bold text-slate-500 uppercase tracking-wider text-center">Kategori</th>
                            <th class="font-bold text-slate-500 uppercase tracking-wider text-center">Jumlah</th>
                            <th class="font-bold text-slate-500 uppercase tracking-wider text-center">Keterangan / Tujuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (mysqli_num_rows($result) > 0) : ?>
                            <?php while($row = mysqli_fetch_assoc($result)) : ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="whitespace-nowrap font-bold text-slate-700">
                                    <?= date('d/m/Y', strtotime($row['tanggal'])); ?>
                                </td>
                                <td class="text-center">
                                    <span class="text-[10px] font-mono font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 uppercase">
                                        <?= $row['kode_barang']; ?>
                                    </span>
                                </td>
                                <td class="font-bold text-slate-800">
                                    <?= htmlspecialchars($row['nama_barang']); ?>
                                </td>
                                <td class="text-center">
                                    <span class="category-badge inline-block px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] font-black uppercase">
                                        <?= str_replace(',', '<br>', htmlspecialchars($row['kategori'] ?: 'Umum')); ?>
                                    </span>
                                </td>
                                <td class="text-right whitespace-nowrap">
                                    <span class="font-black text-rose-600">- <?= number_format($row['jumlah']); ?></span>
                                    <span class="text-[9px] text-slate-400 font-bold uppercase"><?= $row['satuan']; ?></span>
                                </td>
                                <td class="text-xs text-slate-500 italic leading-tight">
                                    <?= $row['keterangan'] ?: '-'; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="p-16 text-center text-slate-400 italic font-medium">Data pengeluaran tidak ditemukan pada periode ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="hidden print:block mt-6">
            <div class="flex justify-between items-start px-10">
                <div class="text-center w-56">
                    <p class="text-[9pt] mb-12">Mengetahui,</p>
                    <p class="text-[9pt] font-bold border-t border-black pt-1">Direksi PT MMM</p>
                </div>
                <div class="text-center w-56">
                    <p class="text-[9pt] mb-2 text-right italic">Dicetak: <?= date('d/m/Y') ?></p>
                    <p class="text-[9pt] mb-12">Kord. Workshop</p>
                    <p class="text-[9pt] font-bold border-t border-black pt-1">Nopri Adrian</p>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // Fitur Pencarian Real-time
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#reportTable tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    });
</script>

<?php include 'templates/footer.php'; ?>