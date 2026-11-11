'use strict';
/* KlikObat - katalog.js (Vanilla JS ES6+, tanpa library) */

const FALLBACK_KATEGORI = [
    { id_kategori: 1, nama_kategori: 'Pereda Nyeri & Demam' },
    { id_kategori: 2, nama_kategori: 'Obat Batuk, Pilek & Flu' },
    { id_kategori: 3, nama_kategori: 'Saluran Pencernaan' },
    { id_kategori: 4, nama_kategori: 'Vitamin & Suplemen' },
    { id_kategori: 5, nama_kategori: 'Antiseptik & Perawatan Luka' }
];
const FALLBACK_OBAT = [
    { id_obat: 1, nama_obat: 'Paracetamol 500 mg (Strip 10 Tablet)', id_kategori: 1, stok: 45, indikasi: 'Meredakan demam serta nyeri ringan hingga sedang seperti sakit kepala, sakit gigi, dan nyeri otot.', aturan_pakai: 'Dewasa: 1-2 tablet, 3-4 kali sehari sesudah makan. Maksimal 8 tablet per hari.', peringatan: 'Hati-hati penggunaan pada penderita gangguan fungsi hati dan ginjal. Jangan melebihi dosis yang dianjurkan.' },
    { id_obat: 2, nama_obat: 'Ibuprofen 400 mg (Strip 10 Tablet)', id_kategori: 1, stok: 8, indikasi: 'Mengatasi nyeri inflamasi ringan hingga sedang, nyeri haid (dismenore), sakit gigi, nyeri sendi, dan menurunkan demam.', aturan_pakai: 'Dewasa: 1 tablet, 3 kali sehari segera sesudah makan.', peringatan: 'Tidak dianjurkan untuk penderita tukak lambung, ibu hamil trimester akhir, dan pasien dengan riwayat alergi OAINS.' },
    { id_obat: 3, nama_obat: 'Asam Mefenamat 500 mg (Strip 10 Kaplet)', id_kategori: 1, stok: 25, indikasi: 'Meredakan nyeri akut dan kronik ringan sampai sedang sehubungan dengan sakit kepala, sakit gigi, dan nyeri pasca operasi.', aturan_pakai: 'Dewasa: Dosis awal 1 kaplet, dilanjutkan 1/2 - 1 kaplet tiap 6 jam sesudah makan.', peringatan: 'Jangan digunakan lebih dari 7 hari. Kontraindikasi pada pasien radang usus dan gangguan ginjal berat.' },
    { id_obat: 4, nama_obat: 'Sanmol Sirup 60 ml', id_kategori: 1, stok: 0, indikasi: 'Menurunkan panas demam dan meredakan rasa sakit pada anak-anak seperti sakit kepala dan nyeri tumbuh gigi.', aturan_pakai: 'Anak 1-2 tahun: 5 ml (1 sendok takar), 3-4 kali sehari. Anak 2-6 tahun: 5-10 ml, 3-4 kali sehari.', peringatan: 'Kocok dahulu sebelum diminum. Hubungi dokter bila demam tidak turun setelah 2 hari penggunaan.' },
    { id_obat: 5, nama_obat: 'OBH Combi Batuk Berdahak Menthol 100 ml', id_kategori: 2, stok: 30, indikasi: 'Meredakan batuk berdahak yang disertai gejala flu seperti demam, sakit kepala, hidung tersumbat, dan bersin-bersin.', aturan_pakai: 'Dewasa & anak di atas 12 tahun: 3 kali sehari 3 sendok takar (15 ml) sesudah makan.', peringatan: 'Dapat menyebabkan kantuk. Jangan mengendarai kendaraan bermotor atau menjalankan mesin setelah meminum obat ini.' },
    { id_obat: 6, nama_obat: 'Siladex Mucolytic & Expectorant 60 ml', id_kategori: 2, stok: 5, indikasi: 'Mengencerkan dahak pada saluran pernapasan dan mempermudah pengeluaran dahak saat batuk.', aturan_pakai: 'Dewasa & anak di atas 10 tahun: 3 kali sehari 1 sendok takar (5 ml) sesudah makan.', peringatan: 'Bebas gula dan alkohol. Hati-hati penggunaan pada penderita tukak lambung dan ibu hamil trimester pertama.' },
    { id_obat: 7, nama_obat: 'Tremenza Sirup 60 ml', id_kategori: 2, stok: 18, indikasi: 'Meringankan gejala-gejala flu karena alergi pada saluran pernapasan bagian atas seperti hidung tersumbat dan bersin.', aturan_pakai: 'Dewasa: 3-4 kali sehari 2 sendok takar (10 ml). Anak 6-12 tahun: 3-4 kali sehari 1 sendok takar (5 ml).', peringatan: 'Tidak dianjurkan untuk penderita hipertensi berat, penyakit jantung koroner, dan pasien yang mengonsumsi antidepresan MAOI.' },
    { id_obat: 8, nama_obat: 'Degrol Hisap Dequalinium (Strip 10 Tablet)', id_kategori: 2, stok: 0, indikasi: 'Tablet hisap antiseptik untuk membantu meredakan radang tenggorokan, sariawan, dan infeksi ringan rongga mulut.', aturan_pakai: 'Hisap perlahan 1 tablet setiap 3-4 jam. Maksimal 8 tablet sehari.', peringatan: 'Hindari penggunaan jangka panjang secara terus-menerus. Tidak untuk anak di bawah usia 6 tahun.' },
    { id_obat: 9, nama_obat: 'Promag Tablet Kunyah (Strip 12 Tablet)', id_kategori: 3, stok: 50, indikasi: 'Meringankan gejala sakit maag dan kelebihan asam lambung seperti perih, mual, kembung, dan nyeri ulu hati.', aturan_pakai: 'Dewasa: 1-2 tablet dikunyah, 3-4 kali sehari. Diminum 1 jam sebelum makan atau 2 jam setelah makan dan menjelang tidur.', peringatan: 'Beri jeda minimal 2 jam jika sedang mengonsumsi antibiotik tetrasiklin atau suplemen zat besi.' },
    { id_obat: 10, nama_obat: 'Mylanta Cair 150 ml', id_kategori: 3, stok: 7, indikasi: 'Mengurangi gejala kelebihan asam lambung, gastritis, tukak lambung, dan tukak usus dua belas jari.', aturan_pakai: 'Dewasa: 1-2 sendok takar (5-10 ml), 3-4 kali sehari di antara waktu makan dan sebelum tidur.', peringatan: 'Tidak dianjurkan digunakan terus-menerus lebih dari 2 minggu kecuali atas petunjuk dokter.' },
    { id_obat: 11, nama_obat: 'New Diatabs Attapulgite (Strip 4 Tablet)', id_kategori: 3, stok: 35, indikasi: 'Pengobatan simtomatik pada diare non-spesifik dengan cara menyerap racun dan bakteri penyebab diare di saluran cerna.', aturan_pakai: 'Dewasa: 2 tablet setiap selesai buang air besar. Maksimal 12 tablet dalam 24 jam.', peringatan: 'Jangan diberikan pada pasien konstipasi (sembelit). Tetap cukupi kebutuhan cairan tubuh (oralit) selama diare.' },
    { id_obat: 12, nama_obat: 'Oralit 200 Garam Elektrolit (Sachet)', id_kategori: 3, stok: 60, indikasi: 'Mencegah dan mengobati dehidrasi akibat diare atau muntah-muntah dengan menggantikan cairan dan elektrolit tubuh.', aturan_pakai: 'Larutkan 1 sachet ke dalam 200 ml air matang hangat. Minum setiap kali selesai buang air besar.', peringatan: 'Larutan yang sudah dibuat tidak boleh digunakan lagi setelah lewat dari 24 jam.' },
    { id_obat: 13, nama_obat: 'Vitamin C IPI 50 mg (Botol 45 Tablet)', id_kategori: 4, stok: 22, indikasi: 'Membantu memenuhi kebutuhan Vitamin C harian, menjaga daya tahan tubuh, serta membantu pemulihan pasca sakit.', aturan_pakai: 'Dewasa: 1-2 tablet hisap sehari sesudah makan.', peringatan: 'Simpan di tempat kering di bawah suhu 30 derajat Celsius dan terhindar dari sinar matahari langsung.' },
    { id_obat: 14, nama_obat: 'Imboost Force (Strip 10 Kaplet)', id_kategori: 4, stok: 4, indikasi: 'Suplemen imunomodulator (Echinacea & Zinc) untuk membantu memelihara daya tahan tubuh dan mempercepat penyembuhan.', aturan_pakai: 'Dewasa: 1 kaplet, 3 kali sehari sesudah makan.', peringatan: 'Tidak boleh digunakan oleh penderita penyakit autoimun, sklerosis multipel, tuberkulosis, dan AIDS. Maksimal konsumsi 8 minggu.' },
    { id_obat: 15, nama_obat: 'Sangobion Kapsul Penambah Darah (Strip 10 Kapsul)', id_kategori: 4, stok: 28, indikasi: 'Suplemen zat besi, vitamin, dan mineral untuk mengatasi anemia (kurang darah) serta membantu pembentukan sel darah merah.', aturan_pakai: 'Dewasa: 1 kapsul sehari pada waktu atau sesudah makan.', peringatan: 'Dapat menyebabkan feses berwarna hitam yang merupakan hal normal akibat kandungan zat besi.' },
    { id_obat: 16, nama_obat: 'Neurobion Forte (Strip 10 Tablet)', id_kategori: 4, stok: 0, indikasi: 'Suplemen vitamin neurotropik (B1, B6, B12) untuk menjaga kesehatan fungsi saraf serta mengatasi kesemutan dan kebas.', aturan_pakai: 'Dewasa: 1 tablet sehari sesudah makan, ditelan utuh dengan air putih.', peringatan: 'Tidak dianjurkan untuk anak-anak karena tingginya dosis vitamin B di dalam tablet.' },
    { id_obat: 17, nama_obat: 'Betadine Antiseptic Solution 15 ml', id_kategori: 5, stok: 19, indikasi: 'Cairan antiseptik Povidone-Iodine 10% untuk membunuh kuman penyebab infeksi pada luka gores, luka bakar ringan, dan luka lecet.', aturan_pakai: 'Bersihkan luka terlebih dahulu, lalu oleskan Betadine secukupnya 1-2 kali sehari pada area yang luka.', peringatan: 'Hanya untuk pemakaian luar. Hentikan pemakaian jika timbul kemerahan, iritasi, atau pembengkakan pada kulit.' },
    { id_obat: 18, nama_obat: 'Hansaplast Kain Elastis (Amplop 10 Lembar)', id_kategori: 5, stok: 40, indikasi: 'Plester penutup luka berbahan kain lentur yang mengikuti pergerakan kulit untuk melindungi luka dari kotoran dan bakteri.', aturan_pakai: 'Bersihkan dan keringkan kulit di sekitar luka, lalu tempelkan plester tanpa diregangkan. Ganti plester setiap hari.', peringatan: 'Hanya untuk sekali pakai. Jangan gunakan pada luka terbuka yang mengalami perdarahan hebat.' },
    { id_obat: 19, nama_obat: 'Rivanol Kompres Luka 100 ml', id_kategori: 5, stok: 3, indikasi: 'Cairan antiseptik Etakridina Laktat 0,1% untuk membersihkan luka dan mengompres luka bernanah atau borok.', aturan_pakai: 'Tuangkan pada kapas atau kasa steril, lalu usapkan atau kompreskan pada bagian luka.', peringatan: 'Hanya untuk penggunaan luar. Jauhkan dari jangkauan anak-anak dan simpan di tempat sejuk.' },
    { id_obat: 20, nama_obat: 'Bioplacenton Gel 15 gr', id_kategori: 5, stok: 14, indikasi: 'Mengobati luka bakar ringan, luka terinfeksi, dan membantu mempercepat regenerasi jaringan kulit baru.', aturan_pakai: 'Oleskan tipis-tipis pada area luka 4-6 kali sehari sesuai kebutuhan.', peringatan: 'Obat keras terbatas. Hindari kontak langsung dengan mata dan jangan digunakan oleh pasien yang alergi terhadap Neomycin.' }
];

