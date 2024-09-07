<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserPreferenceTest extends TestCase
{
    public function test_user_can_set_preferences()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
    
        $response = $this->postJson('/api/user/preferences', [
            'preferred_sources' => ['source1', 'source2'],
            'preferred_categories' => ['technology', 'health'],
            'preferred_authors' => ['author1', 'author2']
        ]);
    
        $response->assertStatus(200)->assertJson([
            'message' => 'Preferences updated successfully'
        ]);
        $user->delete();
    }
    
    public function test_user_can_get_preferences()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
    
        $response = $this->getJson('/api/user/preferences');
        $response->assertStatus(200);
        $user->delete();
    }
    
    public function test_user_can_get_personalized_news_feed()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
    
        // Set some preferences
        $user->preferences()->create([
            'preferred_sources' => ['source1', 'source2'],
            'preferred_categories' => ['technology', 'health'],
            'preferred_authors' => ['author1']
        ]);
    
        $response = $this->getJson('/api/articles/personalized');
        $response->assertStatus(200);
        // TODO Check if the returned articles match the user's preferences

        $user->delete();
    }
}
