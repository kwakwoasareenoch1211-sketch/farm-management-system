<?php
/**
 * Export Helper - CSV and Excel export
 */
class ExportHelper
{
    /**
     * Export data as CSV or Excel-compatible file
     * Excel format = CSV with UTF-8 BOM (opens correctly in Excel)
     */
    public static function export(array $rows, array $headers, string $filename, string $format = 'csv'): void
    {
        $isExcel = ($format === 'excel');
        $ext     = $isExcel ? 'xlsx.csv' : 'csv'; // Excel opens CSV with BOM correctly
        $mime    = 'text/csv';

        header('Content-Type: ' . $mime . '; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.' . $ext . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');

        // UTF-8 BOM for Excel compatibility
        if ($isExcel) {
            fputs($out, "\xEF\xBB\xBF");
        }

        // Header row
        fputcsv($out, $headers);

        // Data rows
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }

        fclose($out);
        exit;
    }

    /**
     * Add totals row at the bottom
     */
    public static function addTotalsRow(array &$rows, array $totalsRow): void
    {
        $rows[] = array_fill(0, count($totalsRow), ''); // blank separator
        $rows[] = $totalsRow;
    }
}