/* ------------------------------- Util & state ----------------------------- */
const API = { kategori: '../api/kategori.php', obat: '../api/obat.php' };
const state = { kategori: [], obat: [], aktif: 'semua', q: '', sumberLokal: false };
const $ = (sel, root = document) => root.querySelector(sel);

const escapeHTML = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => (
    { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
));
const ikon = (n) => `<svg class="ic" aria-hidden="true"><use href="#i-${n}"/></svg>`;
// Pisahkan nama dan kekuatan sediaan: "Paracetamol 500 mg" -> "Paracetamol" + kapsul mono "500 mg"
const judul = (nama) => {
    const m = String(nama ?? '').match(/^(.+?)\s+(\d[\d.,]*\s?(?:mg|mcg|g|ml|iu|%).*)$/i);
    return m ? `${escapeHTML(m[1])}<span class="kekuatan">${escapeHTML(m[2])}</span>` : escapeHTML(nama);
};
const stokHTML = (s, n) => `<strong>${s.t}</strong><span>${s.k === 'low' ? 'sisa ' : ''}<b>${n}</b> unit</span>`;
const status = (s) => (s <= 0 ? { k: 'out', t: 'Habis' } : s <= 5 ? { k: 'low', t: 'Menipis' } : { k: 'ok', t: 'Tersedia' });

