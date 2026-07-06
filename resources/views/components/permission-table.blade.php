@props(['permissions', 'actions', 'checkedIds' => [], 'readonly' => false])

<div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50/50">
                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400 w-40">
                    Modul
                </th>
                @foreach ($actions as $action)
                    <th class="px-3 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                        {{ $action }}
                    </th>
                @endforeach
                @if (!$readonly)
                    <th class="px-3 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                        Semua
                    </th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach ($permissions as $module => $modulePermissions)
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-5 py-3.5">
                        <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-bold uppercase text-gray-600">
                            {{ $module }}
                        </span>
                    </td>
                    @foreach ($actions as $action)
                        <td class="px-3 py-3.5 text-center">
                            @if (isset($modulePermissions[$action]))
                                @php $permission = $modulePermissions[$action]; @endphp
                                @if ($readonly)
                                    {{-- Show mode: visual toggle saja --}}
                                    <div class="inline-flex items-center justify-center">
                                        <div
                                            class="relative h-5 w-9 rounded-full transition-colors
                                                    {{ in_array($permission->id, $checkedIds) ? 'bg-primary-400' : 'bg-gray-200' }}">
                                            <div
                                                class="absolute top-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform
                                                        {{ in_array($permission->id, $checkedIds) ? 'translate-x-4' : 'translate-x-0.5' }}">
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{-- Edit/Create mode: toggle fungsional --}}
                                    <label class="inline-flex cursor-pointer items-center justify-center">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                            {{ in_array($permission->id, $checkedIds) ? 'checked' : '' }}
                                            class="permission-cb peer sr-only" data-module="{{ $module }}" />
                                        <div
                                            class="peer relative h-5 w-9 rounded-full bg-gray-200 transition-colors
                                                    peer-checked:bg-primary-400
                                                    peer-focus:ring-2 peer-focus:ring-primary-400/20
                                                    after:absolute after:left-0.5 after:top-0.5
                                                    after:h-4 after:w-4 after:rounded-full
                                                    after:bg-white after:shadow after:transition-all after:content-['']
                                                    peer-checked:after:translate-x-4">
                                        </div>
                                    </label>
                                @endif
                            @else
                                <span class="text-xs text-gray-200">—</span>
                            @endif
                        </td>
                    @endforeach
                    @if (!$readonly)
                        {{-- Toggle all per module --}}
                        <td class="px-3 py-3.5 text-center">
                            <label class="inline-flex cursor-pointer items-center justify-center">
                                <input type="checkbox" class="module-toggle peer sr-only"
                                    data-module="{{ $module }}" title="Pilih semua {{ $module }}"
                                    onchange="toggleModule('{{ $module }}', this.checked)" />
                                <div
                                    class="peer relative h-5 w-9 rounded-full bg-gray-200 transition-colors
                                            peer-checked:bg-primary-400
                                            peer-focus:ring-2 peer-focus:ring-primary-400/20
                                            after:absolute after:left-0.5 after:top-0.5
                                            after:h-4 after:w-4 after:rounded-full
                                            after:bg-white after:shadow after:transition-all after:content-['']
                                            peer-checked:after:translate-x-4">
                                </div>
                            </label>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if (!$readonly)
    <script>
        function toggleModule(module, checked) {
            document.querySelectorAll(`input.permission-cb[data-module="${module}"]`)
                .forEach(cb => cb.checked = checked);
        }

        document.querySelectorAll('input.permission-cb').forEach(cb => {
            cb.addEventListener('change', function() {
                const module = this.dataset.module;
                const allCbs = document.querySelectorAll(`input.permission-cb[data-module="${module}"]`);
                const allChecked = [...allCbs].every(c => c.checked);
                const toggle = document.querySelector(`input.module-toggle[data-module="${module}"]`);
                if (toggle) toggle.checked = allChecked;
            });
        });

        document.querySelectorAll('input.module-toggle').forEach(toggle => {
            const module = toggle.dataset.module;
            const allCbs = document.querySelectorAll(`input.permission-cb[data-module="${module}"]`);
            toggle.checked = allCbs.length > 0 && [...allCbs].every(c => c.checked);
        });
    </script>
@endif
