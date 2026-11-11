(function () {
  'use strict';

const PETISI_API = 'api/petisi.php';
const PETISI_STORAGE_KEY = 'civilens_petisi_didukung';

function escapeHtml(teks) {
  const el = document.createElement('div');
  el.textContent = teks ?? '';
  return el.innerHTML;
}

function formatTanggal(str) {
  const d = new Date(String(str).replace(' ', 'T'));
  if (isNaN(d)) return '';
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function ambilDidukung() {
  try {
    return JSON.parse(localStorage.getItem(PETISI_STORAGE_KEY)) || [];
  } catch {
    return [];
  }
}

function simpanDidukung(id) {
  try {
    const daftar = ambilDidukung();
    if (!daftar.includes(id)) {
      daftar.push(id);
      localStorage.setItem(PETISI_STORAGE_KEY, JSON.stringify(daftar));
    }
  } catch  {}
}

function kartuPetisi(p) {
  const persen = p.target_tanda_tangan > 0
    ? Math.min(100, Math.round((p.current_tanda_tangan / p.target_tanda_tangan) * 100))
    : 0;
  const sudah = ambilDidukung().includes(p.id);

  return `
    <article class="bg-white rounded-xl shadow-md p-6 flex flex-col gap-4" data-petisi-id="${p.id}">
      <header>
        <h3 class="text-xl font-bold text-gray-800">${escapeHtml(p.judul)}</h3>
        <time class="text-xs text-gray-500">${escapeHtml(formatTanggal(p.created_at))}</time>
      </header>

      <p class="text-gray-600 text-sm leading-relaxed">${escapeHtml(p.deskripsi)}</p>

      <div>
        <div class="flex justify-between text-sm mb-1">
          <span class="font-semibold text-gray-700">
            <span data-role="jumlah">${p.current_tanda_tangan.toLocaleString('id-ID')}</span>
            / ${p.target_tanda_tangan.toLocaleString('id-ID')} tanda tangan
          </span>
          <span class="text-gray-500" data-role="persen">${persen}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
          <div class="bg-blue-600 h-3 rounded-full transition-all duration-500"
               data-role="bar" style="width:${persen}%"></div>
        </div>
      </div>

      <button type="button" data-aksi="dukung" data-id="${p.id}"
        class="mt-auto px-4 py-2 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700
               disabled:bg-gray-400 disabled:cursor-not-allowed transition"
        ${sudah ? 'disabled' : ''}>
        ${sudah ? '✓ Sudah Anda dukung' : 'Dukung Petisi Ini'}
      </button>
    </article>`;
}

async function renderPetisi(target) {
  const container = typeof target === 'string' ? document.querySelector(target) : target;
  if (!container) return;

  container.innerHTML = `
    <section>
      <h2 class="text-3xl font-bold text-gray-800 mb-2">Petisi Warga</h2>
      <p class="text-gray-600 mb-6">Dukung isu yang menurut Anda penting untuk diperjuangkan.</p>
      <div id="petisi-status" class="text-center text-gray-500 py-8">Memuat petisi...</div>
      <div id="petisi-list" class="grid gap-6 md:grid-cols-2"></div>
    </section>`;

  const status = container.querySelector('#petisi-status');
  const list = container.querySelector('#petisi-list');

  try {
    const res = await fetch(PETISI_API);
    const json = await res.json();
    if (!res.ok || !json.success) throw new Error(json.message || 'Gagal memuat petisi.');

    if (json.data.length === 0) {
      status.textContent = 'Belum ada petisi.';
      return;
    }

    status.remove();
    list.innerHTML = json.data.map(kartuPetisi).join('');
    list.addEventListener('click', (e) => tanganiKlikDukung(e, list));
  } catch (err) {
    status.className = 'text-center text-red-600 py-8';
    status.textContent = err.message || 'Tidak dapat terhubung ke server.';
  }
}

async function tanganiKlikDukung(e, list) {
  const btn = e.target.closest('[data-aksi="dukung"]');
  if (!btn || btn.disabled) return;

  const id = Number(btn.dataset.id);
  const teksAwal = btn.textContent;
  btn.disabled = true;
  btn.textContent = 'Memproses...';

  try {
    const res = await fetch(PETISI_API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const json = await res.json();
    if (!res.ok || !json.success) throw new Error(json.message || 'Gagal mendukung petisi.');

    const p = json.data;
    const kartu = list.querySelector(`[data-petisi-id="${id}"]`);
    const persen = p.target_tanda_tangan > 0
      ? Math.min(100, Math.round((p.current_tanda_tangan / p.target_tanda_tangan) * 100))
      : 0;
    kartu.querySelector('[data-role="jumlah"]').textContent = p.current_tanda_tangan.toLocaleString('id-ID');
    kartu.querySelector('[data-role="persen"]').textContent = persen + '%';
    kartu.querySelector('[data-role="bar"]').style.width = persen + '%';

    simpanDidukung(id);
    btn.textContent = '✓ Terima kasih, sudah didukung';
  } catch (err) {
    alert(err.message || 'Tidak dapat terhubung ke server.');
    btn.disabled = false;
    btn.textContent = teksAwal;
  }
}

  window.renderPetisi = renderPetisi;
})();