const TEMA = [
    [/nyeri|analges|demam|antipiret/i, 'bolt', '#2563EB'],
    [/cerna|lambung|maag/i, 'drop', '#0891B2'],
    [/napas|paru|batuk/i, 'wind', '#7C3AED'],
    [/alergi|antihist/i, 'star', '#DB2777'],
    [/vitamin|suplemen/i, 'leaf', '#059669'],
];
const tema = (nama) => {
    const t = TEMA.find(([re]) => re.test(nama));
    return t ? { ikon: t[1], warna: t[2] } : { ikon: 'caps', warna: '#2563EB' };
};

/* ------------------------------- Smart Fetch ------------------------------ */
async function ambilJSON(url) {
    const ctl = new AbortController();
    const timer = setTimeout(() => ctl.abort(), 4000);
    try {
        const res = await fetch(url, { headers: { Accept: 'application/json' }, signal: ctl.signal });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        if (!Array.isArray(json) && (json.status === 'error' || json.status === false)) {
            throw new Error(json.message || 'API mengembalikan status error');
        }
        const data = Array.isArray(json) ? json : (json.data || json.obat || json.kategori);
        if (!Array.isArray(data)) throw new Error('Format JSON tidak valid');
        return data;
    } finally {
        clearTimeout(timer);
    }
}

