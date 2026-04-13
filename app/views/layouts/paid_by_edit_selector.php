<?php
/**
 * Paid By Selector for EDIT forms
 * Shows current paid_by value pre-selected
 */
$owners = $owners ?? [];
$currentPaidBy = $currentPaidBy ?? null; // current paid_by value from record
$ownerColors = ['#3b82f6','#f59e0b','#10b981','#ef4444','#8b5cf6'];
?>
<div class="col-12">
    <label class="form-label fw-semibold">Paid By</label>
    <div class="d-flex gap-2 flex-wrap align-items-center">

        <!-- Business Funds -->
        <label style="cursor:pointer;">
            <input type="radio" name="paid_by" value="" class="d-none paid-by-radio-edit"
                   <?= ($currentPaidBy === null || $currentPaidBy === '') ? 'checked' : '' ?>>
            <div class="paid-by-btn-edit d-flex align-items-center gap-2 px-3 py-2 rounded-pill"
                 style="border:2px solid <?= ($currentPaidBy === null || $currentPaidBy === '') ? '#10b981' : '#e2e8f0' ?>;
                        background:<?= ($currentPaidBy === null || $currentPaidBy === '') ? '#10b98115' : '#fff' ?>;
                        transition:all .15s;">
                <i class="bi bi-building" style="color:#10b981;font-size:16px;"></i>
                <span class="fw-semibold small" style="color:<?= ($currentPaidBy === null || $currentPaidBy === '') ? '#10b981' : '#374151' ?>;">Business Funds</span>
            </div>
        </label>

        <?php foreach ($owners as $i => $owner):
            $color = $ownerColors[$i % count($ownerColors)];
            $isSelected = (string)$currentPaidBy === (string)$owner['id'];
        ?>
        <label style="cursor:pointer;">
            <input type="radio" name="paid_by" value="<?= (int)$owner['id'] ?>"
                   class="d-none paid-by-radio-edit" <?= $isSelected ? 'checked' : '' ?>>
            <div class="paid-by-btn-edit d-flex align-items-center gap-2 px-3 py-2 rounded-pill"
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
    <div class="form-text">Who paid for this? Select "Business Funds" if paid from business capital.</div>
</div>

<script>
(function(){
    const radios = document.querySelectorAll('.paid-by-radio-edit');
    const colors = <?= json_encode($ownerColors) ?>;
    radios.forEach((radio, idx) => {
        radio.addEventListener('change', () => {
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
})();
</script>
