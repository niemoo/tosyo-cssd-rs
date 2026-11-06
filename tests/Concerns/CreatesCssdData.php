<?php

namespace Tests\Concerns;

use App\Models\DistributionRequest;
use App\Models\DistributionRequestItem;
use App\Models\Hospital;
use App\Models\HospitalUser;
use App\Models\Instrument;
use App\Models\InstrumentCategory;
use App\Models\InstrumentItem;
use App\Models\SterilizationBatch;
use App\Models\SterilizationBatchItem;
use App\Models\Sterilizer;
use App\Models\StorageRack;
use App\Models\Tray;
use App\Models\TrayItem;
use App\Models\TrayLifecycleEvent;
use App\Models\TrayTemplate;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

/**
 * Helper factories for building CSSD domain scenarios in tests, following the
 * lifecycle chain documented in CLAUDE.md: Hospital -> User -> Unit -> Sterilizer ->
 * StorageRack -> InstrumentCategory -> Instrument -> InstrumentItem -> TrayTemplate -> Tray.
 */
trait CreatesCssdData
{
    protected function makeHospital(array $attrs = []): Hospital
    {
        return Hospital::create(array_merge([
            'name' => 'RS Test '.Str::random(6),
            'code' => 'RS-'.Str::upper(Str::random(8)),
            'sterile_shelf_life_days' => 30,
            'is_active' => true,
        ], $attrs));
    }

    protected function makeUser(Hospital $hospital, array $attrs = []): User
    {
        $user = User::factory()->create(array_merge([
            'username' => 'user_'.Str::random(10),
        ], $attrs));

        HospitalUser::create([
            'hospital_id' => $hospital->id,
            'user_id' => $user->id,
            'joined_at' => now(),
            'is_active' => true,
        ]);

        return $user;
    }

    /** Log the user in, activate the hospital in session, and grant the given permissions. */
    protected function actingAsHospitalUser(User $user, Hospital $hospital, array $permissions = []): User
    {
        $this->givePermissions($user, $permissions);
        $this->actingAs($user);
        session(['active_hospital_id' => $hospital->id]);

        return $user;
    }

    protected function givePermissions(User $user, array $permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $user->givePermissionTo($permissions);
    }

    protected function makeUnit(Hospital $hospital, array $attrs = []): Unit
    {
        return Unit::create(array_merge([
            'hospital_id' => $hospital->id,
            'name' => 'Unit '.Str::random(6),
            'code' => 'UNIT-'.Str::upper(Str::random(8)),
            'type' => 'RAWAT_INAP',
            'is_active' => true,
        ], $attrs));
    }

    protected function makeSterilizer(Hospital $hospital, array $attrs = []): Sterilizer
    {
        return Sterilizer::create(array_merge([
            'hospital_id' => $hospital->id,
            'name' => 'Autoklaf '.Str::random(6),
            'code' => 'STR-'.Str::upper(Str::random(8)),
            'type' => 'STEAM',
            'is_active' => true,
        ], $attrs));
    }

    protected function makeStorageRack(Hospital $hospital, array $attrs = []): StorageRack
    {
        return StorageRack::create(array_merge([
            'hospital_id' => $hospital->id,
            'name' => 'Rak '.Str::random(6),
            'code' => 'RAK-'.Str::upper(Str::random(8)),
            'is_active' => true,
        ], $attrs));
    }

    protected function makeInstrumentCategory(Hospital $hospital, array $attrs = []): InstrumentCategory
    {
        return InstrumentCategory::create(array_merge([
            'hospital_id' => $hospital->id,
            'name' => 'Kategori '.Str::random(6),
            'code' => 'CAT-'.Str::upper(Str::random(8)),
            'is_active' => true,
        ], $attrs));
    }

    protected function makeInstrument(Hospital $hospital, ?InstrumentCategory $category = null, array $attrs = []): Instrument
    {
        $category ??= $this->makeInstrumentCategory($hospital);

        return Instrument::create(array_merge([
            'hospital_id' => $hospital->id,
            'category_id' => $category->id,
            'name' => 'Instrumen '.Str::random(6),
            'code' => 'INS-'.Str::upper(Str::random(8)),
            'is_active' => true,
        ], $attrs));
    }

    protected function makeInstrumentItem(Hospital $hospital, ?Instrument $instrument = null, array $attrs = []): InstrumentItem
    {
        $instrument ??= $this->makeInstrument($hospital);

        return InstrumentItem::create(array_merge([
            'hospital_id' => $hospital->id,
            'instrument_id' => $instrument->id,
            'code' => 'ITEM-'.Str::upper(Str::random(10)),
            'condition' => 'GOOD',
            'total_cycles' => 0,
            'is_active' => true,
        ], $attrs));
    }

