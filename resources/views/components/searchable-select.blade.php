@props([
    'name' => null,
    'dynamicName' => null,
    'model' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Pilih...',
    'searchPlaceholder' => 'Cari...',
    'emptyText' => 'Tidak ditemukan',
    'onChange' => null,
    'error' => false,
    'disabled' => false,
])

@php
    $uid = 'ss-' . \Illuminate\Support\Str::random(8);
    $valueExpr = $model ?? 'selected';
    $selfExpr = $model ?? 'this.selected';
    $normalizedOptions = collect($options)->map(fn ($option) => [
        'id'    => (string) $option['id'],
        'label' => (string) $option['label'],
    ])->values();
@endphp

<div {{ $attributes->class(['relative']) }} x-data="{
        open: false,
        search: '',
        @if (!$model) selected: @js((string) ($selected ?? '')), @endif
        options: @js($normalizedOptions),
        get filtered() {
            if (!this.search) return this.options;
            const s = this.search.toLowerCase();
            return this.options.filter(o => o.label.toLowerCase().includes(s));
        },
        get selectedLabel() {
            const found = this.options.find(o => o.id === ({{ $selfExpr }}));
            return found ? found.label : '';
        },
        pos: { top: 0, left: 0, width: 0 },
        toggle(btn) {
            if (this.open) { this.open = false; return; }
            const rect = btn.getBoundingClientRect();
            this.pos = { top: rect.bottom + 4, left: rect.left, width: rect.width };
            this.open = true;
            this.$nextTick(() => {
                const el = document.getElementById('{{ $uid }}-search');
                if (el) el.focus();
            });
        },
        choose(option) {
            {{ $selfExpr }} = option.id;
            this.open = false;
            this.search = '';
            @if ($onChange)
                {{ $onChange }};
            @endif
        }
    }" @click.outside="open = false"
    x-init="const closeOnScroll = () => { if (open) open = false; };
    const mainEl = document.querySelector('main');
    if (mainEl) mainEl.addEventListener('scroll', closeOnScroll, { passive: true });">

    @if ($dynamicName)
        <input type="hidden" :name="{{ $dynamicName }}" :value="{{ $valueExpr }}" />
    @else
        <input type="hidden" name="{{ $name }}" :value="{{ $valueExpr }}" />
    @endif

    <button type="button" @click="toggle($el)" {{ $disabled ? 'disabled' : '' }}
        class="flex w-full items-center justify-between rounded-lg border bg-gray-50 px-3.5 py-2.5 text-sm transition
               hover:border-gray-300 focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
               disabled:cursor-not-allowed disabled:opacity-60
               {{ $error ? 'border-red-400' : 'border-gray-200' }}"
        :class="({{ $valueExpr }}) ? 'text-gray-900' : 'text-gray-400'">
        <span class="truncate" x-text="({{ $valueExpr }}) ? selectedLabel : '{{ $placeholder }}'"></span>
        <svg class="ml-2 h-4 w-4 shrink-0 text-gray-400" :class="open ? 'rotate-180' : ''" fill="none"
            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <template x-teleport="#dropdown-portal">
        <div x-show="open" x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            :style="`position: fixed; top: ${pos.top}px; left: ${pos.left}px; width: ${pos.width}px; z-index: 9999;`"
            class="rounded-xl border border-gray-200 bg-white shadow-lg" style="display: none;" x-cloak>
            <div class="border-b border-gray-50 p-2">
                <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                    <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path stroke-linecap="round" d="m21 21-4.35-4.35" />
                    </svg>
                    <input type="text" x-model="search" id="{{ $uid }}-search" placeholder="{{ $searchPlaceholder }}"
                        @click.stop @keydown.escape="open = false"
                        class="w-full border-none bg-transparent p-0 text-xs text-gray-600 placeholder-gray-300 focus:ring-0 focus:outline-none" />
                </div>
            </div>
            <div class="max-h-48 overflow-y-auto py-1">
                <template x-for="option in filtered" :key="option.id">
                    <button type="button" @click="choose(option)"
                        class="flex w-full items-center px-3 py-2 text-left text-xs transition hover:bg-gray-50"
                        :class="({{ $valueExpr }}) === option.id ? 'bg-primary-50 text-primary-600 font-medium' : 'text-gray-700'">
                        <span x-text="option.label"></span>
                    </button>
                </template>
                <div x-show="filtered.length === 0" class="px-3 py-4 text-center text-xs text-gray-400">
                    {{ $emptyText }}
                </div>
            </div>
        </div>
    </template>
</div>
