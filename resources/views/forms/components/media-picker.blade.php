<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @once
    <style>
        /* MediaPicker — admin panelde Tailwind utility'leri bulunmadığından kendi stilleri ile çalışır */
        .mp-row { display: flex; align-items: center; gap: 1rem; }
        .mp-preview { position: relative; width: 7rem; height: 7rem; border-radius: .75rem; overflow: hidden; background: rgb(250 250 250); border: 1px solid rgb(228 228 231); box-shadow: 0 1px 2px rgb(0 0 0 / .05); flex-shrink: 0; }
        .mp-preview img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .mp-preview-overlay { position: absolute; inset: 0; background: rgb(24 24 27 / .6); opacity: 0; display: flex; align-items: center; justify-content: center; gap: .5rem; transition: opacity .2s; }
        .mp-preview:hover .mp-preview-overlay { opacity: 1; }
        .mp-ov-btn { padding: .4rem; border-radius: .5rem; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .mp-ov-btn svg { width: 1rem; height: 1rem; }
        .mp-ov-edit { background: #fff; color: rgb(24 24 27); }
        .mp-ov-del { background: rgb(220 38 38); color: #fff; }
        .mp-empty { width: 7rem; height: 7rem; border-radius: .75rem; border: 2px dashed rgb(212 212 216); background: rgb(250 250 250); cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .35rem; color: rgb(161 161 170); transition: border-color .15s, color .15s; flex-shrink: 0; }
        .mp-empty:hover { border-color: rgb(24 24 27); color: rgb(24 24 27); }
        .mp-empty svg { width: 1.75rem; height: 1.75rem; }
        .mp-empty span { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
        .mp-side { display: flex; flex-direction: column; gap: .4rem; min-width: 0; }
        .mp-choose-btn { padding: .5rem 1rem; border: 1px solid rgb(228 228 231); background: #fff; border-radius: .5rem; font-size: .75rem; font-weight: 600; cursor: pointer; color: rgb(63 63 70); transition: background .15s, border-color .15s; white-space: nowrap; }
        .mp-choose-btn:hover { background: rgb(244 244 245); border-color: rgb(161 161 170); }
        .mp-path { font-size: .6875rem; color: rgb(161 161 170); font-weight: 500; word-break: break-all; }
        .dark .mp-preview { background: rgb(39 39 42); border-color: rgb(63 63 70); }
        .dark .mp-empty { background: rgb(39 39 42); border-color: rgb(63 63 70); }
        .dark .mp-choose-btn { background: rgb(24 24 27); border-color: rgb(63 63 70); color: rgb(212 212 216); }

        /* Modal */
        .mp-modal { position: fixed; inset: 0; z-index: 999; overflow-y: auto; }
        .mp-backdrop { position: fixed; inset: 0; background: rgb(24 24 27 / .6); backdrop-filter: blur(3px); }
        .mp-modal-wrap { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1.5rem; }
        .mp-window { position: relative; z-index: 10; background: #fff; border-radius: .9rem; overflow: hidden; border: 1px solid rgb(228 228 231); box-shadow: 0 20px 50px rgb(0 0 0 / .2); max-width: 56rem; width: 100%; height: 80vh; display: flex; flex-direction: column; }
        .dark .mp-window { background: rgb(24 24 27); border-color: rgb(63 63 70); }
        .mp-head { padding: .9rem 1.4rem; border-bottom: 1px solid rgb(228 228 231); display: flex; align-items: center; justify-content: space-between; background: rgb(250 250 250); }
        .dark .mp-head { background: rgb(39 39 42); border-color: rgb(63 63 70); }
        .mp-head h3 { margin: 0; font-size: .95rem; font-weight: 700; color: rgb(24 24 27); }
        .dark .mp-head h3 { color: #fff; }
        .mp-close { background: none; border: none; cursor: pointer; color: rgb(161 161 170); padding: .25rem; }
        .mp-close:hover { color: rgb(220 38 38); }
        .mp-close svg { width: 1.4rem; height: 1.4rem; }
        .mp-toolbar { padding: 1rem 1.4rem; border-bottom: 1px solid rgb(228 228 231); display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; justify-content: space-between; }
        .dark .mp-toolbar { border-color: rgb(63 63 70); }
        .mp-search { position: relative; flex: 1 1 200px; max-width: 300px; }
        .mp-search input { width: 100%; padding: .55rem .9rem .55rem 2.3rem; border: 1px solid rgb(228 228 231); border-radius: .5rem; font-size: .8125rem; outline: none; }
        .mp-search input:focus { border-color: rgb(161 161 170); box-shadow: 0 0 0 3px rgb(228 228 231 / .6); }
        .dark .mp-search input { background: rgb(39 39 42); border-color: rgb(63 63 70); color: #fff; }
        .mp-search svg { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); width: .95rem; height: .95rem; color: rgb(161 161 170); }
        .mp-upload-btn { display: inline-flex; align-items: center; gap: .45rem; background: rgb(24 24 27); color: #fff; font-size: .75rem; font-weight: 700; padding: .6rem 1.1rem; border-radius: .5rem; border: none; cursor: pointer; }
        .mp-upload-btn:hover { background: rgb(39 39 42); }
        .mp-upload-btn[disabled] { opacity: .5; }
        .mp-upload-btn svg { width: .95rem; height: .95rem; }
        .mp-body { flex: 1; overflow-y: auto; padding: 1.4rem; background: rgb(250 250 250 / .6); }
        .dark .mp-body { background: rgb(24 24 27); }
        .mp-body.mp-drag { outline: 2px dashed rgb(24 24 27); outline-offset: -8px; background: rgb(244 244 245); }
        .mp-drag-hint { text-align: center; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: rgb(220 38 38); margin-bottom: .9rem; }
        .mp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: .9rem; }
        .mp-item { position: relative; aspect-ratio: 1/1; background: #fff; border-radius: .6rem; overflow: hidden; border: 1px solid rgb(228 228 231); cursor: pointer; transition: border-color .15s, box-shadow .15s; }
        .mp-item:hover { border-color: rgb(161 161 170); box-shadow: 0 2px 6px rgb(0 0 0 / .08); }
        .mp-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .mp-item.mp-selected { border-color: rgb(24 24 27); box-shadow: 0 0 0 2px rgb(24 24 27); }
        .mp-item-check { position: absolute; top: .4rem; right: .4rem; background: rgb(24 24 27); color: #fff; border-radius: 999px; padding: .22rem; display: flex; }
        .mp-item-check svg { width: .8rem; height: .8rem; }
        .mp-item-name { position: absolute; inset-inline: 0; bottom: 0; background: linear-gradient(transparent, rgb(0 0 0 / .7)); color: #fff; font-size: .58rem; font-weight: 600; padding: 1rem .45rem .35rem; opacity: 0; transition: opacity .15s; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .mp-item:hover .mp-item-name { opacity: 1; }
        .mp-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 1rem; gap: .7rem; color: rgb(161 161 170); font-size: .8125rem; font-weight: 600; }
        .mp-spinner { width: 2.2rem; height: 2.2rem; border: 3px solid rgb(228 228 231); border-top-color: rgb(24 24 27); border-radius: 999px; animation: mp-spin .8s linear infinite; }
        @keyframes mp-spin { to { transform: rotate(360deg); } }
        .mp-foot { padding: .85rem 1.4rem; border-top: 1px solid rgb(228 228 231); display: flex; justify-content: flex-end; background: rgb(250 250 250); }
        .dark .mp-foot { background: rgb(39 39 42); border-color: rgb(63 63 70); }
        .mp-foot-btn { padding: .55rem 1.2rem; background: #fff; border: 1px solid rgb(228 228 231); color: rgb(63 63 70); font-size: .75rem; font-weight: 700; border-radius: .5rem; cursor: pointer; }
        .mp-foot-btn:hover { background: rgb(244 244 245); }
    </style>
    @endonce

    <div x-data="{
        open: false,
        state: $wire.entangle('{{ $getStatePath() }}'),
        mediaItems: [],
        search: '',
        isLoading: false,
        isUploading: false,
        dragActive: false,
        init() {
            this.$watch('search', () => this.loadMedia());
        },
        async loadMedia() {
            this.isLoading = true;
            try {
                let response = await fetch('/admin/api/media?search=' + encodeURIComponent(this.search));
                let data = await response.json();
                this.mediaItems = data.media || [];
            } catch (e) {
                console.error('Error loading media:', e);
            }
            this.isLoading = false;
        },
        selectImage(filePath) {
            this.state = filePath;
            this.open = false;
        },
        removeImage() {
            this.state = null;
        },
        getPreviewUrl() {
            if (!this.state) return '';
            if (this.state.startsWith('http://') || this.state.startsWith('https://') || this.state.startsWith('/')) {
                return this.state;
            }
            return '/storage/' + this.state;
        },
        async handleFileUpload(event) {
            await this.uploadFiles(event.target.files);
            event.target.value = '';
        },
        async handleDrop(event) {
            this.dragActive = false;
            if (event.dataTransfer && event.dataTransfer.files.length > 0) {
                await this.uploadFiles(event.dataTransfer.files);
            }
        },
        async uploadFiles(files) {
            let list = Array.from(files || []).filter(f => f.type.startsWith('image/'));
            if (list.length === 0) return;

            this.isUploading = true;
            let lastUploadedPath = null;
            let csrfToken = document.querySelector('meta[name=&quot;csrf-token&quot;]')?.getAttribute('content') || '';

            for (let file of list) {
                let formData = new FormData();
                formData.append('file', file);
                try {
                    let response = await fetch('/admin/api/media/upload', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    let data = await response.json();
                    if (data.success) {
                        lastUploadedPath = data.media.file_path;
                    } else {
                        alert('Yükleme başarısız: ' + (data.message || 'Bilinmeyen hata'));
                    }
                } catch (e) {
                    console.error('Upload error:', e);
                    alert('Yükleme sırasında bir hata oluştu.');
                }
            }

            await this.loadMedia();
            if (lastUploadedPath) {
                this.selectImage(lastUploadedPath);
            }
            this.isUploading = false;
        }
    }">
        <!-- Seçili görsel önizleme -->
        <div class="mp-row">
            <template x-if="state">
                <div class="mp-preview">
                    <img :src="getPreviewUrl()">
                    <div class="mp-preview-overlay">
                        <button type="button" @click="open = true; loadMedia()" class="mp-ov-btn mp-ov-edit" title="Değiştir">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </button>
                        <button type="button" @click="removeImage()" class="mp-ov-btn mp-ov-del" title="Kaldır">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            </template>
            <template x-if="!state">
                <div @click="open = true; loadMedia()" class="mp-empty">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                    <span>Görsel Seç</span>
                </div>
            </template>

            <div class="mp-side">
                <button type="button" @click="open = true; loadMedia()" class="mp-choose-btn">
                    Medya Kütüphanesinden Seç
                </button>
                <p class="mp-path" x-text="state ? state : 'Görsel seçilmedi'"></p>
            </div>
        </div>

        <!-- Modal -->
        <div x-show="open" class="mp-modal" style="display: none;" x-transition.opacity>
            <div class="mp-backdrop" @click="open = false"></div>

            <div class="mp-modal-wrap">
                <div class="mp-window" x-show="open" x-transition>
                    <!-- Başlık -->
                    <div class="mp-head">
                        <h3>🖼️ Medya Kütüphanesi</h3>
                        <button type="button" @click="open = false" class="mp-close">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <!-- Arama + Yükleme -->
                    <div class="mp-toolbar">
                        <div class="mp-search">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            <input type="text" x-model.debounce.300ms="search" placeholder="Görsel ara...">
                        </div>
                        <div>
                            <input type="file" id="modal-file-input-{{ $getId() }}" @change="handleFileUpload($event)" class="hidden" style="display:none" multiple accept="image/*">
                            <button type="button" onclick="document.getElementById('modal-file-input-{{ $getId() }}').click()" class="mp-upload-btn" :disabled="isUploading">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                <span x-text="isUploading ? 'Yükleniyor...' : 'Dosya Yükle'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Görsel ızgarası (sürükle-bırak destekli) -->
                    <div
                        class="mp-body"
                        :class="dragActive ? 'mp-drag' : ''"
                        @dragover.prevent="dragActive = true"
                        @dragleave.prevent="dragActive = false"
                        @drop.prevent="handleDrop($event)"
                    >
                        <div x-show="dragActive" class="mp-drag-hint">Dosyaları buraya bırakın — kütüphaneye yüklenecek</div>

                        <template x-if="isLoading">
                            <div class="mp-state">
                                <div class="mp-spinner"></div>
                                <span>Yükleniyor...</span>
                            </div>
                        </template>

                        <template x-if="!isLoading && mediaItems.length === 0">
                            <div class="mp-state">
                                <span>Medya kütüphanesinde dosya bulunamadı.</span>
                                <span style="font-weight:500; font-size:.75rem;">Görselleri buraya sürükleyip bırakabilirsiniz.</span>
                            </div>
                        </template>

                        <template x-if="!isLoading && mediaItems.length > 0">
                            <div class="mp-grid">
                                <template x-for="item in mediaItems" :key="item.id">
                                    <div
                                        @click="selectImage(item.file_path)"
                                        class="mp-item"
                                        :class="state === item.file_path ? 'mp-selected' : ''"
                                    >
                                        <img :src="item.url" loading="lazy">
                                        <div x-show="state === item.file_path" class="mp-item-check">
                                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                        <div class="mp-item-name" x-text="item.name"></div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Alt -->
                    <div class="mp-foot">
                        <button type="button" @click="open = false" class="mp-foot-btn">Kapat</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>
