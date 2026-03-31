# Financial Dashboards - Quick Reference

## Access URLs

| Dashboard | URL | Purpose |
|-----------|-----|---------|
| Financial Dashboard | `/financial` | Main financial overview |
| Financial Traceability | `/financial/traceability` | Audit trail & calculations |
| Economic Dashboard | `/economic` | Business intelligence & decisions |
| Business Health | `/economic/business-health` | Health scoring |
| Going Concern | `/economic/going-concern` | Solvency analysis |
| Decision Support | `/economic/decision-support` | Strategic recommendations |

---

## Key Metrics Explained

### Capital
- **What:** Owner equity contributions
- **Source:** `capital_entries` table
- **Formula:** `SUM(amount WHERE entry_type = 'contribution')`
- **Principle:** Represents owner investment, increases equity

### Revenue
- **What:** Total sales income
- **Source:** `sales` table
- **Formula:** `SUM(total_amount)`
- **Principle:** Increases retained earnings and owner equity

### Expenses
- **What:** All operational costs
- **Sources:** `feed_records`, `medication_records`, `vaccination_records`, `expenses`, `mortality_records`, `animal_batches`
- **Components:**
  * Feed costs
  * Medication costs
  * Vaccination costs
  * Direct expenses
  * Livestock purchase cost
  * Mortality losses
- **Principle:** Reduces profit and retained earnings

### Assets
- **What:** What the business owns
- **Sources:** `inventory_item`, `animal_batches`, `sales`, `investments`
- **Components:**
  * Inventory stock
  * Biological assets (live birds)
  * Accounts receivable
  * Fixed assets
- **Principle:** Assets = Liabilities + Equity

### Liabilities
- **What:** What the business owes
- **Sources:** `liabilities`, `liability_payments`, `expenses`
- **Formula:** `SUM(principal - payments) + SUM(unpaid_expenses)`
- **Principle:** Must be repaid, reduces net worth

### Retained Profit
- **Formula:** `Revenue - Expenses`
- **Principle:** Cumulative profit/loss, increases owner equity

### Owner Equity
- **Formula:** `Capital + Retained Profit`
- **Principle:** Total owner stake in business

### Net Worth
- **Formula:** `Owner Equity - Liabilities`
- **Principle:** True business value after debt

### Working Capital
- **Formula:** `Assets - Liabilities`
- **Principle:** Liquidity for operations

### Profit Margin
- **Formula:** `(Retained Profit / Revenue) × 100`
- **Interpretation:** Higher is better, shows efficiency

### Debt Ratio
- **Formula:** `(Liabilities / Assets) × 100`
- **Interpretation:** Lower is better, <60% is healthy

### ROI
- **Formula:** `(Retained Profit / Capital) × 100`
- **Interpretation:** Return on investment, higher is better

### Current Ratio
- **Formula:** `Assets / Liabilities`
- **Interpretation:** >1.0 = solvent, <1.0 = liquidity risk

### Debt-to-Equity
- **Formula:** `Liabilities / Owner Equity`
- **Interpretation:** <1.0 = healthy, >2.0 = over-leveraged

---

## Accounting Equation

```
Assets = Liabilities + Owner's Equity
```

**Must always balance!**

Example:
- Assets: GHS 10,000
- Liabilities: GHS 3,000
- Owner's Equity: GHS 7,000
- Balance: 10,000 = 3,000 + 7,000 ✓

---

## Double Entry Example

**Transaction:** Buy 100 chicks for GHS 500

**Entries:**
1. **Debit:** Livestock Expense (GHS 500) - Cash paid
2. **Debit:** Biological Asset (GHS 500) - Birds owned

**Result:**
- Expense recorded (reduces profit)
- Asset recorded (increases assets)
- Both sides of equation affected

---

## Common Questions

### Q: Why do expenses show both in Expenses and Assets?
**A:** Double entry accounting. When you buy chicks:
- Cash paid = Expense (reduces profit)
- Live birds = Asset (owned resource)

### Q: Why doesn't Capital decrease when I have expenses?
**A:** Capital is owner investment. Expenses reduce Retained Profit, which reduces Owner Equity (Capital + Profit), but not Capital directly.

### Q: How are Liabilities calculated?
**A:** Real-time from database:
- Registered liabilities: `Principal - Payments Made`
- Unpaid expenses: `Amount - Amount Paid`

### Q: What's the difference between Assets and Capital?
**A:** 
- Capital = source of funds (owner investment)
- Assets = what you own (funded by capital + debt)
- Assets can be greater than capital if you have debt

### Q: When is revenue recognized?
**A:** When earned, not when cash received. Sale on credit:
- Revenue recorded immediately
- Cash collected later (accounts receivable)

---

## Health Score Breakdown

**Total: 100 points**

| Component | Max Points | Criteria |
|-----------|------------|----------|
| Profit Margin | 30 | ≥25% = 30, ≥15% = 24, ≥5% = 16, >0% = 10 |
| Liquidity | 20 | ≥2.0 = 20, ≥1.2 = 15, ≥1.0 = 10 |
| Solvency | 15 | Assets > Liabilities |
| Efficiency | 15 | No loss-making batches |
| Growth Trend | 20 | Positive monthly net |

**Interpretation:**
- 80-100: Strong (Green)
- 60-79: Stable (Yellow)
- 0-59: Risk (Red)

---

## Business Stages

| Stage | Criteria | Action |
|-------|----------|--------|
| Pre-Capital | No capital recorded | Add owner equity |
| Startup | Capital > 0, Profit < 0 | Focus on revenue |
| Recovery | Capital > 0, Profit < 0, Assets < Liabilities | Reduce costs |
| Stable | Profit > 0, ROI < 20% | Maintain operations |
| Growth | Profit > 0, ROI ≥ 20%, Assets > Liabilities | Consider expansion |

---

## Quick Checks

### Is my business solvent?
✓ Assets > Liabilities
✓ Current Ratio > 1.0
✓ Positive Net Worth

### Is my business profitable?
✓ Revenue > Expenses
✓ Positive Retained Profit
✓ Profit Margin > 0%

### Can I expand?
✓ Positive monthly net
✓ Assets > Liabilities
✓ Health Score ≥ 70
✓ No loss-making batches

### Should I reduce debt?
⚠ Debt Ratio > 60%
⚠ Debt-to-Equity > 1.5
⚠ Liabilities growing faster than assets

---

## Traceability Features

Every metric shows:
- ✓ Current value
- ✓ Exact formula
- ✓ Source database tables
- ✓ Accounting principle
- ✓ Component breakdown

**Access:** Click "Audit Trail" button on Financial Dashboard

---

## Support

For questions about:
- **Calculations:** Check `/financial/traceability`
- **Decisions:** Check `/economic/decision-support`
- **Health:** Check `/economic/business-health`
- **Solvency:** Check `/economic/going-concern`

All data is real-time from database - no caching!
