<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{
        open: false,
        state: $wire.entangle('{{ $getStatePath() }}'),
        mediaItems: [],
        search: '',
        isLoading: false,
        isUploading: false,
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
            let files = event.target.files;
            if (!files || files.length === 0) return;
            
            let formData = new FormData();
            formData.append('file', files[0]);
            
            this.isUploading = true;
            try {
                let csrfToken = document.querySelector('meta[name=&quot;csrf-token&quot;]')?.getAttribute('content') || '';
                let response = await fetch('/admin/api/media/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                let data = await response.json();
                if (data.success) {
                    await this.loadMedia();
                    this.selectImage(data.media.file_path);
                } else {
                    alert('Yükleme başarısız: ' + (data.message || 'Bilinmeyen hata'));
                }
            } catch (e) {
                console.error('Upload error:', e);
                alert('Yükleme sırasında bir hata oluştu.');
            }
            this.isUploading = false;
            event.target.value = '';
        }
    }"
    class="space-y-2"
    >
        <!-- Preview of the selected image -->
        <div class="flex items-center gap-4">
            <template x-if="state">
                <div class="relative w-28 h-28 rounded-2xl overflow-hidden bg-slate-50 border border-slate-200 shadow-sm group">
                    <img :src="getPreviewUrl()" class="w-full h-full object-cover">
                    <!-- Overlay with remove and details -->
                    <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 flex items-center justify-center gap-2 transition duration-200">
                        <button type="button" @click="open = true; loadMedia()" class="p-1.5 bg-white text-slate-800 rounded-lg hover:bg-rose-50 transition shadow" title="Değiştir">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                        <button type="button" @click="removeImage()" class="p-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition shadow" title="Kaldır">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
            <template x-if="!state">
                <div @click="open = true; loadMedia()" class="w-28 h-28 rounded-2xl border-2 border-dashed border-slate-300 hover:border-rose-500 bg-slate-50 hover:bg-rose-50/10 cursor-pointer flex flex-col items-center justify-center gap-1.5 text-slate-400 hover:text-rose-600 transition group">
                    <svg class="w-8 h-8 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    <span class="text-[10px] font-bold uppercase tracking-wider">Görsel Seç</span>
                </div>
            </template>
            
            <div class="space-y-1">
                <button type="button" @click="open = true; loadMedia()" class="px-4 py-2 border border-slate-300 hover:border-rose-500 hover:text-rose-600 rounded-xl hover:bg-slate-50 transition text-xs font-bold uppercase tracking-wider focus:outline-none">
                    Medya Kütüphanesinden Seç
                </button>
                <p class="text-[10px] text-slate-400 font-semibold" x-text="state ? 'Seçilen Dosya: ' + state : 'Görsel seçilmedi'"></p>
            </div>
        </div>

        <!-- Popup Modal Window -->
        <div 
            x-show="open" 
            class="fixed inset-0 z-[999] overflow-y-auto" 
            style="display: none;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>

            <!-- Modal Container -->
            <div class="flex items-center justify-center min-h-screen p-4 sm:p-6 md:p-8">
                <div 
                    x-show="open"
                    x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200 transform"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="bg-white rounded-3xl overflow-hidden shadow-2xl border border-slate-200 max-w-4xl w-full flex flex-col h-[80vh] relative z-10"
                >
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">🖼️</span>
                            <h3 class="text-base font-extrabold text-slate-800 font-serif">Medya Kütüphanesi</h3>
                        </div>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-rose-600 transition">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Actions (Search & Upload) -->
                    <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row gap-4 items-center justify-between bg-white">
                        <!-- Search Bar -->
                        <div class="relative w-full sm:max-w-xs">
                            <input 
                                type="text" 
                                x-model.debounce.300ms="search" 
                                placeholder="Görsel ara..." 
                                class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500 focus:border-rose-500 transition font-semibold"
                            >
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Upload File Button -->
                        <div>
                            <input 
                                type="file" 
                                id="modal-file-input-{{ $getId() }}" 
                                @change="handleFileUpload($event)" 
                                class="hidden" 
                                accept="image/*"
                            >
                            <button 
                                type="button" 
                                onclick="document.getElementById('modal-file-input-{{ $getId() }}').click()" 
                                class="flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold uppercase tracking-wider py-3 px-5 rounded-xl transition shadow-lg hover:shadow-rose-100 disabled:opacity-50"
                                :disabled="isUploading"
                            >
                                <template x-if="isUploading">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <template x-if="!isUploading">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                </template>
                                <span x-text="isUploading ? 'Yükleniyor...' : 'YENİ DOSYA YÜKLE'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body (Grid of Images) -->
                    <div class="flex-grow overflow-y-auto p-6 bg-slate-50/50">
                        <template x-if="isLoading">
                            <div class="flex flex-col items-center justify-center py-20 gap-3">
                                <div class="w-10 h-10 border-4 border-rose-200 border-t-rose-600 rounded-full animate-spin"></div>
                                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Yükleniyor...</p>
                            </div>
                        </template>

                        <template x-if="!isLoading && mediaItems.length === 0">
                            <div class="flex flex-col items-center justify-center py-20 text-slate-400 gap-2">
                                <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                <p class="text-sm font-semibold text-slate-500">Medya kütüphanesinde dosya bulunamadı.</p>
                            </div>
                        </template>

                        <template x-if="!isLoading && mediaItems.length > 0">
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4">
                                <template x-for="item in mediaItems" :key="item.id">
                                    <div 
                                        @click="selectImage(item.file_path)"
                                        class="group relative aspect-square bg-white rounded-2xl overflow-hidden border border-slate-200 hover:border-rose-500 cursor-pointer shadow-sm hover:shadow-md transition duration-300 flex items-center justify-center"
                                        :class="state === item.file_path ? 'border-rose-600 ring-2 ring-rose-500/20' : ''"
                                    >
                                        <img :src="item.url" class="w-full h-full object-cover transition duration-300 group-hover:scale-105">
                                        
                                        <!-- Selected checkmark badge -->
                                        <div x-show="state === item.file_path" class="absolute top-2 right-2 bg-rose-600 text-white rounded-full p-1 shadow-md">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>

                                        <!-- Hover detail label -->
                                        <div class="absolute inset-x-0 bottom-0 bg-slate-900/70 p-2 opacity-0 group-hover:opacity-100 transition duration-200">
                                            <p class="text-[9px] font-bold text-white truncate" x-text="item.name"></p>
                                            <p class="text-[8px] text-slate-300 font-semibold mt-0.5" x-text="item.file_size ? (item.file_size/1024).toFixed(1) + ' KB' : ''"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50">
                        <button type="button" @click="open = false" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition uppercase tracking-wider focus:outline-none">
                            KAPAT
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>
