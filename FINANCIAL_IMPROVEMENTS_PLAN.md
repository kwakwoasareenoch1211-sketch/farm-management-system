# Financial & Economic Dashboard Improvements Plan

## Current State
The system already has:
- Good accounting equation tracking (Assets = Liabilities + Equity)
- Capital differentiation from expenses, assets, and liabilities
- Revenue and expense tracking
- Investment monitoring

## Key Improvements Needed

### 1. Mortality as Financial Impact
**Problem**: Mortality reduces asset value but isn't properly reflected financially
**Solution**:
- Track live birds as biological assets
- When mortality occurs, reduce asset value
- Record mortality loss as an expense (asset write-off)
- Show mortality impact on P&L

### 2. Enhanced Expense Categorization
**Current**: Basic categories (feed, medication, vaccination, direct)
**Improved**: Intelligent grouping:
- **Operating Expenses**: Feed, medication, vaccination, utilities
- **Cost of Goods Sold (COGS)**: Chick purchase, direct production costs
- **Administrative**: Salaries, office expenses
- **Mortality Losses**: Bird deaths (asset write-offs)
- **Depreciation**: Equipment and infrastructure

### 3. Liability Tracking
**Add**:
- Unpaid expenses as current liabilities
- Loan capital as long-term liabilities
- Accounts payable
- Accrued expenses

### 4. Complex Financial Computations
**Add**:
- Current Ratio = Current Assets / Current Liabilities
- Quick Ratio = (Current Assets - Inventory) / Current Liabilities
- Debt-to-Equity Ratio = Total Liabilities / Owner's Equity
- Return on Assets (ROA) = Net Income / Total Assets
- Asset Turnover = Revenue / Average Total Assets
- Break-even Analysis
- Cash Flow projections

### 5. Economic Intelligence
**Add**:
- Cost per bird analysis
- Revenue per bird
- Feed Conversion Ratio (FCR) financial impact
- Mortality rate financial impact
- Batch profitability scoring
- Investment payback period
- Economic Order Quantity (EOQ) for inventory

## Implementation Priority

1. **HIGH**: Add mortality financial tracking
2. **HIGH**: Enhance expense categorization
3. **MEDIUM**: Add liability tracking for unpaid expenses
4. **MEDIUM**: Implement advanced financial ratios
5. **LOW**: Add predictive analytics

## Database Changes Needed

```sql
-- Add mortality financial tracking
ALTER TABLE mortality_records ADD COLUMN asset_value_lost DECIMAL(12,2) DEFAULT 0;
ALTER TABLE mortality_records ADD COLUMN expense_recorded BOOLEAN DEFAULT FALSE;

-- Add expense categories
ALTER TABLE expenses ADD COLUMN expense_category ENUM('operating','cogs','administrative','mortality','depreciation','other') DEFAULT 'operating';

-- Track unpaid expenses as liabilities
ALTER TABLE expenses ADD COLUMN payment_status ENUM('paid','unpaid','partial') DEFAULT 'paid';
ALTER TABLE expenses ADD COLUMN amount_paid DECIMAL(12,2) DEFAULT 0;
```
