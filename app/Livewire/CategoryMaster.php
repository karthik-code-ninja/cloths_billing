<?php

namespace App\Livewire;

use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CategoryMaster extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public string $parentFilter = 'all';

    // Modal state
    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $categoryId = null;

    // Form fields
    public string $name = '';

    public ?int $parent_id = null;

    public string $code = '';

    public string $hsn_code = '6203';

    public float $default_tax_rate = 5.00;

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:categories,id',
            'code' => 'nullable|string|max:30',
            'hsn_code' => 'nullable|string|max:20',
            'default_tax_rate' => 'required|numeric|min:0|max:100',
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

    public function updatingParentFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->categoryId = null;
        $this->showModal = true;
    }

    public function editCategory(int $id): void
    {
        $this->resetValidation();
        $category = Category::findOrFail($id);
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->parent_id = $category->parent_id;
        $this->code = $category->code ?? '';
        $this->hsn_code = $category->hsn_code ?? '6203';
        $this->default_tax_rate = (float) $category->default_tax_rate;
        $this->is_active = (bool) $category->is_active;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function saveCategory(): void
    {
        $this->validate();

        // Prevent setting itself as parent
        if ($this->isEditing && $this->categoryId && $this->parent_id === $this->categoryId) {
            $this->addError('parent_id', 'A category cannot be its own parent.');

            return;
        }

        $slug = Str::slug($this->name);
        $existingSlugQuery = Category::where('slug', $slug);
        if ($this->isEditing && $this->categoryId) {
            $existingSlugQuery->where('id', '!=', $this->categoryId);
        }
        if ($existingSlugQuery->exists()) {
            $slug .= '-'.rand(10, 99);
        }

        if ($this->isEditing && $this->categoryId) {
            $category = Category::findOrFail($this->categoryId);
            $category->update([
                'name' => $this->name,
                'slug' => $slug,
                'parent_id' => $this->parent_id,
                'code' => $this->code ?: strtoupper(substr(Str::slug($this->name), 0, 8)),
                'hsn_code' => $this->hsn_code,
                'default_tax_rate' => $this->default_tax_rate,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', "Category '{$category->name}' updated successfully.");
        } else {
            $category = Category::create([
                'name' => $this->name,
                'slug' => $slug,
                'parent_id' => $this->parent_id,
                'code' => $this->code ?: strtoupper(substr(Str::slug($this->name), 0, 8)),
                'hsn_code' => $this->hsn_code,
                'default_tax_rate' => $this->default_tax_rate,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', "Category '{$category->name}' created successfully.");
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleStatus(int $id): void
    {
        $category = Category::findOrFail($id);
        $category->is_active = ! $category->is_active;
        $category->save();
        session()->flash('success', "Category '{$category->name}' status updated to ".($category->is_active ? 'Active' : 'Inactive').'.');
    }

    public function deleteCategory(int $id): void
    {
        $category = Category::withCount(['products', 'children'])->findOrFail($id);

        if ($category->products_count > 0) {
            session()->flash('error', "Cannot delete category '{$category->name}' because it contains {$category->products_count} linked products. Reassign or delete products first.");

            return;
        }

        if ($category->children_count > 0) {
            session()->flash('error', "Cannot delete category '{$category->name}' because it has {$category->children_count} subcategories.");

            return;
        }

        $categoryName = $category->name;
        $category->delete();
        session()->flash('success', "Category '{$categoryName}' deleted successfully.");
    }

    public function resetForm(): void
    {
        $this->name = '';
        $this->parent_id = null;
        $this->code = '';
        $this->hsn_code = '6203';
        $this->default_tax_rate = 5.00;
        $this->is_active = true;
    }

    public function render()
    {
        $categoriesQuery = Category::with(['parent', 'children'])
            ->withCount('products')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%")
                        ->orWhere('hsn_code', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($this->parentFilter !== 'all', function ($q) {
                if ($this->parentFilter === 'root') {
                    $q->whereNull('parent_id');
                } else {
                    $q->where('parent_id', (int) $this->parentFilter);
                }
            })
            ->orderBy('name');

        $categories = $categoriesQuery->paginate(15);
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('livewire.category-master', [
            'categories' => $categories,
            'parentCategories' => $parentCategories,
        ]);
    }
}
