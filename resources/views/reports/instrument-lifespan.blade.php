<x-layouts.app title="Laporan Umur Instrumen">

    <x-slot name="breadcrumb">
        <a href="{{ route('reports.index') }}" class="hover:text-gray-600">Laporan</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">Umur Instrumen</span>
    </x-slot>

    <x-slot name="pageHeader">Umur Instrumen</x-slot>
    <x-slot name="pageSubHeader">{{ $hospitalName }}</x-slot>

    <x-slot name="pageActions">
        <a href="{{ route('reports.instrument-lifespan.print', request()->query()) }}" target="_blank"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 00-1 1v4a1 1 0 001 1zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Cetak
        </a>
        <a href="{{ route('reports.instrument-lifespan.csv', request()->query()) }}"
            class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Unduh CSV
        </a>
    </x-slot>

    <div class="mb-4 flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4">
        <p class="text-sm text-gray-600">
            @if ($showAll)
                Menampilkan <span class="font-semibold">semua</span> instrumen aktif.
            @else
                Menampilkan instrumen yang <span class="font-semibold text-amber-600">mendekati akhir umur pakai</span>
                (≥90% siklus terpakai).
            @endif
        </p>
        <a href="{{ route('reports.instrument-lifespan', ['all' => $showAll ? 0 : 1]) }}"
            class="text-sm font-semibold text-primary-500 hover:text-primary-600">
            {{ $showAll ? 'Tampilkan yang mendekati akhir saja →' : 'Lihat semua instrumen →' }}
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Kode</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Nama</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Kategori</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Kondisi</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">Total Siklus</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">Umur (Siklus)</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">Sisa Siklus</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">% Terpakai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($items as $item)
                        @php
                            $lifespan = $item->instrument->lifespan_cycles;
                            $pct = $lifespan ? round($item->total_cycles / $lifespan * 100, 1) : null;
                            $conditionInfo = \App\Models\InstrumentItem::CONDITIONS[$item->condition] ?? ['label' => $item->condition, 'color' => 'gray'];
                        @endphp
                        <tr class="{{ $item->isNearingLifespan() ? 'bg-amber-50' : '' }}">
                            <td class="px-4 py-3 text-xs font-semibold text-gray-900">{{ $item->code }}</td>
                            <td class="px-4 py-3 text-xs text-gray-700">{{ $item->instrument->name }}</td>
                            <td class="px-4 py-3 text-xs text-gray-700">{{ $item->instrument->category->name }}</td>
                            <td class="px-4 py-3 text-xs">
                                <x-badge :color="$conditionInfo['color']">{{ $conditionInfo['label'] }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-center text-xs font-semibold text-gray-900">{{ $item->total_cycles }}</td>
                            <td class="px-4 py-3 text-center text-xs text-gray-700">{{ $lifespan ?? 'Tidak ditentukan' }}</td>
                            <td class="px-4 py-3 text-center text-xs text-gray-700">{{ $lifespan ? $item->remaining_cycles : '-' }}</td>
                            <td class="px-4 py-3 text-center text-xs {{ $pct !== null && $pct >= 90 ? 'font-bold text-red-600' : 'text-gray-700' }}">
                                {{ $pct !== null ? $pct.'%' : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-400">
                                Tidak ada instrumen {{ $showAll ? '' : 'yang mendekati akhir umur pakai' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
