<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Participant;
use App\Models\User;
use App\Services\AdminSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_search_service_finds_matching_participant(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['role' => 'admin']);

        Participant::factory()->create([
            'organization_id' => $organization->id,
            'first_name' => 'Ayşe',
            'last_name' => 'Arama',
        ]);

        $results = app(AdminSearchService::class)->search($user, 'Ayşe');

        $this->assertNotEmpty($results);
        $this->assertSame('participants', $results[0]['type']);
    }

    public function test_admin_search_requires_authentication(): void
    {
        $this->get(route('admin.search', ['q' => 'test']))->assertRedirect();
    }
}
