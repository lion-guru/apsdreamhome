<?php
/**
 * Pagination Helper
 * 
 * Simple pagination for list views
 * Usage: Pagination::render($totalItems, $perPage, $currentPage, $baseUrl)
 */

namespace App\Helpers;

class Pagination
{
    /**
     * Paginate data array
     */
    public static function paginate(array $data, int $page = 1, int $perPage = 25): array
    {
        $total = count($data);
        $totalPages = (int) ceil($total / $perPage);
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        return [
            'data' => array_slice($data, $offset, $perPage),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'has_more' => $page < $totalPages,
            'has_prev' => $page > 1,
        ];
    }

    /**
     * Calculate offset for SQL queries
     */
    public static function offset(int $page, int $perPage): int
    {
        return (max(1, $page) - 1) * $perPage;
    }

    /**
     * Get page number from request
     */
    public static function getPage(): int
    {
        return max(1, (int) ($_GET['page'] ?? 1));
    }

    /**
     * Render pagination HTML
     */
    public static function render(int $totalItems, int $perPage, int $currentPage, string $baseUrl): string
    {
        $totalPages = (int) ceil($totalItems / $perPage);
        
        if ($totalPages <= 1) {
            return '';
        }

        $currentPage = max(1, min($currentPage, $totalPages));
        $baseUrl = strpos($baseUrl, '?') !== false ? $baseUrl . '&' : $baseUrl . '?';

        $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

        // Previous button
        if ($currentPage > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=' . ($currentPage - 1) . '">&laquo; Previous</a></li>';
        }

        // Page numbers
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);

        if ($start > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=1">1</a></li>';
            if ($start > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            $active = $i === $currentPage ? ' active' : '';
            $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $baseUrl . 'page=' . $i . '">' . $i . '</a></li>';
        }

        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=' . $totalPages . '">' . $totalPages . '</a></li>';
        }

        // Next button
        if ($currentPage < $totalPages) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=' . ($currentPage + 1) . '">Next &raquo;</a></li>';
        }

        $html .= '</ul>';
        $html .= '<p class="text-center text-muted">Showing ' . ((($currentPage - 1) * $perPage) + 1) . ' - ' . min($currentPage * $perPage, $totalItems) . ' of ' . $totalItems . ' items</p>';
        $html .= '</nav>';

        return $html;
    }

    /**
     * Get pagination data for view
     */
    public static function getData(int $totalItems, int $perPage, int $currentPage): array
    {
        $totalPages = (int) ceil($totalItems / $perPage);
        $currentPage = max(1, min($currentPage, $totalPages));

        return [
            'total_items' => $totalItems,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'has_more' => $currentPage < $totalPages,
            'has_prev' => $currentPage > 1,
            'offset' => ($currentPage - 1) * $perPage,
        ];
    }
}
