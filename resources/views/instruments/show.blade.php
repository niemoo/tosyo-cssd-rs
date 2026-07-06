<x-layouts.app title="{{ $instrument->name }}">

    <x-slot name="backButton">
        <a href="{{ route('instruments.index') }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    <x-slot name="breadcrumb">
        <a href="{{ route('instruments.index') }}" class="hover:text-gray-600">Instrumen</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">{{ $instrument->name }}</span>
    </x-slot>

    <x-slot name="pageHeader">{{ $instrument->name }}</x-slot>
    <x-slot name="pageSubHeader">{{ $instrument->code }} · {{ $instrument->category->name }}</x-slot>

    <x-slot name="pageActions">
        @can('instruments.edit')
            <a href="{{ route('instruments.edit', $instrument) }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
        @endcan
    </x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="space-y-4 lg:col-span-2">

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Informasi Instrumen</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach ([['label' => 'Nama', 'value' => $instrument->name], ['label' => 'Kode', 'value' => $instrument->code], ['label' => 'Kategori', 'value' => $instrument->category->name], ['label' => 'Rumah Sakit', 'value' => $instrument->hospital->name], ['label' => 'Brand', 'value' => $instrument->brand ?? '-'], ['label' => 'Material', 'value' => $instrument->material ?? '-'], ['label' => 'Masa Pakai', 'value' => $instrument->lifespan_cycles ? $instrument->lifespan_cycles . ' siklus' : '-'], ['label' => 'Total Item', 'value' => $instrument->items->count() . ' item']] as $item)
                        <div class="flex items-start justify-between px-5 py-3">
                            <span class="w-32 shrink-0 text-xs text-gray-400">{{ $item['label'] }}</span>
                            <span class="text-right text-sm font-medium text-gray-700">{{ $item['value'] }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Status</span>
                        <x-badge :color="$instrument->is_active ? 'green' : 'red'" dot>
                            {{ $instrument->is_active ? 'Aktif' : 'Non-aktif' }}
                        </x-badge>
                    </div>
                </div>
            </div>

            @if ($instrument->items->count() > 0)
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="border-b border-gray-50 px-5 py-3.5">
                        <h3 class="text-sm font-bold text-gray-900">Daftar Item Fisik</h3>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach ($instrument->items as $item)
                            <div class="flex items-center justify-between px-5 py-3">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $item->serial_number }}</div>
                                    <div class="text-xs text-gray-400">{{ $item->condition ?? '-' }}</div>
                                </div>
                                <x-badge :color="$item->is_active ? 'green' : 'red'" dot>
                                    {{ $item->is_active ? 'Aktif' : 'Non-aktif' }}
                                </x-badge>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h4 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Audit Trail</h4>
                <div class="space-y-3">
                    @foreach ([['label' => 'Dibuat oleh', 'value' => $instrument->creator?->name ?? '-'], ['label' => 'Dibuat pada', 'value' => $instrument->created_at->format('d M Y, H:i')], ['label' => 'Diperbarui oleh', 'value' => $instrument->updater?->name ?? '-'], ['label' => 'Diperbarui pada', 'value' => $instrument->updated_at->format('d M Y, H:i')]] as $audit)
                        <div class="flex flex-col gap-0.5">
                            <span class="text-[10px] text-gray-400">{{ $audit['label'] }}</span>
                            <span class="text-xs font-semibold text-gray-700">{{ $audit['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

</x-layouts.app>