async function muatData() {
    const [k, o] = await Promise.allSettled([ambilJSON(API.kategori), ambilJSON(API.obat)]);
    const dariApi = k.status === 'fulfilled' && o.status === 'fulfilled';
    state.sumberLokal = !dariApi;
    state.kategori = dariApi ? k.value : FALLBACK_KATEGORI;
    const peta = new Map(state.kategori.map((x) => [String(x.id_kategori), x.nama_kategori]));
    state.obat = (dariApi ? o.value : FALLBACK_OBAT).map((x) => ({
        ...x,
        stok: Number(x.stok) || 0,
        nama_kategori: x.nama_kategori || peta.get(String(x.id_kategori)) || 'Umum',
    }));
    const notice = $('#status-data');
    if (notice) notice.hidden = dariApi;
}

/* ------------------------------- Ilustrasi SVG ---------------------------- */
function botol(warna, nama = '', kat = '', besar = false) {
    const teks = besar
        ? `<text x="60" y="114" text-anchor="middle" font-size="9" font-weight="800" fill="#0F172A">${escapeHTML(nama.split(' ')[0].slice(0, 11))}</text>` +
        `<text x="60" y="127" text-anchor="middle" font-size="6.5" font-weight="600" fill="${warna}">${escapeHTML(kat.slice(0, 18))}</text>`
        : '';
    return `<svg viewBox="0 0 120 170" aria-hidden="true" font-family="Bricolage Grotesque,sans-serif">
    <ellipse cx="60" cy="163" rx="40" ry="5" fill="#0F172A" opacity=".08"/>
    <rect x="40" y="6" width="40" height="22" rx="7" fill="${warna}"/>
    <rect x="28" y="24" width="64" height="136" rx="18" fill="#fff" stroke="#CBD5E1" stroke-width="2"/>
    <rect x="28" y="64" width="64" height="74" fill="${warna}" opacity=".12"/>
    <path d="M60 74v20M50 84h20" stroke="${warna}" stroke-width="6" stroke-linecap="round"/>${teks}
  </svg>`;
}

