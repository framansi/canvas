<?php

namespace Canvas\Tests\Controllers;

use Canvas\Http\Middleware\Session;
use Canvas\Tests\TestCase;
use Canvas\UserMeta;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([Authorize::class, Session::class, VerifyCsrfToken::class]);

        $this->registerAssertJsonExactFragmentMacro();
    }

    /** @test */
    public function display_a_listing_of_a_new_resource()
    {
        $user = factory(config('canvas.user'))->create();

        $response = $this->actingAs($user)
                         ->getJson('canvas/api/settings')
                         ->assertSuccessful();

        $this->assertArrayHasKey('avatar', $response->decodeResponseJson());
        $this->assertArrayHasKey('dark_mode', $response->decodeResponseJson());
        $this->assertArrayHasKey('digest', $response->decodeResponseJson());
        $this->assertArrayHasKey('summary', $response->decodeResponseJson());
        $this->assertArrayHasKey('locale', $response->decodeResponseJson());
        $this->assertArrayHasKey('username', $response->decodeResponseJson());
    }

    /** @test */
    public function display_a_listing_of_an_existing_resource()
    {
        $userMeta = factory(UserMeta::class)->create([
            'dark_mode' => 1,
            'digest' => 0,
            'locale' => 'en',
        ]);

        $response = $this->actingAs($userMeta->user)
                         ->getJson('canvas/api/settings')
                         ->assertSuccessful();

        $this->assertArrayHasKey('avatar', $response->decodeResponseJson());
        $this->assertArrayHasKey('dark_mode', $response->decodeResponseJson());
        $this->assertArrayHasKey('digest', $response->decodeResponseJson());
        $this->assertArrayHasKey('summary', $response->decodeResponseJson());
        $this->assertArrayHasKey('locale', $response->decodeResponseJson());
        $this->assertArrayHasKey('username', $response->decodeResponseJson());

        $this->assertEquals($userMeta->dark_mode, $response->decodeResponseJson('dark_mode'));
        $this->assertEquals($userMeta->digest, $response->decodeResponseJson('digest'));
        $this->assertEquals($userMeta->locale, $response->decodeResponseJson('locale'));
    }

    /** @test */
    public function store_a_newly_created_resource_in_storage()
    {
        $userMeta = factory(UserMeta::class)->create();

        $response = $this->actingAs($userMeta->user)
                         ->postJson('canvas/api/settings', [
                             'user_id' => $userMeta->user_id,
                         ])
                         ->assertSuccessful();

        $this->assertArrayHasKey('avatar', $response->decodeResponseJson());
        $this->assertArrayHasKey('dark_mode', $response->decodeResponseJson());
        $this->assertArrayHasKey('digest', $response->decodeResponseJson());
        $this->assertArrayHasKey('summary', $response->decodeResponseJson());
        $this->assertArrayHasKey('locale', $response->decodeResponseJson());
        $this->assertArrayHasKey('username', $response->decodeResponseJson());

        $this->assertEquals($userMeta->user_id, $response->decodeResponseJson('user_id'));
    }

    /** @test */
    public function validate_unique_usernames()
    {
        $userMeta = factory(UserMeta::class)->create();

        factory(UserMeta::class)->create([
            'username' => 'an-existing-username',
        ]);

        $response = $this->actingAs($userMeta->user)
                         ->postJson('canvas/api/settings', [
                             'user_id' => $userMeta->user->id,
                             'username' => 'an-existing-username',
                         ])
                         ->assertStatus(422);

        $this->assertArrayHasKey('username', $response->decodeResponseJson('errors'));
    }
}