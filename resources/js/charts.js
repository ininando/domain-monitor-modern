/**
 * Komponen Alpine `chart` untuk Chart.js.
 *
 * Pemakaian (lihat komponen Blade <x-chart>):
 *   x-data="chart({ type: 'bar', labels: [...], values: [...], seriesLabel: 'Domain', filename: 'x.png' })"
 *
 * - Warna dibaca dari token CSS sehingga ikut mode terang/gelap dan digambar ulang saat tema berubah.
 * - Chart.js dimuat dinamis (chunk terpisah) hanya di halaman yang memakai chart.
 * - Export PNG memakai latar surface agar tidak transparan.
 */

const css = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const angka = new Intl.NumberFormat('id-ID');

/** '#0369a1' + 0.12 → 'rgba(3, 105, 161, 0.12)' (canvas tidak selalu mendukung color-mix). */
function transparan(hex, alpha) {
    const h = hex.replace('#', '');
    const [r, g, b] = [0, 2, 4].map((i) => parseInt(h.slice(i, i + 2), 16));
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function palette() {
    return {
        primary: css('--primary'),
        surface: css('--surface'),
        ink: css('--ink'),
        inkMuted: css('--ink-muted'),
        line: css('--line'),
        grid: css('--chart-grid'),
        series: ['--chart-1', '--chart-2', '--chart-3', '--chart-4', '--chart-5'].map(css),
        other: css('--chart-other'),
    };
}

/** Latar solid supaya PNG hasil export tidak transparan. */
const latarPlugin = {
    id: 'latarSurface',
    beforeDraw(chart) {
        const { ctx, width, height } = chart;
        ctx.save();
        ctx.globalCompositeOperation = 'destination-over';
        ctx.fillStyle = palette().surface;
        ctx.fillRect(0, 0, width, height);
        ctx.restore();
    },
};

function potong(teks, maks) {
    return teks.length > maks ? `${teks.slice(0, maks - 1)}…` : teks;
}

function buatConfig({ type, labels, values, seriesLabel, colors }) {
    const p = palette();

    const tooltip = {
        backgroundColor: p.ink,
        titleColor: p.surface,
        bodyColor: p.surface,
        padding: 10,
        cornerRadius: 8,
        displayColors: type === 'doughnut',
        boxPadding: 4,
        titleFont: { weight: '600' },
        callbacks: {
            label: (ctx) => {
                if (type === 'doughnut') {
                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                    const persen = total ? Math.round((ctx.parsed / total) * 100) : 0;
                    return ` ${angka.format(ctx.parsed)} domain (${persen}%)`;
                }
                const nilai = type === 'bar' ? ctx.parsed.x : ctx.parsed.y;
                return `${seriesLabel}: ${angka.format(nilai)}`;
            },
        },
    };

    const sumbu = {
        grid: { color: p.grid, drawTicks: false },
        border: { display: false },
        ticks: { color: p.inkMuted, padding: 8, font: { size: 12 } },
    };

    const dasar = {
        responsive: true,
        maintainAspectRatio: false,
        animation: reducedMotion() ? false : { duration: 500, easing: 'easeOutQuart' },
        plugins: { legend: { display: false }, tooltip },
        font: { family: "'Plus Jakarta Sans Variable', system-ui, sans-serif" },
    };

    if (type === 'bar') {
        return {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: seriesLabel,
                    data: values,
                    backgroundColor: p.primary,
                    hoverBackgroundColor: p.primary,
                    borderRadius: 4,
                    borderSkipped: 'start',
                    maxBarThickness: 22,
                }],
            },
            options: {
                ...dasar,
                indexAxis: 'y',
                scales: {
                    x: { ...sumbu, beginAtZero: true, ticks: { ...sumbu.ticks, precision: 0 } },
                    y: { ...sumbu, grid: { display: false }, ticks: { ...sumbu.ticks, color: p.ink, callback(v) { return potong(this.getLabelForValue(v), 26); } } },
                },
            },
        };
    }

    if (type === 'line') {
        return {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: seriesLabel,
                    data: values,
                    borderColor: p.primary,
                    backgroundColor: transparan(p.primary, 0.12),
                    fill: true,
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBorderWidth: 2,
                    pointHoverBorderColor: p.surface,
                    pointHoverBackgroundColor: p.primary,
                }],
            },
            options: {
                ...dasar,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: { ...sumbu, grid: { display: false } },
                    y: { ...sumbu, beginAtZero: true, ticks: { ...sumbu.ticks, precision: 0 } },
                },
            },
        };
    }

    // doughnut
    const warna = colors ?? labels.map((_, i) => p.series[i] ?? p.other);
    return {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: warna.map((w) => (w === 'other' ? p.other : w.startsWith('--') ? css(w) : w)),
                borderColor: p.surface,
                borderWidth: 2,
                hoverOffset: 6,
            }],
        },
        options: { ...dasar, cutout: '68%', layout: { padding: 6 } },
    };
}

export default (opsi) => ({
    instance: null,
    siap: false,

    async init() {
        const { default: Chart } = await import('chart.js/auto');
        this.Chart = Chart;
        this.render();
        this.siap = true;

        this.onTheme = () => this.render();
        window.addEventListener('theme-changed', this.onTheme);
    },

    destroy() {
        window.removeEventListener('theme-changed', this.onTheme);
        this.instance?.destroy();
    },

    render() {
        this.instance?.destroy();
        this.instance = new this.Chart(this.$refs.canvas, {
            ...buatConfig(opsi),
            plugins: [latarPlugin],
        });
    },

    unduhPng() {
        if (!this.instance) {
            return;
        }
        const tautan = document.createElement('a');
        tautan.href = this.instance.toBase64Image('image/png', 1);
        tautan.download = opsi.filename ?? 'chart.png';
        tautan.click();
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Gambar chart diunduh.' } }));
    },
});
