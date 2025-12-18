function openModal() {
  document.getElementById('modal').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('modal').classList.add('hidden');
}

document.getElementById("search")?.addEventListener("keyup", function () {
  let v = this.value.toLowerCase();
  document.querySelectorAll("#barangTable tr").forEach(r => {
    r.style.display = r.innerText.toLowerCase().includes(v) ? "" : "none";
  });
});

function exitApp() {
  if (confirm("Yakin ingin keluar?")) window.close();
}
