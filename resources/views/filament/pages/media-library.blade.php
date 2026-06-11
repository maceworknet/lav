<x-filament-panels::page>
<div
    x-data="mediaLibrary()"
    x-init="loadMedia()"
    class="ml-root"
    @dragover.prevent="dragActive = true"
    @dragleave.prevent="dragActive = false"
    @drop.prevent="handleDrop($event)"
>
    <style>
        .ml-root { position: relative; }
        .ml-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; margin-bottom: 1rem; }
        .ml-search { position: relative; flex: 1 1 220px; max-width: 320px; }
        .ml-search input { width: 100%; padding: .55rem .9rem .55rem 2.4rem; border: 1px solid rgb(228 228 231); border-radius: .5rem; font-size: .875rem; background: #fff; outline: none; transition: box-shadow .15s, border-color .15s; }
        .dark .ml-search input { background: rgb(24 24 27); border-color: rgb(63 63 70); color: #fff; }
        .ml-search input:focus { border-color: rgb(161 161 170); box-shadow: 0 0 0 3px rgb(228 228 231 / .6); }
        .ml-search svg { position: absolute; left: .8rem; top: 50%; transform: translateY(-50%); width: 1rem; height: 1rem; color: rgb(161 161 170); }
        .ml-btn { display: inline-flex; align-items: center; gap: .45rem; padding: .55rem 1rem; border-radius: .5rem; font-size: .8125rem; font-weight: 600; cursor: pointer; border: 1px solid transparent; transition: background .15s, border-color .15s, color .15s; white-space: nowrap; }
        .ml-btn-primary { background: rgb(24 24 27); color: #fff; }
        .ml-btn-primary:hover { background: rgb(39 39 42); }
        .dark .ml-btn-primary { background: #fff; color: rgb(24 24 27); }
        .ml-btn-outline { background: #fff; border-color: rgb(228 228 231); color: rgb(63 63 70); }
        .ml-btn-outline:hover { background: rgb(244 244 245); }
        .dark .ml-btn-outline { background: rgb(24 24 27); border-color: rgb(63 63 70); color: rgb(212 212 216); }
        .ml-btn-danger { background: rgb(220 38 38); color: #fff; }
        .ml-btn-danger:hover { background: rgb(185 28 28); }
        .ml-btn[disabled] { opacity: .5; pointer-events: none; }
        .ml-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: .9rem; }
        .ml-item { position: relative; aspect-ratio: 1/1; border-radius: .6rem; overflow: hidden; border: 1px solid rgb(228 228 231); background: #fff; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
        .dark .ml-item { background: rgb(24 24 27); border-color: rgb(63 63 70); }
        .ml-item:hover { border-color: rgb(161 161 170); box-shadow: 0 1px 3px rgb(0 0 0 / .1); }
        .ml-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .ml-item.selected { border-color: rgb(24 24 27); box-shadow: 0 0 0 2.5px rgb(24 24 27); }
        .dark .ml-item.selected { border-color: #fff; box-shadow: 0 0 0 2.5px #fff; }
        .ml-check { position: absolute; top: .45rem; left: .45rem; width: 1.35rem; height: 1.35rem; border-radius: .35rem; background: #fff; border: 1.5px solid rgb(161 161 170); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity .12s; }
        .ml-item:hover .ml-check, .ml-item.selected .ml-check, .ml-root.select-mode .ml-check { opacity: 1; }
        .ml-item.selected .ml-check { background: rgb(24 24 27); border-color: rgb(24 24 27); color: #fff; }
        .ml-name { position: absolute; inset-inline: 0; bottom: 0; background: linear-gradient(transparent, rgb(0 0 0 / .65)); color: #fff; font-size: .6rem; font-weight: 600; padding: 1.2rem .5rem .4rem; opacity: 0; transition: opacity .15s; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ml-item:hover .ml-name { opacity: 1; }
        .ml-dropzone { border: 2px dashed rgb(212 212 216); border-radius: .75rem; padding: 2.2rem 1rem; text-align: center; color: rgb(113 113 122); margin-bottom: 1.1rem; transition: border-color .15s, background .15s; font-size: .8125rem; }
        .ml-dropzone.active { border-color: rgb(24 24 27); background: rgb(244 244 245); }
        .dark .ml-dropzone { border-color: rgb(63 63 70); }
        .dark .ml-dropzone.active { border-color: #fff; background: rgb(39 39 42); }
        .ml-overlay { position: fixed; inset: 0; z-index: 60; background: rgb(24 24 27 / .55); display: flex; align-items: center; justify-content: center; pointer-events: none; }
        .ml-overlay-box { background: #fff; border-radius: 1rem; padding: 2.5rem 3.5rem; text-align: center; border: 2px dashed rgb(24 24 27); font-weight: 700; color: rgb(24 24 27); }
        .ml-detail { position: fixed; top: 0; right: 0; bottom: 0; width: 340px; max-width: 90vw; background: #fff; border-left: 1px solid rgb(228 228 231); z-index: 70; padding: 1.4rem; overflow-y: auto; box-shadow: -8px 0 24px rgb(0 0 0 / .08); }
        .dark .ml-detail { background: rgb(24 24 27); border-color: rgb(63 63 70); color: #fff; }
        .ml-detail img { width: 100%; border-radius: .6rem; border: 1px solid rgb(228 228 231); margin-bottom: 1rem; }
        .ml-detail label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: rgb(113 113 122); display: block; margin-bottom: .25rem; }
        .ml-detail .ml-field { margin-bottom: .9rem; }
        .ml-detail input[type=text] { width: 100%; padding: .5rem .7rem; border: 1px solid rgb(228 228 231); border-radius: .5rem; font-size: .8125rem; background: #fff; }
        .dark .ml-detail input[type=text] { background: rgb(39 39 42); border-color: rgb(63 63 70); color: #fff; }
        .ml-meta { font-size: .8125rem; color: rgb(63 63 70); word-break: break-all; }
        .dark .ml-meta { color: rgb(212 212 216); }
        .ml-progress { height: .4rem; border-radius: 999px; background: rgb(228 228 231); overflow: hidden; margin-top: .6rem; }
        .ml-progress > div { height: 100%; background: rgb(24 24 27); transition: width .2s; }
        .ml-count-badge { font-size: .75rem; font-weight: 600; color: rgb(113 113 122); }
        .ml-empty { text-align: center; padding: 4rem 1rem; color: rgb(161 161 170); font-size: .875rem; }
    </style>

    <!-- Sürükle bırak tam ekran kaplaması -->
    <div x-show="dragActive" class="ml-overlay" x-transition.opacity>
        <div class="ml-overlay-box">
            Dosyaları buraya bırakın
            <div style="font-size:.75rem; font-weight:500; color:rgb(113 113 122); margin-top:.4rem;">Görseller medya kütüphanesine yüklenecek</div>
        </div>
    </div>

    <!-- Araç çubuğu -->
    <div class="ml-toolbar">
        <div class="ml-search">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model.debounce.350ms="search" @input="page = 1" placeholder="Medya ara..." x-effect="search; loadMedia()">
        </div>

        <button type="button" class="ml-btn ml-btn-primary" @click="$refs.fileInput.click()" :disabled="uploading">
            <svg style="width:1rem;height:1rem" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
            <span x-text="uploading ? 'Yükleniyor...' : 'Dosya Yükle'"></span>
        </button>
        <input type="file" x-ref="fileInput" class="hidden" style="display:none" multiple accept="image/*" @change="uploadFiles($event.target.files); $event.target.value = ''">

        <button type="button" class="ml-btn ml-btn-outline" @click="toggleSelectMode()">
            <span x-text="selectMode ? 'Seçimi İptal Et' : 'Toplu Seç'"></span>
        </button>

        <template x-if="selected.length > 0">
            <button type="button" class="ml-btn ml-btn-danger" @click="confirmDelete()">
                <svg style="width:1rem;height:1rem" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                <span x-text="'Seçilenleri Sil (' + selected.length + ')'"></span>
            </button>
        </template>

        <span class="ml-count-badge" x-show="!loading" x-text="total + ' dosya'" style="margin-left:auto"></span>
    </div>

    <!-- Yükleme ilerleme çubuğu -->
    <template x-if="uploading">
        <div style="margin-bottom:1rem">
            <div style="font-size:.75rem; font-weight:600; color:rgb(113 113 122)" x-text="'Yükleniyor: ' + uploadDone + ' / ' + uploadTotal"></div>
            <div class="ml-progress"><div :style="'width:' + (uploadTotal ? Math.round(uploadDone / uploadTotal * 100) : 0) + '%'"></div></div>
        </div>
    </template>

    <!-- Boş durumda büyük dropzone -->
    <div class="ml-dropzone" :class="dragActive ? 'active' : ''" x-show="!loading && items.length === 0 && !search">
        <div style="font-size:2rem; margin-bottom:.4rem;">🖼️</div>
        <strong>Görselleri buraya sürükleyip bırakın</strong>
        <div style="margin-top:.25rem;">veya yukarıdan "Dosya Yükle" butonunu kullanın</div>
    </div>

    <!-- Grid -->
    <div x-show="loading" class="ml-empty">Yükleniyor...</div>
    <div x-show="!loading && items.length === 0 && search" class="ml-empty">Aramanızla eşleşen dosya bulunamadı.</div>

    <div class="ml-grid" :class="selectMode ? 'select-mode' : ''" x-show="!loading">
        <template x-for="item in items" :key="item.id">
            <div
                class="ml-item"
                :class="isSelected(item.id) ? 'selected' : ''"
                @click="handleItemClick(item, $event)"
            >
                <img :src="item.url" loading="lazy" :alt="item.name">
                <div class="ml-check" @click.stop="toggleSelect(item.id)">
                    <svg x-show="isSelected(item.id)" style="width:.85rem;height:.85rem" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="ml-name" x-text="item.name"></div>
            </div>
        </template>
    </div>

    <!-- Daha fazla yükle -->
    <div style="text-align:center; margin-top:1.25rem;" x-show="!loading && page < lastPage">
        <button type="button" class="ml-btn ml-btn-outline" @click="loadMore()">Daha Fazla Yükle</button>
    </div>

    <!-- Detay paneli -->
    <template x-if="detail">
        <div class="ml-detail" @click.outside="detail = null">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <strong style="font-size:.9rem;">Dosya Detayı</strong>
                <button type="button" class="ml-btn ml-btn-outline" style="padding:.3rem .6rem" @click="detail = null">✕</button>
            </div>
            <img :src="detail.url" :alt="detail.name">
            <div class="ml-field">
                <label>Dosya Adı</label>
                <input type="text" x-model="detail.name" @keydown.enter.prevent="renameDetail()">
            </div>
            <div class="ml-field">
                <label>Dosya Yolu</label>
                <div class="ml-meta" x-text="detail.file_path"></div>
            </div>
            <div class="ml-field">
                <label>Boyut / Tür</label>
                <div class="ml-meta" x-text="(detail.file_size ? (detail.file_size/1024).toFixed(1) + ' KB' : '-') + ' · ' + (detail.file_type || '-')"></div>
            </div>
            <div class="ml-field">
                <label>Yüklenme</label>
                <div class="ml-meta" x-text="new Date(detail.created_at).toLocaleString('tr-TR')"></div>
            </div>
            <div style="display:flex; flex-direction:column; gap:.5rem; margin-top:1.2rem;">
                <button type="button" class="ml-btn ml-btn-primary" style="justify-content:center" @click="renameDetail()">Adı Kaydet</button>
                <button type="button" class="ml-btn ml-btn-outline" style="justify-content:center" @click="copyUrl(detail.url)">
                    <span x-text="copied ? 'Kopyalandı ✓' : 'URL Kopyala'"></span>
                </button>
                <button type="button" class="ml-btn ml-btn-danger" style="justify-content:center" @click="deleteSingle(detail.id)">Dosyayı Sil</button>
            </div>
        </div>
    </template>

    <script>
        function mediaLibrary() {
            return {
                items: [],
                total: 0,
                page: 1,
                lastPage: 1,
                perPage: 40,
                search: '',
                loading: false,
                uploading: false,
                uploadDone: 0,
                uploadTotal: 0,
                dragActive: false,
                selectMode: false,
                selected: [],
                detail: null,
                copied: false,
                csrf() {
                    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                },
                async loadMedia(append = false) {
                    if (!append) { this.loading = true; this.page = 1; }
                    try {
                        const res = await fetch(`/admin/api/media?per_page=${this.perPage}&page=${this.page}&search=${encodeURIComponent(this.search)}`);
                        const data = await res.json();
                        this.items = append ? this.items.concat(data.media) : data.media;
                        this.total = data.total ?? data.media.length;
                        this.lastPage = data.last_page ?? 1;
                    } catch (e) { console.error(e); }
                    this.loading = false;
                },
                loadMore() { this.page++; this.loadMedia(true); },
                toggleSelectMode() {
                    this.selectMode = !this.selectMode;
                    if (!this.selectMode) this.selected = [];
                },
                isSelected(id) { return this.selected.includes(id); },
                toggleSelect(id) {
                    this.isSelected(id)
                        ? this.selected = this.selected.filter(i => i !== id)
                        : this.selected.push(id);
                },
                handleItemClick(item, e) {
                    if (this.selectMode || e.ctrlKey || e.metaKey) {
                        this.toggleSelect(item.id);
                    } else {
                        this.detail = JSON.parse(JSON.stringify(item));
                    }
                },
                handleDrop(e) {
                    this.dragActive = false;
                    if (e.dataTransfer?.files?.length) this.uploadFiles(e.dataTransfer.files);
                },
                async uploadFiles(files) {
                    const list = Array.from(files).filter(f => f.type.startsWith('image/'));
                    if (!list.length) return;
                    this.uploading = true;
                    this.uploadTotal = list.length;
                    this.uploadDone = 0;
                    for (const file of list) {
                        const fd = new FormData();
                        fd.append('file', file);
                        try {
                            const res = await fetch('/admin/api/media/upload', {
                                method: 'POST',
                                body: fd,
                                headers: { 'X-CSRF-TOKEN': this.csrf() }
                            });
                            const data = await res.json();
                            if (!data.success) console.warn('Yükleme hatası:', data.message);
                        } catch (err) { console.error(err); }
                        this.uploadDone++;
                    }
                    this.uploading = false;
                    await this.loadMedia();
                },
                confirmDelete() {
                    if (!this.selected.length) return;
                    if (!confirm(this.selected.length + ' dosya kalıcı olarak silinecek. Emin misiniz?')) return;
                    this.deleteIds(this.selected);
                },
                deleteSingle(id) {
                    if (!confirm('Bu dosya kalıcı olarak silinecek. Emin misiniz?')) return;
                    this.deleteIds([id]);
                },
                async deleteIds(ids) {
                    try {
                        const res = await fetch('/admin/api/media/delete', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                            body: JSON.stringify({ ids })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.selected = [];
                            this.detail = null;
                            await this.loadMedia();
                        }
                    } catch (e) { console.error(e); }
                },
                async renameDetail() {
                    if (!this.detail) return;
                    try {
                        const res = await fetch(`/admin/api/media/${this.detail.id}/rename`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                            body: JSON.stringify({ name: this.detail.name })
                        });
                        const data = await res.json();
                        if (data.success) {
                            const idx = this.items.findIndex(i => i.id === this.detail.id);
                            if (idx > -1) this.items[idx].name = data.media.name;
                        }
                    } catch (e) { console.error(e); }
                },
                copyUrl(url) {
                    navigator.clipboard.writeText(url).then(() => {
                        this.copied = true;
                        setTimeout(() => this.copied = false, 1500);
                    });
                }
            };
        }
    </script>
</div>
</x-filament-panels::page>
