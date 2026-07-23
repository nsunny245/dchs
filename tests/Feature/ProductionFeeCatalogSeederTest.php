<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\FeeHead;
use App\Models\FeeStructure;
use Database\Seeders\CampusAndCourseSeeder;
use Database\Seeders\ProductionFeeCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionFeeCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_fee_catalog_sync_is_complete_and_idempotent(): void
    {
        $this->seed(CampusAndCourseSeeder::class);

        FeeHead::query()->create([
            'name' => 'Keep Existing Custom Head',
            'code' => 'CUSTOM_LIVE_HEAD',
            'category' => 'miscellaneous',
            'default_amount' => '999.00',
            'applies_to' => 'both',
            'is_active' => true,
        ]);

        $this->seed(ProductionFeeCatalogSeeder::class);
        $this->seed(ProductionFeeCatalogSeeder::class);

        $this->assertSame(85, FeeHead::query()->count());
        $this->assertSame(11, FeeStructure::query()->count());

        $lhv = Course::query()->where('code', 'LHV')->firstOrFail();
        $bsn = Course::query()->where('code', 'BSN')->firstOrFail();

        $this->assertDatabaseHas('fee_structures', [
            'course_id' => $lhv->id,
            'campus_id' => null,
            'academic_session_id' => null,
            'shift' => null,
            'version' => 1,
            'total_fee' => '250000.00',
            'installment_count' => 12,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('fee_heads', [
            'course_id' => $bsn->id,
            'code' => 'EXAM_BSN',
            'default_amount' => '25000.00',
            'applies_to' => 'both',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('fee_heads', [
            'course_id' => null,
            'code' => 'TUITION_REC',
            'default_amount' => '5882.35',
            'applies_to' => 'monthly_installment',
        ]);

        $this->assertDatabaseHas('fee_heads', [
            'code' => 'CUSTOM_LIVE_HEAD',
            'default_amount' => '999.00',
        ]);
    }
}
