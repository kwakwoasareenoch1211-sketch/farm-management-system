<style id="print-styles">
@media print {
    /* Hide everything except the printable area */
    .topbar, .sidebar, .app-shell > .sidebar,
    .btn, .action-btns, .d-print-none,
    .source-breakdown, .finance-card:not(.print-include),
    nav, .alert, form { display: none !important; }

    /* Reset layout for print */
    body { background: white !important; font-size: 11pt; }
    .main-content { margin: 0 !important; padding: 10px !important; width: 100% !important; }
    .app-shell { display: block !important; }

    /* Show print header */
    .print-header { display: block !important; }

    /* Make tables print-friendly */
    table { width: 100% !important; border-collapse: collapse !important; font-size: 10pt; }
    th, td { border: 1px solid #ccc !important; padding: 5px 8px !important; }
    thead { background: #f0f0f0 !important; -webkit-print-color-adjust: exact; }
    tr { page-break-inside: avoid; }

    /* Show all rows - no truncation */
    .table-responsive { overflow: visible !important; }

    /* Page settings */
    @page { margin: 1.5cm; size: A4 landscape; }

    /* Force show the main content card */
    .finance-card, .work-card, .inv-card, .cap-card, .pou-card,
    .card { display: block !important; box-shadow: none !important; border: 1px solid #ddd !important; }

    /* Hide badges colors for ink saving */
    .badge { border: 1px solid #999 !important; background: white !important; color: black !important; }
}

/* Print header - hidden on screen, shown on print */
.print-header {
    display: none;
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 2px solid #333;
}
.print-header h3 { margin: 0 0 4px; font-size: 16pt; }
.print-header p  { margin: 0; font-size: 10pt; color: #555; }
</style>
