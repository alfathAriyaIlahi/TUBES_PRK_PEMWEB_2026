
function renderHoaks(contentArea) {
    contentArea.innerHTML = `
        <section class="max-w-3xl mx-auto">

            <header class="mb-6 pb-4 border-b border-gray-200">
                <span class="text-sm font-semibold text-blue-700 uppercase tracking-wider">
                    CiviLens - Ruang Publik
                </span>

                <h1 class="text-2xl font-bold text-gray-900 mt-2">
                    Laporkan Informasi Hoaks
                </h1>

                <p class="text-gray-600 text-sm mt-2">
                    Temukan informasi yang diduga mengandung hoaks?
                    Bantu masyarakat dengan melaporkannya melalui formulir berikut.
                </p>
            </header>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-blue-800">
                    <strong>Informasi:</strong>
                    Setiap laporan akan diperiksa terlebih dahulu oleh admin
                    sebelum ditentukan sebagai fakta atau hoaks.
                </p>
            </div>

            <form id="form-lapor-hoaks"
                class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-5">

                <div>
                    <label for="judul_isu"
                        class="block text-sm font-semibold text-gray-700 mb-2">
                        Judul Isu / Informasi
                    </label>

                    <input
                        type="text"
                        id="judul_isu"
                        name="judul_isu"
                        maxlength="255"
                        required
                        placeholder="Masukkan judul informasi yang diduga hoaks"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="link_sumber"
                        class="block text-sm font-semibold text-gray-700 mb-2">
                        Link Sumber Informasi
                    </label>

                    <input
                        type="url"
                        id="link_sumber"
                        name="link_sumber"
                        maxlength="255"
                        required
                        placeholder="https://contoh.com/informasi"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

                    <p class="text-xs text-gray-500 mt-1">
                        Masukkan tautan berita, artikel, atau unggahan yang dilaporkan.
                    </p>
                </div>

                <div>
                    <label for="keterangan"
                        class="block text-sm font-semibold text-gray-700 mb-2">
                        Keterangan Laporan
                    </label>

                    <textarea
                        id="keterangan"
                        name="keterangan"
                        rows="5"
                        required
                        placeholder="Jelaskan alasan informasi tersebut diduga hoaks..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div id="hoaks-message"
                    class="hidden p-3 rounded-lg text-sm"
                    role="alert"
                    aria-live="polite">
                </div>

                <button
                    type="submit"
                    id="btn-submit-hoaks"
                    class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-3 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">

                    Kirim Laporan
                </button>

            </form>

            <div id="hoaks-feedback-list" class="mt-10"></div>

        </section>
    `;

    const form = document.getElementById('form-lapor-hoaks');
    const message = document.getElementById('hoaks-message');
    const submitButton = document.getElementById('btn-submit-hoaks');

    function showMessage(text, success) {
        message.textContent = text;
        message.classList.remove(
            'hidden',
            'bg-green-50',
            'text-green-800',
            'border-green-200',
            'bg-red-50',
            'text-red-800',
            'border-red-200'
        );

        if (success) {
            message.classList.add(
                'bg-green-50',
                'text-green-800',
                'border',
                'border-green-200'
            );
        } else {
            message.classList.add(
                'bg-red-50',
                'text-red-800',
                'border',
                'border-red-200'
            );
        }
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const judul = document.getElementById('judul_isu').value.trim();
        const link = document.getElementById('link_sumber').value.trim();
        const keterangan = document.getElementById('keterangan').value.trim();

        if (!judul || !link || !keterangan) {
            showMessage('Semua kolom wajib diisi.', false);
            return;
        }

        try {
            const parsedUrl = new URL(link);

            if (!['http:', 'https:'].includes(parsedUrl.protocol)) {
                throw new Error('URL tidak valid');
            }
        } catch (error) {
            showMessage('Masukkan link sumber yang valid.', false);
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = 'Mengirim laporan...';

        try {
            const response = await fetch('api/hoaks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    judul_isu: judul,
                    link_sumber: link,
                    keterangan: keterangan
                })
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || 'Gagal mengirim laporan.'
                );
            }

            showMessage(
                result.message + ' Status laporan: ' + result.data.status,
                true
            );

            form.reset();

        } catch (error) {
            showMessage(
                error.message || 'Terjadi kesalahan saat mengirim laporan.',
                false
            );

        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Kirim Laporan';
        }
    });

    async function loadHoaksFeedback() {
        const listContainer = document.getElementById('hoaks-feedback-list');
        if (!listContainer) return;

        listContainer.innerHTML = '<p class="text-sm text-slate-500 animate-pulse">Memuat papan klarifikasi...</p>';

        try {
            const response = await fetch('api/hoaks.php');
            const result = await response.json();

            if (result.status === 'success' && result.data.length > 0) {
                let html = '<h3 class="text-xl font-bold text-slate-800 border-b-2 border-slate-200 pb-2 mb-4">Papan Klarifikasi Isu</h3><div class="grid gap-3">';

                result.data.forEach(item => {
                    const badgeStyle = item.status === 'fakta'
                        ? 'bg-green-100 text-green-700 border-green-300'
                        : 'bg-red-100 text-red-700 border-red-300';

                    html += `
                        <div class="p-4 border border-slate-200 rounded-lg bg-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
                            <div>
                                <h4 class="font-semibold text-slate-800 text-sm">${item.judul_isu}</h4>
                                <a href="${item.link_sumber}" target="_blank" class="text-xs text-blue-600 hover:underline">Tinjau Sumber Laporan</a>
                            </div>
                            <span class="px-3 py-1 border rounded-md text-xs font-bold uppercase ${badgeStyle}">
                                ${item.status}
                            </span>
                        </div>
                    `;
                });
                html += '</div>';
                listContainer.innerHTML = html;
            } else {
                listContainer.innerHTML = '<p class="text-sm text-slate-500 bg-slate-50 p-4 rounded-lg border border-slate-200 text-center">Belum ada laporan isu yang selesai diverifikasi oleh Admin.</p>';
            }
        } catch (error) {
            listContainer.innerHTML = '<p class="text-sm text-red-500">Gagal memuat papan klarifikasi.</p>';
        }
    }

    setTimeout(loadHoaksFeedback, 100);
}
