<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FormSectionTest extends TestCase
{
    use RefreshDatabase;

    protected function formSection()
    {
        $page = Country::where('slug', 'en-in')->first()->pages()->where('is_home', true)->first();

        return $page->sections()->create([
            'type' => 'form', 'sort_order' => 1, 'is_visible' => true,
            'content' => [
                'name' => 'Contact us', 'heading' => 'Reach out', 'success_message' => 'Got it!',
                'fields' => [
                    ['label' => 'Name', 'type' => 'text', 'required' => '1'],
                    ['label' => 'Email', 'type' => 'email', 'required' => '1'],
                    ['label' => 'Topic', 'type' => 'select', 'required' => '0', 'options' => "Sales\nSupport"],
                ],
            ],
        ]);
    }

    public function test_form_renders_and_stores_submission()
    {
        $s = $this->formSection();
        $this->get('/en-in')->assertOk()->assertSee('Reach out')->assertSee('name="f1"', false);

        $this->post("/en-in/forms/{$s->id}", ['f0' => 'Asha', 'f1' => 'a@b.com', 'f2' => 'Sales'])
            ->assertRedirect()->assertSessionHas("form_success_{$s->id}", 'Got it!');

        $sub = FormSubmission::first();
        $this->assertSame('Contact us', $sub->form_name);
        $this->assertSame('Asha', $sub->data[0]['value']);
    }

    public function test_validation_and_honeypot()
    {
        $s = $this->formSection();
        $this->post("/en-in/forms/{$s->id}", ['f0' => '', 'f1' => 'nope'])->assertSessionHasErrors(['f0', 'f1']);
        $this->post("/en-in/forms/{$s->id}", ['f0' => 'x', 'f1' => 'a@b.com', 'f2' => 'Other'])->assertSessionHasErrors('f2');
        $this->post("/en-in/forms/{$s->id}", ['f0' => 'x', 'f1' => 'a@b.com', 'website' => 'spam'])->assertRedirect();
        $this->assertSame(0, FormSubmission::count());
    }

    public function test_admin_sees_submissions()
    {
        $s = $this->formSection();
        $this->post("/en-in/forms/{$s->id}", ['f0' => 'Asha', 'f1' => 'a@b.com']);

        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get('/admin/submissions')->assertOk()->assertSee('Contact us')->assertSee('Asha');
        $this->actingAs($admin)->get('/admin/submissions/' . FormSubmission::first()->id)->assertOk()->assertSee('a@b.com');
        $this->actingAs($admin)->get('/admin/submissions/export')->assertOk();
    }
}
