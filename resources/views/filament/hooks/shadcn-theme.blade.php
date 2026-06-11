{{--
    shadcn/ui tasarım sistemi — Filament admin paneli için tam tema.
    shadcn token'ları birebir uygulanır: Inter font, 14px taban yazı boyutu,
    --radius: 0.5rem sistemi, zinc nötr paleti, ince kenarlık + hafif gölge dili.
    Sınıf adları Filament v5.6 (public/css/filament/filament/app.css) ile doğrulanmıştır.
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    /* ============ shadcn/ui design tokens ============ */
    :root {
        --background: 0 0% 100%;
        --foreground: 240 10% 3.9%;
        --card: 0 0% 100%;
        --card-foreground: 240 10% 3.9%;
        --muted: 240 4.8% 95.9%;
        --muted-foreground: 240 3.8% 46.1%;
        --border: 240 5.9% 90%;
        --input: 240 5.9% 90%;
        --primary: 240 5.9% 10%;
        --primary-foreground: 0 0% 98%;
        --accent: 240 4.8% 95.9%;
        --accent-foreground: 240 5.9% 10%;
        --destructive: 0 84.2% 60.2%;
        --ring: 240 5% 64.9%;
        --radius: 0.5rem;
        --sidebar: 0 0% 98%;
        --sidebar-border: 240 5.9% 90%;
        --shadow-xs: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-sm: 0 1px 3px 0 rgb(0 0 0 / 0.07), 0 1px 2px -1px rgb(0 0 0 / 0.07);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    }
    .dark {
        --background: 240 10% 3.9%;
        --foreground: 0 0% 98%;
        --card: 240 10% 5.9%;
        --card-foreground: 0 0% 98%;
        --muted: 240 3.7% 15.9%;
        --muted-foreground: 240 5% 64.9%;
        --border: 240 3.7% 15.9%;
        --input: 240 3.7% 15.9%;
        --primary: 0 0% 98%;
        --primary-foreground: 240 5.9% 10%;
        --accent: 240 3.7% 15.9%;
        --accent-foreground: 0 0% 98%;
        --destructive: 0 62.8% 50.6%;
        --ring: 240 4.9% 83.9%;
        --sidebar: 240 10% 5.9%;
        --sidebar-border: 240 3.7% 15.9%;
    }

    /* ============ Tipografi ============ */
    body, .fi-body {
        font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif !important;
        font-size: 0.875rem !important;          /* shadcn text-sm taban */
        line-height: 1.5 !important;
        letter-spacing: -0.006em;
        font-feature-settings: "rlig" 1, "calt" 1;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;
        color: hsl(var(--foreground));
    }

    /* Sayfa başlığı: shadcn dashboard başlık ölçeği */
    .fi-header-heading {
        font-size: 1.5rem !important;            /* text-2xl */
        font-weight: 600 !important;             /* semibold */
        letter-spacing: -0.025em !important;     /* tracking-tight */
        line-height: 2rem !important;
        color: hsl(var(--foreground)) !important;
    }
    .fi-header-subheading {
        font-size: 0.875rem !important;
        color: hsl(var(--muted-foreground)) !important;
    }

    /* Section başlıkları */
    .fi-section-header-heading {
        font-size: 1rem !important;              /* text-base */
        font-weight: 600 !important;
        letter-spacing: -0.015em !important;
        color: hsl(var(--foreground)) !important;
    }
    .fi-section-header-description {
        font-size: 0.8125rem !important;
        color: hsl(var(--muted-foreground)) !important;
    }

    /* Form etiketleri: text-sm font-medium */
    .fi-fo-field-label, .fi-fo-field-label-content {
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        color: hsl(var(--foreground)) !important;
    }

    /* ============ Zemin ============ */
    .fi-main-ctn, .fi-body, .fi-main {
        background-color: hsl(var(--background)) !important;
    }

    /* ============ Kartlar / Section'lar ============ */
    .fi-section, .fi-ta-ctn, .fi-wi-stats-overview-stat, .fi-fo-tabs {
        border-radius: 0.75rem !important;        /* shadcn card: rounded-xl */
        border: 1px solid hsl(var(--border)) !important;
        background-color: hsl(var(--card)) !important;
        box-shadow: var(--shadow-xs) !important;
    }

    /* İstatistik kartları */
    .fi-wi-stats-overview-stat-label {
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        color: hsl(var(--muted-foreground)) !important;
    }
    .fi-wi-stats-overview-stat-value {
        font-size: 1.5rem !important;
        font-weight: 700 !important;
        letter-spacing: -0.025em !important;
    }
    .fi-wi-stats-overview-stat-description {
        font-size: 0.75rem !important;
    }

    /* ============ Butonlar ============ */
    .fi-btn {
        border-radius: calc(var(--radius) - 2px) !important;   /* rounded-md */
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        letter-spacing: 0 !important;
        text-transform: none !important;
        min-height: 2.25rem;                                    /* h-9 */
        padding-block: 0.375rem;
        padding-inline: 1rem;                                   /* px-4 */
        box-shadow: var(--shadow-xs);
        transition: background-color .15s ease, border-color .15s ease, color .15s ease, opacity .15s ease;
    }
    .fi-btn.fi-size-sm { min-height: 2rem; padding-inline: 0.75rem; font-size: 0.8125rem !important; }
    .fi-btn.fi-size-lg { min-height: 2.5rem; padding-inline: 2rem; }

    /* Birincil buton: shadcn "default" — zinc-900 */
    .fi-btn.fi-color-primary {
        background-color: hsl(var(--primary)) !important;
        color: hsl(var(--primary-foreground)) !important;
        border: 1px solid hsl(var(--primary)) !important;
    }
    .fi-btn.fi-color-primary:hover { opacity: .9; }
    .fi-btn.fi-color-primary .fi-icon { color: hsl(var(--primary-foreground)) !important; }

    /* Gri/ikincil buton: shadcn "outline" */
    .fi-btn.fi-color-gray {
        background-color: hsl(var(--background)) !important;
        color: hsl(var(--foreground)) !important;
        border: 1px solid hsl(var(--border)) !important;
    }
    .fi-btn.fi-color-gray:hover {
        background-color: hsl(var(--accent)) !important;
        color: hsl(var(--accent-foreground)) !important;
    }

    .fi-btn.fi-color-danger {
        background-color: hsl(var(--destructive)) !important;
        color: #fff !important;
        border: 1px solid hsl(var(--destructive)) !important;
    }
    .fi-btn.fi-color-danger:hover { opacity: .9; }

    /* Link butonlar gölgesiz */
    .fi-link { box-shadow: none !important; font-weight: 500 !important; font-size: 0.875rem !important; }

    /* Odak halkası: shadcn ring */
    .fi-btn:focus-visible, .fi-link:focus-visible {
        outline: none !important;
        box-shadow: 0 0 0 2px hsl(var(--background)), 0 0 0 4px hsl(var(--ring)) !important;
    }

    /* ============ Giriş alanları ============ */
    .fi-input-wrp {
        border-radius: calc(var(--radius) - 2px) !important;
        border: 1px solid hsl(var(--input)) !important;
        background-color: transparent !important;
        box-shadow: var(--shadow-xs) !important;
        --tw-ring-color: transparent !important;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .fi-input-wrp:not(:has(textarea)) { min-height: 2.25rem; }   /* h-9 */
    .fi-input-wrp input, .fi-input-wrp select {
        font-size: 0.875rem !important;
        padding-block: 0.375rem !important;
    }
    .fi-input-wrp:focus-within {
        border-color: hsl(var(--ring)) !important;
        box-shadow: 0 0 0 1px hsl(var(--ring)) !important;
    }
    .fi-fo-textarea textarea, textarea.fi-input {
        border-radius: calc(var(--radius) - 2px) !important;
        font-size: 0.875rem !important;
    }
    .fi-input::placeholder, .fi-input-wrp input::placeholder {
        color: hsl(var(--muted-foreground)) !important;
        opacity: .8;
    }

    /* Checkbox / Radio / Toggle vurgu rengi */
    .fi-checkbox-input, .fi-radio-input {
        border-radius: calc(var(--radius) - 4px) !important;
    }
    .fi-checkbox-input:checked, .fi-radio-input:checked {
        background-color: hsl(var(--primary)) !important;
        border-color: hsl(var(--primary)) !important;
    }
    .fi-toggle button[aria-checked="true"], button.fi-toggle[aria-checked="true"] {
        background-color: hsl(var(--primary)) !important;
    }

    /* ============ Sidebar: shadcn sidebar bileşeni ============ */
    .fi-sidebar {
        background-color: hsl(var(--sidebar)) !important;
        border-inline-end: 1px solid hsl(var(--sidebar-border)) !important;
    }
    .fi-sidebar-header {
        background-color: hsl(var(--sidebar)) !important;
        box-shadow: none !important;
        border-bottom: 1px solid hsl(var(--sidebar-border));
    }
    .fi-sidebar-item-btn {
        border-radius: calc(var(--radius) - 2px) !important;
        min-height: 2rem !important;                 /* h-8 */
        padding-block: 0.25rem !important;
        transition: background-color .12s ease, color .12s ease;
    }
    .fi-sidebar-item-label {
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        color: hsl(var(--foreground) / .75);
    }
    .fi-sidebar-item-btn:hover {
        background-color: hsl(var(--accent)) !important;
    }
    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn,
    .fi-sidebar-item-btn[aria-current="page"] {
        background-color: hsl(var(--accent)) !important;
        box-shadow: none !important;
    }
    .fi-sidebar-item.fi-active .fi-sidebar-item-label,
    .fi-sidebar-item-btn[aria-current="page"] .fi-sidebar-item-label {
        color: hsl(var(--accent-foreground)) !important;
        font-weight: 600 !important;
    }
    .fi-sidebar-item .fi-icon {
        width: 1.1rem !important;
        height: 1.1rem !important;
        color: hsl(var(--muted-foreground)) !important;
    }
    .fi-sidebar-item.fi-active .fi-icon {
        color: hsl(var(--accent-foreground)) !important;
    }
    .fi-sidebar-group-label {
        font-size: 0.6875rem !important;             /* shadcn sidebar group label */
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: hsl(var(--muted-foreground)) !important;
        font-weight: 600 !important;
    }
    .fi-sidebar-group-btn { border-radius: calc(var(--radius) - 2px) !important; }

    /* ============ Topbar ============ */
    .fi-topbar > nav, .fi-topbar {
        background-color: hsl(var(--background) / .85) !important;
        backdrop-filter: blur(8px);
        border-bottom: 1px solid hsl(var(--border)) !important;
        box-shadow: none !important;
    }

    /* ============ Tablolar: shadcn table ============ */
    .fi-ta-header-cell {
        font-size: 0.8125rem !important;
        font-weight: 500 !important;
        text-transform: none !important;
        letter-spacing: 0 !important;
        color: hsl(var(--muted-foreground)) !important;
        height: 2.5rem;                              /* h-10 */
    }
    .fi-ta-cell { font-size: 0.875rem !important; }
    .fi-ta-row { transition: background-color .12s ease; }
    .fi-ta-row:hover { background-color: hsl(var(--muted) / .5) !important; }
    .fi-ta-text { font-size: 0.875rem !important; }

    /* ============ Rozetler: shadcn badge ============ */
    .fi-badge {
        border-radius: calc(var(--radius) - 2px) !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        padding-inline: 0.625rem !important;        /* px-2.5 */
        letter-spacing: 0 !important;
    }

    /* ============ Sekmeler: shadcn tabs ============ */
    .fi-tabs {
        border-radius: calc(var(--radius) - 2px) !important;
        background-color: hsl(var(--muted)) !important;
        padding: 0.25rem !important;
        gap: 0.125rem !important;
        border: none !important;
    }
    .fi-tabs-item {
        border-radius: calc(var(--radius) - 4px) !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        color: hsl(var(--muted-foreground)) !important;
        padding-block: 0.3rem !important;
    }
    .fi-tabs-item-label { font-size: 0.875rem !important; font-weight: 500 !important; }
    .fi-tabs-item.fi-active {
        background-color: hsl(var(--background)) !important;
        color: hsl(var(--foreground)) !important;
        box-shadow: var(--shadow-xs) !important;
    }
    .fi-tabs-item:hover:not(.fi-active) {
        color: hsl(var(--foreground)) !important;
        background-color: transparent !important;
    }

    /* ============ Modal ve dropdown ============ */
    .fi-modal-window {
        border-radius: 0.75rem !important;
        border: 1px solid hsl(var(--border)) !important;
        box-shadow: var(--shadow-lg) !important;
    }
    .fi-modal-heading {
        font-size: 1.125rem !important;
        font-weight: 600 !important;
        letter-spacing: -0.02em !important;
    }
    .fi-dropdown-panel {
        border-radius: calc(var(--radius) + 2px) !important;
        border: 1px solid hsl(var(--border)) !important;
        box-shadow: var(--shadow-lg) !important;
        padding: 0.25rem !important;
    }
    .fi-dropdown-list-item {
        border-radius: calc(var(--radius) - 4px) !important;
        font-size: 0.875rem !important;
        font-weight: 400 !important;
    }
    .fi-dropdown-list-item:hover { background-color: hsl(var(--accent)) !important; }

    /* ============ Bildirimler ============ */
    .fi-no-notification {
        border-radius: 0.75rem !important;
        border: 1px solid hsl(var(--border)) !important;
        box-shadow: var(--shadow-lg) !important;
    }
    .fi-no-notification-title { font-weight: 600 !important; font-size: 0.875rem !important; }

    /* ============ Pagination ============ */
    .fi-pagination-item {
        border-radius: calc(var(--radius) - 2px) !important;
        font-size: 0.875rem !important;
    }

    /* ============ Breadcrumbs ============ */
    .fi-breadcrumbs li, .fi-breadcrumbs a, .fi-breadcrumbs span {
        font-size: 0.875rem !important;
        color: hsl(var(--muted-foreground));
        font-weight: 400 !important;
    }
    .fi-breadcrumbs li:last-child span {
        color: hsl(var(--foreground)) !important;
        font-weight: 500 !important;
    }

    /* ============ Repeater / Builder ============ */
    .fi-fo-repeater-item {
        border-radius: var(--radius) !important;
        border: 1px solid hsl(var(--border)) !important;
        box-shadow: var(--shadow-xs) !important;
    }

    /* ============ Scrollbar (ince, nötr) ============ */
    .fi-sidebar-nav::-webkit-scrollbar, .fi-main::-webkit-scrollbar { width: 8px; height: 8px; }
    .fi-sidebar-nav::-webkit-scrollbar-thumb, .fi-main::-webkit-scrollbar-thumb {
        background-color: hsl(var(--border));
        border-radius: 999px;
    }
    .fi-sidebar-nav::-webkit-scrollbar-track, .fi-main::-webkit-scrollbar-track { background: transparent; }

    /* ============ Giriş (login) sayfası ============ */
    .fi-simple-layout { background-color: hsl(var(--muted) / .4) !important; }
    .fi-simple-main {
        border-radius: 0.75rem !important;
        border: 1px solid hsl(var(--border)) !important;
        box-shadow: var(--shadow-sm) !important;
    }
</style>
