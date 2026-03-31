<?php $base = rtrim(BASE_URL, '/'); $r = $record ?? []; ?>
<div class="container py-4" style="max-width:860px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Edit Investment</h2>
            <p class="text-muted small mb-0"><?= htmlspecialchars($r['title'] ?? '') ?></p>
        </div>
        <a href="<?= $base ?>/investments" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST" action="<?= $base ?>/investments/update">
                        <input type="hidden" name="id" value="<?= (int)($r['id']??0) ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Farm</label>
                                <select name="farm_id" class="form-select" required>
                                    <?php foreach ($farms ?? [] as $f): ?>
                                        <option value="<?= (int)$f['id'] ?>" <?= (int)($r['farm_id']??0)===(int)$f['id']?'selected':'' ?>><?= htmlspecialchars($f['farm_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Investment Date</label>
                                <input type="date" name="investment_date" class="form-control" value="<?= htmlspecialchars($r['investment_date']??'') ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Title</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($r['title']??'') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Investment Type</label>
                                <select name="investment_type" class="form-select" required>
                                    <?php foreach (['eggs'=>'🥚 Egg Production','livestock'=>'🐔 Live Birds / Livestock','equipment'=>'🔧 Equipment','infrastructure'=>'🏗️ Infrastructure','land'=>'🌍 Land','technology'=>'💻 Technology','other'=>'📦 Other'] as $v=>$l): ?>
                                        <option value="<?= $v ?>" <?= ($r['investment_type']??'')===$v?'selected':'' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Amount (GHS)</label>
                                <input type="number" name="amount" id="amount" class="form-control" step="0.01" value="<?= htmlspecialchars($r['amount']??'') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Expected Return (GHS)</label>
                                <input type="number" name="expected_return" id="expected_return" class="form-control" step="0.01" value="<?= htmlspecialchars($r['expected_return']??'') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Useful Life (Years)</label>
                                <input type="number" name="useful_life_years" id="useful_life_years" class="form-control" value="<?= htmlspecialchars($r['useful_life_years']??'') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <?php foreach (['active'=>'Active','disposed'=>'Disposed','depreciated'=>'Fully Depreciated'] as $v=>$l): ?>
                                        <option value="<?= $v ?>" <?= ($r['status']??'')===$v?'selected':'' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Reference No.</label>
                                <input type="text" name="reference_no" class="form-control" value="<?= htmlspecialchars($r['reference_no']??'') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($r['description']??'') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($r['notes']??'') ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-dark w-100">Update Investment</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Live Calculator (same as create) -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top:80px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-calculator me-2 text-primary"></i>Investment Calculator</h6>
                    <div id="calcEmpty" class="text-center text-muted py-4 d-none">
                        <i class="bi bi-graph-up fs-2 d-block mb-2 opacity-25"></i>
                        <p class="small">Enter amount, expected return, and useful life.</p>
                    </div>
                    <div id="calcResults">
                        <div class="border rounded-4 p-3 mb-3 bg-light">
                            <div class="fw-semibold small text-warning mb-2">Depreciation</div>
                            <div class="row g-2 text-center">
                                <div class="col-4"><div class="small text-muted">Weekly</div><div class="fw-bold text-warning" id="c_depr_week">—</div></div>
                                <div class="col-4"><div class="small text-muted">Monthly</div><div class="fw-bold text-warning" id="c_depr_month">—</div></div>
                                <div class="col-4"><div class="small text-muted">Yearly</div><div class="fw-bold text-warning" id="c_depr_year">—</div></div>
                            </div>
                        </div>
                        <div class="border rounded-4 p-3 mb-3 bg-light">
                            <div class="fw-semibold small text-success mb-2">Return Projections</div>
                            <div class="row g-2 text-center">
                                <div class="col-4"><div class="small text-muted">Weekly</div><div class="fw-bold text-success" id="c_ret_week">—</div></div>
                                <div class="col-4"><div class="small text-muted">Monthly</div><div class="fw-bold text-success" id="c_ret_month">—</div></div>
                                <div class="col-4"><div class="small text-muted">Yearly</div><div class="fw-bold text-success" id="c_ret_year">—</div></div>
                            </div>
                        </div>
                        <div class="border rounded-4 p-3 mb-3 bg-light">
                            <div class="fw-semibold small mb-2">Summary</div>
                            <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Net Gain</span><span class="fw-bold" id="c_net_gain">—</span></div>
                            <div class="d-flex justify-content-between small mb-1"><span class="text-muted">ROI</span><span class="fw-bold" id="c_roi">—</span></div>
                            <div class="d-flex justify-content-between small"><span class="text-muted">Payback Period</span><span class="fw-bold" id="c_payback">—</span></div>
                        </div>
                        <div id="yearTableWrap" class="d-none">
                            <div class="fw-semibold small mb-2">Year-by-Year</div>
                            <div class="table-responsive" style="max-height:200px;overflow-y:auto;">
                                <table class="table table-sm mb-0"><thead class="table-light sticky-top"><tr><th>Year</th><th>Book Value</th><th>Return</th><th>Net</th></tr></thead><tbody id="yearTableBody"></tbody></table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const amtEl=document.getElementById('amount'), retEl=document.getElementById('expected_return'), lifeEl=document.getElementById('useful_life_years');
    function fmt(n){ return 'GHS '+parseFloat(n).toFixed(2); }
    function calc(){
        const amount=parseFloat(amtEl.value)||0, ret=parseFloat(retEl.value)||0, life=parseInt(lifeEl.value)||0;
        const annDepr=life>0?amount/life:0, monDepr=annDepr/12, wkDepr=annDepr/52;
        document.getElementById('c_depr_week').textContent=life>0?fmt(wkDepr):'N/A';
        document.getElementById('c_depr_month').textContent=life>0?fmt(monDepr):'N/A';
        document.getElementById('c_depr_year').textContent=life>0?fmt(annDepr):'N/A';
        const netGain=ret-amount, annRet=(life>0&&netGain>0)?netGain/life:0, monRet=annRet/12, wkRet=annRet/52;
        document.getElementById('c_ret_week').textContent=ret>0?fmt(wkRet):'N/A';
        document.getElementById('c_ret_month').textContent=ret>0?fmt(monRet):'N/A';
        document.getElementById('c_ret_year').textContent=ret>0?fmt(annRet):'N/A';
        const roi=amount>0?(netGain/amount)*100:0, payYears=annRet>0?amount/annRet:0, payMonths=monRet>0?amount/monRet:0;
        const ngEl=document.getElementById('c_net_gain'); ngEl.textContent=ret>0?fmt(netGain):'N/A'; ngEl.className='fw-bold '+(netGain>=0?'text-success':'text-danger');
        document.getElementById('c_roi').textContent=ret>0?roi.toFixed(1)+'%':'N/A';
        document.getElementById('c_payback').textContent=annRet>0?payYears.toFixed(1)+' yrs ('+payMonths.toFixed(0)+' months)':'N/A';
        if(life>0&&amount>0){
            document.getElementById('yearTableWrap').classList.remove('d-none');
            const tbody=document.getElementById('yearTableBody'); tbody.innerHTML='';
            for(let y=1;y<=life;y++){
                const bv=Math.max(0,amount-annDepr*y), re=Math.min(netGain>0?netGain:0,annRet*y), net=re-(annDepr*y);
                const tr=document.createElement('tr');
                tr.innerHTML=`<td>Yr ${y}</td><td>GHS ${bv.toFixed(2)}</td><td class="text-success">GHS ${re.toFixed(2)}</td><td class="${net>=0?'text-success':'text-danger'}">GHS ${net.toFixed(2)}</td>`;
                tbody.appendChild(tr);
            }
        } else { document.getElementById('yearTableWrap').classList.add('d-none'); }
    }
    [amtEl,retEl,lifeEl].forEach(el=>el.addEventListener('input',calc));
    calc(); // run on load with existing values
})();
</script>
