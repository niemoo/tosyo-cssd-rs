<x-layouts.app title="Item Instrumen">

    <x-slot name="pageHeader">Item Instrumen</x-slot>
    <x-slot name="pageSubHeader">Kelola item fisik instrumen CSSD</x-slot>
    <x-slot name="pageActions">
        @can('instrument-items.create')
            <a href="{{ route('instrument-items.create') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Item
            </a>
        @endcan
    </x-slot>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-5">
        <x-stat-card label="Total Item" :value="$stats['total']" color="teal"
            icon="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
        <x-stat-card label="Baik" :value="$stats['good']" color="green"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat-card label="Rusak" :value="$stats['damaged']" color="red"
            icon="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat-card label="Perbaikan" :value="$stats['under_repair']" color="amber"
            icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat-card label="Pensiun" :value="$stats['retired']" color="gray"
            icon="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
    </div>

    {{-- Filter --}}
    <div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl border border-gray-100 bg-white p-4">
        <form method="GET" class="flex flex-1 flex-wrap items-center gap-3">
            <div
                class="flex flex-1 items-center gap-2 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 min-w-[200px]">
                <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path stroke-linecap="round" d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari kode, serial, barcode, atau instrumen..."
                    class="w-full border-none bg-transparent p-0 text-sm text-gray-600 placeholder-gray-300 focus:ring-0 focus:outline-none" />
            </div>

            @if ($multiHospital)
                <select name="hospital_id"
                    class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                    <option value="">Semua RS Saya</option>
                    @foreach ($userHospitals as $hospital)
                        <option value="{{ $hospital->id }}"
                            {{ request('hospital_id') == $hospital->id ? 'selected' : '' }}>
                            {{ $hospital->name }}
                        </option>
                    @endforeach
                </select>
            @endif

            <select name="instrument_id"
                class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                <option value="">Semua Instrumen</option>
                @foreach ($instruments as $instrument)
                    <option value="{{ $instrument->id }}"
                        {{ request('instrument_id') == $instrument->id ? 'selected' : '' }}>
                        {{ $instrument->name }}
                    </option>
                @endforeach
            </select>

            <select name="condition"
                class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                <option value="">Semua Kondisi</option>
                @foreach ($conditions as $key => $condition)
                    <option value="{{ $key }}" {{ request('condition') === $key ? 'selected' : '' }}>
                        {{ $condition['label'] }}
                    </option>
                @endforeach
            </select>

            <select name="status"
                class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-aktif</option>
            </select>

            <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-500">
                <input type="checkbox" name="show_deleted" value="1"
                    {{ request('show_deleted') ? 'checked' : '' }}
                    class="rounded border-gray-300 text-primary-500 focus:ring-primary-400" />
                Tampilkan dihapus
            </label>

            <button type="submit"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">
                Filter
            </button>

            @if (request()->hasAny(['search', 'status', 'show_deleted', 'hospital_id', 'instrument_id', 'condition']))
                <a href="{{ route('instrument-items.index') }}"
                    class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
        <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
            <p class="text-xs text-gray-400">
                Menampilkan <span
                    class="font-semibold text-gray-700">{{ $items->firstItem() }}–{{ $items->lastItem() }}</span>
                dari <span class="font-semibold text-gray-700">{{ $items->total() }}</span> item
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[960px]">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50/50">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="code" label="Kode" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th
                            class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            QR Code
                        </th>
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Instrumen
                        </th>
                        @if ($multiHospital)
                            <th
                                class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                Rumah Sakit
                            </th>
                        @endif
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Serial / Barcode
                        </th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="condition" label="Kondisi" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="total_cycles" label="Siklus" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="is_active" label="Status" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($items as $item)
                        <tr class="transition hover:bg-gray-50/50 {{ $item->trashed() ? 'opacity-50' : '' }}">
                            <td class="px-5 py-3.5">
                                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-bold text-gray-500">
                                    {{ $item->code }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <button type="button"
                                    onclick="document.getElementById('qr-modal-{{ $item->id }}').showModal()"
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-100 p-1 transition hover:border-primary-200 hover:bg-primary-50">
                                    {!! $item->qrCodeSvg(36) !!}
                                </button>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-gray-900">{{ $item->instrument->name }}</div>
                                <div class="text-xs text-gray-400">{{ $item->instrument->category->name }}</div>
                                @if ($item->trashed())
                                    <span
                                        class="mt-1 inline-block rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-600">
                                        Dihapus {{ $item->deleted_at->diffForHumans() }}
                                    </span>
                                @endif
                            </td>
                            @if ($multiHospital)
                                <td class="px-5 py-3.5">
                                    <div class="text-xs font-medium text-gray-700">{{ $item->hospital->name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $item->hospital->code }}</div>
                                </td>
                            @endif
                            <td class="px-5 py-3.5">
                                @if ($item->serial_number)
                                    <div class="text-xs text-gray-600">S/N: {{ $item->serial_number }}</div>
                                @endif
                                @if ($item->barcode)
                                    <div class="text-xs text-gray-400">BC: {{ $item->barcode }}</div>
                                @endif
                                @if (!$item->serial_number && !$item->barcode)
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @php $cond = $conditions[$item->condition] ?? ['label' => $item->condition, 'color' => 'gray']; @endphp
                                <x-badge :color="$cond['color']" dot>{{ $cond['label'] }}</x-badge>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @php
                                    $lifespan = $item->instrument->lifespan_cycles;
                                    $cycles = $item->total_cycles;
                                @endphp
                                <div class="text-sm font-semibold text-gray-900">{{ number_format($cycles) }}</div>
                                @if ($lifespan)
                                    <div class="text-[10px] text-gray-400">/ {{ number_format($lifespan) }}</div>
                                    @if ($item->isNearingLifespan())
                                        <x-badge color="red">Hampir Habis</x-badge>
                                    @endif
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if ($item->trashed())
                                    <x-badge color="red" dot>Dihapus</x-badge>
                                @else
                                    <x-badge :color="$item->is_active ? 'green' : 'red'" dot>
                                        {{ $item->is_active ? 'Aktif' : 'Non-aktif' }}
                                    </x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <x-kebab-menu>
                                    @if ($item->trashed())
                                        @can('instrument-items.delete')
                                            <form method="POST"
                                                action="{{ route('instrument-items.restore', $item->id) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                    class="flex w-full items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-green-600 hover:bg-green-50">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                    Pulihkan
                                                </button>
                                            </form>
                                        @endcan
                                    @else
                                        @can('instrument-items.view')
                                            <a href="{{ route('instrument-items.show', $item) }}"
                                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Lihat Detail
                                            </a>
                                        @endcan

                                        @can('instrument-items.edit')
                                            <a href="{{ route('instrument-items.edit', $item) }}"
                                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </a>

                                            <button type="button"
                                                onclick="document.getElementById('confirm-toggle-{{ $item->id }}').showModal()"
                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-xs font-medium hover:bg-gray-50
                                                           {{ $item->is_active ? 'text-orange-600' : 'text-green-600' }}">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    @if ($item->is_active)
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    @endif
                                                </svg>
                                                {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        @endcan

                                        @can('instrument-items.delete')
                                            <div class="my-1 border-t border-gray-100"></div>
                                            <button type="button"
                                                onclick="document.getElementById('confirm-{{ $item->id }}').showModal()"
                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Hapus
                                            </button>
                                        @endcan
                                    @endif
                                </x-kebab-menu>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $multiHospital ? 9 : 8 }}">
                                <x-empty-state title="Belum ada item instrumen"
                                    description="Tambahkan item fisik untuk instrumen yang sudah terdaftar."
                                    action-label="Tambah Item" action-route="instrument-items.create"
                                    icon="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="border-t border-gray-50 px-5 py-3.5">
                {{ $items->links('components.pagination') }}
            </div>
        @endif
    </div>

    {{-- QR Code Modals --}}
    @foreach ($items as $item)
        <dialog id="qr-modal-{{ $item->id }}" onclick="if(event.target === this) this.close()"
            class="overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="flex w-64 flex-col items-center p-6 text-center">
                <div class="mb-2 flex w-full items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900">QR Code</h3>
                    <button type="button" onclick="document.getElementById('qr-modal-{{ $item->id }}').close()"
                        class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-1">
                    {!! $item->qrCodeSvg(200) !!}
                </div>
                <div class="mt-3 font-mono text-base font-bold text-gray-900">{{ $item->code }}</div>
                <div class="text-xs text-gray-400">{{ $item->instrument->name }}</div>
            </div>
        </dialog>
    @endforeach

    {{-- Delete & Toggle Forms + Dialogs --}}
    @foreach ($items as $item)
        @unless ($item->trashed())
            @can('instrument-items.delete')
                <form method="POST" action="{{ route('instrument-items.destroy', $item) }}"
                    id="form-delete-{{ $item->id }}">
                    @csrf @method('DELETE')
                </form>
                <x-modal-confirm :id="'confirm-' . $item->id" type="danger" title="Hapus Item Instrumen?" :description="'Item ' .
                    $item->code .
                    ' akan dihapus. Data tidak hilang permanen dan masih bisa dipulihkan.'"
                    confirm-text="Ya, Hapus" :form-id="'form-delete-' . $item->id" />
            @endcan

            @can('instrument-items.edit')
                <form method="POST" action="{{ route('instrument-items.toggle-active', $item) }}"
                    id="form-toggle-{{ $item->id }}">
                    @csrf @method('PATCH')
                </form>
                <x-modal-confirm :id="'confirm-toggle-' . $item->id" type="warning" :title="$item->is_active ? 'Nonaktifkan Item?' : 'Aktifkan Item?'" :description="$item->is_active
                    ? 'Item ' . $item->code . ' akan dinonaktifkan.'
                    : 'Item ' . $item->code . ' akan diaktifkan kembali.'" :confirm-text="$item->is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"
                    :form-id="'form-toggle-' . $item->id" />
            @endcan
        @endunless
    @endforeach

</x-layouts.app>
