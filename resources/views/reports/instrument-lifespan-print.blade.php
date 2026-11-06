<x-layouts.print title="Laporan Umur Instrumen" page-size="A4 landscape" container-class="max-w-none">

    <x-report-print-header title="Laporan Umur Instrumen{{ $showAll ? '' : ' — Mendekati Akhir Umur Pakai' }}"
        :hospital-name="$hospitalName" />

    <table class="w-full border-collapse text-xs">
        <thead>
            <tr class="border-b-2 border-gray-800">
                <th class="p-1.5 text-left">Kode</th>
                <th class="p-1.5 text-left">Nama</th>
                <th class="p-1.5 text-left">Kategori</th>
                <th class="p-1.5 text-left">Kondisi</th>
                <th class="p-1.5 text-left">Total Siklus</th>
                <th class="p-1.5 text-left">Umur (Siklus)</th>
                <th class="p-1.5 text-left">Sisa Siklus</th>
                <th class="p-1.5 text-left">% Terpakai</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                @php
                    $lifespan = $item->instrument->lifespan_cycles;
                    $pct = $lifespan ? round($item->total_cycles / $lifespan * 100, 1) : null;
                    $conditionInfo = \App\Models\InstrumentItem::CONDITIONS[$item->condition] ?? ['label' => $item->condition];
                @endphp
                <tr class="border-b border-gray-200 {{ $item->isNearingLifespan() ? 'bg-amber-50 font-semibold' : '' }}">
                    <td class="p-1.5">{{ $item->code }}</td>
                    <td class="p-1.5">{{ $item->instrument->name }}</td>
                    <td class="p-1.5">{{ $item->instrument->category->name }}</td>
                    <td class="p-1.5">{{ $conditionInfo['label'] }}</td>
                    <td class="p-1.5">{{ $item->total_cycles }}</td>
                    <td class="p-1.5">{{ $lifespan ?? 'Tidak ditentukan' }}</td>
                    <td class="p-1.5">{{ $lifespan ? $item->remaining_cycles : '-' }}</td>
                    <td class="p-1.5">{{ $pct !== null ? $pct.'%' : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="p-4 text-center text-gray-400">Tidak ada instrumen.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</x-layouts.print>
