<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | PT Muara Mitra Mandiri</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
    .slide-fade { animation: fadeEffect 1s; }
    @keyframes fadeEffect {
      from { opacity: 0.4; } 
      to { opacity: 1; }
    }
  </style>
</head>

<body class="bg-[#0f172a] min-h-screen flex items-center justify-center p-4">

<div class="flex flex-col md:flex-row w-full max-w-6xl bg-[#1e293b] rounded-3xl shadow-2xl overflow-hidden min-h-[650px]">
  
  <div class="md:w-1/2 relative flex flex-col items-center justify-center text-center overflow-hidden min-h-[400px]">
    
<div id="slider-container" class="absolute inset-0 z-0">
    <div class="slide h-full w-full">
        <img src="../img/gambar1.png" class="h-full w-full object-cover brightness-[0.4]" alt="Gudang 1">
        <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-transparent to-transparent"></div>
    </div>
    <div class="slide hidden h-full w-full">
        <img src="../img/gambar2.png" class="h-full w-full object-cover brightness-[0.4]" alt="Gudang 2">
        <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-transparent to-transparent"></div>
    </div>
    <div class="slide hidden h-full w-full">
        <img src="../img/gambar3.jpeg" class="h-full w-full object-cover brightness-[0.4]" alt="Gudang 3">
        <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-transparent to-transparent"></div>
    </div>
</div>
    
    <div class="relative z-10 px-12 slide-fade">
        <div class="mb-6 flex justify-center">
            <div class="w-24 h-24 bg-white p-2 rounded-2xl shadow-xl">
                <img src="../img/logoMMM.png" alt="Logo PT MMM" class="w-full h-full object-contain">
            </div>
        </div>
        
        <h2 id="slider-title" class="text-white text-4xl font-bold mb-4 leading-tight">
            Manajemen Efisien
        </h2>
        <p id="slider-desc" class="text-blue-100 mb-8 text-lg opacity-90">
            Sistem pergudangan terintegrasi untuk PT Muara Mitra Mandiri.
        </p>
        
        <div class="mb-10">
            <p class="text-sm text-blue-200 mb-4">Belum punya akun?</p>
            <button id="showRegister" class="px-10 py-3 bg-cyan-500 hover:bg-cyan-400 text-white font-bold rounded-xl transition-all shadow-lg transform hover:scale-105">
                DAFTAR SEKARANG
            </button>
        </div>
    </div>

    <div class="absolute bottom-6 flex space-x-2 z-20">
        <span class="dot h-2 w-2 bg-white/50 rounded-full transition-all"></span>
        <span class="dot h-2 w-2 bg-white/50 rounded-full transition-all"></span>
        <span class="dot h-2 w-2 bg-white/50 rounded-full transition-all"></span>
    </div>
  </div>

  <div class="md:w-1/2 bg-[#111827] p-8 md:p-16 flex flex-col justify-center">
    
    <div class="mb-10">
        <h3 id="formTitle" class="text-white text-3xl font-bold">Sign In</h3>
        <p class="text-gray-400 text-sm mt-2">Silahkan masuk ke akun Anda</p>
        <div class="w-12 h-1 bg-cyan-500 mt-2"></div>
    </div>

    <?php if (isset($_GET['error'])) { ?>
      <div class="bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl text-sm mb-6 flex items-center">
        Username atau password salah!
      </div>
    <?php } ?>

    <form id="loginForm" action="login_proses.php" method="POST" class="space-y-6">
      <div class="space-y-2">
        <label class="text-gray-400 text-sm ml-1">Username</label>
        <input type="text" name="username" required placeholder="Masukkan username"
          class="w-full bg-[#1f2937] border border-gray-700 rounded-xl px-5 py-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
      </div>
      
      <div class="space-y-2">
        <div class="flex justify-between">
            <label class="text-gray-400 text-sm ml-1">Password</label>
            <a href="#" class="text-xs text-cyan-500 hover:underline">Lupa password?</a>
        </div>
        <input type="password" name="password" required placeholder="••••••••"
          class="w-full bg-[#1f2937] border border-gray-700 rounded-xl px-5 py-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
      </div>

      <button type="submit" class="w-full bg-cyan-600 hover:bg-cyan-500 text-white py-3 rounded-xl font-bold text-lg shadow-lg shadow-cyan-900/20 transition-all transform hover:-translate-y-1">
        LOGIN
      </button>
    </form>

    <form id="registerForm" action="register_proses.php" method="POST" class="space-y-5 hidden">
      <input type="text" name="nama" placeholder="Nama Lengkap" required
        class="w-full bg-[#1f2937] border border-gray-700 rounded-xl px-5 py-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
      <input type="text" name="username" placeholder="Username Baru" required
        class="w-full bg-[#1f2937] border border-gray-700 rounded-xl px-5 py-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
      <input type="password" name="password" placeholder="Password" required
        class="w-full bg-[#1f2937] border border-gray-700 rounded-xl px-5 py-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
      <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-bold text-lg transition-all">
        DAFTAR
      </button>
      <button type="button" id="backToLogin" class="w-full text-gray-400 text-sm hover:text-white transition-colors">
        Sudah punya akun? Kembali ke Login
      </button>
    </form>
  </div>
</div>

<script>
// Logic Slider
let currentSlide = 0;
const slides = document.querySelectorAll(".slide");
const dots = document.querySelectorAll(".dot");
const titles = ["Manajemen Efisien", "Keamanan Terjamin", "Laporan Real-time"];
const descs = [
    "Sistem pergudangan terintegrasi untuk PT Muara Mitra Mandiri.",
    "Data inventaris aman dengan sistem enkripsi terbaru.",
    "Pantau stok barang kapanpun dan dimanapun secara akurat."
];

function showSlides() {
    slides.forEach((slide, i) => {
        slide.classList.add("hidden");
        dots[i].classList.replace("bg-cyan-400", "bg-white/50");
        dots[i].classList.remove("w-6");
    });
    
    currentSlide++;
    if (currentSlide > slides.length) {currentSlide = 1}
    
    slides[currentSlide-1].classList.remove("hidden");
    dots[currentSlide-1].classList.replace("bg-white/50", "bg-cyan-400");
    dots[currentSlide-1].classList.add("w-6");
    
    // Update Text
    document.getElementById("slider-title").innerText = titles[currentSlide-1];
    document.getElementById("slider-desc").innerText = descs[currentSlide-1];
    
    setTimeout(showSlides, 4000); // Ganti setiap 4 detik
}
showSlides();

// Logic Toggle Form
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const showRegister = document.getElementById('showRegister');
const backToLogin = document.getElementById('backToLogin');
const formTitle = document.getElementById('formTitle');

showRegister.onclick = () => {
    loginForm.classList.add('hidden');
    registerForm.classList.remove('hidden');
    formTitle.innerText = 'Sign Up';
}

backToLogin.onclick = () => {
    registerForm.classList.add('hidden');
    loginForm.classList.remove('hidden');
    formTitle.innerText = 'Sign In';
}
</script>

</body>
</html>