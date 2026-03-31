# Test Liabilities System

## Quick Test

### Step 1: Access Liabilities Page
```
http://localhost/farmapp/liabilities
```

**Expected Result:**
- ✅ Page loads successfully
- ✅ Shows "Liabilities Management" heading
- ✅ Shows summary statistics (all zeros if no data)
- ✅ Shows empty table with "No liability records yet" message
- ✅ "Add Liability" button visible

### Step 2: Create a Test Liability
```
1. Click "Add Liability" button
2. Fill in the form:
   - Liability Name: "Test Bank Loan"
   - Liability Type: "loan"
   - Principal Amount: 10000
   - Interest Rate: 12
   - Start Date: Today's date
   - Due Date: 1 year from now
   - Status: "active"
3. Click "Create Liability"
```

**Expected Result:**
- ✅ Redirects to liabilities list
- ✅ New liability appears in table
- ✅ Shows GHS 10,000.00 as principal
- ✅ Shows GHS 10,000.00 as outstanding balance
- ✅ Status badge shows "Active"

### Step 3: View Liability Details
```
1. Click "View" button on the test liability
```

**Expected Result:**
- ✅ Shows liability details page
- ✅ Payment progress shows 0% (no payments yet)
- ✅ Shows "Record Payment" form
- ✅ Payment history section shows "No payments recorded yet"

### Step 4: Record a Payment
```
1. In "Record Payment" section:
   - Payment Date: Today
   - Amount Paid: 1000
   - Notes: "First payment"
2. Click "Record Payment"
```

**Expected Result:**
- ✅ Page reloads
- ✅ Progress bar shows 10% (1000/10000)
- ✅ Outstanding balance shows GHS 9,000.00
- ✅ Payment appears in history table
- ✅ Total paid shows GHS 1,000.00

### Step 5: Edit Liability
```
1. Click "Edit" button
2. Change any field (e.g., notes)
3. Click "Update Liability"
```

**Expected Result:**
- ✅ Redirects to liabilities list
- ✅ Changes are saved and visible

## Troubleshooting

### Issue: 404 Route Not Found
**Solution:** Routes file has been fixed. Clear browser cache and try again.

### Issue: Controller Not Found
**Solution:** Verify `app/controllers/LiabilityController.php` exists

### Issue: Model Not Found
**Solution:** Verify `app/models/Liability.php` exists

### Issue: Database Error
**Solution:** Verify `liabilities` and `liability_payments` tables exist in database

## Files Checklist

- ✅ `app/models/Liability.php` - Model
- ✅ `app/controllers/LiabilityController.php` - Controller
- ✅ `app/views/liabilities/index.php` - List view
- ✅ `app/views/liabilities/create.php` - Create form
- ✅ `app/views/liabilities/edit.php` - Edit form
- ✅ `app/views/liabilities/view.php` - Detail view
- ✅ `app/Router/web.php` - Routes (FIXED)

## Routes Available

```
GET  /liabilities              - List all liabilities
GET  /liabilities/view?id=X    - View liability details
GET  /liabilities/create       - Create form
POST /liabilities/store        - Save new liability
GET  /liabilities/edit?id=X    - Edit form
POST /liabilities/update       - Update liability
POST /liabilities/delete?id=X  - Delete liability
POST /liabilities/addPayment   - Record payment
```

## Status

✅ Routes file fixed and verified
✅ All liability files created
✅ Syntax checks passed
✅ Ready for testing

**Next:** Try accessing http://localhost/farmapp/liabilities
