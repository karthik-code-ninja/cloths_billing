<?php

namespace App\Livewire;

use App\Models\SizeGroup;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SizeGroupMaster extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    // Modal state
    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $sizeGroupId = null;

    // Form fields
    public string $name = '';

    public string $code = '';

    public array $sizes = [];

    public string $newSize = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:30|unique:size_groups,code,'.$this->sizeGroupId,
            'sizes' => 'required|array|min:1',
            'sizes.*' => 'required|string|max:20',
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
        $this->sizeGroupId = null;
        $this->sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        $this->showModal = true;
    }

    public function editSizeGroup(int $id): void
    {
        $this->resetValidation();
        $group = SizeGroup::findOrFail($id);
        $this->sizeGroupId = $group->id;
        $this->name = $group->name;
        $this->code = $group->code;
        $this->sizes = is_array($group->sizes) ? $group->sizes : [];
        $this->is_active = (bool) $group->is_active;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function addSize(): void
    {
        $val = trim($this->newSize);
        if ($val !== '' && ! in_array($val, $this->sizes)) {
            $this->sizes[] = $val;
            $this->newSize = '';
        }
    }

    public function removeSize(int $index): void
    {
        if (isset($this->sizes[$index])) {
            unset($this->sizes[$index]);
            $this->sizes = array_values($this->sizes);
        }
    }

    public function loadPreset(string $preset): void
    {
        switch ($preset) {
            case 'alpha':
                $this->sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'];
                if (empty($this->name)) {
                    $this->name = 'Standard Alpha (XS - 3XL)';
                    $this->code = 'ALPHA-'.rand(10, 99);
                }
                break;
            case 'waist':
                $this->sizes = ['28', '30', '32', '34', '36', '38', '40'];
                if (empty($this->name)) {
                    $this->name = 'Waist Numeric (28 - 40)';
                    $this->code = 'WAIST-'.rand(10, 99);
                }
                break;
            case 'kids':
                $this->sizes = ['1Y-2Y', '2Y-3Y', '3Y-4Y', '4Y-5Y', '6Y-7Y', '8Y-9Y', '10Y-12Y'];
                if (empty($this->name)) {
                    $this->name = 'Kids Ages (1Y - 12Y)';
                    $this->code = 'KIDS-'.rand(10, 99);
                }
                break;
            case 'footwear':
                $this->sizes = ['UK-6', 'UK-7', 'UK-8', 'UK-9', 'UK-10', 'UK-11'];
                if (empty($this->name)) {
                    $this->name = 'Footwear UK/IND (6 - 11)';
                    $this->code = 'SHOE-'.rand(10, 99);
                }
                break;
            case 'free':
                $this->sizes = ['Free Size'];
                if (empty($this->name)) {
                    $this->name = 'One Size Fits All';
                    $this->code = 'FREE-'.rand(10, 99);
                }
                break;
        }
    }

    public function saveSizeGroup(): void
    {
        if (empty($this->code)) {
            $this->code = strtoupper(Str::slug($this->name));
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => strtoupper($this->code),
            'sizes' => array_values(array_filter($this->sizes)),
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing && $this->sizeGroupId) {
            $group = SizeGroup::findOrFail($this->sizeGroupId);
            $group->update($data);
            session()->flash('success', "Size group '{$group->name}' updated successfully.");
        } else {
            $group = SizeGroup::create($data);
            session()->flash('success', "Size group '{$group->name}' created successfully.");
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleStatus(int $id): void
    {
        $group = SizeGroup::findOrFail($id);
        $group->is_active = ! $group->is_active;
        $group->save();

        session()->flash('success', "Size group '{$group->name}' status updated.");
    }

    public function deleteSizeGroup(int $id): void
    {
        $group = SizeGroup::findOrFail($id);
        $name = $group->name;
        $group->delete();

        session()->flash('success', "Size group '{$name}' deleted successfully.");
    }

    public function resetForm(): void
    {
        $this->name = '';
        $this->code = '';
        $this->sizes = [];
        $this->newSize = '';
        $this->is_active = true;
    }

    public function render()
    {
        $query = SizeGroup::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'LIKE', "%{$this->search}%")
                    ->orWhere('code', 'LIKE', "%{$this->search}%");
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('is_active', $this->statusFilter === 'active');
            })
            ->orderBy('name');

        $sizeGroups = $query->paginate(10);

        return view('livewire.size-group-master', [
            'sizeGroups' => $sizeGroups,
        ]);
    }
}
