<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportEmployee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'alternate_phone',
        'address',
        'date_of_birth',
        'gender',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'id_document_path',
        'id_document_number',
        'other_documents',
        'job_title',
        'designation',
        'date_of_joining',
        'employment_type',
        'branch_id',
        'department',
        'job_description',
        'vehicle_id',
        'license_number',
        'license_expiry_date',
        'monthly_salary',
        'hourly_rate',
        'bank_name',
        'bank_account_number',
        'iban',
        'current_balance',
        'total_advance_taken',
        'total_salary_paid',
        'is_active',
        'date_of_leaving',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_joining' => 'date',
        'date_of_leaving' => 'date',
        'license_expiry_date' => 'date',
        'other_documents' => 'array',
        'monthly_salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'total_advance_taken' => 'decimal:2',
        'total_salary_paid' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            if (empty($employee->employee_number)) {
                $employee->employee_number = static::generateEmployeeNumber();
            }
        });
    }

    /**
     * Generate unique employee number
     */
    public static function generateEmployeeNumber(): string
    {
        $lastEmployee = static::orderBy('id', 'desc')->first();
        if ($lastEmployee && $lastEmployee->employee_number) {
            $lastNumber = (int) substr($lastEmployee->employee_number, 4);
            $number = $lastNumber + 1;
        } else {
            $number = 1;
        }
        return 'TEMP' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Relationships
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function salaryTransactions(): HasMany
    {
        return $this->hasMany(TransportEmployeeSalaryTransaction::class)->orderBy('transaction_date', 'desc');
    }

    /**
     * Get current balance (negative means advance taken)
     */
    public function getCurrentBalance(): float
    {
        return (float) $this->current_balance;
    }

    /**
     * Add advance payment
     */
    public function addAdvance(float $amount, array $data = []): TransportEmployeeSalaryTransaction
    {
        $currentBalance = $this->getCurrentBalance();
        $newBalance = $currentBalance - $amount;

        $transaction = $this->salaryTransactions()->create([
            'transaction_number' => TransportEmployeeSalaryTransaction::generateTransactionNumber(),
            'transaction_date' => $data['transaction_date'] ?? now(),
            'transaction_type' => 'advance',
            'amount' => $amount,
            'payment_method' => $data['payment_method'] ?? 'cash',
            'reference_number' => $data['reference_number'] ?? null,
            'description' => $data['description'] ?? 'Advance payment',
            'is_advance' => true,
            'balance_after' => $newBalance,
            'created_by' => $data['created_by'] ?? auth()->id(),
            'notes' => $data['notes'] ?? null,
        ]);

        // Update employee balance
        $this->current_balance = $newBalance;
        $this->total_advance_taken += $amount;
        $this->save();

        // Create expense entry for Employee_Pay
        $this->createExpenseEntry($amount, $data, 'Advance Payment');

        return $transaction;
    }

    /**
     * Add salary payment
     */
    public function addSalaryPayment(float $amount, array $data = []): TransportEmployeeSalaryTransaction
    {
        $currentBalance = $this->getCurrentBalance();
        $newBalance = $currentBalance + $amount; // Salary payment reduces negative balance

        $transaction = $this->salaryTransactions()->create([
            'transaction_number' => TransportEmployeeSalaryTransaction::generateTransactionNumber(),
            'transaction_date' => $data['transaction_date'] ?? now(),
            'transaction_type' => 'salary',
            'amount' => $amount,
            'payment_method' => $data['payment_method'] ?? 'cash',
            'reference_number' => $data['reference_number'] ?? null,
            'description' => $data['description'] ?? 'Salary payment',
            'month' => $data['month'] ?? now()->format('Y-m'),
            'year' => $data['year'] ?? now()->year,
            'is_advance' => false,
            'balance_after' => $newBalance,
            'created_by' => $data['created_by'] ?? auth()->id(),
            'notes' => $data['notes'] ?? null,
        ]);

        // Update employee balance
        $this->current_balance = $newBalance;
        $this->total_salary_paid += $amount;
        $this->save();

        // Create expense entry for Employee_Pay
        $this->createExpenseEntry($amount, $data, 'Salary Payment');

        return $transaction;
    }

    /**
     * Create expense entry for transport employee payment
     */
    protected function createExpenseEntry(float $amount, array $data, string $type): void
    {
        // Get or create Employee_Pay expense category
        $category = \App\Models\ExpenseCategory::firstOrCreate(
            ['code' => 'EMPLOYEE_PAY'],
            [
                'name' => 'Employee_Pay',
                'description' => 'Employee salary and advance payments',
                'is_active' => true,
            ]
        );

        // Create expense entry
        \App\Models\Expense::create([
            'branch_id' => $this->branch_id ?? \App\Models\Branch::first()->id,
            'expense_category_id' => $category->id,
            'user_id' => $data['created_by'] ?? auth()->id(),
            'transport_employee_id' => $this->id,
            'expense_type' => 'operational',
            'title' => "{$type} - {$this->name}",
            'description' => $data['description'] ?? "{$type} for transport employee {$this->name}",
            'amount' => $amount,
            'expense_date' => $data['transaction_date'] ?? now(),
            'payment_method' => $data['payment_method'] ?? 'cash',
            'reference_number' => $data['reference_number'] ?? null,
            'is_approved' => true,
            'approved_by' => $data['created_by'] ?? auth()->id(),
            'approved_at' => now(),
        ]);
    }
}
