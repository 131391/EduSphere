@php
    $success = session('success');
    $error   = session('error');
    $info    = session('info');
    $warning = session('warning');

    $activeMessage = $success ?? $error ?? $info ?? $warning;
    $type = $success ? 'success' : ($error ? 'error' : ($info ? 'info' : 'warning'));
@endphp

<div id="edu-toast-container"
     x-data="eduToastComponent()"
     class="fixed top-6 right-6 z-[10000] flex flex-col gap-3 items-end pointer-events-none"
     x-cloak>

    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
             class="pointer-events-auto w-80 bg-white rounded-2xl shadow-lg border overflow-hidden"
             :class="{
                 'border-emerald-100': toast.type === 'success',
                 'border-red-100':     toast.type === 'error',
                 'border-blue-100':    toast.type === 'info',
                 'border-amber-100':   toast.type === 'warning',
             }">

            {{-- Main row --}}
            <div class="flex items-start gap-3 px-4 pt-4 pb-3">

                {{-- Icon --}}
                <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5"
                     :class="{
                         'bg-emerald-100 text-emerald-600': toast.type === 'success',
                         'bg-red-100 text-red-600':         toast.type === 'error',
                         'bg-blue-100 text-blue-600':       toast.type === 'info',
                         'bg-amber-100 text-amber-600':     toast.type === 'warning',
                     }">
                    <i class="text-sm"
                       :class="{
                           'fas fa-check':              toast.type === 'success',
                           'fas fa-times':              toast.type === 'error',
                           'fas fa-info':               toast.type === 'info',
                           'fas fa-exclamation':        toast.type === 'warning',
                       }"></i>
                </div>

                {{-- Text --}}
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wide mb-0.5"
                       :class="{
                           'text-emerald-700': toast.type === 'success',
                           'text-red-700':     toast.type === 'error',
                           'text-blue-700':    toast.type === 'info',
                           'text-amber-700':   toast.type === 'warning',
                       }"
                       x-text="{
                           success: 'Success',
                           error:   'Error',
                           info:    'Info',
                           warning: 'Warning'
                       }[toast.type]"></p>
                    <p class="text-sm text-gray-700 leading-snug break-words" x-text="toast.message"></p>
                </div>

                {{-- Close --}}
                <button @click="dismiss(toast.id)"
                        class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-lg text-gray-300 hover:text-gray-500 hover:bg-gray-100 transition-colors mt-0.5">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>

            {{-- Progress bar --}}
            <div class="h-0.5 w-full"
                 :class="{
                     'bg-emerald-100': toast.type === 'success',
                     'bg-red-100':     toast.type === 'error',
                     'bg-blue-100':    toast.type === 'info',
                     'bg-amber-100':   toast.type === 'warning',
                 }">
                <div class="h-full transition-none rounded-full"
                     :class="{
                         'bg-emerald-500': toast.type === 'success',
                         'bg-red-500':     toast.type === 'error',
                         'bg-blue-500':    toast.type === 'info',
                         'bg-amber-500':   toast.type === 'warning',
                     }"
                     :style="`width: ${toast.progress}%; transition: width ${toast.duration}ms linear`"
                     x-ref="bar"></div>
            </div>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('eduToastComponent', () => ({
            toasts: [],
            counter: 0,

            init() {
                @if($activeMessage)
                    this.add(@json($activeMessage), @json($type));
                @endif

                window.addEventListener('show-toast', (e) => {
                    this.add(e.detail.message, e.detail.type || 'info');
                });
            },

            add(message, type = 'info', duration = 4500) {
                const id = ++this.counter;
                const toast = { id, message, type, duration, progress: 100, visible: true };
                this.toasts.push(toast);

                // Kick off progress bar shrink on next tick so transition applies
                this.$nextTick(() => {
                    const t = this.toasts.find(t => t.id === id);
                    if (t) t.progress = 0;
                });

                setTimeout(() => this.dismiss(id), duration);
            },

            dismiss(id) {
                const toast = this.toasts.find(t => t.id === id);
                if (toast) {
                    toast.visible = false;
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 300);
                }
            }
        }));
    });
</script>
