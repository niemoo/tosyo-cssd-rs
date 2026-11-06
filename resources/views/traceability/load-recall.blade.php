<x-layouts.app title="Recall per Load">

    <x-slot name="pageHeader">Recall per Load</x-slot>
    <x-slot name="pageSubHeader">Cari nomor load atau batch untuk melihat dampak distribusinya</x-slot>

    @if ($batch)
        <x-slot name="pageActions">
            @can('trays.view')
                <a href="{{ route('traceability.load-recall.print', ['batch_id' => $batch->id]) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 00-1 1v4a1 1 0 001 1zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak
                </a>
            @endcan
        </x-slot>
    @endif

    {{-- Pencarian --}}
    <div class="mb-4 rounded-xl border border-gray-200 bg-white p-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex flex-1 items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 min-w-[240px]">
                <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path stroke-linecap="round" d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nomor load atau batch..."
                    class="w-full border-none bg-transparent p-0 text-sm text-gray-600 placeholder-gray-300 focus:ring-0 focus:outline-none" />
            </div>

            @if ($multiHospital)
                <x-searchable-select name="hospital_id" placeholder="Semua RS"
                    :options="$userHospitals->map(fn ($h) => ['id' => (string) $h->id, 'label' => $h->name])->values()"
                    :selected="request('hospital_id')" />
            @endif

            <button type="submit"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">
                Cari
            </button>

            @if (request()->hasAny(['search', 'hospital_id', 'batch_id']))
                <a href="{{ route('traceability.load-recall') }}" class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>
    </div>

    @if (request()->filled('batch_id') && ! $batch)
        <x-empty-state title="Load tidak ditemukan"
            description="Batch/load tidak ditemukan, atau bukan bagian dari rumah sakit Anda." />
    @elseif (! $batch && ! $matches)
        <x-empty-state title="Mulai pencarian load"
            description="Masukkan nomor load atau nomor batch untuk melihat dampak recall-nya."
            icon="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    @elseif ($matches)
        {{-- Daftar hasil pencarian --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-50 px-5 py-3.5">
                <p class="text-xs text-gray-400">
                    Ditemukan <span class="font-semibold text-gray-700">{{ $matches->total() }}</span> load — pilih salah
                    satu untuk melihat dampaknya
                </p>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse ($matches as $b)
                    @php $statusInfo = \App\Models\SterilizationBatch::STATUSES[$b->status] ?? ['label' => $b->status, 'color' => 'gray']; @endphp
                    <a href="{{ route('traceability.load-recall', array_merge(request()->only(['search', 'hospital_id']), ['batch_id' => $b->id])) }}"
                        class="flex items-center justify-between px-5 py-3 transition hover:bg-gray-50/50 {{ $b->trashed() ? 'opacity-50' : '' }}">
                        <div>
                            <div class="font-semibold text-gray-900">
                                {{ $b->load_number ?? $b->batch_number }}
                                <span class="ml-1 rounded-md bg-gray-100 px-1.5 py-0.5 text-[11px] font-bold text-gray-500">{{ $b->batch_number }}</span>
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ $b->sterilizer->name }} · {{ $b->started_at?->format('d M Y') ?? '-' }}
                                @if ($b->trashed())
                                    · <span class="text-red-500">Dihapus</span>
                                @endif
                            </div>
                        </div>
                        <x-badge :color="$statusInfo['color']" dot>{{ $statusInfo['label'] }}</x-badge>
                    </a>
                @empty
                    <x-empty-state title="Tidak ada load ditemukan"
                        description="Coba kata kunci lain, atau periksa filter rumah sakit." />
                @endforelse
            </div>

            @if ($matches->hasPages())
                <div class="border-t border-gray-50 px-5 py-3.5">
                    {{ $matches->links('components.pagination') }}
                </div>
            @endif
        </div>
    @elseif ($batch)
        @php
            $statusInfo = \App\Models\SterilizationBatch::STATUSES[$batch->status] ?? ['label' => $batch->status, 'color' => 'gray'];
            $bowieDick = \App\Models\SterilizationBatch::BOWIE_DICK[$batch->bowie_dick_result] ?? null;

            $categoryInfo = [
                'AT_UNIT' => ['label' => 'Masih di Unit', 'color' => 'red', 'action' => 'Hubungi unit — tarik'],
                'ON_SHELF' => ['label' => 'Steril di Rak', 'color' => 'blue', 'action' => 'Tarik dari rak'],
                'RETURNED' => ['label' => 'Sudah Kembali', 'color' => 'green', 'action' => 'Sudah kembali ✓'],
                'SEVERED' => ['label' => 'Tak Lagi Steril dari Load Ini', 'color' => 'gray', 'action' => '-'],
                'FAILED' => ['label' => 'Gagal Sterilisasi', 'color' => 'red', 'action' => '-'],
                'PENDING' => ['label' => 'Menunggu Hasil', 'color' => 'gray', 'action' => '-'],
                'UNTRACEABLE' => ['label' => 'Tidak Dapat Ditelusuri', 'color' => 'amber', 'action' => 'Periksa manual (data lama)'],
            ];
        @endphp

        @if ($batch->biBlocksRelease())
            <div class="mb-4 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <div class="text-sm font-bold text-red-700">Indikator Biologis Positif — Load Ini Gagal</div>
                    <p class="mt-0.5 text-sm text-red-600">
                        Uji spora menunjukkan pertumbuhan. Seluruh tray dari load ini harus dianggap tidak steril —
                        tarik yang masih di rak dan hubungi unit untuk yang sudah terlanjur keluar.
                    </p>
                </div>
            </div>
        @endif

        @if ($kpi['untraceable'] > 0)
            <div class="mb-4 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <div class="text-sm font-bold text-amber-700">Data Tidak Lengkap</div>
                    <p class="mt-0.5 text-sm text-amber-600">
                        {{ $kpi['untraceable'] }} tray dalam load ini tidak memiliki jejak digital (dibuat sebelum
                        pencatatan riwayat diaktifkan). Sistem tidak dapat memastikan ke mana tray tersebut pergi —
                        periksa manual.
                    </p>
                </div>
            </div>
        @endif

        {{-- Info Load --}}
        <div class="mb-4 overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-50 px-5 py-3.5">
                <h3 class="text-sm font-bold text-gray-900">Informasi Load</h3>
            </div>
            <div class="grid grid-cols-1 divide-y divide-gray-50 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                <div class="divide-y divide-gray-50">
                    @foreach ([['label' => 'Nomor Batch', 'value' => $batch->batch_number], ['label' => 'Nomor Load', 'value' => $batch->load_number ?? '-'], ['label' => 'Sterilizer', 'value' => $batch->sterilizer->name . ' (' . $batch->sterilizer->type . ')'], ['label' => 'Operator', 'value' => $batch->operator->name]] as $item)
                        <div class="flex items-start justify-between px-5 py-3">
                            <span class="w-32 shrink-0 text-xs text-gray-400">{{ $item['label'] }}</span>
                            <span class="text-right text-sm font-medium text-gray-700">{{ $item['value'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="divide-y divide-gray-50">
                    <div class="flex items-start justify-between px-5 py-3">
                        <span class="w-32 shrink-0 text-xs text-gray-400">Tanggal</span>
                        <span class="text-right text-sm font-medium text-gray-700">{{ $batch->started_at?->format('d M Y, H:i') ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Status Batch</span>
                        <x-badge :color="$statusInfo['color']" dot>{{ $statusInfo['label'] }}</x-badge>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Indikator Biologis (BI)</span>
                        <x-badge :color="$batch->bi_color" dot>{{ $batch->bi_label }}</x-badge>
                    </div>
                    @if ($bowieDick)
                        <div class="flex items-center justify-between px-5 py-3">
                            <span class="text-xs text-gray-400">Uji Bowie-Dick</span>
                            <x-badge :color="$bowieDick['color']" dot>{{ $bowieDick['label'] }}</x-badge>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- KPI --}}
        <div class="mb-1.5 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            <div class="rounded-xl border border-gray-200 bg-white p-4 text-center">
                <div class="text-2xl font-bold text-gray-900">{{ $kpi['total'] }}</div>
                <div class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Total Tray dalam Load</div>
            </div>
            <div class="rounded-xl border {{ $kpi['at_unit'] > 0 ? 'border-red-200 bg-red-50' : 'border-gray-200 bg-white' }} p-4 text-center">
                <div class="text-2xl font-bold {{ $kpi['at_unit'] > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $kpi['at_unit'] }}</div>
                <div class="mt-1 text-[10px] font-semibold uppercase tracking-wider {{ $kpi['at_unit'] > 0 ? 'text-red-500' : 'text-gray-400' }}">Masih di Unit (Bahaya)</div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 text-center">
                <div class="text-2xl font-bold text-gray-900">{{ $kpi['on_shelf'] }}</div>
                <div class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Steril di Rak</div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 text-center">
                <div class="text-2xl font-bold text-gray-900">{{ $kpi['returned'] }}</div>
                <div class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Sudah Kembali</div>
            </div>
            <div class="rounded-xl border {{ $kpi['untraceable'] > 0 ? 'border-amber-200 bg-amber-50' : 'border-gray-200 bg-white' }} p-4 text-center">
                <div class="text-2xl font-bold {{ $kpi['untraceable'] > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $kpi['untraceable'] }}</div>
                <div class="mt-1 text-[10px] font-semibold uppercase tracking-wider {{ $kpi['untraceable'] > 0 ? 'text-amber-600' : 'text-gray-400' }}">Tidak Dapat Ditelusuri</div>
            </div>
        </div>
        <p class="mb-4 text-[11px] text-gray-400">
            Tray berstatus Gagal Sterilisasi, Menunggu Hasil, atau Tak Lagi Steril dari Load Ini tetap
            ditampilkan di tabel di bawah, namun tidak dihitung dalam KPI di atas.
        </p>

        {{-- Tabel Dampak --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
                <h3 class="text-sm font-bold text-gray-900">Tabel Dampak</h3>
                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600">
                    {{ $rows->count() }} tray
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead>
                        <tr class="border-b border-gray-50 bg-gray-50/50">
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Tray</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Status Kini</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Tujuan</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Unit</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Tgl Keluar</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($rows as $row)
                            @php
                                $tray = $row->tray;
                                $trayStatusInfo = $tray ? (\App\Models\Tray::STATUSES[$tray->status] ?? ['label' => $tray->status, 'color' => 'gray']) : null;
                                $cat = $categoryInfo[$row->category];
                            @endphp
                            <tr class="transition hover:bg-gray-50/50">
                                <td class="px-5 py-3.5">
                                    @if ($tray)
                                        <div class="font-semibold text-gray-900">{{ $tray->name }}
                                            <span class="ml-1 rounded-md bg-gray-100 px-1.5 py-0.5 text-[11px] font-bold text-gray-500">{{ $tray->code }}</span>
                                        </div>
                                        <div class="text-xs text-gray-400">{{ $tray->template?->name ?? 'Tray Bebas' }}</div>
                                    @else
                                        <span class="text-xs italic text-gray-400">Tray telah dihapus permanen</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-col items-start gap-1">
                                        @if ($trayStatusInfo)
                                            <x-badge :color="$trayStatusInfo['color']" dot>{{ $trayStatusInfo['label'] }}</x-badge>
                                        @endif
                                        <x-badge :color="$cat['color']">{{ $cat['label'] }}</x-badge>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($row->distribution_request)
                                        <a href="{{ route('distribution-requests.show', $row->distribution_request->id) }}"
                                            class="text-sm font-medium text-primary-600 hover:underline">
                                            {{ $row->distribution_request->request_number }}
                                        </a>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-sm text-gray-700">{{ $row->distribution_request?->unit?->name ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-xs text-gray-600">{{ $row->issued_at?->format('d M Y, H:i') ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-sm font-semibold {{ $cat['color'] === 'red' ? 'text-red-600' : ($cat['color'] === 'green' ? 'text-green-600' : 'text-gray-500') }}">
                                        {{ $cat['action'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state title="Tidak ada tray dalam load ini" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</x-layouts.app>
