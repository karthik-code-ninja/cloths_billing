<?php

namespace App\Livewire;

use App\Models\Brand;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class BrandMaster extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    // Modal state
    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $brandId = null;

    // Form fields
    public string $name = '';

    public string $code = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        $codeRule = 'required|string|max:30|unique:brands,code';
        if ($this->isEditing && $this->brandId) {
            $codeRule .= ','.$this->brandId;
        }

        return [
            'name' => 'required|string|max:100',
            'code' => $codeRule,
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
        $this->brandId = null;
        $this->showModal = true;
    }

    public function editBrand(int $id): void
    {
        $this->resetValidation();
        $brand = Brand::findOrFail($id);
        $this->brandId = $brand->id;
        $this->name = $brand->name;
        $this->code = $brand->code;
        $this->is_active = (bool) $brand->is_active;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function updatedName(): void
    {
        if (! $this->isEditing && empty($this->code)) {
            $cleaned = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $this->name));
            $this->code = substr($cleaned, 0, 6);
        }
    }

    public function saveBrand(): void
    {
        $this->validate();

        $code = strtoupper(trim($this->code));

        if ($this->isEditing && $this->brandId) {
            $brand = Brand::findOrFail($this->brandId);
            $brand->update([
                'name' => trim($this->name),
                'code' => $code,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', "Brand '{$brand->name}' updated successfully.");
        } else {
            $brand = Brand::create([
                'name' => trim($this->name),
                'code' => $code,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', "Brand '{$brand->name}' created successfully.");
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleStatus(int $id): void
    {
        $brand = Brand::findOrFail($id);
        $brand->is_active = ! $brand->is_active;
        $brand->save();
        session()->flash('success', "Brand '{$brand->name}' status updated to ".($brand->is_active ? 'Active' : 'Inactive').'.');
    }

    public function deleteBrand(int $id): void
    {
        $brand = Brand::withCount('products')->findOrFail($id);

        if ($brand->products_count > 0) {
            session()->flash('error', "Cannot delete brand '{$brand->name}' because it contains {$brand->products_count} linked products. Reassign or delete products first.");

            return;
        }

        $brandName = $brand->name;
        $brand->delete();
        session()->flash('success', "Brand '{$brandName}' deleted successfully.");
    }

    public function resetForm(): void
    {
        $this->name = '';
        $this->code = '';
        $this->is_active = true;
    }

    public function render()
    {
        $brandsQuery = Brand::withCount('products')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name');

        $brands = $brandsQuery->paginate(15);

        return view('livewire.brand-master', [
            'brands' => $brands,
        ]);
    }
}
