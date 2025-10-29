<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use App\Models\ExpenseCategory;

class ExpenseViewForm extends Component
{
    public $startDate;
    public $endDate;
    public $branchId;
    public $expenseCategoryId;

    public $branches = [];
    public $expenseCategories = [];

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->loadOptions();
    }

    public function loadOptions()
    {
        $this->branches = Branch::where('is_active', true)->get();
        $this->expenseCategories = ExpenseCategory::where('is_active', true)->get();
    }

    public function submitReport()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $this->dispatch('generateExpenseReport', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'branchId' => $this->branchId,
            'expenseCategoryId' => $this->expenseCategoryId,
        ]);
    }

    public function render()
    {
        return view('livewire.expense-view-form');
    }
}

