@php
    $categoryInfo = [
        'AT_UNIT' => ['label' => 'Masih di Unit', 'action' => 'Hubungi unit — tarik'],
        'ON_SHELF' => ['label' => 'Steril di Rak', 'action' => 'Tarik dari rak'],
        'RETURNED' => ['label' => 'Sudah Kembali', 'action' => 'Sudah kembali'],
        'SEVERED' => ['label' => 'Tak Lagi Steril dari Load Ini', 'action' => '-'],
        'FAILED' => ['label' => 'Gagal Sterilisasi', 'action' => '-'],
        'PENDING' => ['label' => 'Menunggu Hasil', 'action' => '-'],
        'UNTRACEABLE' => ['label' => 'Tidak Dapat Ditelusuri', 'action' => 'Periksa manual (data lama)'],
    ];
@endphp

<x-layouts.print title="Recall — {{ $batch->load_number ?? $batch->batch_number }}" page-size="A4 landscape"
    container-class="max-w-none">

    <x-report-print-header title="Bukti Recall per Load — {{ $batch->load_number ?? $batch->batch_number }}"
        :hospital-name="$batch->hospital->name" />

    <table class="mb-6 w-full border-collapse text-xs">
        <tbody>
            <tr class="border-b border-gray-200">
                <td class="p-1.5 font-semibold">Nomor Batch</td>
                <td class="p-1.5">{{ $batch->batch_number }}</td>
                <td class="p-1.5 font-semibold">Sterilizer</td>
                <td class="p-1.5">{{ $batch->sterilizer->name }}</td>
            </tr>
            <tr class="border-b border-gray-200">
                <td class="p-1.5 font-semibold">Nomor Load</td>
                <td class="p-1.5">{{ $batch->load_number ?? '-' }}</td>
                <td class="p-1.5 font-semibold">Operator</td>
                <td class="p-1.5">{{ $batch->operator->name }}</td>
            </tr>
            <tr class="border-b border-gray-200">
                <td class="p-1.5 font-semibold">Tanggal</td>
                <td class="p-1.5">{{ $batch->started_at?->format('d/m/Y H:i') ?? '-' }}</td>
                <td class="p-1.5 font-semibold">Hasil BI</td>
                <td class="p-1.5 {{ $batch->biBlocksRelease() ? 'font-bold text-red-700' : '' }}">{{ $batch->bi_label }}</td>
            </tr>
        </tbody>
    </table>

    @if ($batch->biBlocksRelease())
        <p class="mb-4 border border-red-300 bg-red-50 p-2 text-xs font-semibold text-red-700">
            Indikator Biologis Positif — seluruh tray dari load ini harus dianggap tidak steril.
        </p>
    @endif

    <table class="w-full border-collapse text-xs">
        <thead>
            <tr class="border-b-2 border-gray-800">
                <th class="p-1.5 text-left">Tray</th>
                <th class="p-1.5 text-left">Status Kini</th>
                <th class="p-1.5 text-left">Tujuan</th>
                <th class="p-1.5 text-left">Unit</th>
                <th class="p-1.5 text-left">Tgl Keluar</th>
                <th class="p-1.5 text-left">Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php $cat = $categoryInfo[$row->category]; @endphp
                <tr class="border-b border-gray-200 {{ $row->category === 'AT_UNIT' ? 'bg-red-50 font-semibold' : '' }}">
                    <td class="p-1.5">{{ $row->tray?->code }} — {{ $row->tray?->name ?? 'Tray telah dihapus permanen' }}</td>
                    <td class="p-1.5">{{ $cat['label'] }}</td>
                    <td class="p-1.5">{{ $row->distribution_request?->request_number ?? '-' }}</td>
                    <td class="p-1.5">{{ $row->distribution_request?->unit?->name ?? '-' }}</td>
                    <td class="p-1.5">{{ $row->issued_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="p-1.5">{{ $cat['action'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-4 text-center text-gray-400">Tidak ada tray dalam load ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</x-layouts.print>
