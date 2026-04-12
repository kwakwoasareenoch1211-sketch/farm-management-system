<?php
/**
 * Reusable Owner Selector Partial
 * Supports: individual owner OR shared (both owners contribute)
 * Usage: include with $owners array and optional $selectedOwnerId, $isShared
 */
$owners = $owners ?? [];
$selectedOwnerId = $selectedOwnerId ?? null;
$isShared = $isShared ?? false;
$ownerColors = ['#3b82f6','#f59e0b','#10b981','#ef4444','#8b5cf6'];
?>
<div class="col-12">
    <label class="form-label fw-semibold">Owner / Responsibility <span class="text-danger">*</span></label>
    <div class="d-flex gap-2 flex-wrap align-items-center" id="ownerSelectorWrap">

        <?php foreach ($owners as $i => $owner):
            $color = $ownerColors[$i % count($ownerColors)];
            $isSelected = !$isShared && $selectedOwnerId == $owner['id'];
        ?>
        <label class="owner-option" style="cursor:pointer;">
            <input type="radio" name="owner_id" value="<?= (int)$owner['id'] ?>"
                   class="d-none owner-radio"
                   <?= $isSelected ? 'checked' : '' ?>>
            <div class="owner-btn d-flex align-items-center gap-2 px-3 py-2 rounded-pill"
                 style="border:2px solid <?= $isSelected ? $color : '#e2e8f0' ?>;
                        background:<?= $isSelected ? $color.'15' : '#fff' ?>;
                        transition:all .15s;user-select:none;">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                     style="width:28px;height:28px;background:<?= $color ?>;font-size:12px;flex-shrink:0;">
                    <?= strtoupper(substr($owner['full_name'], 0, 1)) ?>
                </div>
                <span class="fw-semibold small" style="color:<?= $isSelected ? $color : '#374151' ?>;">
                    <?= htmlspecialchars($owner['full_name']) ?>
                </span>
            </div>
        </label>
        <?php endforeach; ?>

        <!-- Shared option -->
        <label class="owner-option" style="cursor:pointer;">
            <input type="radio" name="owner_id" value="shared"
                   class="d-none owner-radio"
                   id="sharedRadio"
                   <?= $isShared ? 'checked' : '' ?>>
            <div class="owner-btn d-flex align-items-center gap-2 px-3 py-2 rounded-pill"
                 style="border:2px solid <?= $isShared ? '#7c3aed' : '#e2e8f0' ?>;
                        background:<?= $isShared ? '#7c3aed15' : '#fff' ?>;
                        transition:all .15s;user-select:none;">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                     style="width:28px;height:28px;background:linear-gradient(135deg,#3b82f6,#f59e0b);font-size:11px;flex-shrink:0;">
                    <i class="bi bi-people-fill" style="font-size:12px;"></i>
                </div>
                <span class="fw-semibold small" style="color:<?= $isShared ? '#7c3aed' : '#374151' ?>;">
                    Shared (Both)
                </span>
            </div>
        </label>

        <!-- Hidden field for is_shared -->
        <input type="hidden" name="is_shared" id="isSharedField" value="<?= $isShared ? '1' : '0' ?>">
    </div>
    <div class="form-text">Select owner, or "Shared" if both owners contribute equally</div>
</div>

<script>
(function(){
    const radios = document.querySelectorAll('#ownerSelectorWrap .owner-radio');
    const colors = <?= json_encode($ownerColors) ?>;
    const sharedColor = '#7c3aed';
    const isSharedField = document.getElementById('isSharedField');

    radios.forEach((radio, idx) => {
        radio.addEventListener('change', () => {
            const isSharedSelected = radio.value === 'shared';
            isSharedField.value = isSharedSelected ? '1' : '0';

            radios.forEach((r, i) => {
                const btn = r.nextElementSibling;
                const isSharedBtn = r.value === 'shared';
                const c = isSharedBtn ? sharedColor : colors[i % colors.length];
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

    // Require at least one selection
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const checked = document.querySelector('#ownerSelectorWrap .owner-radio:checked');
            if (!checked) {
                e.preventDefault();
                alert('Please select an owner or "Shared (Both)"');
            }
        });
    }
})();
</script>
