let currentKeyword = '';
let currentKategori = '';
let currentJenis = '';
let currentPage = 1;
let debounceTimer = null;

function esc(str) {
    if (str === null || str === undefined) {
        return '';
    }
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function badgeStatus(status) {
    const s = String(status || '').toLowerCase().trim();
    let badgeClass = 'badge-menunggu';
    let label = 'Menunggu';

    if (s === 'diproses') {
        badgeClass = 'badge-diproses';
        label = 'Diproses';
    } else if (s === 'selesai') {
        badgeClass = 'badge-selesai';
        label = 'Selesai';
    }

    return `<span class="badge ${badgeClass}">${esc(label)}</span>`;
}

function formatTanggal(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return esc(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

function loadLaporan(page = 1) {
    currentPage = page;
    const container = document.getElementById('laporan-container');
    if (!container) return;

    container.innerHTML = `
        <div style="text-align: center; padding: 2.5rem 1rem; color: var(--color-text-muted);">
            <em>Memuat data dari server...</em>
        </div>
    `;

    const params = new URLSearchParams();
    if (currentKeyword) params.append('search', currentKeyword);
    if (currentKategori) params.append('kategori', currentKategori);
    if (currentJenis) params.append('jenis', currentJenis);
    params.append('page', currentPage);

    const url = `/api/laporan.php?${params.toString()}`;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Respons jaringan tidak berhasil');
            }
            return response.json();
        })
        .then(res => {
            if (res.status === 'success') {
                renderLaporan(res.data, res.pagination);
                renderPagination(res.pagination);
            } else {
                showError(res.message || 'Gagal memuat data laporan.');
            }
        })
        .catch(err => {
            console.error('Error saat fetch laporan:', err);
            showError('Gagal memuat data dari server. Pastikan koneksi server aktif.');
        });
}

