<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\DashboardImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_dashboard_can_render_a_signed_public_disk_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put(
            'student-photos/student.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true),
        );

        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->actingAs($user, 'admin');

        $url = DashboardImage::url('storage/student-photos/student.png');

        $this->assertNotNull($url);
        $this->get($url)
            ->assertOk()
            ->assertHeader('content-type', 'image/png')
            ->assertHeader('x-content-type-options', 'nosniff');
    }

    public function test_dashboard_image_requires_a_valid_signature(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->actingAs($user, 'admin');

        $this->get(route('dashboard.images.show', ['path' => 'student-photos/student.png']))
            ->assertForbidden();
    }

    public function test_avatar_fallback_is_local_and_contains_initials(): void
    {
        $avatar = DashboardImage::avatar('Muhammad Noman Shaukat');
        $svg = base64_decode(str($avatar)->after('base64,')->toString(), true);

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $avatar);
        $this->assertStringContainsString('MN', $svg);
        $this->assertStringContainsString('#082245', $svg);
    }

    public function test_filament_user_avatar_uses_the_local_fallback(): void
    {
        $user = User::factory()->make(['name' => 'Ayesha Khan']);

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $user->getFilamentAvatarUrl());
    }
}
