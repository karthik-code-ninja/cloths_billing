<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\CashShift;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class UserManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = 'all';

    public string $statusFilter = 'all';

    public string $branchFilter = 'all';

    // Create / Edit User Modal
    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $role = 'cashier';

    public int $branch_id = 1;

    public string $password = '';

    public string $password_confirmation = '';

    public float $max_discount_percentage = 10.00;

    public bool $can_override_price = false;

    public bool $can_give_refund = false;

    public bool $is_active = true;

    // Reset Password Modal
    public bool $showResetPasswordModal = false;

    public ?int $resetUserId = null;

    public string $resetUserName = '';

    public string $newPassword = '';

    public function mount(): void
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->isManager()) {
            abort(403, 'Unauthorized. Staff and User management requires Admin or Store Manager privileges.');
        }

        $this->branch_id = Auth::user()->branch_id ?: 1;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingBranchFilter(): void
    {
        $this->resetPage();
    }

    public function updatedRole(string $newRole): void
    {
        // Recommend sensible clothing store permissions based on selected role
        switch ($newRole) {
            case 'admin':
                $this->max_discount_percentage = 100.00;
                $this->can_override_price = true;
                $this->can_give_refund = true;
                break;
            case 'manager':
                $this->max_discount_percentage = 30.00;
                $this->can_override_price = true;
                $this->can_give_refund = true;
                break;
            case 'cashier':
                $this->max_discount_percentage = 10.00;
                $this->can_override_price = false;
                $this->can_give_refund = false;
                break;
            case 'inventory_staff':
                $this->max_discount_percentage = 5.00;
                $this->can_override_price = false;
                $this->can_give_refund = false;
                break;
            case 'accountant':
                $this->max_discount_percentage = 10.00;
                $this->can_override_price = false;
                $this->can_give_refund = false;
                break;
        }
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->userId = null;
        $this->branch_id = Auth::user()->branch_id ?: 1;
        $this->role = 'cashier';
        $this->max_discount_percentage = 10.00;
        $this->can_override_price = false;
        $this->can_give_refund = false;
        $this->is_active = true;
        $this->showModal = true;
    }

    public function editUser(int $id): void
    {
        $this->resetValidation();
        $user = User::findOrFail($id);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->role = $user->role;
        $this->branch_id = $user->branch_id ?: 1;
        $this->max_discount_percentage = (float) $user->max_discount_percentage;
        $this->can_override_price = (bool) $user->can_override_price;
        $this->can_give_refund = (bool) $user->can_give_refund;
        $this->is_active = (bool) $user->is_active;

        $this->password = '';
        $this->password_confirmation = '';
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function saveUser(): void
    {
        $rules = [
            'name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:120',
                Rule::unique('users', 'email')->ignore($this->userId),
            ],
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,manager,cashier,inventory_staff,accountant',
            'branch_id' => 'required|exists:branches,id',
            'max_discount_percentage' => 'required|numeric|min:0|max:100',
            'can_override_price' => 'boolean',
            'can_give_refund' => 'boolean',
            'is_active' => 'boolean',
        ];

        if (! $this->isEditing) {
            $rules['password'] = 'required|string|min:6|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:6|confirmed';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'role' => $this->role,
            'branch_id' => $this->branch_id,
            'max_discount_percentage' => $this->max_discount_percentage,
            'can_override_price' => $this->can_override_price,
            'can_give_refund' => $this->can_give_refund,
            'is_active' => $this->is_active,
        ];

        if (! empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->isEditing && $this->userId) {
            $user = User::findOrFail($this->userId);

            // Prevent logged in user from demoting or disabling self
            if ($user->id === Auth::id() && (! $this->is_active || $this->role !== 'admin')) {
                session()->flash('error', 'You cannot deactivate or demote your own administrator account.');

                return;
            }

            $user->update($data);
            session()->flash('success', "Staff member '{$user->name}' updated successfully.");
        } else {
            $user = User::create($data);
            session()->flash('success', "New staff member '{$user->name}' created successfully.");
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleStatus(int $id): void
    {
        if ($id === Auth::id()) {
            session()->flash('error', 'You cannot deactivate your own active account.');

            return;
        }

        $user = User::findOrFail($id);
        $user->is_active = ! $user->is_active;
        $user->save();

        session()->flash('success', "Staff account '{$user->name}' status changed to ".($user->is_active ? 'Active' : 'Inactive').'.');
    }

    public function openResetPasswordModal(int $id): void
    {
        $user = User::findOrFail($id);
        $this->resetUserId = $user->id;
        $this->resetUserName = $user->name;
        $this->newPassword = '';
        $this->showResetPasswordModal = true;
    }

    public function saveResetPassword(): void
    {
        $this->validate([
            'newPassword' => 'required|string|min:6',
        ]);

        if (! $this->resetUserId) {
            return;
        }

        $user = User::findOrFail($this->resetUserId);
        $user->password = Hash::make($this->newPassword);
        $user->save();

        $this->showResetPasswordModal = false;
        $userName = $user->name;
        $this->resetUserId = null;
        $this->newPassword = '';

        session()->flash('success', "Password successfully reset for '{$userName}'.");
    }

    public function deleteUser(int $id): void
    {
        if ($id === Auth::id()) {
            session()->flash('error', 'You cannot delete your own logged-in account.');

            return;
        }

        $user = User::findOrFail($id);

        // Check if user has associated financial records
        $hasOrders = Order::where('cashier_id', $user->id)->orWhere('salesperson_id', $user->id)->exists();
        $hasShifts = CashShift::where('user_id', $user->id)->exists();

        if ($hasOrders || $hasShifts) {
            // Deactivate instead to preserve store audit trail and accounting
            $user->is_active = false;
            $user->save();
            session()->flash('error', "Staff member '{$user->name}' has sales or cash shift transactions attached. To protect your store audit history, the account has been deactivated instead of deleted.");

            return;
        }

        $name = $user->name;
        $user->delete();
        session()->flash('success', "Staff member '{$name}' deleted successfully.");
    }

    public function resetForm(): void
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->role = 'cashier';
        $this->branch_id = Auth::user()->branch_id ?: 1;
        $this->password = '';
        $this->password_confirmation = '';
        $this->max_discount_percentage = 10.00;
        $this->can_override_price = false;
        $this->can_give_refund = false;
        $this->is_active = true;
    }

    public function render()
    {
        $query = User::with('branch')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'LIKE', "%{$this->search}%")
                        ->orWhere('email', 'LIKE', "%{$this->search}%")
                        ->orWhere('phone', 'LIKE', "%{$this->search}%");
                });
            })
            ->when($this->roleFilter !== 'all', fn ($q) => $q->where('role', $this->roleFilter))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('is_active', $this->statusFilter === 'active'))
            ->when($this->branchFilter !== 'all', fn ($q) => $q->where('branch_id', $this->branchFilter))
            ->orderBy('name');

        $users = $query->paginate(10);
        $branches = Branch::where('is_active', true)->get();

        // Summary metrics
        $totalStaff = User::count();
        $activeStaff = User::where('is_active', true)->count();
        $totalCashiers = User::where('role', 'cashier')->count();
        $totalManagers = User::whereIn('role', ['admin', 'manager'])->count();

        return view('livewire.user-manager', [
            'users' => $users,
            'branches' => $branches,
            'totalStaff' => $totalStaff,
            'activeStaff' => $activeStaff,
            'totalCashiers' => $totalCashiers,
            'totalManagers' => $totalManagers,
        ]);
    }
}