function renderLaporan(laporanList, pagination) {
    const container = document.getElementById('laporan-container');
    const statusInfo = document.getElementById('search-info');
    if (!container) return;

    if (statusInfo) {
        const total = pagination && pagination.total_data !== undefined ? pagination.total_data : (laporanList ? laporanList.length : 0);
        let infoText = `Menampilkan ${total} laporan publik`;
        if (currentKeyword) {
            infoText = `Menampilkan hasil pencarian "${currentKeyword}" (${total} laporan ditemukan)`;
        }
        if (pagination && pagination.total_pages > 1) {
            infoText += ` • Halaman ${pagination.current_page} dari ${pagination.total_pages}`;
        }
        statusInfo.textContent = infoText;
    }

    if (!laporanList || laporanList.length === 0) {
        container.innerHTML = `
            <div style="background-color: var(--color-surface); border: 1px dashed var(--color-border); border-radius: var(--radius); padding: 3rem 1.5rem; text-align: center;">
                <p style="font-size: 1.05rem; font-weight: 600; color: var(--color-primary); margin-bottom: 0.5rem;">
                    Tidak ada laporan yang ditemukan
                </p>
                <p style="font-size: 0.9rem; color: var(--color-text-muted); margin: 0;">
                    ${currentKeyword || currentKategori || currentJenis ? 'Coba sesuaikan kata kunci atau filter pencarian Anda.' : 'Belum ada laporan publik yang terdaftar saat ini.'}
                </p>
            </div>
        `;
        const pagContainer = document.getElementById('pagination-container');
        if (pagContainer) pagContainer.innerHTML = '';
        return;
    }

    let html = '<div style="display: flex; flex-direction: column; gap: 1rem;">';

    laporanList.forEach(item => {
        const jenisLabel = esc(item.jenis).toUpperCase();
        const judul = esc(item.judul);
        const isi = esc(item.isi);
        const lokasi = esc(item.lokasi || '-');
        const kategori = esc(item.nama_kategori || 'Umum');
        const pelapor = esc(item.nama_pelapor);
        const tanggal = formatTanggal(item.created_at);
        const badge = badgeStatus(item.status);

        let tanggapanHtml = '';
        if (item.tanggapan_terakhir) {
            tanggapanHtml = `
                <div style="margin-top: 0.85rem; padding: 0.65rem 0.85rem; background-color: var(--color-bg); border-left: 3px solid var(--color-secondary); border-radius: 4px; font-size: 0.85rem;">
                    <strong style="color: var(--color-primary);">Tanggapan Terakhir Admin:</strong>
                    <div style="color: var(--color-text-muted); margin-top: 0.2rem;">${esc(item.tanggapan_terakhir)}</div>
                </div>
            `;
        }

        html += `
            <article class="card" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.5rem;">
                    <div>
                        <span style="font-size: 0.75rem; font-weight: 700; color: var(--color-secondary); letter-spacing: 0.5px;">
                            ${jenisLabel} &bull; ${kategori}
                        </span>
                        <h2 style="font-size: 1.15rem; color: var(--color-primary); margin: 0.2rem 0; line-height: 1.35;">
                            ${judul}
                        </h2>
                    </div>
                    <div>
                        ${badge}
                    </div>
                </div>

                <p style="font-size: 0.95rem; color: var(--color-text); margin-bottom: 0.75rem; line-height: 1.5;">
                    ${isi}
                </p>

                ${tanggapanHtml}

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.85rem; padding-top: 0.75rem; border-top: 1px solid var(--color-border); font-size: 0.85rem; color: var(--color-text-muted);">
                    <div>
                        <span>Pelapor: <strong>${pelapor}</strong></span> &bull; 
                        <span>Lokasi: ${lokasi}</span> &bull; 
                        <span>${tanggal}</span>
                    </div>
                    <a href="/detail.php?id=${encodeURIComponent(item.id)}" class="btn btn-secondary btn-sm">
                        Lihat Detail &rarr;
                    </a>
                </div>
            </article>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
}

function renderPagination(pagination) {
    const container = document.getElementById('pagination-container');
    if (!container) return;

    if (!pagination || pagination.total_pages <= 1) {
        container.innerHTML = '';
        return;
    }

    const totalPages = pagination.total_pages;
    const current = pagination.current_page;
    let html = '';

    if (current > 1) {
        html += `<button type="button" class="btn btn-secondary btn-sm" onclick="goToPage(${current - 1})">&laquo; Sebelumnya</button>`;
    } else {
        html += `<button type="button" class="btn btn-secondary btn-sm" disabled style="opacity: 0.5; cursor: not-allowed;">&laquo; Sebelumnya</button>`;
    }

    for (let i = 1; i <= totalPages; i++) {
        if (i === current) {
            html += `<button type="button" class="btn btn-primary btn-sm" style="font-weight: 700;">${i}</button>`;
        } else {
            html += `<button type="button" class="btn btn-secondary btn-sm" onclick="goToPage(${i})">${i}</button>`;
        }
    }

    if (current < totalPages) {
        html += `<button type="button" class="btn btn-secondary btn-sm" onclick="goToPage(${current + 1})">Selanjutnya &raquo;</button>`;
    } else {
        html += `<button type="button" class="btn btn-secondary btn-sm" disabled style="opacity: 0.5; cursor: not-allowed;">Selanjutnya &raquo;</button>`;
    }

    container.innerHTML = html;
}

function goToPage(page) {
    loadLaporan(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showError(pesan) {
    const container = document.getElementById('laporan-container');
    if (!container) return;

    container.innerHTML = `
        <div class="alert alert-danger" style="margin-bottom: 0;">
            ${esc(pesan)}
        </div>
    `;
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    const filterKategori = document.getElementById('filter-kategori');
    const filterJenis = document.getElementById('filter-jenis');
    const btnReset = document.getElementById('btn-reset');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentKeyword = e.target.value.trim();
                loadLaporan(1);
            }, 300);
        });
    }

    if (filterKategori) {
        filterKategori.addEventListener('change', (e) => {
            currentKategori = e.target.value;
            loadLaporan(1);
        });
    }

    if (filterJenis) {
        filterJenis.addEventListener('change', (e) => {
            currentJenis = e.target.value;
            loadLaporan(1);
        });
    }

    if (btnReset) {
        btnReset.addEventListener('click', () => {
            currentKeyword = '';
            currentKategori = '';
            currentJenis = '';
            if (searchInput) searchInput.value = '';
            if (filterKategori) filterKategori.value = '';
            if (filterJenis) filterJenis.value = '';
            loadLaporan(1);
        });
    }

    loadLaporan(1);
});
