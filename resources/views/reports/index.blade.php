<x-layouts.app title="Laporan">

    <x-slot name="pageHeader">Laporan</x-slot>
    <x-slot name="pageSubHeader">Dokumen untuk akreditasi KARS/STARKES & pelaporan PPI</x-slot>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

        <a href="{{ route('reports.sterilization-log') }}"
            class="group rounded-xl border border-gray-200 bg-white p-5 transition hover:border-primary-300 hover:shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-primary-50">
                <svg class="h-5 w-5 text-primary-500" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-gray-900 group-hover:text-primary-600">Log Sterilisasi</h3>
            <p class="mt-1 text-xs text-gray-400">Riwayat batch sterilisasi lengkap — termasuk Log Indikator
                Biologis (filter Hasil BI). Dokumen inti audit KARS/STARKES.</p>
        </a>

        <a href="{{ route('reports.productivity') }}"
            class="group rounded-xl border border-gray-200 bg-white p-5 transition hover:border-primary-300 hover:shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M22 12h-4l-3 9L9 3l-3 9H2" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-gray-900 group-hover:text-primary-600">Produktivitas</h3>
            <p class="mt-1 text-xs text-gray-400">Total tray & load per periode, tingkat kegagalan, rincian per
                operator dan per sterilizer.</p>
        </a>

        <a href="{{ route('reports.instrument-lifespan') }}"
            class="group rounded-xl border border-gray-200 bg-white p-5 transition hover:border-primary-300 hover:shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50">
                <svg class="h-5 w-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-gray-900 group-hover:text-primary-600">Umur Instrumen</h3>
            <p class="mt-1 text-xs text-gray-400">Instrumen yang mendekati akhir umur pakai — untuk perencanaan
                anggaran penggantian alat.</p>
        </a>

    </div>

</x-layouts.app>
