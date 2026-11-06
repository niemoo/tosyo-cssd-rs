<x-layouts.print title="Laporan Produktivitas" page-size="A4 landscape" container-class="max-w-none">

    <x-report-print-header title="Laporan Produktivitas" :hospital-name="$hospitalName" :from="$from" :to="$to" />

    <table class="mb-6 w-full border-collapse text-xs">
        <tbody>
            <tr class="border-b border-gray-200">
                <td class="p-1.5 font-semibold">Total Tray Disterilkan</td>
                <td class="p-1.5">{{ $summary['total_trays'] }}</td>
            </tr>
            <tr class="border-b border-gray-200">
                <td class="p-1.5 font-semibold">Total Load</td>
                <td class="p-1.5">{{ $summary['total_loads'] }}</td>
            </tr>
            <tr class="border-b border-gray-200">
                <td class="p-1.5 font-semibold">Tingkat Kegagalan</td>
                <td class="p-1.5">{{ $summary['failure_rate'] }}%</td>
            </tr>
            <tr class="border-b border-gray-200">
                <td class="p-1.5 font-semibold">Tray Kedaluwarsa</td>
                <td class="p-1.5">{{ $summary['expired_trays'] }}</td>
            </tr>
        </tbody>
    </table>

    <h3 class="mb-2 text-sm font-bold text-gray-900">Per Operator</h3>
    <table class="mb-6 w-full border-collapse text-xs">
        <thead>
            <tr class="border-b-2 border-gray-800">
                <th class="p-1.5 text-left">Operator</th>
                <th class="p-1.5 text-left">Load</th>
                <th class="p-1.5 text-left">Tray</th>
                <th class="p-1.5 text-left">Gagal</th>
                <th class="p-1.5 text-left">Tingkat Kegagalan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($byOperator as $row)
                <tr class="border-b border-gray-200">
                    <td class="p-1.5">{{ $row['name'] }}</td>
                    <td class="p-1.5">{{ $row['loads'] }}</td>
                    <td class="p-1.5">{{ $row['trays'] }}</td>
                    <td class="p-1.5">{{ $row['failed'] }}</td>
                    <td class="p-1.5">{{ $row['failure_rate'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-4 text-center text-gray-400">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3 class="mb-2 text-sm font-bold text-gray-900">Per Sterilizer</h3>
    <table class="w-full border-collapse text-xs">
        <thead>
            <tr class="border-b-2 border-gray-800">
                <th class="p-1.5 text-left">Sterilizer</th>
                <th class="p-1.5 text-left">Siklus</th>
                <th class="p-1.5 text-left">Tray</th>
                <th class="p-1.5 text-left">Gagal</th>
                <th class="p-1.5 text-left">Tingkat Kegagalan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bySterilizer as $row)
                <tr class="border-b border-gray-200">
                    <td class="p-1.5">{{ $row['name'] }}</td>
                    <td class="p-1.5">{{ $row['loads'] }}</td>
                    <td class="p-1.5">{{ $row['trays'] }}</td>
                    <td class="p-1.5">{{ $row['failed'] }}</td>
                    <td class="p-1.5">{{ $row['failure_rate'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-4 text-center text-gray-400">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</x-layouts.print>
