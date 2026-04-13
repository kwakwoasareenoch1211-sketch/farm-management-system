<style id="print-styles">
/* Scrollable table wrapper on screen */
.table-responsive {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f8fafc;
}
.table-responsive::-webkit-scrollbar { height: 6px; width: 6px; }
.table-responsive::-webkit-scrollbar-track { background: #f8fafc; }
.table-responsive::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

@media print {
    /* ===== CRITICAL: Remove ALL overflow/height constraints ===== */
    * {
        overflow: visible !important;
        max-height: none !important;
        height: auto !important;
    }

    /* Hide navigation chrome */
    .topbar, .sidebar, .app-shell > .sidebar,
    .btn, .action-btns, .d-print-none,
    #reportToolbar, nav, form,
    .source-breakdown, .alert,
    .finance-card .d-flex.justify-content-between.align-items-center.mb-3,
    .pou-hero { display: none !important; }

    /* Reset layout - full width, no sidebar */
    html, body {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        font-size: 10pt !important;
        color: #000 !important;
    }

    .app-shell {
        display: block !important;
        height: auto !important;
        overflow: visible !important;
    }

    .main-content {
        margin-left: 0 !important;
        width: 100% !important;
        height: auto !important;
        overflow: visible !important;
        padding: 8px !important;
    }

    /* Show print header */
    .print-header { display: block !important; }

    /* Tables - full width, all rows visible */
    .table-responsive {
        overflow: visible !important;
        max-height: none !important;
        height: auto !important;
        border: none !important;
    }

    table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 9pt !important;
        page-break-inside: auto !important;
    }

    thead {
        display: table-header-group !important; /* Repeat on every page */
    }

    tr {
        page-break-inside: avoid !important;
        page-break-after: auto !important;
    }

    th {
        background: #e8e8e8 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        font-weight: bold !important;
    }

    th, td {
        border: 1px solid #bbb !important;
        padding: 4px 7px !important;
    }

    /* Cards as plain boxes */
    .finance-card, .work-card, .inv-card, .cap-card,
    .pou-card, .card, .adv-card, .container {
        box-shadow: none !important;
        border: none !important;
        margin-bottom: 8px !important;
        padding: 0 !important;
    }

    /* Badges as plain text */
    .badge {
        border: 1px solid #999 !important;
        background: white !important;
        color: black !important;
        padding: 1px 4px !important;
    }

    /* Page settings */
    @page {
        margin: 1.2cm;
        size: A4 landscape;
    }
}

/* Print header - hidden on screen, shown on print */
.print-header {
    display: none;
    margin-bottom: 14px;
    padding-bottom: 8px;
    border-bottom: 2px solid #333;
}
.print-header h3 { margin: 0 0 3px; font-size: 15pt; }
.print-header p  { margin: 0; font-size: 9pt; color: #555; }
</style>
