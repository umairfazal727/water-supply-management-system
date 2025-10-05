# REEM AL FALAJ POS System Enhancement Plan

## Current System Analysis
- **Base System**: Laravel Easy POS with Filament v3
- **Current Features**: Basic POS, customer management, product management, sales tracking
- **Technology Stack**: Laravel 10, Filament v3, MySQL, Livewire

## New Requirements Analysis

### 1. Multi-Branch Support
- **Main Branch**: Contact info, sales, expenses, accounting
- **Branch 1**: Same operations as main branch
- **Water Transport (WATER TR)**: Specialized operations for water delivery

### 2. Enhanced Business Features
- **Contact Information Management**: Customer details, vehicle details, driver info
- **Sales Management**: Daily transactions with delivery options
- **Expense Tracking**: Diesel, generator, filtration, food, employee expenses
- **Accounting & Reports**: Daily/monthly reports, profit & loss, customer statements
- **User Management**: 2 admin users with branch selection

## Implementation Plan

### Phase 1: Database Schema Enhancement

#### New Tables to Create:
1. **branches** - Branch management
2. **vehicles** - Vehicle information
3. **drivers** - Driver information  
4. **expenses** - Expense tracking
5. **expense_categories** - Expense categorization
6. **reports** - Report generation tracking

#### Existing Tables to Modify:
1. **customers** - Add vehicle_id, driver_id, company info
2. **orders** - Add branch_id
3. **payments** - Add branch_id, payment_type

### Phase 2: Models Enhancement

#### Files to Create/Modify:
- `app/Models/Branch.php` - New
- `app/Models/Vehicle.php` - New  
- `app/Models/Driver.php` - New
- `app/Models/Expense.php` - New
- `app/Models/ExpenseCategory.php` - New
- `app/Models/Report.php` - New
- `app/Models/Customer.php` - Modify (add vehicle/driver relationships)
- `app/Models/Order.php` - Modify (add branch relationship)

### Phase 3: Database Migrations
#### New Migration Files:
- `2024_01_01_000001_create_branches_table.php`
- `2024_01_01_000002_create_vehicles_table.php`
- `2024_01_01_000003_create_drivers_table.php`
- `2024_01_01_000004_create_expense_categories_table.php`
- `2024_01_01_000005_create_expenses_table.php`
- `2024_01_01_000007_add_branch_support_to_existing_tables.php`

### Phase 4: Filament Resources Enhancement

#### New Resources:
- `app/Filament/Resources/BranchResource.php`
- `app/Filament/Resources/VehicleResource.php`
- `app/Filament/Resources/DriverResource.php`
- `app/Filament/Resources/ExpenseResource.php`
- `app/Filament/Resources/ExpenseCategoryResource.php`

#### Enhanced Resources:
- `app/Filament/Resources/CustomerResource.php` - Add vehicle/driver fields
- `app/Filament/Resources/OrderResource.php` - Add branch filtering,
- `app/Filament/Resources/ProductResource.php` - Add branch filtering

### Phase 5: Filament Pages Enhancement

#### New Pages:
- `app/Filament/Pages/Reports.php` - Comprehensive reporting
- `app/Filament/Pages/ExpenseManagement.php` - Expense tracking
- `app/Filament/Pages/BranchSelection.php` - Branch switching

#### Enhanced Pages:
- `app/Filament/Pages/Pos.php` - Add branch context
- `app/Filament/Pages/Settings.php` - Add branch settings

### Phase 6: Livewire Components Enhancement

#### New Components:
- `app/Livewire/ExpenseForm.php`
- `app/Livewire/ReportGenerator.php`
- `app/Livewire/BranchSelector.php`

#### Enhanced Components:
- `app/Livewire/Order/Cart.php` - Add branch context
- `app/Livewire/Order/BarcodeScan.php` - Add branch filtering
- `app/Livewire/CustomerSearch.php` - Add vehicle/driver search
- `app/Livewire/ProductSearch.php` - Add branch filtering


### Phase 8: Business Logic Implementation

#### Core Features:
1. **Branch Management**
   - Branch creation and configuration
   - User assignment to branches
   - Branch-specific settings

2. **Contact Information Management**
   - Customer details with vehicle/driver associations
   - Vehicle information (plate, tanker size, rates)
   - Driver information and assignments

3. **Sales Management**
   - Daily transaction recording
   - Branch-specific sales tracking

4. **Expense Tracking**
   - Diesel expenses (daily basis)
   - Generator, filtration, food expenses
   - Employee-related expenses
   - Other operational expenses

5. **Accounting & Reports**
   - Daily sales reports
   - Daily expense reports
   - Customer statements (monthly) (in order there is customer selection so when in report on selected customer get report with select from or to date)
   - Profit & Loss statements
   - PDF export functionality of report as order date, amount , gallen/litter, vehicle , driver

### Phase 9: UI/UX Enhancements

#### Dashboard Improvements:
- Real-time expense tracking

#### Report Generation:
- Interactive report filters
- PDF export functionality

## File Structure Overview

```
app/
├── Models/
│   ├── Branch.php (NEW)
│   ├── Vehicle.php (NEW)
│   ├── Driver.php (NEW)
│   ├── Expense.php (NEW)
│   ├── ExpenseCategory.php (NEW)
│   ├── User.php (MODIFY)
│   ├── Customer.php (MODIFY)
│   ├── Order.php (MODIFY)
│   └── Product.php (MODIFY)
├── Filament/
│   ├── Resources/
│   │   ├── BranchResource.php (NEW)
│   │   ├── VehicleResource.php (NEW)
│   │   ├── ExpenseResource.php (NEW)
│   │   ├── ExpenseCategoryResource.php (NEW)
│   │   ├── DeliveryResource.php (NEW)
│   │   ├── CustomerResource.php (MODIFY)
│   │   ├── OrderResource.php (MODIFY)
│   │   └── ProductResource.php (MODIFY)
│   └── Pages/
│       ├── Reports.php (NEW)
│       ├── ExpenseManagement.php (NEW)
│       ├── BranchSelection.php (NEW)
│       ├── Pos.php (MODIFY)
│       └── Settings.php (MODIFY)
├── Livewire/
│   ├── ExpenseForm.php (NEW)
│   ├── ReportGenerator.php (NEW)
│   ├── BranchSelector.php (NEW)
│   ├── Order/Cart.php (MODIFY)
│   ├── Order/BarcodeScan.php (MODIFY)
│   ├── CustomerSearch.php (MODIFY)
│   └── ProductSearch.php (MODIFY)

database/migrations/
├── 2024_01_01_000001_create_branches_table.php (NEW)
├── 2024_01_01_000002_create_vehicles_table.php (NEW)
├── 2024_01_01_000003_create_drivers_table.php (NEW)
├── 2024_01_01_000004_create_expense_categories_table.php (NEW)
├── 2024_01_01_000005_create_expenses_table.php (NEW)
└── 2024_01_01_000007_add_branch_support_to_existing_tables.php (NEW)
```