<?php

namespace App\Livewire;

use App\Models\Season;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SeasonMaster extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    // Modal state
    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $seasonId = null;

    // Form fields
    public string $name = '';

    public string $code = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:30|unique:seasons,code,'.$this->seasonId,
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
        $this->seasonId = null;
        $this->showModal = true;
    }

    public function editSeason(int $id): void
    {
        $this->resetValidation();
        $season = Season::findOrFail($id);
        $this->seasonId = $season->id;
        $this->name = $season->name;
        $this->code = $season->code;
        $this->is_active = (bool) $season->is_active;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function saveSeason(): void
    {
        if (empty($this->code)) {
            $this->code = strtoupper(Str::slug($this->name));
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => strtoupper($this->code),
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing && $this->seasonId) {
            $season = Season::findOrFail($this->seasonId);
            $season->update($data);
            session()->flash('success', "Season '{$season->name}' updated successfully.");
        } else {
            $season = Season::create($data);
            session()->flash('success', "Season '{$season->name}' created successfully.");
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleStatus(int $id): void
    {
        $season = Season::findOrFail($id);
        $season->is_active = ! $season->is_active;
        $season->save();

        session()->flash('success', "Season '{$season->name}' status updated.");
    }

    public function deleteSeason(int $id): void
    {
        $season = Season::withCount('products')->findOrFail($id);

        if ($season->products_count > 0) {
            session()->flash('error', "Cannot delete season '{$season->name}' because {$season->products_count} product styles are linked to it.");

            return;
        }

        $name = $season->name;
        $season->delete();

        session()->flash('success', "Season '{$name}' deleted successfully.");
    }

    public function resetForm(): void
    {
        $this->name = '';
        $this->code = '';
        $this->is_active = true;
    }

    public function render()
    {
        $query = Season::withCount('products')
            ->when($this->search, function ($q) {
                $q->where('name', 'LIKE', "%{$this->search}%")
                    ->orWhere('code', 'LIKE', "%{$this->search}%");
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('is_active', $this->statusFilter === 'active');
            })
            ->orderBy('name');

        $seasons = $query->paginate(10);

        return view('livewire.season-master', [
            'seasons' => $seasons,
        ]);
    }
}
