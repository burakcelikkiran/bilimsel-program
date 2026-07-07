<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminParticipantApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_participants_api(): void
    {
        $this->getJson('/api/v1/admin/participants')->assertUnauthorized();
    }

    public function test_authenticated_user_can_list_accessible_participants(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['role' => 'editor']);
        $organization->users()->attach($user->id, ['role' => 'editor']);

        Participant::factory()->count(2)->create(['organization_id' => $organization->id]);
        Participant::factory()->create(); // other org

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/admin/participants');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_authenticated_user_can_search_participants(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['role' => 'editor']);
        $organization->users()->attach($user->id, ['role' => 'editor']);

        Participant::factory()->create([
            'organization_id' => $organization->id,
            'first_name' => 'Ayşe',
            'last_name' => 'Yılmaz',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/admin/participants/search?q=Ayşe')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
