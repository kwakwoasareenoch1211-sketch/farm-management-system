<?php
/**
 * Paid By Selector
 * Tracks who funded this expense:
 * - Business Funds (from business capital)
 * - Owner personally (creates a liability - business owes them back)
 *
 * Usage: include with $owners array and optional $selectedPaidBy (user_id or null)
 */
$owners = $owners ?? [];
$selectedPaidBy = $selectedPaidBy ?? null;
$ownerColors = ['#3b82f6','#f59e0b','#10b981','#ef4444','#8b5cf6'];
?>
<div class="col-12">
    <label class="form-label fw-semibold">Paid By <span class="text-danger">*</span></label>
    <div class="d-flex gap-2 flex-wrap align-items-center" id="paidBySelector">

        <!-- Business Funds option -->
        <label style="cursor:pointer;">
            <input type="radio" name="paid_by" value="" class="d-none paid-by-radio" <?= $selectedPaidBy === null || $selectedPaidBy === '' ? 'checked' : '' ?>>
            <div class="paid-by-btn d-flex align-items-center gap-2 px-3 py-2 rounded-pill"
                 style="border:2px solid <?= ($selectedPaidBy === null || $selectedPaidBy === '') ? '#10b981' : '#e2e8f0' ?>;
                        background:<?= ($selectedPaidBy === null || $selectedPaidBy === '') ? '#10b98115' : '#fff' ?>;
                        transition:all .15s;">
                <i class="bi bi-building" style="color:#10b981;font-size:16px;"></i>
                <span class="fw-semibold small" style="color:<?= ($selectedPaidBy === null || $selectedPaidBy === '') ? '#10b981' : '#374151' ?>;">Business Funds</span>
            </div>
        </label>

        <?php foreach ($owners as $i => $owner):
            $color = $ownerColors[$i % count($ownerColors)];
            $isSelected = (string)$selectedPaidBy === (string)$owner['id'];
        ?>
        <label style="cursor:pointer;">
            <input type="radio" name="paid_by" value="<?= (int)$owner['id'] ?>"
                   class="d-none paid-by-radio" <?= $isSelected ? 'checked' : '' ?>>
            <div class="paid-by-btn d-flex align-items-center gap-2 px-3 py-2 rounded-pill"
                 style="border:2px solid <?= $isSelected ? $color : '#e2e8f0' ?>;
                        background:<?= $isSelected ? $color.'15' : '#fff' ?>;
                        transition:all .15s;">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                     style="width:24px;height:24px;background:<?= $color ?>;font-size:11px;flex-shrink:0;">
                    <?= strtoupper(substr($owner['full_name'], 0, 1)) ?>
                </div>
                <span class="fw-semibold small" style="color:<?= $isSelected ? $color : '#374151' ?>;">
                    <?= htmlspecialchars($owner['full_name']) ?> (Personal)
                </span>
            </div>
        </label>
        <?php endforeach; ?>
    </div>
    <div class="form-text" id="paidByNote">
        Select "Business Funds" if paid from business capital. Select an owner if they paid personally — the business will owe them back.
    </div>
</div>

<!-- Alert shown when owner pays personally -->
<div class="col-12 d-none" id="personalFundingAlert">
    <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-0" style="border-radius:10px;">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div class="small">
            <strong>Personal Funding:</strong> This will be recorded as a business liability — the business owes this amount back to the owner.
        </div>
    </div>
</div>

<script>
(function(){
    const radios = document.querySelectorAll('#paidBySelector .paid-by-radio');
    const colors = <?= json_encode($ownerColors) ?>;
    const alert  = document.getElementById('personalFundingAlert');

    radios.forEach((radio, idx) => {
        radio.addEventListener('change', () => {
            const isPersonal = radio.value !== '';
            if (alert) {
                isPersonal ? alert.classList.remove('d-none') : alert.classList.add('d-none');
            }

            radios.forEach((r, i) => {
                const btn = r.nextElementSibling;
                const isBusinessBtn = r.value === '';
                const c = isBusinessBtn ? '#10b981' : colors[(i-1) % colors.length];
                if (r.checked) {
                    btn.style.borderColor = c;
                    btn.style.background  = c + '15';
                    btn.querySelector('span').style.color = c;
                } else {
                    btn.style.borderColor = '#e2e8f0';
                    btn.style.background  = '#fff';
                    btn.querySelector('span').style.color = '#374151';
                }
            });
        });
    });

    // Show alert on load if personal is pre-selected
    const checked = document.querySelector('#paidBySelector .paid-by-radio:checked');
    if (checked && checked.value !== '' && alert) {
        alert.classList.remove('d-none');
    }
})();
</script>