const kosong = () => `<div class="empty">
  <div class="empty-art"><svg viewBox="0 0 100 100" width="96" height="96" aria-hidden="true">
    <path d="M14 38 50 22l36 16v38L50 92 14 76z" fill="#fff" stroke="#CBD5E1" stroke-width="3" stroke-linejoin="round"/>
    <path d="M14 38l36 16 36-16M50 54v38" fill="none" stroke="#CBD5E1" stroke-width="3"/>
    <rect x="34" y="6" width="18" height="24" rx="5" fill="#BFDBFE"/>
    <circle cx="74" cy="30" r="14" fill="#2563EB"/><path d="M74 23v14M67 30h14" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
  </svg></div>
  <h3>Obat Tidak Ditemukan!</h3>
  <p>Coba kata kunci lain atau pilih kategori yang berbeda, atau tampilkan kembali seluruh obat.</p>
  <button type="button" class="btn-primary" id="btn-reset-semua">Tampilkan Semua Obat</button>
</div>`;

/* --------------------------------- Render --------------------------------- */
function skeleton() {
    const sk = (h, extra = '') => `<div class="sk" style="height:${h}px;${extra}"></div>`;
    $('#katalog-grid').innerHTML = Array.from({ length: 6 }, () =>
        `<div class="medicine-card skel" aria-hidden="true">${sk(145, 'border-radius:16px')}${sk(20, 'width:70%')}${sk(14)}${sk(38)}${sk(34, 'margin-top:.4rem')}</div>`
    ).join('');
}

function renderKategori() {
    const semua = { id_kategori: 'semua', nama_kategori: 'Semua Obat' };
    $('#kategori-list').innerHTML = [semua, ...state.kategori].map((k) => {
        const id = String(k.id_kategori);
        const ik = id === 'semua' ? 'grid' : tema(k.nama_kategori).ikon;
        return `<button type="button" class="chip" data-id="${escapeHTML(id)}" aria-pressed="${id === state.aktif}"><span class="ico">${ikon(ik)}</span>${escapeHTML(k.nama_kategori)}</button>`;
    }).join('');
}
const tandaiAktif = () => document.querySelectorAll('.chip').forEach((c) =>
    c.setAttribute('aria-pressed', String(c.dataset.id === state.aktif)));

function daftarTerfilter() {
    const q = state.q.trim().toLowerCase();
    return state.obat.filter((o) =>
        (state.aktif === 'semua' || String(o.id_kategori) === state.aktif) &&
        (!q || [o.nama_obat, o.indikasi, o.nama_kategori].some((v) => String(v ?? '').toLowerCase().includes(q))));
}

function kartu(o) {
    const s = status(o.stok), t = tema(o.nama_kategori), nama = escapeHTML(o.nama_obat);
    return `<article class="medicine-card${s.k === 'out' ? ' habis' : ''}" data-id="${escapeHTML(o.id_obat)}">
    <div class="thumb">
      <span class="tag">${escapeHTML(o.nama_kategori)}</span>
      ${botol(t.warna)}
    </div>
    <div class="card-body">
      <h3 class="card-name">${judul(o.nama_obat)}</h3>
      <p class="card-ind">${escapeHTML(o.indikasi)}</p>
      <div class="dose">${ikon('clock')}<span>${escapeHTML(o.aturan_pakai)}</span></div>
    </div>
    <div class="card-foot">
      <div class="stok st-${s.k}">${stokHTML(s, o.stok)}</div>
      <button type="button" class="btn-detail" aria-label="Lihat detail ${nama}">Detail ${ikon('plus')}</button>
    </div>
  </article>`;
}

function renderGrid() {
    const data = daftarTerfilter();
    $('#info-jumlah-obat').innerHTML = `Menampilkan <b>${data.length}</b> obat`;
    $('#katalog-grid').innerHTML = data.length ? data.map((o) => kartu(o)).join('') : kosong();
}
function transisi() {
    const g = $('#katalog-grid');
    g.classList.add('fade');
    setTimeout(() => { renderGrid(); g.classList.remove('fade'); }, 130);
}

