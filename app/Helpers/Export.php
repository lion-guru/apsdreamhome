<?php
/**
 * Data Export Helper
 * 
 * Export data to CSV and Excel formats
 * Usage: Export::csv($data, $filename) or Export::excel($data, $filename)
 */

namespace App\Helpers;

class Export
{
    /**
     * Export data to CSV
     */
    public static function csv(array $data, string $filename = 'export'): void
    {
        if (empty($data)) {
            return;
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Headers
        fputcsv($output, array_keys($data[0]));

        // Data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    /**
     * Export data to Excel (HTML table format)
     */
    public static function excel(array $data, string $filename = 'export'): void
    {
        if (empty($data)) {
            return;
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="UTF-8"></head>';
        echo '<body><table border="1">';

        // Headers
        echo '<tr>';
        foreach (array_keys($data[0]) as $header) {
            echo '<th class="style-67848">' . htmlspecialchars($header) . '</th>';
        }
        echo '</tr>';

        // Data
        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . htmlspecialchars((string) $cell) . '</td>';
            }
            echo '</tr>';
        }

        echo '</table></body></html>';
        exit;
    }

    /**
     * Export leads
     */
    public static function leads(array $leads): void
    {
        $data = [];
        foreach ($leads as $lead) {
            $data[] = [
                'ID' => $lead['id'],
                'Name' => $lead['name'],
                'Email' => $lead['email'],
                'Phone' => $lead['phone'],
                'Status' => $lead['status'],
                'Source' => $lead['source'],
                'Score' => $lead['lead_score'] ?? 0,
                'Created' => $lead['created_at'],
            ];
        }
        self::csv($data, 'leads');
    }

    /**
     * Export bookings
     */
    public static function bookings(array $bookings): void
    {
        $data = [];
        foreach ($bookings as $booking) {
            $data[] = [
                'Booking ID' => $booking['booking_number'] ?? $booking['id'],
                'Customer' => $booking['customer_name'] ?? '',
                'Plot' => $booking['plot_number'] ?? '',
                'Amount' => $booking['total_amount'] ?? 0,
                'Status' => $booking['status'],
                'Date' => $booking['created_at'],
            ];
        }
        self::csv($data, 'bookings');
    }

    /**
     * Export commissions
     */
    public static function commissions(array $commissions): void
    {
        $data = [];
        foreach ($commissions as $comm) {
            $data[] = [
                'ID' => $comm['id'],
                'User' => $comm['user_name'] ?? '',
                'Type' => $comm['commission_type'],
                'Amount' => $comm['amount'],
                'Status' => $comm['status'],
                'Date' => $comm['created_at'],
            ];
        }
        self::csv($data, 'commissions');
    }
}
