{{-- shadcn/ui esintili panel teması: nötr zinc tonları, ince kenarlıklar, yumuşak köşeler --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --shadcn-border: 240 5.9% 90%;
        --shadcn-radius: 0.5rem;
    }

    body, .fi-body {
        font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important;
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }

    /* Sayfa zemini: çok hafif gri, içerik beyaz kartlar */
    .fi-main-ctn, .fi-body {
        background-color: rgb(250 250 250);
    }
    .dark .fi-main-ctn, .dark .fi-body {
        background-color: rgb(9 9 11);
    }

    /* Kartlar / section'lar: ince kenarlık, yumuşak gölge, lg radius */
    .fi-section, .fi-ta-ctn, .fi-wi-stats-overview-stat, .fi-fo-tabs, .fi-wi-widget > .fi-section {
        border-radius: 0.75rem !important;
        border: 1px solid hsl(var(--shadcn-border)) !important;
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.04) !important;
        background-color: #fff;
    }
    .dark .fi-section, .dark .fi-ta-ctn, .dark .fi-wi-stats-overview-stat, .dark .fi-fo-tabs {
        border-color: rgb(39 39 42) !important;
        background-color: rgb(24 24 27);
    }

    /* Butonlar: shadcn radius ve ağırlık, uppercase yok */
    .fi-btn {
        border-radius: var(--shadcn-radius) !important;
        font-weight: 500 !important;
        letter-spacing: 0 !important;
        text-transform: none !important;
        transition: background-color .15s ease, border-color .15s ease, color .15s ease;
    }

    /* Birincil buton: zinc-900 (shadcn "default") */
    .fi-btn-color-primary {
        background-color: rgb(24 24 27) !important;
        color: #fff !important;
    }
    .fi-btn-color-primary:hover {
        background-color: rgb(39 39 42) !important;
    }
    .dark .fi-btn-color-primary {
        background-color: #fff !important;
        color: rgb(24 24 27) !important;
    }
    .dark .fi-btn-color-primary:hover {
        background-color: rgb(228 228 231) !important;
    }

    /* Giriş alanları */
    .fi-input-wrp, .fi-select-input, .fi-fo-textarea textarea {
        border-radius: var(--shadcn-radius) !important;
    }
    .fi-input-wrp {
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.03) !important;
    }
    .fi-input-wrp:focus-within {
        box-shadow: 0 0 0 3px rgb(24 24 27 / 0.12) !important;
    }

    /* Sidebar: beyaz, ince sağ kenarlık; aktif öğe zinc vurgusu */
    .fi-sidebar {
        background-color: #fff !important;
        border-inline-end: 1px solid hsl(var(--shadcn-border)) !important;
    }
    .dark .fi-sidebar {
        background-color: rgb(24 24 27) !important;
        border-inline-end-color: rgb(39 39 42) !important;
    }
    .fi-sidebar-item-button {
        border-radius: var(--shadcn-radius) !important;
    }
    .fi-sidebar-item.fi-active > .fi-sidebar-item-button,
    .fi-sidebar-item-button[aria-current="page"] {
        background-color: rgb(244 244 245) !important;
    }
    .dark .fi-sidebar-item.fi-active > .fi-sidebar-item-button,
    .dark .fi-sidebar-item-button[aria-current="page"] {
        background-color: rgb(39 39 42) !important;
    }
    .fi-sidebar-group-label {
        font-size: 0.7rem !important;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: rgb(113 113 122) !important;
        font-weight: 600 !important;
    }

    /* Topbar */
    .fi-topbar > nav, .fi-topbar {
        background-color: rgb(255 255 255 / 0.85) !important;
        backdrop-filter: blur(8px);
        border-bottom: 1px solid hsl(var(--shadcn-border)) !important;
        box-shadow: none !important;
    }
    .dark .fi-topbar > nav, .dark .fi-topbar {
        background-color: rgb(24 24 27 / 0.85) !important;
        border-bottom-color: rgb(39 39 42) !important;
    }

    /* Tablolar */
    .fi-ta-header-cell {
        font-size: 0.75rem !important;
        text-transform: none !important;
        color: rgb(113 113 122) !important;
        font-weight: 600 !important;
    }
    .fi-ta-row:hover {
        background-color: rgb(250 250 250) !important;
    }
    .dark .fi-ta-row:hover {
        background-color: rgb(39 39 42 / 0.5) !important;
    }

    /* Rozetler */
    .fi-badge {
        border-radius: calc(var(--shadcn-radius) - 0.125rem) !important;
        font-weight: 500 !important;
    }

    /* Modallar ve dropdownlar */
    .fi-modal-window, .fi-dropdown-panel {
        border-radius: 0.75rem !important;
        border: 1px solid hsl(var(--shadcn-border)) !important;
        box-shadow: 0 10px 30px -10px rgb(0 0 0 / 0.15) !important;
    }
    .dark .fi-modal-window, .dark .fi-dropdown-panel {
        border-color: rgb(39 39 42) !important;
    }

    /* Sekmeler */
    .fi-tabs-tab {
        border-radius: calc(var(--shadcn-radius) - 0.125rem) !important;
        font-weight: 500 !important;
    }

    /* Başlıklar */
    .fi-header-heading {
        letter-spacing: -0.025em !important;
        font-weight: 700 !important;
    }
</style>