/* ---------------------------------- Modal --------------------------------- */
let fokusSebelumnya = null;
const modalTerbuka = () => !$('#modal-detail').hidden;

window.bukaModalDetail = function (o) {
    const s = status(o.stok), t = tema(o.nama_kategori);
    $('#modal-visual').innerHTML = botol(t.warna, o.nama_obat, o.nama_kategori, true);
    $('#modal-tags').innerHTML = `<span class="tag">${escapeHTML(o.nama_kategori)}</span>`;
    $('#modal-nama').innerHTML = judul(o.nama_obat);
    const ms = $('#modal-stok');
    ms.className = `stok st-${s.k}`;
    ms.innerHTML = stokHTML(s, o.stok);
    $('#modal-indikasi').textContent = o.indikasi;
    $('#modal-aturan').textContent = o.aturan_pakai;
    $('#modal-peringatan').textContent = o.peringatan;
    fokusSebelumnya = document.activeElement;
    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
    const m = $('#modal-detail');
    document.body.style.paddingRight = `${scrollbarWidth}px`;
    document.body.style.overflow = 'hidden';
    document.body.classList.add('lock');
    m.hidden = false;
    $('.modal', m).scrollTop = 0;
    $('#modal-tutup').focus();
};
window.tutupModalDetail = function () {
    $('#modal-detail').hidden = true;
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
    document.body.classList.remove('lock');
    if (fokusSebelumnya) fokusSebelumnya.focus();
};

/* --------------------------------- Event ---------------------------------- */
function pasang() {
    const input = $('#input-cari'), reset = $('#btn-reset-cari'), hero = $('#hero-banner');
    let timer;
    const sinkronkanHero = () => {
        hero.hidden = input.value.trim().length > 0;
    };
    const bersihkanCari = () => {
        clearTimeout(timer);
        input.value = '';
        reset.hidden = true;
        state.q = '';
        sinkronkanHero();
        transisi();
    };

    input.addEventListener('input', () => {
        reset.hidden = !input.value;
        sinkronkanHero();
        clearTimeout(timer);
        timer = setTimeout(() => { state.q = input.value; transisi(); }, 300); // debounce 300 ms
    });
    reset.addEventListener('click', () => { bersihkanCari(); input.focus(); });

    $('#kategori-list').addEventListener('click', (e) => {
        const b = e.target.closest('.chip');
        if (!b) return;
        state.aktif = b.dataset.id;
        tandaiAktif();
        transisi();
    });

    $('#katalog-grid').addEventListener('click', (e) => {
        if (e.target.closest('#btn-reset-semua')) { state.aktif = 'semua'; tandaiAktif(); bersihkanCari(); return; }
        const detail = e.target.closest('.btn-detail');
        if (!detail) return;
        const kartuEl = detail.closest('.medicine-card');
        if (!kartuEl || kartuEl.classList.contains('skel')) return;
        const o = state.obat.find((x) => String(x.id_obat) === kartuEl.dataset.id);
        if (o) window.bukaModalDetail(o);
    });

    $('#modal-detail').addEventListener('click', (e) => { if (e.target === e.currentTarget) window.tutupModalDetail(); });
    $('#modal-tutup').addEventListener('click', () => window.tutupModalDetail());

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (modalTerbuka()) window.tutupModalDetail();
            else if (input.value) bersihkanCari();
        } else if (e.key === '/' && !modalTerbuka() && !e.ctrlKey && !e.metaKey && !e.altKey &&
            !/^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement.tagName)) {
            e.preventDefault();
            input.focus();
        } else if (e.key === 'Tab' && modalTerbuka()) { // jaga fokus tetap di dalam modal
            const f = [...document.querySelectorAll('#modal-detail button')];
            const awal = f[0], akhir = f[f.length - 1];
            if (e.shiftKey && document.activeElement === awal) { e.preventDefault(); akhir.focus(); }
            else if (!e.shiftKey && document.activeElement === akhir) { e.preventDefault(); awal.focus(); }
        }
    });
}

/* ---------------------------------- Init ---------------------------------- */
async function init() {
    pasang();
    skeleton();
    await muatData();
    renderKategori();
    renderGrid();
}
init();