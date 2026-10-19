<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_saves_and_cleans_page_content()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        Role::findOrCreate('admin');
        $u = User::factory()->create(); $u->assignRole('admin');
        $p = Product::create(['name' => 'X', 'slug' => 'x']);
        $r = $this->actingAs($u)->put(route('admin.products.update', $p), [
            'name' => 'X', 'is_active' => 1,
            'content' => ['subtitle' => 'Hi', 'consult_url' => '/en-in/e/foo', 'benefits' => ['a', ''], 'faq' => [['q' => 'Q', 'a' => 'A'], ['q' => '', 'a' => '']]],
        ]);
        $r->assertSessionHasNoErrors();
        $this->assertSame(['subtitle' => 'Hi', 'consult_url' => '/en-in/e/foo', 'benefits' => ['a'], 'faq' => [['q' => 'Q', 'a' => 'A']]], $p->fresh()->content);

        $r = $this->actingAs($u)->put(route('admin.products.update', $p), ['name' => 'X', 'content' => ['consult_url' => 'javascript:alert(1)']]);
        $r->assertSessionHasErrors('content.consult_url');
    }
}
