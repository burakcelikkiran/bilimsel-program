<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_service_returns_structured_payload_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = app(AdminNotificationService::class)->forUser($admin);

        $this->assertArrayHasKey('notifications', $payload);
        $this->assertArrayHasKey('unread_count', $payload);
        $this->assertIsArray($payload['notifications']);
    }

    public function test_dashboard_renders_for_authenticated_user(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['role' => 'editor']);
        $organization->users()->attach($user->id, ['role' => 'editor']);

        $response = $this->actingAs($user)
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Dashboard'));
    }
}
