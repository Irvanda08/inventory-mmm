<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrasi Akun | PT Muara Mitra Mandiri</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>

<body class="bg-[#0f172a] min-h-screen flex items-center justify-center p-4">

<div class="flex flex-col md:flex-row-reverse w-full max-w-5xl bg-[#1e293b] rounded-3xl shadow-2xl overflow-hidden min-h-[600px]">
  
  <div class="md:w-1/2 bg-gradient-to-br from-[#0891b2] to-[#0e7490] p-12 flex flex-col items-center justify-center text-center relative overflow-hidden">
    <div class="absolute bottom-0 left-0 w-64 h-64 bg-white rounded-full opacity-5 -ml-20 -mb-20"></div>
    
    <div class="relative z-10">
        <div class="mb-6 flex justify-center">
            <div class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-2xl rotate-45 flex items-center justify-center shadow-lg border border-white/30">
                <span class="text-white text-3xl font-bold -rotate-45">M</span>
            </div>
        </div>
        
        <h2 class="text-white text-4xl font-bold mb-4 leading-tight">
            Bergabung Bersama <br> <span class="text-cyan-200">Tim Pergudangan</span>
        </h2>
        <p class="text-cyan-100 mb-8 text-lg opacity-80">PT Muara Mitra Mandiri</p>
        
        <div class="p-4 border border-cyan-400/30 rounded-2xl bg-cyan-900/20 backdrop-blur-sm">
            <p class="text-xs text-cyan-200 uppercase tracking-widest font-semibold">Keamanan Terjamin</p>
            <p class="text-sm text-white opacity-70 mt-1">Seluruh data inventaris terenkripsi secara aman dalam sistem kami.</p>
        </div>
    </div>
  </div>

  <div class="md:w-1/2 bg-[#111827] p-8 md:p-16 flex flex-col justify-center">
    
    <div class="mb-10">
        <h3 class="text-white text-3xl font-bold">Daftar Akun</h3>
        <p class="text-gray-400 mt-2">Lengkapi data untuk akses sistem</p>
        <div class="w-12 h-1 bg-cyan-500 mt-4"></div>
    </div>

    <form action="register_proses.php" method="POST" class="space-y-5">
      <div class="space-y-2">
        <label class="text-gray-400 text-sm ml-1">Nama Lengkap</label>
        <input type="text" name="nama" required placeholder="Nama sesuai KTP"
          class="w-full bg-[#1f2937] border border-gray-700 rounded-xl px-5 py-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all placeholder:text-gray-600">
      </div>

      <div class="space-y-2">
        <label class="text-gray-400 text-sm ml-1">Username</label>
        <input type="text" name="username" required placeholder="Buat username unik"
          class="w-full bg-[#1f2937] border border-gray-700 rounded-xl px-5 py-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all placeholder:text-gray-600">
      </div>
      
      <div class="space-y-2">
        <label class="text-gray-400 text-sm ml-1">Password</label>
        <input type="password" name="password" required placeholder="Minimal 8 karakter"
          class="w-full bg-[#1f2937] border border-gray-700 rounded-xl px-5 py-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all placeholder:text-gray-600">
      </div>

      <div class="pt-2">
        <button type="submit" class="w-full bg-cyan-600 hover:bg-cyan-500 text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-cyan-900/20 transition-all transform hover:-translate-y-1">
          DAFTAR SEKARANG
        </button>
      </div>

      <p class="text-center text-gray-500 text-sm mt-6">
        Sudah memiliki akun? 
        <a href="login.php" class="text-cyan-500 font-semibold hover:text-cyan-400 transition-colors ml-1">Masuk di sini</a>
      </p>
    </form>

  </div>
</div>

</body>
</html>