<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_event_management()
    {
        $user = User::factory()->create();
        auth()->login($user);

        $middleware = new \Spatie\Permission\Middleware\RoleMiddleware();
        $request = \Illuminate\Http\Request::create('/dummy', 'GET');

        $this->expectException(\Spatie\Permission\Exceptions\UnauthorizedException::class);
        $middleware->handle($request, function () {
            return response('ok');
        }, 'admin');
    }

    public function test_admin_can_access_event_management()
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        auth()->login($admin);

        $middleware = new \Spatie\Permission\Middleware\RoleMiddleware();
        $request = \Illuminate\Http\Request::create('/dummy', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('ok');
        }, 'admin');

        $this->assertEquals('ok', $response->getContent());
    }
}
