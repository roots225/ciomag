@props([
    'name' => 'featured_media_id',
    'valueMode' => 'id', // 'id' (UUID media) ou 'url' (URL directe)
    'value' => '',
    'previewUrl' => '',
    'previewName' => '',
    'label' => null,
])

<div
    x-data="{
        fieldValue: @js((string) ($value ?? '')),
        mediaId: @js($valueMode === 'id' ? (string) ($value ?? '') : ''),
        imageUrl: @js((string) ($previewUrl ?: ($valueMode === 'url' ? ($value ?? '') : ''))),
        fileName: @js((string) ($previewName ?: '')),
        valueMode: @js($valueMode),
        openPicker(openCrop = false) {
            if (typeof window.openMediaManager === 'function') {
                window.openMediaManager({
                    mediaId: this.mediaId || null,
                    openCrop: openCrop,
                    onSelect: (media) => {
                        this.mediaId = media.id;
                        this.imageUrl = media.url;
                        this.fileName = media.file_name;
                        this.fieldValue = this.valueMode === 'url' ? media.url : media.id;
                    }
                });
            }
        },
        clearMedia() {
            this.fieldValue = '';
            this.mediaId = '';
            this.imageUrl = '';
            this.fileName = '';
        }
    }"
    class="space-y-2"
>
    @if($label)
        <label class="admin-label">{{ $label }}</label>
    @endif

    <input type="hidden" name="{{ $name }}" :value="fieldValue">

    <!-- État : Aucun média sélectionné -->
    <div
        x-show="!imageUrl"
        @click="openPicker(false)"
        class="border-2 border-dashed border-slate-300 hover:border-primary bg-slate-50 hover:bg-red-50/20 rounded-xl p-5 text-center cursor-pointer transition group"
    >
        <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 text-slate-500 group-hover:text-primary mx-auto flex items-center justify-center mb-2 shadow-2xs">
            <i data-lucide="image-plus" class="w-5 h-5"></i>
        </div>
        <p class="text-xs font-semibold text-slate-800 group-hover:text-primary transition">Choisir, téléverser ou recadrer un média</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Ouvrir la médiathèque CIOMAG</p>
    </div>

    <!-- État : Média sélectionné avec aperçu et actions -->
    <div
        x-show="imageUrl"
        style="display: none;"
        class="rounded-xl border border-slate-200 bg-slate-50 overflow-hidden"
    >
        <div class="relative h-40 bg-slate-900 flex items-center justify-center overflow-hidden">
            <img :src="imageUrl" alt="Preview" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
            <div class="absolute bottom-2 left-3 right-3 flex items-center justify-between text-white text-xs">
                <span class="font-semibold truncate" x-text="fileName || 'Visuel sélectionné'"></span>
            </div>
        </div>

        <div class="p-2.5 bg-white flex items-center justify-between gap-2 text-xs">
            <button
                type="button"
                @click="openPicker(false)"
                class="flex-1 btn btn-sm btn-secondary justify-center"
            >
                <i data-lucide="replace" class="w-3.5 h-3.5"></i>
                <span>Changer</span>
            </button>

            <button
                type="button"
                x-show="mediaId"
                @click="openPicker(true)"
                class="btn btn-sm btn-secondary text-amber-700 hover:text-amber-800 justify-center"
                title="Recadrer ce média"
            >
                <i data-lucide="crop" class="w-3.5 h-3.5"></i>
                <span>Recadrer</span>
            </button>

            <button
                type="button"
                @click="clearMedia()"
                class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer"
                title="Retirer"
            >
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>
