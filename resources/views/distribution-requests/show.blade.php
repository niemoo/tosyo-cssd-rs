<x-layouts.app title="{{ $distributionRequest->request_number }}">

    <x-slot name="backButton">
        <a href="{{ route('distribution-requests.index') }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    <x-slot name="breadcrumb">
        <a href="{{ route('distribution-requests.index') }}" class="hover:text-gray-600">Permintaan Distribusi</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">{{ $distributionRequest->request_number }}</span>
    </x-slot>

    <x-slot name="pageHeader">{{ $distributionRequest->request_number }}</x-slot>
    <x-slot name="pageSubHeader">{{ $distributionRequest->unit->name }} ·
        {{ $distributionRequest->hospital->name }}</x-slot>

    <x-slot name="pageActions">
        @can('distribution-requests.return')
            @if (in_array($distributionRequest->status, [
                    \App\Models\DistributionRequest::STATUS_FULFILLED,
                    \App\Models\DistributionRequest::STATUS_CLOSED,
                ]))
                @php
                    $hasReturnable = $distributionRequest->items
                        ->filter(fn($item) => $item->tray && $item->tray->status === \App\Models\Tray::STATUS_IN_USE)
                        ->isNotEmpty();
                @endphp
                @if ($hasReturnable)
                    <a href="{{ route('tray-returns.create', $distributionRequest) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-teal-500 to-teal-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Catat Pengembalian
                    </a>
                @endif
            @endif
        @endcan

        @can('distribution-requests.fulfill')
            @if ($distributionRequest->canBeFulfilled())
                <a href="{{ route('distribution-requests.fulfill', $distributionRequest) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Proses Pemenuhan
                </a>
            @endif
        @endcan

        @can('distribution-requests.approve')
            @if ($distributionRequest->canBeApproved())
                <button type="button" onclick="document.getElementById('approve-modal').showModal()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-green-500 to-green-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Review Permintaan
                </button>
            @endif
        @endcan

        @can('distribution-requests.edit')
            @if (in_array($distributionRequest->status, [
                    \App\Models\DistributionRequest::STATUS_DRAFT,
                    \App\Models\DistributionRequest::STATUS_REJECTED,
                ]))
                <a href="{{ route('distribution-requests.edit', $distributionRequest) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ $distributionRequest->status === \App\Models\DistributionRequest::STATUS_REJECTED ? 'Revisi' : 'Edit' }}
                </a>
            @endif
        @endcan
    </x-slot>

    @php $statusInfo = \App\Models\DistributionRequest::STATUSES[$distributionRequest->status] ?? ['label' => $distributionRequest->status, 'color' => 'gray']; @endphp

    @if (
        $distributionRequest->status === \App\Models\DistributionRequest::STATUS_REJECTED &&
            $distributionRequest->rejection_notes)
        <div class="mb-4 rounded-xl border border-red-100 bg-red-50 p-4">
            <div class="mb-1 flex items-center gap-1.5 text-xs font-bold text-red-600">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Alasan Penolakan
            </div>
            <p class="text-sm text-red-700">{{ $distributionRequest->rejection_notes }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="space-y-4 lg:col-span-2">

            {{-- Info --}}
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                <div class="border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Informasi Permintaan</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach ([['label' => 'No. Permintaan', 'value' => $distributionRequest->request_number], ['label' => 'Unit/Bangsal', 'value' => $distributionRequest->unit->name], ['label' => 'Diminta oleh', 'value' => $distributionRequest->requester->name], ['label' => 'Tanggal Permintaan', 'value' => $distributionRequest->requested_at->format('d M Y, H:i')], ['label' => 'Disetujui oleh', 'value' => $distributionRequest->approver?->name ?? '-'], ['label' => 'Tanggal Approval', 'value' => $distributionRequest->approved_at?->format('d M Y, H:i') ?? '-'], ['label' => 'Dipenuhi oleh', 'value' => $distributionRequest->fulfiller?->name ?? '-'], ['label' => 'Tanggal Fulfill', 'value' => $distributionRequest->fulfilled_at?->format('d M Y, H:i') ?? '-']] as $item)
                        <div class="flex items-start justify-between px-5 py-3">
                            <span class="w-40 shrink-0 text-xs text-gray-400">{{ $item['label'] }}</span>
                            <span class="text-right text-sm font-medium text-gray-700">{{ $item['value'] }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Status</span>
                        <x-badge :color="$statusInfo['color']" dot>{{ $statusInfo['label'] }}</x-badge>
                    </div>
                    @if ($distributionRequest->notes)
                        <div class="px-5 py-3">
                            <span class="text-xs text-gray-400">Catatan</span>
                            <p class="mt-1 text-sm text-gray-700">{{ $distributionRequest->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Items --}}
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Daftar Tray</h3>
                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600">
                        {{ $distributionRequest->items->count() }} jenis
                    </span>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach ($distributionRequest->items as $item)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $item->template?->name ?? 'Tidak spesifik' }}
                                </div>
                                @if ($item->tray)
                                    <div class="text-xs text-gray-400">
                                        Tray: {{ $item->tray->code }} — {{ $item->tray->name }}
                                    </div>
                                @endif
                                @if ($item->notes)
                                    <div class="text-[10px] italic text-gray-400">{{ $item->notes }}</div>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold text-gray-900">{{ $item->quantity }}</span>
                                <span class="text-xs text-gray-400"> unit</span>
                                @if (!$item->tray && in_array($distributionRequest->status, ['APPROVED', 'IN_PROCESS']))
                                    <div class="mt-0.5"><x-badge color="amber">Belum di-assign</x-badge></div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tray Returns --}}
            @if ($distributionRequest->trayReturns->count() > 0)
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                    <div class="border-b border-gray-50 px-5 py-3.5">
                        <h3 class="text-sm font-bold text-gray-900">Riwayat Pengembalian</h3>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach ($distributionRequest->trayReturns as $return)
                            @php $condInfo = \App\Models\TrayReturn::CONDITIONS[$return->condition] ?? ['label' => $return->condition, 'color' => 'gray']; @endphp
                            <div class="flex items-center justify-between px-5 py-3">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $return->tray->name }}
                                        ({{ $return->tray->code }})
                                    </div>
                                    <div class="text-xs text-gray-400">Diterima {{ $return->receiver->name }} ·
                                        {{ $return->returned_at->format('d M Y, H:i') }}</div>
                                    @if ($return->missing_items)
                                        <div class="mt-0.5 text-xs text-red-500">Hilang: {{ $return->missing_items }}
                                        </div>
                                    @endif
                                </div>
                                <x-badge :color="$condInfo['color']">{{ $condInfo['label'] }}</x-badge>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <div class="space-y-4">
            <div class="rounded-xl border border-gray-100 bg-white p-5">
                <h4 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Audit Trail</h4>
                <div class="space-y-3">
                    @foreach ([['label' => 'Dibuat oleh', 'value' => $distributionRequest->creator?->name ?? '-'], ['label' => 'Dibuat pada', 'value' => $distributionRequest->created_at->format('d M Y, H:i')], ['label' => 'Diperbarui oleh', 'value' => $distributionRequest->updater?->name ?? '-'], ['label' => 'Diperbarui pada', 'value' => $distributionRequest->updated_at->format('d M Y, H:i')]] as $audit)
                        <div class="flex flex-col gap-0.5">
                            <span class="text-[10px] text-gray-400">{{ $audit['label'] }}</span>
                            <span class="text-xs font-semibold text-gray-700">{{ $audit['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- Approval Modal --}}
    @can('distribution-requests.approve')
        @if ($distributionRequest->canBeApproved())
            <dialog id="approve-modal" class="w-full max-w-md rounded-xl border-0 p-0 shadow-2xl">
                <form method="POST" action="{{ route('distribution-requests.approve', $distributionRequest) }}"
                    x-data="{ decision: 'approve' }">
                    @csrf @method('PATCH')
                    <div class="border-b border-gray-100 p-5">
                        <h3 class="text-base font-bold text-gray-900">Review Permintaan</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $distributionRequest->request_number }} —
                            {{ $distributionRequest->unit->name }}</p>
                    </div>
                    <div class="space-y-4 p-5">
                        <div class="grid grid-cols-2 gap-2">
                            <label
                                class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border p-3 transition"
                                :class="decision === 'approve' ? 'border-green-400 bg-green-50' :
                                    'border-gray-200 hover:border-green-300'">
                                <input type="radio" name="decision" value="approve" x-model="decision"
                                    class="border-gray-300 text-green-500 focus:ring-green-400" />
                                <span class="text-sm font-semibold text-green-700">Setujui</span>
                            </label>
                            <label
                                class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border p-3 transition"
                                :class="decision === 'reject' ? 'border-red-400 bg-red-50' :
                                    'border-gray-200 hover:border-red-300'">
                                <input type="radio" name="decision" value="reject" x-model="decision"
                                    class="border-gray-300 text-red-500 focus:ring-red-400" />
                                <span class="text-sm font-semibold text-red-700">Tolak</span>
                            </label>
                        </div>
                        <div x-show="decision === 'reject'" x-cloak>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Alasan Penolakan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="rejection_notes" rows="3" placeholder="Jelaskan alasan penolakan..."
                                class="w-full resize-none rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                             focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20"></textarea>
                            @error('rejection_notes')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex gap-2 border-t border-gray-100 p-5">
                        <button type="button" onclick="document.getElementById('approve-modal').close()"
                            class="flex-1 rounded-lg border border-gray-200 bg-white py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                            Kirim Keputusan
                        </button>
                    </div>
                </form>
            </dialog>
        @endif
    @endcan

</x-layouts.app>
