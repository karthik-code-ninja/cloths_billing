<?php

namespace Tests\Feature;

use App\Livewire\ProfileManager;
use App\Livewire\UserManager;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected User $cashier;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::first() ?? Branch::create([
            'name' => 'Test Salem Branch',
            'code' => 'SLM-TEST',
            'address' => '123 Main Road',
            'city' => 'Salem',
            'state' => 'Tamil Nadu',
            'state_code' => '33',
            'is_active' => true,
        ]);

        $this->admin = User::where('role', 'admin')->first() ?? User::create([
            'name' => 'Admin Tester',
            'email' => 'admin_test_'.Str::random(6).'@nextdna.com',
            'password' => bcrypt('Nextdna@123'),
            'role' => 'admin',
            'branch_id' => $this->branch->id,
            'is_active' => true,
            'max_discount_percentage' => 100.00,
        ]);

        $this->cashier = User::create([
            'name' => 'Cashier Test User',
            'email' => 'cashier_test_'.Str::random(6).'@nextdna.com',
            'password' => bcrypt('Cashier@123'),
            'role' => 'cashier',
            'branch_id' => $this->branch->id,
            'is_active' => true,
            'max_discount_percentage' => 10.00,
        ]);
    }

    public function test_user_manager_screen_can_be_rendered_by_admin(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(UserManager::class)
            ->assertStatus(200)
            ->assertSee('Staff &amp; User Management', false)
            ->assertSee($this->admin->name);
    }

    public function test_user_manager_forbidden_for_regular_cashier(): void
    {
        $this->actingAs($this->cashier);

        $this->get(route('settings.users'))
            ->assertStatus(403);
    }

    public function test_admin_can_create_new_staff_member(): void
    {
        $this->actingAs($this->admin);

        $email = 'staff_'.Str::random(6).'@nextdna.com';

        Livewire::test(UserManager::class)
            ->call('openCreateModal')
            ->set('name', 'Priya Mani (Sales Cashier)')
            ->set('email', $email)
            ->set('phone', '9842199887')
            ->set('role', 'cashier')
            ->set('branch_id', $this->branch->id)
            ->set('password', 'Priya@12345')
            ->set('password_confirmation', 'Priya@12345')
            ->set('max_discount_percentage', 15.00)
            ->set('can_override_price', false)
            ->set('can_give_refund', false)
            ->call('saveUser')
            ->assertHasNoErrors();

        $newUser = User::where('email', $email)->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('Priya Mani (Sales Cashier)', $newUser->name);
        $this->assertEquals('cashier', $newUser->role);
        $this->assertEquals(15.00, (float) $newUser->max_discount_percentage);
        $this->assertTrue(Hash::check('Priya@12345', $newUser->password));
    }

    public function test_admin_can_edit_existing_staff_member(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(UserManager::class)
            ->call('editUser', $this->cashier->id)
            ->set('name', 'Cashier Test User Updated')
            ->set('phone', '9842100000')
            ->set('max_discount_percentage', 12.50)
            ->call('saveUser')
            ->assertHasNoErrors();

        $this->cashier->refresh();
        $this->assertEquals('Cashier Test User Updated', $this->cashier->name);
        $this->assertEquals('9842100000', $this->cashier->phone);
        $this->assertEquals(12.50, (float) $this->cashier->max_discount_percentage);
    }

    public function test_admin_can_toggle_staff_status(): void
    {
        $this->actingAs($this->admin);

        $this->assertTrue($this->cashier->is_active);

        Livewire::test(UserManager::class)
            ->call('toggleStatus', $this->cashier->id);

        $this->assertFalse((bool) $this->cashier->fresh()->is_active);

        // Cannot toggle self
        Livewire::test(UserManager::class)
            ->call('toggleStatus', $this->admin->id);

        $this->assertTrue((bool) $this->admin->fresh()->is_active);
    }

    public function test_admin_can_reset_staff_password(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(UserManager::class)
            ->call('openResetPasswordModal', $this->cashier->id)
            ->set('newPassword', 'NewSecret@999')
            ->call('saveResetPassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('NewSecret@999', $this->cashier->fresh()->password));
    }

    public function test_profile_manager_screen_can_be_rendered(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ProfileManager::class)
            ->assertStatus(200)
            ->assertSee('My Account Profile')
            ->assertSee($this->admin->name)
            ->assertSee($this->admin->email);
    }

    public function test_user_can_update_profile_information(): void
    {
        $this->actingAs($this->cashier);

        Livewire::test(ProfileManager::class)
            ->set('name', 'Updated Self Name')
            ->set('phone', '9988776655')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $this->cashier->refresh();
        $this->assertEquals('Updated Self Name', $this->cashier->name);
        $this->assertEquals('9988776655', $this->cashier->phone);
    }

    public function test_user_can_update_password_with_valid_current_password(): void
    {
        $this->actingAs($this->cashier);

        Livewire::test(ProfileManager::class)
            ->set('current_password', 'Cashier@123')
            ->set('new_password', 'NewSuperPass@2026')
            ->set('new_password_confirmation', 'NewSuperPass@2026')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('NewSuperPass@2026', $this->cashier->fresh()->password));
    }

    public function test_user_cannot_update_password_with_invalid_current_password(): void
    {
        $this->actingAs($this->cashier);

        Livewire::test(ProfileManager::class)
            ->set('current_password', 'WrongOldPassword!')
            ->set('new_password', 'NewSuperPass@2026')
            ->set('new_password_confirmation', 'NewSuperPass@2026')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);

        // Password must remain original
        $this->assertTrue(Hash::check('Cashier@123', $this->cashier->fresh()->password));
    }
}
