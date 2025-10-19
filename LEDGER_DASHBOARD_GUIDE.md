# Ledger Dashboard Guide

## Overview
The Ledger Dashboard provides comprehensive financial tracking with customer ledger entries, revenue/expense calculations, and delivery status monitoring.

## Features

### 1. **Ledger Stats Cards** (Top Section)
Three summary cards displaying:
- **Total Customer Debit**: Total amount debited from customers
- **Total Customer Credit**: Total amount credited to customers  
- **Net Balance**: Overall balance (Debit - Credit)

### 2. **Ledger Entries Table**
Comprehensive table showing all ledger transactions with:
- Entry Number
- Transaction Date
- Origin (JV Number)
- Customer Information (Company, Driver, Vehicle)
- Transaction Type (Order, Payment, Opening Balance, Manual)
- Debit Amount
- Credit Amount
- Running Balance
- Description

#### Table Features:
- **Search**: Search by entry number, origin, customer, or description
- **Filters**: 
  - Filter by transaction type
  - Filter by customer
  - Filter by date range
- **Sorting**: Click column headers to sort
- **Pagination**: 25 entries per page by default
- **Auto-refresh**: Updates every 30 seconds

### 3. **Export Options**

#### CSV Export
- Click "Export CSV" button in table header
- Downloads complete ledger with financial summary
- Includes:
  - Financial summary section
  - Total Debit/Credit
  - Net Balance
  - Orders Total (Revenue)
  - Expenses Total
  - Profit/Loss calculation
  - Pending/Scheduled deliveries count
  - Complete ledger entries with all details

#### PDF Export
- Click "Export PDF" button in table header
- Generates professionally formatted PDF report
- Landscape orientation for better table viewing
- Includes all summary data and ledger entries
- Printer-friendly format

### 4. **View Summary Modal**
Click "View Summary" button to see detailed financial breakdown:

#### Summary Cards:
- Total Debit
- Total Credit
- Net Balance
- Profit/Loss

#### Revenue & Expenses Section:
- Orders Total (Revenue)
- Expenses Total
- Net Profit/Loss calculation

#### Deliveries Status:
- Pending Deliveries count
- Scheduled Deliveries count
- Note: Delivery amounts are NOT included in profit/loss calculations

## Financial Calculations

### Revenue
- Calculated from **Orders Total** (`price` field)
- Includes all approved orders

### Expenses
- Only **approved expenses** are included
- Filtered by `is_approved = true`

### Profit/Loss Formula
```
Profit/Loss = Orders Total - Expenses Total
```

### Important Notes:
1. **Deliveries are NOT included in revenue calculations**
   - Only pending and scheduled deliveries are listed for tracking
   - Delivery amounts (rate_per_gallon, total_amount) are excluded from profit/loss
   
2. **Net Balance Calculation**
   ```
   Net Balance = Total Debit - Total Credit
   ```
   - This represents customer account balances
   - Separate from profit/loss calculation

## Dashboard Location
The Ledger widgets appear on the main Dashboard after:
- Branch Statistics (DashboardStatsWidget - sort: 1)
- Sales Overview (SalesOverview)
- Expense Stats (ExpenseStatsWidget)
- Ledger Stats Cards (LedgerStatsWidget - sort: 2)
- Ledger Entries Table (LedgerOverviewWidget - sort: 4)

## Widget Sorting
Widgets are displayed in order based on their `$sort` property:
1. Branch-specific stats
2. Ledger summary cards
3. Other dashboard widgets
4. Ledger entries table (full width)

## Access Control
- All widgets are available to authenticated admin users
- Respects Filament's authentication middleware
- Export features available to all dashboard users

## Technical Details
- **Auto-discovery**: Widgets are automatically discovered from `app/Filament/Widgets`
- **Real-time updates**: Table polls every 30 seconds for fresh data
- **Responsive**: Works on desktop and tablet devices
- **Dark mode**: Fully supports Filament's dark mode theme

## Customization
To modify widget behavior, edit:
- `app/Filament/Widgets/LedgerStatsWidget.php` - Summary cards
- `app/Filament/Widgets/LedgerOverviewWidget.php` - Table and exports
- `resources/views/filament/widgets/ledger-summary.blade.php` - Modal view
- `resources/views/filament/widgets/ledger-pdf.blade.php` - PDF template

