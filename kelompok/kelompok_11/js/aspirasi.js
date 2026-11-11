function renderAspirasi(container) {
  container.innerHTML = `
    <section class="max-w-xl mx-auto p-6 bg-white rounded-xl shadow mb-8">
      <h2 class="text-2xl font-bold mb-4">Ruang Aspirasi Warga</h2>
      <form id="form-aspirasi" class="space-y-4">
        <input name="nama_pengirim" required placeholder="Nama"
          class="w-full border rounded p-2 text-sm">
        <input name="email" type="email" required placeholder="Email"
          class="w-full border rounded p-2 text-sm">
        <textarea name="pesan_aspirasi" required rows="4"
          placeholder="Tulis aspirasimu..."
          class="w-full border rounded p-2 text-sm"></textarea>
        <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded transition">Kirim Aspirasi</button>
        <p id="aspirasi-notif" class="text-sm font-medium mt-2"></p>
      </form>
    </section>

    <!-- Tempat daftar aspirasi nongol -->
    <section class="max-w-xl mx-auto">
        <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Aspirasi Terbaru</h3>
        <div id="aspirasi-list" class="space-y-3"></div>
    </section>
  `;

  async function loadAspirasi() {
    const listContainer = document.getElementById('aspirasi-list');
    if (!listContainer) return;
    
    listContainer.innerHTML = '<p class="text-sm text-gray-500">Memuat aspirasi warga...</p>';
    
    try {
        const res = await fetch('api/aspirasi.php');
        const json = await res.json();
        
        if (json.status === 'success' && json.data.length > 0) {
            let html = '';
            json.data.forEach(item => {
                html += `
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs">
                                ${item.nama_pengirim.charAt(0).toUpperCase()}
                            </div>
                            <span class="font-semibold text-gray-800 text-sm">${item.nama_pengirim}</span>
                        </div>
                        <p class="text-gray-600 text-sm ml-10">${item.pesan_aspirasi}</p>
                    </div>
                `;
            });
            listContainer.innerHTML = html;
        } else {
            listContainer.innerHTML = '<p class="text-sm text-gray-500 italic bg-white p-4 rounded-lg border text-center">Belum ada aspirasi yang masuk. Jadilah yang pertama!</p>';
        }
    } catch (error) {
        listContainer.innerHTML = '<p class="text-sm text-red-500">Gagal memuat daftar aspirasi.</p>';
    }
  }

  loadAspirasi();

  document.getElementById('form-aspirasi').addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = Object.fromEntries(new FormData(e.target));
    const notif = document.getElementById('aspirasi-notif');
    const submitBtn = e.target.querySelector('button');
    
    submitBtn.textContent = 'Mengirim...';
    submitBtn.disabled = true;

    try {
      const res = await fetch('api/aspirasi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
      });
      const json = await res.json();
      
      notif.textContent = json.message;
      notif.className = json.status === 'success' ? 'text-sm font-medium mt-2 text-green-600' : 'text-sm font-medium mt-2 text-red-600';
      
      if (json.status === 'success') {
          e.target.reset();
          loadAspirasi(); 
      }
    } catch {
      notif.textContent = 'Terjadi kesalahan jaringan';
      notif.className = 'text-sm font-medium mt-2 text-red-600';
    } finally {
      submitBtn.textContent = 'Kirim Aspirasi';
      submitBtn.disabled = false;
    }
  });
}