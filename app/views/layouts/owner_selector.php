<?php
/**
 * Reusable Owner Selector Partial
 * Usage: include with $owners array and optional $selectedOwnerId
 */
$owners = $owners ?? [];
$selectedOwnerId = $selectedOwnerId ?? null;
$ownerColors = ['#3b82f6','#f59e0b','#10b981','#ef4444','#8b5cf6'];
?>
<div class="col-12">
    <label class="form-label fw-semibold">Owner <span class="text-danger">*</span></label>
    <div class="d-flex gap-3 flex-wrap" id="ownerSelector">
        <?php foreach ($owners as $i => $owner):
            $color = $ownerColors[$i % count($ownerColors)];
            $isSelected = $selectedOwnerId == $owner['id'];
        ?>
        <label class="owner-option" style="cursor:pointer;">
            <input type="radio" name="owner_id" value="<?= (int)$owner['id'] ?>"
                   class="d-none owner-radio"
                   <?= $isSelected ? 'checked' : '' ?> required>
            <div class="owner-btn d-flex align-items-center gap-2 px-4 py-2 rounded-pill border-2"
                 style="border:2px solid <?= $isSelected ? $color : '#e2e8f0' ?>;
                        background:<?= $isSelected ? $color.'15' : '#fff' ?>;
                        transition:all .2s;">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                     style="width:32px;height:32px;background:<?= $color ?>;font-size:13px;flex-shrink:0;">
                    <?= strtoupper(substr($owner['full_name'], 0, 1)) ?>
                </div>
                <span class="fw-semibold" style="color:<?= $isSelected ? $color : '#374151' ?>;">
                    <?= htmlspecialchars($owner['full_name']) ?>
                </span>
            </div>
        </label>
        <?php endforeach; ?>
    </div>
    <div class="form-text">Select which owner this record belongs to</div>
</div>

<script>
(function(){
    const radios = document.querySelectorAll('.owner-radio');
    const colors = <?= json_encode($ownerColors) ?>;
    radios.forEach((radio, idx) => {
        radio.addEventListener('change', () => {
            radios.forEach((r, i) => {
                const btn = r.nextElementSibling;
                const c = colors[i % colors.length];
                if (r.checked) {
                    btn.style.borderColor = c;
                    btn.style.background = c + '15';
                    btn.querySelector('span').style.color = c;
                } else {
                    btn.style.borderColor = '#e2e8f0';
                    btn.style.background = '#fff';
                    btn.querySelector('span').style.color = '#374151';
                }
            });
        });
    });
})();
</script>
