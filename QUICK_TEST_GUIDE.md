# Quick Test Guide - Database & Expense System

## 🚀 Quick Start (3 Minutes)

### Test 1: Verify Database (30 seconds)
```
http://localhost/farmapp/verify_database_schema.php
```
✅ Look for: "All Database Checks Passed!"

---

### Test 2: Test Expense System (30 seconds)
```
http://localhost/farmapp/test_expense_system.php
```
✅ Look for: "All Tests Passed!"

---

### Test 3: View Expense Dashboard (1 minute)
```
http://localhost/farmapp/index.php?url=expenses
```
✅ Check:
- Source breakdown cards appear
- Click "Feed Costs" → filters to feed only
- Click "All Sources" → shows everything
- Totals display correctly

---

### Test 4: View Inventory Dashboard (30 seconds)
```
http://localhost/farmapp/index.php?url=inventory
```
✅ Check:
- Page loads without errors
- Recent activities display
- Stock movements show

---

## 📊 What Was Fixed

### Database Verification
- ✅ Verified all table structures match schema
- ✅ Confirmed all columns exist
- ✅ Tested all JOIN queries

### Expense Model Updates
- ✅ Added NULL handling with COALESCE()
- ✅ Added IS NOT NULL checks in WHERE clauses
- ✅ Improved query filtering
- ✅ Better data display for missing values
- ✅ Optimized sorting

### Key Changes
| Issue | Fix |
|-------|-----|
| NULL × cost = NULL | Added `IS NOT NULL AND > 0` checks |
| Missing batch names | Use `COALESCE(batch_name, batch_code)` |
| Missing supplier names | Handle gracefully with CASE |
| Vaccination column | Use `dose_qty` not `quantity_used` |
| Inconsistent sorting | Sort by date DESC, then ID DESC |

---

## 🔍 Understanding Your Expenses

### 5 Expense Sources

| Source | Badge | What It Tracks | Can Edit? |
|--------|-------|----------------|-----------|
| Manual | 🔵 Blue | Direct entries | ✅ Yes |
| Feed | 🟡 Yellow | Feed usage costs | ❌ Edit feed record |
| Medication | 🔴 Red | Medication costs | ❌ Edit medication record |
| Vaccination | 🟢 Green | Vaccination costs | ❌ Edit vaccination record |
| Stock Purchase | 🔵 Cyan | Inventory purchases | ❌ Edit stock receipt |

### How Costs Are Calculated

```
Manual:        amount (direct entry)
Feed:          quantity_kg × unit_cost
Medication:    quantity_used × unit_cost
Vaccination:   cost_amount (direct entry)
Stock Receipt: quantity × unit_cost
```

---

## ⚠️ Common Questions

### Q: Why is my total higher than expected?
**A:** Your total includes ALL 5 sources. Click each source card to see breakdown.

### Q: I see "Manual" for old feed records?
**A:** These are historical records before inventory integration. They're safe to keep.

### Q: Can I edit a feed expense?
**A:** No, it's auto-tracked. Edit the feed record instead: Feed → Edit

### Q: Some records show NULL cost?
**A:** That's normal. Only records with actual costs are included in totals.

### Q: How do I clean up old records?
**A:** Run: `http://localhost/farmapp/cleanup_old_feed_records.php`

---

## 📁 New Files Created

1. **verify_database_schema.php** - Database verification tool
2. **DATABASE_VERIFICATION_COMPLETE.md** - Complete documentation
3. **QUICK_TEST_GUIDE.md** - This file

## 📝 Files Updated

1. **app/models/Expense.php** - Enhanced NULL handling & filtering
2. **app/models/InventorySummary.php** - Fixed vaccination query

---

## ✅ Checklist

- [ ] Run verify_database_schema.php
- [ ] Run test_expense_system.php
- [ ] Test expense dashboard
- [ ] Test inventory dashboard
- [ ] Click source filter cards
- [ ] Verify totals match expectations
- [ ] Review any NULL warnings (usually normal)

---

## 🎯 Expected Results

### verify_database_schema.php
```
✅ All tables exist
✅ All expected columns present
✅ All queries execute successfully
✅ JOIN queries work
⚠️ Some NULL values (normal for optional fields)
```

### test_expense_system.php
```
✅ Expense model loaded
✅ Totals calculated
✅ Breakdown by source working
✅ Sample records displayed
```

### Expense Dashboard
```
✅ Page loads
✅ Source cards display
✅ Filtering works
✅ Totals accurate
✅ Records show correct badges
```

### Inventory Dashboard
```
✅ Page loads
✅ Activities display
✅ Stock movements show
```

---

## 🆘 Troubleshooting

### Issue: Database connection error
**Fix:** Check Apache/MySQL are running

### Issue: Table not found
**Fix:** Run `database/rebuild_complete.sql`

### Issue: Column not found
**Fix:** Run verify_database_schema.php to identify issue

### Issue: Totals don't match
**Fix:** Click each source card to see breakdown

### Issue: NULL values in results
**Fix:** This is normal for optional fields

---

## 📞 Support Files

- **SYSTEM_STATUS.md** - Overall system documentation
- **DATABASE_VERIFICATION_COMPLETE.md** - Database details
- **EXPENSE_TRACKING_GUIDE.md** - User guide
- **FIXES_APPLIED.md** - Summary of fixes

---

**Status:** ✅ Ready to test! Start with verify_database_schema.php
