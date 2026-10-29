<?php

namespace App\Livewire;

use App\Models\CashShift;
use App\Models\CashTransaction;
use App\Models\Counter;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CashShiftManager extends Component
{
    use WithPagination;

    public ?CashShift $activeShift = null;

    // Open Shift Form
    public float $openingFloat = 2000.00;

    public ?int $counterId = null;

    // Cash In / Cash Out Modal
    public bool $showDrawerModal = false;

    public string $drawerType = 'cash_in'; // cash_in, cash_out

    public float $drawerAmount = 0.0;

    public string $drawerReason = '';

    // Store Expense Modal
    public bool $showExpenseModal = false;

    public string $expenseCategory = 'tea_refreshments';

    public float $expenseAmount = 0.0;

    public string $expenseDescription = '';

    // Close Shift Modal
    public bool $showCloseModal = false;

    public float $countedCash = 0.0;

    public string $closeNotes = '';

    public function mount()
    {
        $this->loadActiveShift();
        $this->counterId = Counter::where('branch_id', Auth::user()->branch_id ?: 1)->first()->id ?? null;
    }

    public function render()
    {
        $shifts = CashShift::with(['counter', 'cashier', 'closedBy'])
            ->latest('opened_at')
            ->paginate(10);

        $expenses = Expense::where('branch_id', Auth::user()->branch_id ?: 1)
            ->latest('expense_date')
            ->take(10)
            ->get();

        $expectedCash = $this->activeShift ? $this->activeShift->calculateExpectedCash() : 0.0;

        return view('livewire.cash-shift-manager', [
            'shifts' => $shifts,
            'expenses' => $expenses,
            'expectedCash' => $expectedCash,
            'counters' => Counter::where('branch_id', Auth::user()->branch_id ?: 1)->get(),
        ]);
    }

    public function loadActiveShift()
    {
        $this->activeShift = CashShift::with(['counter', 'cashier', 'orderPayments', 'cashTransactions', 'expenses'])
            ->where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();
    }

    public function openShift()
    {
        if ($this->activeShift) {
            session()->flash('error', 'You already have an open shift.');

            return;
        }

        $shift = CashShift::create([
            'branch_id' => Auth::user()->branch_id ?: 1,
            'counter_id' => $this->counterId ?: 1,
            'user_id' => Auth::id(),
            'opened_at' => now(),
            'opening_float' => $this->openingFloat,
            'expected_closing_cash' => $this->openingFloat,
            'status' => 'open',
        ]);

        $this->loadActiveShift();
        session()->flash('success', "Cash shift #{$shift->id} opened with opening float ₹{$this->openingFloat}.");
    }

    public function openDrawerModal(string $type = 'cash_in')
    {
        $this->drawerType = $type;
        $this->drawerAmount = 0.0;
        $this->drawerReason = ($type === 'cash_in') ? 'Additional Change Float' : 'Bank Drop / Excess Cash';
        $this->showDrawerModal = true;
    }

    public function saveDrawerTransaction()
    {
        if (! $this->activeShift || $this->drawerAmount <= 0) {
            return;
        }

        CashTransaction::create([
            'cash_shift_id' => $this->activeShift->id,
            'user_id' => Auth::id(),
            'type' => $this->drawerType,
            'amount' => $this->drawerAmount,
            'reason' => $this->drawerReason,
        ]);

        $this->showDrawerModal = false;
        $this->loadActiveShift();
        session()->flash('success', ucfirst(str_replace('_', ' ', $this->drawerType))." of ₹{$this->drawerAmount} recorded.");
    }

    public function openExpenseModal()
    {
        $this->expenseCategory = 'tea_refreshments';
        $this->expenseAmount = 0.0;
        $this->expenseDescription = '';
        $this->showExpenseModal = true;
    }

    public function saveExpense()
    {
        if ($this->expenseAmount <= 0) {
            return;
        }

        Expense::create([
            'branch_id' => Auth::user()->branch_id ?: 1,
            'user_id' => Auth::id(),
            'cash_shift_id' => $this->activeShift?->id,
            'category' => $this->expenseCategory,
            'amount' => $this->expenseAmount,
            'payment_method' => 'cash',
            'description' => $this->expenseDescription ?: 'Petty cash expense',
            'expense_date' => now(),
        ]);

        $this->showExpenseModal = false;
        $this->loadActiveShift();
        session()->flash('success', "Store expense of ₹{$this->expenseAmount} recorded.");
    }

    public function openCloseModal()
    {
        if (! $this->activeShift) {
            return;
        }

        $this->countedCash = $this->activeShift->calculateExpectedCash();
        $this->closeNotes = '';
        $this->showCloseModal = true;
    }

    public function closeShift()
    {
        if (! $this->activeShift) {
            return;
        }

        $expected = $this->activeShift->calculateExpectedCash();
        $counted = $this->countedCash;
        $variance = round($counted - $expected, 2);

        $this->activeShift->update([
            'closed_at' => now(),
            'expected_closing_cash' => $expected,
            'counted_closing_cash' => $counted,
            'variance' => $variance,
            'status' => 'closed',
            'notes' => $this->closeNotes,
            'closed_by_id' => Auth::id(),
        ]);

        $this->showCloseModal = false;
        $this->loadActiveShift();
        session()->flash('success', "Shift closed! Counted: ₹{$counted}, Expected: ₹{$expected}, Variance: ₹{$variance}.");
    }
}
