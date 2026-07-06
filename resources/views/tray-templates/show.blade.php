<x-layouts.app title="{{ $trayTemplate->name }}">

    <x-slot name="backButton">
        <a href="{{ route('tray-templates.index') }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    <x-slot name="breadcrumb">
        <a href="{{ route('tray-templates.index') }}" class="hover:text-gray-600">Tray Template</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">{{ $trayTemplate->name }}</span>
    </x-slot>

    <x-slot name="pageHeader">{{ $trayTemplate->name }}</x-slot>
    <x-slot name="pageSubHeader">{{ $trayTemplate->code }} · {{ $trayTemplate->hospital->name }}</x-slot>

    <x-slot name="pageActions">
        @can('tray-templates.edit')
            <a href="{{ route('tray-templates.edit', $trayTemplate) }}"
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

            {{-- Info Template --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Informasi Template</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach ([['label' => 'Nama', 'value' => $trayTemplate->name], ['label' => 'Kode', 'value' => $trayTemplate->code], ['label' => 'Deskripsi', 'value' => $trayTemplate->description ?? '-'], ['label' => 'Rumah Sakit', 'value' => $trayTemplate->hospital->name]] as $item)
                        <div class="flex items-start justify-between px-5 py-3">
                            <span class="w-32 shrink-0 text-xs text-gray-400">{{ $item['label'] }}</span>
                            <span class="text-right text-sm font-medium text-gray-700">{{ $item['value'] }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Lockable</span>
                        @if ($trayTemplate->is_lockable)
                            <x-badge color="blue">Ya</x-badge>
                        @else
                            <x-badge color="gray">Tidak</x-badge>
                        @endif
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Status</span>
                        <x-badge :color="$trayTemplate->is_active ? 'green' : 'red'" dot>
                            {{ $trayTemplate->is_active ? 'Aktif' : 'Non-aktif' }}
                        </x-badge>
                    </div>
                </div>
            </div>

            {{-- Daftar Instrumen --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Daftar Instrumen</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">{{ $trayTemplate->templateItems->count() }} jenis</span>
                        <span class="text-xs text-gray-300">·</span>
                        <span class="text-xs text-gray-400">{{ $trayTemplate->total_instruments }} total unit</span>
                    </div>
                </div>
                @if ($trayTemplate->templateItems->count() > 0)
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-50 bg-gray-50/50">
                                <th
                                    class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    Instrumen</th>
                                <th
                                    class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    Kategori</th>
                                <th
                                    class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($trayTemplate->templateItems as $item)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-5 py-3">
                                        <div class="font-medium text-gray-900">{{ $item->instrument->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $item->instrument->code }}</div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <x-badge color="blue">{{ $item->instrument->category->name }}</x-badge>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="text-sm font-semibold text-gray-900">{{ $item->quantity }}</span>
                                        <span class="text-xs text-gray-400"> unit</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="px-5 py-8 text-center text-sm text-gray-400">
                        Belum ada instrumen dalam template ini
                    </div>
                @endif
            </div>

        </div>

        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h4 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Audit Trail</h4>
                <div class="space-y-3">
                    @foreach ([['label' => 'Dibuat oleh', 'value' => $trayTemplate->creator?->name ?? '-'], ['label' => 'Dibuat pada', 'value' => $trayTemplate->created_at->format('d M Y, H:i')], ['label' => 'Diperbarui oleh', 'value' => $trayTemplate->updater?->name ?? '-'], ['label' => 'Diperbarui pada', 'value' => $trayTemplate->updated_at->format('d M Y, H:i')]] as $audit)
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