    protected function makeTrayTemplate(Hospital $hospital, array $attrs = []): TrayTemplate
    {
        return TrayTemplate::create(array_merge([
            'hospital_id' => $hospital->id,
            'name' => 'Template '.Str::random(6),
            'code' => 'TPL-'.Str::upper(Str::random(8)),
            'is_active' => true,
        ], $attrs));
    }

    /**
     * Create a fully-populated tray (with `$itemCount` instrument items) sitting directly at
     * `$status`. This writes the `status` column only — it does NOT go through TrayService, so
     * no lifecycle events are recorded. Use TrayService::transitionTo (or recordGenesis) in the
     * test body when the lifecycle event trail itself is what's under test.
     */
    protected function makeTray(
        Hospital $hospital,
        string $status = Tray::STATUS_ASSEMBLING,
        array $attrs = [],
        int $itemCount = 1,
        ?TrayTemplate $template = null,
    ): Tray {
        $tray = Tray::create(array_merge([
            'hospital_id' => $hospital->id,
            'template_id' => $template?->id,
            'code' => 'TRAY-'.Str::upper(Str::random(10)),
            'name' => 'Tray '.Str::random(6),
            'status' => $status,
            'assembled_at' => now(),
            'is_active' => true,
        ], $attrs));

        for ($i = 0; $i < $itemCount; $i++) {
            TrayItem::create([
                'tray_id' => $tray->id,
                'instrument_item_id' => $this->makeInstrumentItem($hospital)->id,
            ]);
        }

        return $tray->refresh();
    }

    /** Write a tray_lifecycle_events row directly, bypassing TrayService, for building historical scenarios. */
    protected function recordTrayEvent(
        Tray $tray,
        string $eventType,
        ?string $from,
        string $to,
        ?Model $reference = null,
        ?Carbon $performedAt = null,
        array $context = [],
    ): TrayLifecycleEvent {
        return TrayLifecycleEvent::create([
            'hospital_id' => $tray->hospital_id,
            'tray_id' => $tray->id,
            'event_type' => $eventType,
            'from_status' => $from,
            'to_status' => $to,
            'reference_type' => $reference ? $reference::class : null,
            'reference_id' => $reference?->getKey(),
            'context' => $context ?: null,
            'performed_at' => $performedAt ?? now(),
        ]);
    }

    protected function makeDistributionRequest(
        Hospital $hospital,
        Unit $unit,
        User $requester,
        string $status = DistributionRequest::STATUS_APPROVED,
        array $attrs = [],
    ): DistributionRequest {
        return DistributionRequest::create(array_merge([
            'hospital_id' => $hospital->id,
            'unit_id' => $unit->id,
            'request_number' => 'REQ-'.Str::upper(Str::random(8)),
            'status' => $status,
            'requested_by' => $requester->id,
            'requested_at' => now(),
        ], $attrs));
    }

    protected function makeDistributionRequestItem(
        DistributionRequest $request,
        ?TrayTemplate $template = null,
        int $quantity = 1,
        array $attrs = [],
    ): DistributionRequestItem {
        return DistributionRequestItem::create(array_merge([
            'request_id' => $request->id,
            'template_id' => $template?->id,
            'quantity' => $quantity,
        ], $attrs));
    }

    protected function makeSterilizationBatch(
        Hospital $hospital,
        Sterilizer $sterilizer,
        User $operator,
        string $status = SterilizationBatch::STATUS_IN_PROGRESS,
        array $attrs = [],
    ): SterilizationBatch {
        return SterilizationBatch::create(array_merge([
            'hospital_id' => $hospital->id,
            'sterilizer_id' => $sterilizer->id,
            'batch_number' => 'BATCH-'.Str::upper(Str::random(8)),
            'load_number' => 'LOAD-'.Str::upper(Str::random(8)),
            'status' => $status,
            'bi_result' => SterilizationBatch::BI_PENDING,
            'operator_id' => $operator->id,
            'started_at' => now(),
        ], $attrs));
    }

    protected function makeSterilizationBatchItem(
        SterilizationBatch $batch,
        Tray $tray,
        string $result = SterilizationBatchItem::RESULT_PENDING,
        array $attrs = [],
    ): SterilizationBatchItem {
        return SterilizationBatchItem::create(array_merge([
            'batch_id' => $batch->id,
            'tray_id' => $tray->id,
            'result' => $result,
        ], $attrs));
    }
}
