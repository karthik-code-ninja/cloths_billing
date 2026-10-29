<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ProfileManager extends Component
{
    // Profile information
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $role = '';

    public string $branchName = '';

    public string $memberSince = '';

    // Password change
    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->role = $user->role;
        $this->branchName = $user->branch->name ?? 'Main Store';
        $this->memberSince = $user->created_at ? $user->created_at->format('d M Y') : 'Unknown';
    }

    public function updateProfile(): void
    {
        $user = Auth::user();

        $this->validate([
            'name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:120',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
        ]);

        session()->flash('profile_success', 'Your personal profile details have been successfully updated.');
    }

    public function updatePassword(): void
    {
        $user = Auth::user();

        $this->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'The current password you provided does not match our records.');

            return;
        }

        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        session()->flash('password_success', 'Your password has been changed securely.');
    }

    public function render()
    {
        return view('livewire.profile-manager', [
            'user' => Auth::user(),
        ]);
    }
}
