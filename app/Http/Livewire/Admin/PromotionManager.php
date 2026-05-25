<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Domains\Promotion\Models\Promotion;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Session;

class PromotionManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public bool $showCreateForm = false;
    public ?int $editingId = null;

    // Form fields
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public string $type = 'percentage';
    public string $target = 'order';
    public float $value = 0;
    public array $conditions = [];
    public bool $isStackable = false;
    public bool $isActive = true;

    protected $listeners = ['deletePromotion'];

    public function create(): void
    {
        $this->resetForm();
        $this->showCreateForm = true;
    }

    public function edit(int $id): void
    {
        $promotion = Promotion::find($id);

        $this->editingId = $id;
        $this->name = $promotion->name;
        $this->slug = $promotion->slug;
        $this->description = $promotion->description ?? '';
        $this->type = $promotion->type->value;
        $this->target = $promotion->target->value;
        $this->value = (float) $promotion->value;
        $this->conditions = $promotion->conditions ?? [];
        $this->isStackable = $promotion->is_stackable;
        $this->isActive = $promotion->is_active;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:promotions,slug' . ($this->editingId ? ",{$this->editingId}" : ''),
            'type' => 'required|in:percentage,fixed_amount,free_shipping',
            'target' => 'required|in:order,product,category',
            'value' => 'required|numeric|min:0',
            'isActive' => 'boolean',
            'isStackable' => 'boolean',
        ]);

        if ($this->editingId) {
            $promotion = Promotion::find($this->editingId);
            $promotion->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'type' => $this->type,
                'target' => $this->target,
                'value' => $this->value,
                'conditions' => $this->conditions,
                'is_stackable' => $this->isStackable,
                'is_active' => $this->isActive,
            ]);

            Session::flash('success', 'Promotion updated successfully');
        } else {
            Promotion::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'type' => $this->type,
                'target' => $this->target,
                'value' => $this->value,
                'conditions' => $this->conditions,
                'is_stackable' => $this->isStackable,
                'is_active' => $this->isActive,
            ]);

            Session::flash('success', 'Promotion created successfully');
        }

        $this->resetForm();
    }

    public function deletePromotion(int $id): void
    {
        Promotion::find($id)->delete();
        Session::flash('success', 'Promotion deleted successfully');
    }

    public function resetForm(): void
    {
        $this->resetValidation();
        $this->showCreateForm = false;
        $this->editingId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->type = 'percentage';
        $this->target = 'order';
        $this->value = 0;
        $this->conditions = [];
        $this->isStackable = false;
        $this->isActive = true;
    }

    #[Computed]
    public function promotions()
    {
        return Promotion::when($this->search, function ($query) {
            $query->where('name', 'like', "%{$this->search}%")
                ->orWhere('slug', 'like', "%{$this->search}%");
        })
            ->when($this->filterStatus, function ($query) {
                $query->where('is_active', $this->filterStatus === 'active');
            })
            ->latest()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.admin.promotion-manager', [
            'promotions' => $this->promotions,
        ]);
    }
}
