<!-- Modale Universelle de Gestion et Recadrage de Médias (Cropper.js) -->
<div
    x-data="ciomagMediaManager()"
    x-init="initManager()"
    x-show="isOpen"
    x-cloak
    @keydown.escape.window="if(isOpen) closeModal()"
    class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-slate-950/75 backdrop-blur-xs"
    style="display: none;"
>
    <div
        @click.outside="closeModal()"
        class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-6xl h-[86vh] flex flex-col overflow-hidden"
    >
        <!-- Header Modale -->
        <div class="px-6 py-4 bg-slate-950 text-white flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-700 flex items-center justify-center">
                    <i data-lucide="image" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h2 class="text-base font-black tracking-tight">Studio Médiathèque & Recadrage CIOMAG</h2>
                    <p class="text-[11px] text-slate-400">Parcourir, téléverser, éditer les métadonnées SEO et recadrer vos visuels</p>
                </div>
            </div>

            <!-- Navigation Onglets -->
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="switchTab('library')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 transition cursor-pointer"
                    :class="activeTab === 'library' ? 'bg-red-700 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                >
                    <i data-lucide="grid" class="w-3.5 h-3.5"></i>
                    <span>Bibliothèque</span>
                </button>

                <button
                    type="button"
                    @click="switchTab('upload')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 transition cursor-pointer"
                    :class="activeTab === 'upload' ? 'bg-red-700 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                >
                    <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                    <span>Téléverser / URL</span>
                </button>

                <button
                    type="button"
                    x-show="selectedItem && selectedItem.is_image"
                    @click="openCropper(selectedItem)"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 transition cursor-pointer"
                    :class="activeTab === 'crop' ? 'bg-amber-500 text-slate-950' : 'bg-slate-800 text-amber-400 hover:bg-slate-700'"
                >
                    <i data-lucide="crop" class="w-3.5 h-3.5"></i>
                    <span>Recadrer & Retoucher</span>
                </button>

                <button
                    type="button"
                    @click="closeModal()"
                    class="ml-3 p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition cursor-pointer"
                    title="Fermer"
                >
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Notification interne -->
        <div x-show="notification" x-transition class="px-6 py-2.5 bg-emerald-600 text-white text-xs font-semibold flex items-center justify-between shrink-0">
            <span x-text="notification"></span>
            <button type="button" @click="notification = ''" class="text-white/80 hover:text-white">✕</button>
        </div>

        <!-- ONGLET 1 : BIBLIOTHÈQUE & INSPECTEUR -->
        <div x-show="activeTab === 'library'" class="flex-1 flex flex-col lg:flex-row min-h-0 overflow-hidden">
            <!-- Grille des médias (Gauche) -->
            <div class="flex-1 flex flex-col min-h-0 border-r border-slate-200">
                <!-- Barre de recherche -->
                <div class="p-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between gap-3 shrink-0">
                    <div class="relative flex-1">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-2.5"></i>
                        <input
                            type="search"
                            x-model="searchQuery"
                            @input.debounce.250ms="fetchMedia()"
                            placeholder="Rechercher par nom de fichier, texte alternatif ou légende..."
                            class="w-full pl-10 pr-4 py-2 text-xs bg-white border border-slate-300 rounded-xl focus:outline-none focus:border-red-700"
                        >
                    </div>
                    <button
                        type="button"
                        @click="fetchMedia()"
                        class="px-3 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl flex items-center gap-1.5 cursor-pointer"
                    >
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="loading ? 'animate-spin' : ''"></i>
                        <span>Actualiser</span>
                    </button>
                </div>

                <!-- Grille scrollable -->
                <div class="flex-1 overflow-y-auto p-4">
                    <div x-show="loading" class="py-16 text-center text-xs text-slate-400">
                        Chargement de la médiathèque...
                    </div>

                    <div x-show="!loading && items.length === 0" class="py-16 text-center space-y-3">
                        <p class="text-sm text-slate-500">Aucun média trouvé.</p>
                        <button type="button" @click="switchTab('upload')" class="px-4 py-2 bg-red-700 text-white text-xs font-bold rounded-xl cursor-pointer">
                            + Téléverser un visuel
                        </button>
                    </div>

                    <div x-show="!loading && items.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5">
                        <template x-for="item in items" :key="item.id">
                            <div
                                @click="selectItem(item)"
                                @dblclick="confirmSelection(item)"
                                class="group relative rounded-xl border-2 overflow-hidden cursor-pointer transition bg-slate-100 flex flex-col justify-between h-40"
                                :class="selectedItem && selectedItem.id === item.id ? 'border-red-700 ring-2 ring-red-700/25' : 'border-slate-200 hover:border-slate-400'"
                            >
                                <img :src="item.thumbnail || item.url" :alt="item.alt_text" class="w-full h-28 object-cover group-hover:scale-105 transition duration-300">
                                <div class="p-2 bg-white border-t border-slate-100 flex items-center justify-between text-[11px]">
                                    <span class="font-semibold text-slate-800 truncate" x-text="item.file_name"></span>
                                    <span class="text-[10px] text-slate-400 shrink-0 ml-1" x-text="item.width ? item.width + '×' + item.height : item.human_size"></span>
                                </div>
                                <div
                                    x-show="selectedItem && selectedItem.id === item.id"
                                    class="absolute top-2 right-2 w-6 h-6 rounded-full bg-red-700 text-white flex items-center justify-center text-xs font-bold shadow-md"
                                >
                                    ✓
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Inspecteur & Métadonnées (Droite) -->
            <div class="w-full lg:w-88 bg-slate-50 p-5 flex flex-col justify-between overflow-y-auto shrink-0">
                <template x-if="!selectedItem">
                    <div class="my-auto text-center text-xs text-slate-400 space-y-2 py-12">
                        <p class="font-semibold text-slate-600">Aucun média sélectionné</p>
                        <p>Cliquez sur un visuel pour inspecter ses propriétés, le recadrer ou l'insérer.</p>
                    </div>
                </template>

                <template x-if="selectedItem">
                    <div class="space-y-4">
                        <div class="rounded-xl overflow-hidden bg-slate-900 border border-slate-200 h-44 flex items-center justify-center relative">
                            <img :src="selectedItem.url" :alt="selectedItem.alt_text" class="max-h-full max-w-full object-contain">
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-slate-500 border-b border-slate-200 pb-2.5">
                            <span x-text="selectedItem.mime_type + ' • ' + selectedItem.human_size"></span>
                            <span x-show="selectedItem.width" x-text="selectedItem.width + ' × ' + selectedItem.height + ' px'"></span>
                        </div>

                        <!-- Actions Rapides : Recadrer -->
                        <div class="flex gap-2">
                            <button
                                type="button"
                                x-show="selectedItem.is_image"
                                @click="openCropper(selectedItem)"
                                class="flex-1 py-2 px-3 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold rounded-xl flex items-center justify-center gap-1.5 transition cursor-pointer"
                            >
                                <span>✂ Recadrer / Pivoter</span>
                            </button>
                            <button
                                type="button"
                                @click="deleteSelectedMedia()"
                                class="py-2 px-3 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-xl transition cursor-pointer"
                                title="Supprimer ce média"
                            >
                                Supprimer
                            </button>
                        </div>

                        <!-- Édition des métadonnées -->
                        <div class="space-y-3 pt-2 border-t border-slate-200">
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1">Nom du fichier</label>
                                <input type="text" x-model="selectedItem.file_name" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1">Texte alternatif (SEO Alt)</label>
                                <input type="text" x-model="selectedItem.alt_text" placeholder="Description pour l'accessibilité..." class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1">Légende / Crédit photo</label>
                                <textarea x-model="selectedItem.caption" rows="2" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg"></textarea>
                            </div>
                            <button
                                type="button"
                                @click="saveMetadata()"
                                :disabled="savingMeta"
                                class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition cursor-pointer"
                            >
                                <span x-text="savingMeta ? 'Enregistrement...' : 'Enregistrer les métadonnées'"></span>
                            </button>
                        </div>

                        <!-- Bouton de sélection finale -->
                        <div class="pt-3 border-t border-slate-200">
                            <button
                                type="button"
                                @click="confirmSelection(selectedItem)"
                                class="w-full py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md transition cursor-pointer"
                            >
                                ✓ Utiliser ce média
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ONGLET 2 : TÉLÉVERSER OU AJOUTER PAR URL -->
        <div x-show="activeTab === 'upload'" class="flex-1 overflow-y-auto p-8 grid grid-cols-1 md:grid-cols-2 gap-8" style="display: none;">
            <!-- Upload Fichier Local -->
            <div class="bg-slate-50 rounded-2xl border-2 border-dashed border-slate-300 p-6 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-700 flex items-center justify-center">
                        <i data-lucide="upload" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-black text-slate-900">Téléverser un fichier depuis votre ordinateur</h3>
                    <p class="text-xs text-slate-500">Formats acceptés : JPG, PNG, WebP, SVG, PDF (Max 20 Mo).</p>

                    <div class="space-y-3 pt-2">
                        <input
                            type="file"
                            x-ref="uploadFileInput"
                            accept="image/*,application/pdf"
                            class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-red-700"
                        >
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Texte alternatif (Alt SEO)</label>
                            <input type="text" x-model="uploadForm.alt_text" placeholder="Ex: Salle des serveurs Data Center..." class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Légende éditoriale</label>
                            <input type="text" x-model="uploadForm.caption" placeholder="Crédit photo / Légende..." class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg">
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    @click="uploadLocalFile()"
                    :disabled="uploading"
                    class="mt-6 w-full py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition cursor-pointer"
                >
                    <span x-text="uploading ? 'Téléversement en cours...' : 'Téléverser dans la médiathèque'"></span>
                </button>
            </div>

            <!-- Importer depuis URL externe -->
            <div class="bg-slate-50 rounded-2xl border border-slate-200 p-6 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center">
                        <i data-lucide="globe" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-black text-slate-900">Importer depuis une URL distante (CDN / S3 / Unsplash)</h3>
                    <p class="text-xs text-slate-500">Référencez directement une image hébergée sur votre CDN externe.</p>

                    <div class="space-y-3 pt-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">URL complète de l'image *</label>
                            <input type="url" x-model="externalForm.external_url" placeholder="https://images.unsplash.com/..." class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nom du fichier *</label>
                            <input type="text" x-model="externalForm.file_name" placeholder="couverture-magazine.jpg" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Texte alternatif (Alt SEO)</label>
                            <input type="text" x-model="externalForm.alt_text" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg">
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    @click="uploadExternalUrl()"
                    :disabled="uploading"
                    class="mt-6 w-full py-2.5 bg-slate-900 hover:bg-red-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition cursor-pointer"
                >
                    <span x-text="uploading ? 'Importation...' : 'Ajouter à la médiathèque'"></span>
                </button>
            </div>
        </div>

        <!-- ONGLET 3 : STUDIO DE RECADRAGE (CROPPER.JS) -->
        <div x-show="activeTab === 'crop'" class="flex-1 flex flex-col lg:flex-row min-h-0 overflow-hidden" style="display: none;">
            <!-- Zone Canvas Cropper -->
            <div class="flex-1 bg-slate-900 flex items-center justify-center p-4 min-h-0 overflow-hidden">
                <div class="w-full h-full max-h-[62vh] flex items-center justify-center">
                    <img x-ref="cropperImage" src="" alt="Cropper Target" crossorigin="anonymous" class="max-w-full max-h-full block">
                </div>
            </div>

            <!-- Barre d'outils de Recadrage -->
            <div class="w-full lg:w-80 bg-slate-50 border-l border-slate-200 p-5 flex flex-col justify-between overflow-y-auto shrink-0">
                <div class="space-y-5">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">Outils de Recadrage</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Ajustez le cadrage pour le format magazine</p>
                    </div>

                    <!-- Ratios d'aspect -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-600 mb-2">Format / Ratio d'aspect</label>
                        <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                            <button type="button" @click="setAspectRatio(16/9)" class="py-2 px-3 bg-white border border-slate-300 hover:border-red-700 rounded-lg cursor-pointer">
                                16:9 (Une / Hero)
                            </button>
                            <button type="button" @click="setAspectRatio(4/3)" class="py-2 px-3 bg-white border border-slate-300 hover:border-red-700 rounded-lg cursor-pointer">
                                4:3 (Article)
                            </button>
                            <button type="button" @click="setAspectRatio(1)" class="py-2 px-3 bg-white border border-slate-300 hover:border-red-700 rounded-lg cursor-pointer">
                                1:1 (Carré / Avatar)
                            </button>
                            <button type="button" @click="setAspectRatio(NaN)" class="py-2 px-3 bg-white border border-slate-300 hover:border-red-700 rounded-lg cursor-pointer">
                                Libre
                            </button>
                        </div>
                    </div>

                    <!-- Rotation & Symétrie -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-600 mb-2">Rotation & Miroir</label>
                        <div class="grid grid-cols-4 gap-2 text-xs font-semibold">
                            <button type="button" @click="rotateCrop(-90)" class="py-2 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg cursor-pointer" title="Pivoter -90°">
                                ↺ -90°
                            </button>
                            <button type="button" @click="rotateCrop(90)" class="py-2 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg cursor-pointer" title="Pivoter +90°">
                                ↻ +90°
                            </button>
                            <button type="button" @click="flipCropH()" class="py-2 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg cursor-pointer" title="Miroir Horizontal">
                                ⇄ H
                            </button>
                            <button type="button" @click="flipCropV()" class="py-2 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg cursor-pointer" title="Miroir Vertical">
                                ⇅ V
                            </button>
                        </div>
                    </div>

                    <!-- Zoom & Reset -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-600 mb-2">Zoom</label>
                        <div class="grid grid-cols-3 gap-2 text-xs font-semibold">
                            <button type="button" @click="zoomCrop(0.1)" class="py-2 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg cursor-pointer">
                                Zoom +
                            </button>
                            <button type="button" @click="zoomCrop(-0.1)" class="py-2 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg cursor-pointer">
                                Zoom -
                            </button>
                            <button type="button" @click="resetCrop()" class="py-2 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg cursor-pointer">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions d'enregistrement du recadrage -->
                <div class="space-y-2.5 pt-4 border-t border-slate-200">
                    <button
                        type="button"
                        @click="saveCroppedImage('new')"
                        :disabled="cropping"
                        class="w-full py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition cursor-pointer"
                    >
                        <span x-text="cropping ? 'Traitement...' : 'Enregistrer comme nouvelle copie'"></span>
                    </button>

                    <button
                        type="button"
                        @click="saveCroppedImage('replace')"
                        :disabled="cropping"
                        class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl transition cursor-pointer"
                    >
                        Remplacer l'image actuelle
                    </button>

                    <button
                        type="button"
                        @click="switchTab('library')"
                        class="w-full py-1.5 text-xs text-slate-500 hover:text-slate-800 cursor-pointer"
                    >
                        Annuler et revenir à la bibliothèque
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function ciomagMediaManager() {
        return {
            isOpen: false,
            activeTab: 'library',
            items: [],
            loading: false,
            uploading: false,
            savingMeta: false,
            cropping: false,
            searchQuery: '',
            selectedItem: null,
            onSelectCallback: null,
            cropperInstance: null,
            scaleX: 1,
            scaleY: 1,
            notification: '',
            uploadForm: { alt_text: '', caption: '' },
            externalForm: { external_url: '', file_name: '', alt_text: '' },

            initManager() {
                window.openMediaManager = (options = {}) => {
                    this.onSelectCallback = options.onSelect || null;
                    this.isOpen = true;
                    this.activeTab = options.tab || 'library';
                    this.fetchMedia().then(() => {
                        if (options.mediaId) {
                            const found = this.items.find(i => String(i.id) === String(options.mediaId));
                            if (found) {
                                this.selectedItem = found;
                                if (options.openCrop) {
                                    this.openCropper(found);
                                }
                            }
                        }
                    });
                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                };
            },

            closeModal() {
                this.destroyCropper();
                this.isOpen = false;
            },

            switchTab(tab) {
                if (tab !== 'crop') {
                    this.destroyCropper();
                }
                this.activeTab = tab;
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            },

            notify(msg) {
                this.notification = msg;
                setTimeout(() => { this.notification = ''; }, 4000);
            },

            async fetchMedia() {
                this.loading = true;
                try {
                    const url = `{{ route('admin.api.media.index') }}?q=${encodeURIComponent(this.searchQuery)}`;
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const json = await res.json();
                    this.items = json.data || [];
                    if (!this.selectedItem && this.items.length > 0) {
                        this.selectedItem = this.items[0];
                    }
                } catch (e) {
                    console.error(e);
                } finally {
                    this.loading = false;
                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                }
            },

            selectItem(item) {
                this.selectedItem = { ...item };
            },

            confirmSelection(item) {
                if (!item) return;
                if (typeof this.onSelectCallback === 'function') {
                    this.onSelectCallback(item);
                }
                window.dispatchEvent(new CustomEvent('ciomag-media-updated', { detail: item }));
                this.closeModal();
            },

            async uploadLocalFile() {
                const fileInput = this.$refs.uploadFileInput;
                if (!fileInput || !fileInput.files.length) {
                    alert('Veuillez choisir un fichier à téléverser.');
                    return;
                }
                this.uploading = true;
                try {
                    const formData = new FormData();
                    formData.append('file', fileInput.files[0]);
                    formData.append('alt_text', this.uploadForm.alt_text || '');
                    formData.append('caption', this.uploadForm.caption || '');

                    const res = await fetch(`{{ route('admin.api.media.store') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    const json = await res.json();
                    if (res.ok && json.media) {
                        this.items.unshift(json.media);
                        this.selectedItem = json.media;
                        fileInput.value = '';
                        this.uploadForm = { alt_text: '', caption: '' };
                        this.switchTab('library');
                        this.notify('Fichier téléversé avec succès !');
                    } else {
                        alert(json.message || 'Erreur lors du téléversement.');
                    }
                } finally {
                    this.uploading = false;
                }
            },

            async uploadExternalUrl() {
                if (!this.externalForm.external_url || !this.externalForm.file_name) {
                    alert('Veuillez renseigner l\'URL et le nom du fichier.');
                    return;
                }
                this.uploading = true;
                try {
                    const res = await fetch(`{{ route('admin.api.media.store') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.externalForm),
                    });
                    const json = await res.json();
                    if (res.ok && json.media) {
                        this.items.unshift(json.media);
                        this.selectedItem = json.media;
                        this.externalForm = { external_url: '', file_name: '', alt_text: '' };
                        this.switchTab('library');
                        this.notify('Média externe ajouté au catalogue !');
                    }
                } finally {
                    this.uploading = false;
                }
            },

            async saveMetadata() {
                if (!this.selectedItem) return;
                this.savingMeta = true;
                try {
                    const res = await fetch(`/admin/api/media/${this.selectedItem.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            file_name: this.selectedItem.file_name,
                            alt_text: this.selectedItem.alt_text,
                            caption: this.selectedItem.caption,
                        }),
                    });
                    const json = await res.json();
                    if (res.ok && json.media) {
                        const idx = this.items.findIndex(i => i.id === json.media.id);
                        if (idx !== -1) this.items[idx] = json.media;
                        this.selectedItem = json.media;
                        this.notify('Métadonnées enregistrées !');
                    }
                } finally {
                    this.savingMeta = false;
                }
            },

            async deleteSelectedMedia() {
                if (!this.selectedItem || !confirm('Supprimer définitivement ce média ?')) return;
                const id = this.selectedItem.id;
                const res = await fetch(`/admin/api/media/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                if (res.ok) {
                    this.items = this.items.filter(i => i.id !== id);
                    this.selectedItem = this.items[0] || null;
                    this.notify('Média supprimé.');
                }
            },

            openCropper(item) {
                if (!item) return;
                this.selectedItem = item;
                this.activeTab = 'crop';
                this.destroyCropper();

                this.$nextTick(() => {
                    const img = this.$refs.cropperImage;
                    img.src = item.url;
                    img.onload = () => {
                        if (window.Cropper) {
                            this.cropperInstance = new window.Cropper(img, {
                                viewMode: 1,
                                autoCropArea: 0.9,
                                responsive: true,
                                background: true,
                            });
                        }
                    };
                });
            },

            destroyCropper() {
                if (this.cropperInstance) {
                    this.cropperInstance.destroy();
                    this.cropperInstance = null;
                }
            },

            setAspectRatio(ratio) {
                if (this.cropperInstance) this.cropperInstance.setAspectRatio(ratio);
            },
            rotateCrop(deg) {
                if (this.cropperInstance) this.cropperInstance.rotate(deg);
            },
            flipCropH() {
                this.scaleX = this.scaleX === 1 ? -1 : 1;
                if (this.cropperInstance) this.cropperInstance.scaleX(this.scaleX);
            },
            flipCropV() {
                this.scaleY = this.scaleY === 1 ? -1 : 1;
                if (this.cropperInstance) this.cropperInstance.scaleY(this.scaleY);
            },
            zoomCrop(ratio) {
                if (this.cropperInstance) this.cropperInstance.zoom(ratio);
            },
            resetCrop() {
                if (this.cropperInstance) this.cropperInstance.reset();
            },

            async saveCroppedImage(mode = 'new') {
                if (!this.cropperInstance || !this.selectedItem) return;
                this.cropping = true;
                try {
                    const canvas = this.cropperInstance.getCroppedCanvas({
                        maxWidth: 1920,
                        maxHeight: 1920,
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });

                    const base64 = canvas.toDataURL('image/jpeg', 0.9);
                    const res = await fetch(`/admin/api/media/${this.selectedItem.id}/crop`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            image_base64: base64,
                            mode: mode,
                            width: Math.round(canvas.width),
                            height: Math.round(canvas.height),
                        }),
                    });

                    const json = await res.json();
                    if (res.ok && json.media) {
                        if (mode === 'replace') {
                            const idx = this.items.findIndex(i => i.id === json.media.id);
                            if (idx !== -1) this.items[idx] = json.media;
                        } else {
                            this.items.unshift(json.media);
                        }
                        this.selectedItem = json.media;
                        this.switchTab('library');
                        this.notify(json.message || 'Image recadrée enregistrée !');
                    } else {
                        alert(json.message || 'Impossible de recadrer cette image (vérifiez les règles CORS si image distante).');
                    }
                } catch (err) {
                    alert('Attention : Les images externes sans en-tête CORS doivent être téléversées localement pour être recadrées.');
                } finally {
                    this.cropping = false;
                }
            }
        };
    }
</script>
