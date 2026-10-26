import '@fontsource-variable/plus-jakarta-sans';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import chart from './charts';

Alpine.plugin(focus);
Alpine.data('chart', chart);

/**
 * Tema: 'light' | 'dark' | 'system'. Pilihan disimpan di localStorage('theme');
 * tanpa pilihan, mengikuti preferensi sistem. Kelas awal dipasang oleh
 * partial theme-script di <head> agar tidak ada kedipan.
 */
Alpine.store('theme', {
    mode: localStorage.getItem('theme') ?? 'system',

    get isDark() {
        return document.documentElement.classList.contains('dark');
    },

    apply() {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const dark = this.mode === 'dark' || (this.mode === 'system' && prefersDark);
        document.documentElement.classList.toggle('dark', dark);
    },

    set(mode) {
        this.mode = mode;
        if (mode === 'system') {
            localStorage.removeItem('theme');
        } else {
            localStorage.setItem('theme', mode);
        }
        this.apply();
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { dark: this.isDark } }));
    },

    toggle() {
        this.set(this.isDark ? 'light' : 'dark');
    },
});

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (Alpine.store('theme').mode === 'system') {
        Alpine.store('theme').apply();
    }
});

/**
 * Toast notifikasi. Pemakaian dari mana saja:
 *   window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: '...' } }))
 * atau di Alpine: $dispatch('toast', { type: 'error', message: '...' })
 */
Alpine.data('toastStack', (initial = []) => ({
    items: [],
    nextId: 1,

    init() {
        initial.forEach((toast) => this.push(toast));
    },

    push({ type = 'success', message = '', title = null, duration = 5000 }) {
        const id = this.nextId++;
        this.items.push({ id, type, message, title, visible: true });
        if (duration > 0) {
            setTimeout(() => this.dismiss(id), duration);
        }
    },

    dismiss(id) {
        const toast = this.items.find((item) => item.id === id);
        if (toast) {
            toast.visible = false;
            setTimeout(() => {
                this.items = this.items.filter((item) => item.id !== id);
            }, 200);
        }
    },
}));

/**
 * Modal konfirmasi hapus global. Buka dengan:
 *   $dispatch('confirm-delete', { action: url, title: '...', message: '...' })
 */
Alpine.data('confirmDialog', () => ({
    open: false,
    submitting: false,
    action: '',
    title: '',
    message: '',
    confirmLabel: 'Hapus',

    show({ action, title = 'Hapus data?', message = '', confirmLabel = 'Hapus' }) {
        this.action = action;
        this.title = title;
        this.message = message;
        this.confirmLabel = confirmLabel;
        this.submitting = false;
        this.open = true;
    },

    close() {
        if (!this.submitting) {
            this.open = false;
        }
    },

    submit() {
        this.submitting = true;
        this.$refs.form.submit();
    },
}));

/**
 * Status sidebar: collapse (desktop, tersimpan) dan drawer (mobile).
 * Lebar sidebar digerakkan CSS lewat atribut data-sidebar pada <html>
 * (variant `sidebar-collapsed:`), sehingga tidak berkedip saat dimuat.
 */
Alpine.data('appShell', () => ({
    collapsed: document.documentElement.dataset.sidebar === 'collapsed',
    drawerOpen: false,

    toggleCollapse() {
        this.collapsed = !this.collapsed;
        document.documentElement.dataset.sidebar = this.collapsed ? 'collapsed' : 'expanded';
        localStorage.setItem('sidebar-collapsed', this.collapsed ? '1' : '0');
    },
}));

window.Alpine = Alpine;
Alpine.start();
