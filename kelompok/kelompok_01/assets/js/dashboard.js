document.addEventListener('DOMContentLoaded', function () {

    const NAMA_BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    function rupiah(n) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(Number(n) || 0);
    }

    function ringkas(v) {
        if (v >= 1000000) {
            return 'Rp ' + (v / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt';
        }
        if (v >= 1000) return 'Rp ' + (v / 1000) + ' rb';
        return 'Rp ' + v;
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    async function getJson(url) {
        const res = await fetch(url, { headers: { Accept: 'application/json' } });

        if (res.status === 401 || res.redirected) {
            window.location.href = '../login.php';
            throw new Error('Sesi berakhir.');
        }

        const data = await res.json();

        if (!res.ok || data.error) {
            throw new Error(data.error || 'Terjadi kesalahan.');
        }

        return data;
    }

    // ---------- Ringkasan ----------
    async function loadRingkasan() {
        const d = await getJson('../backend/dashboard_total.php');

        setText('cardPemasukan', rupiah(d.total_pemasukan));
        setText('cardPengeluaran', rupiah(d.total_pengeluaran));
        setText('cardSaldo', rupiah(d.saldo));
        setText('cardTabungan', rupiah(d.total_tabungan));

        setText('infoPemasukan', rupiah(d.total_pemasukan));
        setText('infoPengeluaran', rupiah(d.total_pengeluaran));
        setText('infoSetoran', rupiah(d.total_setoran));
        setText('infoSaldo', rupiah(d.saldo));
    }

    // ---------- Grafik ----------
    function enamBulanTerakhir() {
        const now = new Date();
        const hasil = [];

        for (let i = 5; i >= 0; i--) {
            const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
            hasil.push({ tahun: d.getFullYear(), bulan: d.getMonth() + 1 });
        }

        return hasil;
    }

    async function loadGrafik() {
        const canvas = document.getElementById('financeChart');
        if (!canvas || typeof Chart === 'undefined') return;

        const rows = await getJson('../backend/grafik.php');

        // Bulan tanpa transaksi tidak dikirim backend, jadi diisi 0
        const peta = {};
        rows.forEach(r => { peta[r.tahun + '-' + Number(r.bulan)] = r; });

        const bulan = enamBulanTerakhir();
        const labels = bulan.map(b => NAMA_BULAN[b.bulan - 1]);
        const pemasukan = bulan.map(b => Number((peta[b.tahun + '-' + b.bulan] || {}).total_pemasukan) || 0);
        const pengeluaran = bulan.map(b => Number((peta[b.tahun + '-' + b.bulan] || {}).total_pengeluaran) || 0);

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: pemasukan,
                        backgroundColor: '#26966c',
                        borderRadius: 7,
                        barThickness: 15
                    },
                    {
                        label: 'Pengeluaran',
                        data: pengeluaran,
                        backgroundColor: '#e8a0a5',
                        borderRadius: 7,
                        barThickness: 15
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'end' },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': ' + rupiah(ctx.raw)
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: v => ringkas(v) },
                        grid: { color: '#edf2ee' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    loadRingkasan().catch(err => console.error(err));
    loadGrafik().catch(err => console.error(err));
});