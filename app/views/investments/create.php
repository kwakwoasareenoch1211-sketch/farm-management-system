<?php $base = rtrim(BASE_URL, '/'); ?>
<div class="container py-4" style="max-width:1000px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="badge bg-primary rounded-pill mb-2 px-3 py-2">Smart Investment Entry</span>
            <h2 class="fw-bold mb-0">Add Investment</h2>
            <p class="text-muted small mb-0">Enter the amount and type — the system analyses your business performance and auto-computes the expected return, investor share, and payback period.</p>
        </div>
        <a href="<?= $base ?>/investments" class="btn btn-outline-secondary btn-sm">← Back</a>
    </div>

    <div class="row g-4">
        <!-- Form -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST" action="<?= $base ?>/investments/store" id="invForm">
                        <!-- Hidden: auto-computed values injected before submit -->
                        <input type="hidden" name="expected_return"   id="h_expected_return">
                        <input type="hidden" name="investor_share_pct" id="h_investor_share">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Farm <span class="text-danger">*</span></label>
                                <select name="farm_id" class="form-select" required>
                                    <option value="">Select Farm</option>
                                    <?php foreach ($farms ?? [] as $f): ?>
                                        <option value="<?= (int)$f['id'] ?>"><?= htmlspecialchars($f['farm_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Investment Date <span class="text-danger">*</span></label>
                                <input type="date" name="investment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="disposed">Disposed</option>
                                    <option value="depreciated">Fully Depreciated</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. Layer Flock Investment" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Investment Type <span class="text-danger">*</span></label>
                                <select name="investment_type" id="inv_type" class="form-select" required>
                                    <option value="eggs">🥚 Egg Production</option>
                                    <option value="livestock">🐔 Live Birds / Livestock</option>
                                    <option value="equipment">🔧 Equipment</option>
                                    <option value="infrastructure">🏗️ Infrastructure</option>
                                    <option value="land">🌍 Land</option>
                                    <option value="technology">💻 Technology</option>
                                    <option value="other">📦 Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Useful Life (Years) <span class="text-danger">*</span></label>
                                <input type="number" name="useful_life_years" id="useful_life_years" class="form-control" min="1" max="50" value="3" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Amount Invested (GHS) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="amount" class="form-control form-control-lg" step="0.01" min="0.01" placeholder="0.00" required>
                                <div class="small text-muted mt-1">Enter amount — system will auto-compute returns from your business data.</div>
                            </div>

                            <!-- Investor section -->
                            <div class="col-12">
                                <div class="border rounded-4 p-3 bg-light">
                                    <div class="fw-semibold small mb-2"><i class="bi bi-person-check me-1 text-primary"></i>Investor Details (Optional)</div>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <input type="text" name="investor_name" id="investor_name" class="form-control form-control-sm" placeholder="Investor name (optional)">
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="small text-muted mb-0 text-nowrap">Investor Share %</label>
                                                <input type="number" name="investor_share_input" id="investor_share_input" class="form-control form-control-sm" min="1" max="49" step="0.5" placeholder="Auto-suggested">
                                                <span class="badge bg-info text-dark" id="share_badge" style="display:none;white-space:nowrap;"></span>
                                            </div>
                                            <div class="small text-muted mt-1">Leave blank to use the system's suggested share based on investment type and business health.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Reference No.</label>
                                <input type="text" name="reference_no" class="form-control" placeholder="Optional">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="What is this investment for?"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea name="notes" id="notes_field" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" id="submitBtn" class="btn btn-dark w-100 py-3 fw-bold" disabled>
                                    <i class="bi bi-save me-2"></i>Save Investment
                                </button>
                                <div class="small text-muted text-center mt-2" id="submitHint">Enter amount to enable save</div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Intelligence Panel -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top:80px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-1"><i class="bi bi-cpu text-primary me-2"></i>Business Intelligence Engine</h6>
                    <p class="text-muted small mb-3">Auto-analyses your actual sales performance to compute the best expected return and investor split.</p>

                    <!-- Empty state -->
                    <div id="intel_empty" class="text-center py-5 text-muted">
                        <i class="bi bi-graph-up-arrow fs-1 d-block mb-3 opacity-25"></i>
                        <p class="small">Enter an amount and select investment type to see intelligent projections.</p>
                    </div>

                    <!-- Loading -->
                    <div id="intel_loading" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="small text-muted mt-2">Analysing business performance...</p>
                    </div>

                    <!-- Results -->
                    <div id="intel_results" class="d-none">

                        <!-- Business context -->
                        <div class="border rounded-4 p-3 mb-3 bg-light">
                            <div class="fw-semibold small text-muted mb-2"><i class="bi bi-bar-chart me-1"></i>Business Performance Context</div>
                            <div class="row g-2 text-center">
                                <div class="col-4">
                                    <div class="small text-muted">Avg Monthly Revenue</div>
                                    <div class="fw-bold text-success" id="r_monthly_rev">—</div>
                                </div>
                                <div class="col-4">
                                    <div class="small text-muted">Profit Margin</div>
                                    <div class="fw-bold" id="r_margin">—</div>
                                </div>
                                <div class="col-4">
                                    <div class="small text-muted">Data Source</div>
                                    <div class="fw-bold text-info" id="r_source" style="font-size:10px;">—</div>
                                </div>
                            </div>
                        </div>

                        <!-- Auto-computed return -->
                        <div class="border rounded-4 p-3 mb-3" style="border-color:#22c55e!important;background:#f0fdf4;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="fw-semibold small text-success"><i class="bi bi-magic me-1"></i>Auto-Computed Expected Return</div>
                                <span class="badge bg-success" id="r_roi_badge">—</span>
                            </div>
                            <div class="fs-4 fw-bold text-success mb-1" id="r_expected_return">GHS —</div>
                            <div class="small text-muted" id="r_return_basis">—</div>
                            <div class="row g-2 text-center mt-2">
                                <div class="col-4"><div class="small text-muted">Weekly</div><div class="fw-bold text-success" id="r_weekly">—</div></div>
                                <div class="col-4"><div class="small text-muted">Monthly</div><div class="fw-bold text-success" id="r_monthly">—</div></div>
                                <div class="col-4"><div class="small text-muted">Yearly</div><div class="fw-bold text-success" id="r_annual">—</div></div>
                            </div>
                        </div>

                        <!-- Payback -->
                        <div class="border rounded-4 p-3 mb-3 bg-light">
                            <div class="fw-semibold small text-muted mb-2"><i class="bi bi-clock-history me-1"></i>Payback Period</div>
                            <div class="row g-2 text-center">
                                <div class="col-6"><div class="small text-muted">Years</div><div class="fw-bold fs-5" id="r_payback_years">—</div></div>
                                <div class="col-6"><div class="small text-muted">Months</div><div class="fw-bold fs-5" id="r_payback_months">—</div></div>
                            </div>
                        </div>

                        <!-- Investor split -->
                        <div class="border rounded-4 p-3 mb-3" style="border-color:#8b5cf6!important;background:#faf5ff;">
                            <div class="fw-semibold small mb-2" style="color:#8b5cf6;"><i class="bi bi-person-check me-1"></i>Suggested Investor Split</div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <div class="text-center border rounded-3 p-2 bg-white">
                                        <div class="small text-muted">Investor Gets</div>
                                        <div class="fw-bold fs-5" style="color:#8b5cf6;" id="r_inv_share_pct">—%</div>
                                        <div class="small text-muted" id="r_inv_total">—</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center border rounded-3 p-2 bg-white">
                                        <div class="small text-muted">Business Keeps</div>
                                        <div class="fw-bold fs-5 text-success" id="r_biz_share_pct">—%</div>
                                        <div class="small text-muted" id="r_biz_total">—</div>
                                    </div>
                                </div>
                            </div>
                            <div class="small fw-semibold text-muted mb-1">Investor Return Schedule</div>
                            <div class="row g-2 text-center">
                                <div class="col-4"><div class="small text-muted">Weekly</div><div class="fw-bold" style="color:#8b5cf6;" id="r_inv_weekly">—</div></div>
                                <div class="col-4"><div class="small text-muted">Monthly</div><div class="fw-bold" style="color:#8b5cf6;" id="r_inv_monthly">—</div></div>
                                <div class="col-4"><div class="small text-muted">Yearly</div><div class="fw-bold" style="color:#8b5cf6;" id="r_inv_annual">—</div></div>
                            </div>
                        </div>

                        <!-- Year-by-year -->
                        <div id="yearTableWrap" class="d-none">
                            <div class="fw-semibold small mb-2">Year-by-Year Projection</div>
                            <div class="table-responsive" style="max-height:200px;overflow-y:auto;">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr><th>Year</th><th>Total Return</th><th>Investor</th><th>Business</th><th>Net Gain</th></tr>
                                    </thead>
                                    <tbody id="yearTableBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="alert alert-info py-2 small mt-3 mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            These projections are auto-computed from your actual business data. The expected return is saved automatically — you don't need to enter it manually.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const amtEl    = document.getElementById('amount');
    const typeEl   = document.getElementById('inv_type');
    const lifeEl   = document.getElementById('useful_life_years');
    const shareEl  = document.getElementById('investor_share_input');
    const submitBtn= document.getElementById('submitBtn');
    const submitHint=document.getElementById('submitHint');

    let debounceTimer = null;
    let lastData = null;

    function fmt(n){ return 'GHS ' + parseFloat(n||0).toFixed(2); }

    // Default investor share by type (used for local calc before AJAX returns)
    const defaultShare = {eggs:30,livestock:35,equipment:25,infrastructure:20,land:20,technology:28,other:30};

    // Local calculation — runs immediately, no server needed
    function calcLocal(amount, type, life){
        const rateMap = {eggs:0,livestock:0,equipment:20,infrastructure:15,land:10,technology:18,other:12};
        const rate = rateMap[type] ?? 12;
        // For eggs/livestock we can't know revenue locally, use 15% as placeholder
        const annualRate = (type==='eggs'||type==='livestock') ? 15 : rate;
        const suggestedReturn = amount * (1 + (annualRate/100) * life);
        const netGain = suggestedReturn - amount;
        const annReturn = life > 0 ? netGain / life : 0;
        const invShare = defaultShare[type] ?? 30;
        const paybackYears = annReturn > 0 ? amount / annReturn : 0;

        return {
            type, amount, life_years: life,
            suggested_return: suggestedReturn,
            annual_return_rate_pct: annualRate,
            return_basis: (type==='eggs'||type==='livestock')
                ? 'Estimated 15% annual return (will update with actual sales data)'
                : rate + '% annual return (industry standard)',
            roi_pct: amount > 0 ? ((netGain/amount)*100).toFixed(1) : 0,
            annual_return: annReturn,
            monthly_return: annReturn/12,
            weekly_return: annReturn/52,
            payback_years: paybackYears.toFixed(1),
            payback_months: Math.round(paybackYears*12),
            suggested_investor_share: invShare,
            avg_monthly_revenue: 0,
            overall_margin_pct: 0,
            revenue_source: 'Local estimate (fetching live data...)',
        };
    }

    function fetchIntelligence(){
        const amount = parseFloat(amtEl.value) || 0;
        const type   = typeEl.value;
        const life   = parseInt(lifeEl.value) || 3;

        if (amount <= 0) {
            document.getElementById('intel_empty').classList.remove('d-none');
            document.getElementById('intel_loading').classList.add('d-none');
            document.getElementById('intel_results').classList.add('d-none');
            submitBtn.disabled = true;
            submitHint.textContent = 'Enter amount to enable save';
            return;
        }

        // Show local calc immediately so user sees something right away
        const localData = calcLocal(amount, type, life);
        lastData = localData;
        document.getElementById('intel_empty').classList.add('d-none');
        document.getElementById('intel_loading').classList.add('d-none');
        renderResults(localData);

        // Then fetch live data from server
        const url = '<?= $base ?>/investments/performance'
                  + '?type=' + encodeURIComponent(type)
                  + '&amount=' + amount
                  + '&life=' + life;

        fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
            .then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.text();
            })
            .then(text => {
                // Guard: only parse if it looks like JSON
                const trimmed = text.trim();
                if (!trimmed.startsWith('{') && !trimmed.startsWith('[')) {
                    console.warn('Performance endpoint returned non-JSON:', trimmed.substring(0, 100));
                    return; // keep local calc
                }
                const d = JSON.parse(trimmed);
                if (d.error) { console.warn('Performance error:', d.error); return; }
                lastData = d;
                renderResults(d);
            })
            .catch(err => {
                console.warn('Performance fetch failed, using local calc:', err);
                // Local calc already shown — just update source label
                document.getElementById('r_source').textContent = 'Local estimate (server unavailable)';
            });
    }

    function renderResults(d){
        document.getElementById('intel_loading').classList.add('d-none');
        document.getElementById('intel_results').classList.remove('d-none');

        // Business context
        document.getElementById('r_monthly_rev').textContent = fmt(d.avg_monthly_revenue);
        const margin = parseFloat(d.overall_margin_pct) || 0;
        const mEl = document.getElementById('r_margin');
        mEl.textContent = margin.toFixed(1) + '%';
        mEl.className = 'fw-bold ' + (margin >= 20 ? 'text-success' : margin >= 5 ? 'text-warning' : 'text-secondary');
        document.getElementById('r_source').textContent = d.revenue_source || 'Business data';

        // Expected return
        document.getElementById('r_expected_return').textContent = fmt(d.suggested_return);
        document.getElementById('r_return_basis').textContent    = d.return_basis || '';
        document.getElementById('r_roi_badge').textContent       = 'ROI ' + d.roi_pct + '%';
        document.getElementById('r_weekly').textContent  = fmt(d.weekly_return);
        document.getElementById('r_monthly').textContent = fmt(d.monthly_return);
        document.getElementById('r_annual').textContent  = fmt(d.annual_return);

        // Payback
        document.getElementById('r_payback_years').textContent  = d.payback_years > 0 ? d.payback_years + ' yrs' : 'N/A';
        document.getElementById('r_payback_months').textContent = d.payback_months > 0 ? d.payback_months + ' mo' : 'N/A';

        // Investor split
        const manualShare = parseFloat(shareEl.value) || 0;
        const invShare    = manualShare > 0 ? manualShare : (d.suggested_investor_share || 30);
        const bizShare    = 100 - invShare;
        const invTotal    = d.suggested_return * invShare / 100;
        const bizTotal    = d.suggested_return * bizShare / 100;
        const life        = d.life_years || 3;
        const invAnn      = invTotal / Math.max(1, life);
        const invMon      = invAnn / 12;
        const invWk       = invAnn / 52;

        document.getElementById('r_inv_share_pct').textContent = invShare + '%';
        document.getElementById('r_biz_share_pct').textContent = bizShare + '%';
        document.getElementById('r_inv_total').textContent     = fmt(invTotal) + ' total';
        document.getElementById('r_biz_total').textContent     = fmt(bizTotal) + ' total';
        document.getElementById('r_inv_weekly').textContent    = fmt(invWk);
        document.getElementById('r_inv_monthly').textContent   = fmt(invMon);
        document.getElementById('r_inv_annual').textContent    = fmt(invAnn);

        const badge = document.getElementById('share_badge');
        badge.textContent = 'Suggested: ' + (d.suggested_investor_share || invShare) + '%';
        badge.style.display = 'inline';

        // Year-by-year table
        const annRet = parseFloat(d.annual_return) || 0;
        const amount = parseFloat(amtEl.value) || 0;
        if (life > 0 && annRet > 0) {
            document.getElementById('yearTableWrap').classList.remove('d-none');
            const tbody = document.getElementById('yearTableBody');
            tbody.innerHTML = '';
            const maxGain = d.suggested_return - amount;
            for (let y = 1; y <= life; y++) {
                const totalRet = Math.min(maxGain > 0 ? maxGain : annRet * life, annRet * y);
                const invRet   = totalRet * invShare / 100;
                const bizRet   = totalRet * bizShare / 100;
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>Year ${y}</td>
                    <td class="text-success">GHS ${totalRet.toFixed(2)}</td>
                    <td style="color:#8b5cf6;">GHS ${invRet.toFixed(2)}</td>
                    <td class="text-success">GHS ${bizRet.toFixed(2)}</td>
                    <td class="text-success">GHS ${totalRet.toFixed(2)}</td>`;
                tbody.appendChild(tr);
            }
        } else {
            document.getElementById('yearTableWrap').classList.add('d-none');
        }

        // Inject hidden values
        document.getElementById('h_expected_return').value = d.suggested_return;
        document.getElementById('h_investor_share').value  = invShare;

        submitBtn.disabled = false;
        submitHint.textContent = 'Expected return ' + fmt(d.suggested_return) + ' auto-computed';
    }

    function onInput(){
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchIntelligence, 400);
    }

    amtEl.addEventListener('input', onInput);
    typeEl.addEventListener('change', onInput);
    lifeEl.addEventListener('change', onInput);
    shareEl.addEventListener('input', () => { if (lastData) renderResults(lastData); });

    document.getElementById('invForm').addEventListener('submit', function(){
        const invName  = document.getElementById('investor_name').value.trim();
        const invShare = document.getElementById('h_investor_share').value;
        const notesEl  = document.getElementById('notes_field');
        let notes = notesEl.value;
        if (invShare > 0) {
            notes = notes.replace(/investor_share:\d+(\.\d+)?/g, '');
            notes += '\ninvestor_share:' + invShare;
        }
        if (invName) {
            notes = notes.replace(/investor_name:[^\n]*/g, '');
            notes += '\ninvestor_name:' + invName;
        }
        notesEl.value = notes.trim();
    });
})();
</script>
