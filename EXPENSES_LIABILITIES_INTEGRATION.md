# Expenses-Liabilities Integration Complete

## Overview
Unpaid and partially paid expenses now automatically create liabilities, providing a unified view of all financial obligations.

## Database Changes

### Expenses Table - New Columns
1. `payment_status` - ENUM('paid', 'unpaid', 'partial') - Tracks payment status
2. `amount_paid` - DECIMAL(15,2) - Amount already paid
3. `liability_id` - INT - Links to auto-created liability
4. `expense_reference` - VARCHAR(100) - Reference number

### Liabilities Table - New Columns
1. `source_type` - ENUM('manual', 'expense', 'purchase_order') - Tracks origin
2. `source_id` - INT - ID of source record (expense ID, etc.)

## How It Works

### Creating an Expense

**Paid Expense:**
- payment_status = 'paid'
- amount_paid = amount
- No liability created

**Unpaid Expense:**
- payment_status = 'unpaid'
- amount_paid = 0
- Liability automatically created with:
  - liability_type = 'accounts_payable'
  - principal_amount = expense amount
  - outstanding_balance = expense amount
  - source_type = 'expense'
  - source_id = expense ID

**Partially Paid Expense:**
- payment_status = 'partial'
- amount_paid = (user specified)
- Liability created for outstanding amount:
  - principal_amount = amount - amount_paid
  - outstanding_balance = amount - amount_paid

### Liabilities Page

Now shows two sections:

1. **Unpaid Expenses** (new)
   - Lists all expenses with payment_status = 'unpaid' or 'partial'
   - Shows total amount, amount paid, and outstanding
   - Links to associated liability
   - Allows quick view of all unpaid obligations

2. **All Liabilities** (existing)
   - Shows all liabilities including auto-generated ones
   - Displays source information for expense-based liabilities

## Features

### Automatic Liability Creation
When you create an expense with payment_status = 'unpaid' or 'partial':
1. System calculates outstanding amount
2. Creates liability record automatically
3. Links expense to liability
4. Liability appears in liabilities list

### Unified Debt Tracking
- All unpaid expenses visible on liabilities page
- Track payment status separately from payment method
- See which liabilities came from expenses vs manual entry

### Payment Tracking
- Record partial payments on expenses
- Outstanding amount automatically calculated
- Liability balance reflects unpaid portion

## Usage Examples

### Example 1: Fully Paid Expense
```
Description: Feed Purchase
Amount: 5000
Payment Status: Paid
Amount Paid: 5000
Result: No liability created
```

### Example 2: Unpaid Expense
```
Description: Veterinary Services
Amount: 3000
Payment Status: Unpaid
Amount Paid: 0
Due Date: 2026-04-30
Result: Liability created for GHS 3000
```

### Example 3: Partial Payment
```
Description: Equipment Purchase
Amount: 10000
Payment Status: Partial
Amount Paid: 4000
Due Date: 2026-05-15
Result: Liability created for GHS 6000
```

## Benefits

1. **Complete Financial Picture**: See all debts in one place
2. **Automatic Tracking**: No manual liability entry for unpaid expenses
3. **Payment Monitoring**: Track partial payments easily
4. **Due Date Alerts**: Set due dates for unpaid expenses
5. **Source Traceability**: Know which liabilities came from expenses

## Testing

Run the test script:
```bash
php test_expense_liability_integration.php
```

## Files Modified

1. `app/models/Expense.php` - Added liability creation logic
2. `app/models/Liability.php` - Added source tracking
3. `app/controllers/LiabilityController.php` - Added unpaid expenses
4. `app/views/expenses/create.php` - Added payment status fields
5. `app/views/liabilities/index.php` - Added unpaid expenses section
6. `database/add_expense_payment_status.sql` - Database migration
7. `integrate_expenses_liabilities.php` - Integration script

## Next Steps

To use the new system:
1. Create expenses and mark payment status
2. View unpaid expenses on liabilities page
3. Make payments on liabilities to reduce outstanding balance
4. System automatically tracks everything

The integration is complete and ready to use!
