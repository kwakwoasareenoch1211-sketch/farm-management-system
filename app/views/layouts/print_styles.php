<style id="print-styles">
/* Scrollable table wrapper */
.table-responsive {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f8fafc;
}
.table-responsive::-webkit-scrollbar { height: 6px; width: 6px; }
.table-responsive::-webkit-scrollbar-track { background: #f8fafc; }
.table-responsive::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

@media print {
    /* Hide navigation and UI chrome */
    .topbar, .sidebar, .app-shell > .sidebar,
    .btn, .action-btns, .d-print-none,
    #reportToolbar, nav, form,
    .source-breakdown, .alert { display: none !important; }

    /* Reset layout */
    body { background: white !important; font-size: 10pt; color: #000; }
    .main-content { margin: 0 !important; padding: 8px !important; width: 100% !important; }
    .app-shell { display: block !important; }

    /* Show print header */
    .print-header { display: block !important; }

    /* Remove scroll constraints - show ALL rows */
    .table-responsive {
        max-height: none !important;
        overflow: visible !important;
        border: none !important;
    }

    /* Full-width clean tables */
    table { width: 100% !important; border-collapse: collapse !important; font-size: 9pt; }
    th { background: #e8e8e8 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; font-weight: bold; }
    th, td { border: 1px solid #bbb !important; padding: 4px 7px !important; }
    tr { page-break-inside: avoid; }
    thead { display: table-header-group; } /* Repeat header on each page */

    /* Cards become plain boxes */
    .finance-card, .work-card, .inv-card, .cap-card, .pou-card, .card, .adv-card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        margin-bottom: 12px !important;
    }

    /* Badges as plain text */
    .badge {
        border: 1px solid #999 !important;
        background: white !important;
        color: black !important;
        padding: 1px 4px !important;
    }

    /* Page settings */
    @page { margin: 1.2cm; size: A4 landscape; }

    /* Totals row emphasis */
    tfoot td, tr.total-row td { font-weight: bold; background: #f0f0f0 !important; }
}

/* Print header - hidden on screen */
.print-header {
    display: none;
    margin-bottom: 14px;
    padding-bottom: 8px;
    border-bottom: 2px solid #333;
}
.print-header h3 { margin: 0 0 3px; font-size: 15pt; }
.print-header p  { margin: 0; font-size: 9pt; color: #555; }
</style>
