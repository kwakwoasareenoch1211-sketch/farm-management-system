<?php $reportRows = $reportRows ?? []; ?>

<div class="container py-4">
    <h2 class="fw-bold mb-1">Batch Performance Report</h2>
    <p class="text-muted mb-4">Profitability and performance summary for all batches.</p>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Batch Code</th>
                            <th>Name</th>
                            <th>Purpose</th>
                            <th>Subtype</th>
                            <th>Breed</th>
                            <th>Start Date</th>
                            <th>Initial Qty</th>
                            <th>Current Qty</th>
                            <th>Feed (kg)</th>
                            <th>Mortality</th>
                            <th>Expenses</th>
                            <th>Sales</th>
                            <th>Gross Profit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reportRows)): ?>
                            <?php foreach ($reportRows as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['batch_code'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['batch_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['production_purpose'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['bird_subtype'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['breed'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['start_date'] ?? '') ?></td>
                                    <td><?= number_format((float)($row['initial_quantity'] ?? 0)) ?></td>
                                    <td><?= number_format((float)($row['current_quantity'] ?? 0)) ?></td>
                                    <td><?= number_format((float)($row['total_feed_kg'] ?? 0), 2) ?></td>
                                    <td><?= number_format((float)($row['total_mortality'] ?? 0), 2) ?></td>
                                    <td>GHS <?= number_format((float)($row['total_expenses'] ?? 0), 2) ?></td>
                                    <td>GHS <?= number_format((float)($row['total_sales'] ?? 0), 2) ?></td>
                                    <td>GHS <?= number_format((float)($row['gross_profit'] ?? 0), 2) ?></td>
                                    <td><?= htmlspecialchars($row['status'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="14" class="text-center text-muted py-4">
                                    No batch performance data available.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>