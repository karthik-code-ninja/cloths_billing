<?php

namespace App\Livewire;

use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SupplierMaster extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    // Modal state
    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $supplierId = null;

    // Form fields
    public string $name = '';

    public string $company_name = '';

    public string $gstin = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public string $city = '';

    public string $state = 'Tamil Nadu';

    public string $state_code = '33';

    public int $payment_terms_days = 30;

    public float $opening_balance = 0.00;

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'company_name' => 'nullable|string|max:150',
            'gstin' => 'nullable|string|max:25',
            'phone' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'required|string|max:100',
            'state_code' => 'required|string|max:5',
            'payment_terms_days' => 'required|integer|min:0|max:365',
            'opening_balance' => 'numeric|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->supplierId = null;
        $this->showModal = true;
    }

    public function editSupplier(int $id): void
    {
        $this->resetValidation();
        $supplier = Supplier::findOrFail($id);
        $this->supplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->company_name = $supplier->company_name ?? '';
        $this->gstin = $supplier->gstin ?? '';
        $this->phone = $supplier->phone ?? '';
        $this->email = $supplier->email ?? '';
        $this->address = $supplier->address ?? '';
        $this->city = $supplier->city ?? '';
        $this->state = $supplier->state ?? 'Tamil Nadu';
        $this->state_code = $supplier->state_code ?? '33';
        $this->payment_terms_days = (int) $supplier->payment_terms_days;
        $this->opening_balance = (float) $supplier->opening_balance;
        $this->is_active = (bool) $supplier->is_active;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function saveSupplier(): void
    {
        $this->validate();

        $data = [
            'name' => trim($this->name),
            'company_name' => trim($this->company_name) ?: null,
            'gstin' => strtoupper(trim($this->gstin)) ?: null,
            'phone' => trim($this->phone) ?: null,
            'email' => trim($this->email) ?: null,
            'address' => trim($this->address) ?: null,
            'city' => trim($this->city) ?: null,
            'state' => trim($this->state),
            'state_code' => trim($this->state_code),
            'payment_terms_days' => $this->payment_terms_days,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing && $this->supplierId) {
            $supplier = Supplier::findOrFail($this->supplierId);
            $supplier->update($data);
            session()->flash('success', "Supplier '{$supplier->name}' updated successfully.");
        } else {
            $data['opening_balance'] = $this->opening_balance;
            $data['current_balance'] = $this->opening_balance;
            $supplier = Supplier::create($data);
            session()->flash('success', "Supplier '{$supplier->name}' created successfully.");
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleStatus(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->is_active = ! $supplier->is_active;
        $supplier->save();
        session()->flash('success', "Supplier '{$supplier->name}' status updated.");
    }

    public function deleteSupplier(int $id): void
    {
        $supplier = Supplier::withCount(['purchaseOrders', 'goodsReceivedNotes'])->findOrFail($id);

        if ($supplier->purchase_orders_count > 0 || $supplier->goods_received_notes_count > 0) {
            session()->flash('error', "Cannot delete supplier '{$supplier->name}' because purchase orders or goods receipts are linked to it.");

            return;
        }

        $name = $supplier->name;
        $supplier->delete();
        session()->flash('success', "Supplier '{$name}' deleted successfully.");
    }

    public function resetForm(): void
    {
        $this->name = '';
        $this->company_name = '';
        $this->gstin = '';
        $this->phone = '';
        $this->email = '';
        $this->address = '';
        $this->city = '';
        $this->state = 'Tamil Nadu';
        $this->state_code = '33';
        $this->payment_terms_days = 30;
        $this->opening_balance = 0.00;
        $this->is_active = true;
    }

    public function render()
    {
        $suppliers = Supplier::withCount(['purchaseOrders'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('company_name', 'like', "%{$this->search}%")
                        ->orWhere('gstin', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%")
                        ->orWhere('city', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.supplier-master', [
            'suppliers' => $suppliers,
        ]);
    }
}
