<div
    x-data="{
        isOpen: false,
        title: 'Confirmation requise',
        message: 'Êtes-vous sûr de vouloir effectuer cette action critique ?',
        confirmText: 'Confirmer',
        cancelText: 'Annuler',
        variant: 'danger',
        icon: 'alert-triangle',
        targetFormId: null,
        targetFormEl: null,
        callback: null,

        open(payload = {}) {
            this.title = payload.title || 'Confirmation requise';
            this.message = payload.message || 'Êtes-vous sûr de vouloir effectuer cette action ?';
            this.confirmText = payload.confirmText || 'Confirmer';
            this.cancelText = payload.cancelText || 'Annuler';
            this.variant = payload.variant || 'danger';
            this.icon = payload.icon || (this.variant === 'danger' ? 'alert-triangle' : 'info');
            this.targetFormId = payload.formId || null;
            this.targetFormEl = payload.form || null;
            this.callback = typeof payload.onConfirm === 'function' ? payload.onConfirm : null;
            this.isOpen = true;

            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        close() {
            this.isOpen = false;
            this.targetFormId = null;
            this.targetFormEl = null;
            this.callback = null;
        },

        confirm() {
            const formId = this.targetFormId;
            const formEl = this.targetFormEl;
            const cb = this.callback;

            this.close();

            if (cb) {
                cb();
            } else if (formEl && typeof formEl.submit === 'function') {
                formEl.submit();
            } else if (formId) {
                const el = document.getElementById(formId);
                if (el && typeof el.submit === 'function') {
                    el.submit();
                }
            }
        }
    }"
    @open-confirm-dialog.window="open($event.detail)"
    x-init="
        window.confirmAction = function(options) {
            window.dispatchEvent(new CustomEvent('open-confirm-dialog', { detail: options }));
        };
    "
    x-show="isOpen"
    x-cloak
    class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs transition-opacity"
    @keydown.escape.window="close()"
>
    <div
        @click.outside="close()"
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-3 sm:scale-95"
        class="bg-white rounded-xl border border-slate-200 shadow-xl w-full max-w-md overflow-hidden relative"
        role="dialog"
        aria-modal="true"
    >
        <!-- Barre supérieure colorée selon la sévérité -->
        <div
            class="h-1 w-full"
            :class="{
                'bg-red-700': variant === 'danger',
                'bg-amber-500': variant === 'warning',
                'bg-slate-900': variant === 'info'
            }"
        ></div>

        <div class="p-5">
            <div class="flex items-start gap-3.5">
                <!-- Icône dynamique avec pastille -->
                <div
                    class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                    :class="{
                        'bg-red-50 text-red-700 border border-red-200': variant === 'danger',
                        'bg-amber-50 text-amber-700 border border-amber-200': variant === 'warning',
                        'bg-slate-100 text-slate-800 border border-slate-200': variant === 'info'
                    }"
                >
                    <template x-if="icon === 'log-out'">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                    </template>
                    <template x-if="icon === 'trash-2'">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </template>
                    <template x-if="icon === 'user-x'">
                        <i data-lucide="user-x" class="w-5 h-5"></i>
                    </template>
                    <template x-if="icon === 'alert-triangle' || (icon !== 'log-out' && icon !== 'trash-2' && icon !== 'user-x')">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </template>
                </div>

                <div class="space-y-1 flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-slate-900 tracking-tight" x-text="title"></h3>
                    <p class="text-xs text-slate-600 leading-relaxed" x-text="message"></p>
                </div>
            </div>

            <!-- Actions de confirmation -->
            <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-end gap-2">
                <button
                    type="button"
                    @click="close()"
                    class="btn btn-md btn-secondary"
                    x-text="cancelText"
                ></button>

                <button
                    type="button"
                    @click="confirm()"
                    class="btn btn-md"
                    :class="{
                        'btn-primary': variant === 'danger',
                        'bg-amber-600 hover:bg-amber-700 text-white shadow-2xs': variant === 'warning',
                        'btn-dark': variant === 'info'
                    }"
                >
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span x-text="confirmText"></span>
                </button>
            </div>
        </div>
    </div>
</div>
