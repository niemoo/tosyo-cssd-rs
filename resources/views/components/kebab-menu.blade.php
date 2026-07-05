@props(['id'])

<div class="relative flex justify-end" x-data="{
    open: false,
    pos: { top: 0, right: 0 },
    toggle(btn) {
        if (this.open) {
            this.open = false;
            return;
        }
        this.btnRect = btn.getBoundingClientRect();
        this.open = true;
        this.$nextTick(() => {
            const portal = document.querySelector('#dropdown-portal > div:last-child');
            if (!portal) return;
            const dropdownHeight = portal.offsetHeight;
            const viewportHeight = window.innerHeight;
            const spaceBelow = viewportHeight - this.btnRect.bottom;
            if (spaceBelow < dropdownHeight + 8) {
                this.pos = {
                    top: this.btnRect.top - dropdownHeight - 4,
                    right: document.documentElement.clientWidth - this.btnRect.right,
                };
            } else {
                this.pos = {
                    top: this.btnRect.bottom + 4,
                    right: document.documentElement.clientWidth - this.btnRect.right,
                };
            }
        });
    }
}" x-init="const closeOnScroll = () => { if (open) open = false; };
const mainEl = document.querySelector('main');
if (mainEl) mainEl.addEventListener('scroll', closeOnScroll, { passive: true });
window.addEventListener('scroll', closeOnScroll, { passive: true });" @click.outside="open = false"
    @keydown.escape.window="open = false">

    <button @click="toggle($el)"
        class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:bg-gray-50">
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="5" r="1.5" />
            <circle cx="12" cy="12" r="1.5" />
            <circle cx="12" cy="19" r="1.5" />
        </svg>
    </button>

    <template x-teleport="#dropdown-portal">
        <div x-show="open" x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            :style="`position: fixed; top: ${pos.top}px; right: ${pos.right}px; z-index: 9999;`"
            class="w-44 rounded-xl border border-gray-100 bg-white py-1 shadow-lg" style="display: none;" x-cloak>
            {{ $slot }}
        </div>
    </template>

</div>
