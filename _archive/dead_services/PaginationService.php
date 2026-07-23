<?php

namespace App\Services;

/**
 * PaginationService
 * 
 * Provides common methods to handle pagination logic
 * Eliminates repetitive code for calculating pagination values
 */
class PaginationService
{
    /**
     * Get pagination data array
     * 
     * @param int $total Total number of records
     * @param int $page Current page number (default: 1)
     * @param int $perPage Records per page (default: 10)
     * @return array Pagination data
     */
    public static function getPagination($total, $page = 1, $perPage = 10)
    {
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $totalPages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        return [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'offset' => $offset,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1,
            'next_page' => $page < $totalPages ? $page + 1 : null,
            'prev_page' => $page > 1 ? $page - 1 : null,
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $total)
        ];
    }
    
    /**
     * Get offset for pagination
     * 
     * @param int $page Current page number
     * @param int $perPage Records per page
     * @return int Offset value
     */
    public static function getOffset($page = 1, $perPage = 10)
    {
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        
        return ($page - 1) * $perPage;
    }
    
    /**
     * Get total number of pages
     * 
     * @param int $total Total number of records
     * @param int $perPage Records per page
     * @return int Total pages
     */
    public static function getTotalPages($total, $perPage = 10)
    {
        $perPage = max(1, (int)$perPage);
        
        return (int)ceil($total / $perPage);
    }
    
    /**
     * Get LIMIT clause for SQL query
     * 
     * @param int $page Current page number
     * @param int $perPage Records per page
     * @return string LIMIT clause
     */
    public static function getLimitClause($page = 1, $perPage = 10)
    {
        $offset = self::getOffset($page, $perPage);
        
        return "LIMIT {$perPage} OFFSET {$offset}";
    }
    
    /**
     * Get pagination parameters from request
     * 
     * @param array $request Request array (e.g., $_GET)
     * @param int $defaultPage Default page number (default: 1)
     * @param int $defaultPerPage Default records per page (default: 10)
     * @return array Array with page and per_page
     */
    public static function getParamsFromRequest($request, $defaultPage = 1, $defaultPerPage = 10)
    {
        $page = isset($request['page']) ? (int)$request['page'] : $defaultPage;
        $perPage = isset($request['per_page']) ? (int)$request['per_page'] : $defaultPerPage;
        
        // Validate and sanitize
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage)); // Max 100 per page
        
        return [
            'page' => $page,
            'per_page' => $perPage
        ];
    }
    
    /**
     * Build pagination URL parameters
     * 
     * @param array $filters Current filters
     * @param int $page Target page
     * @param int $perPage Records per page
     * @return string URL parameter string
     */
    public static function buildUrlParams($filters, $page, $perPage = null)
    {
        $params = array_merge($filters, ['page' => $page]);
        
        if ($perPage !== null) {
            $params['per_page'] = $perPage;
        }
        
        return http_build_query($params);
    }
    
    /**
     * Get page numbers for pagination display (e.g., [1, 2, 3, ..., 10])
     * 
     * @param int $currentPage Current page
     * @param int $totalPages Total pages
     * @param int $surround Number of pages to show around current page (default: 2)
     * @return array Array of page numbers to display
     */
    public static function getDisplayPages($currentPage, $totalPages, $surround = 2)
    {
        $pages = [];
        
        if ($totalPages <= 7) {
            // Show all pages if total is small
            for ($i = 1; $i <= $totalPages; $i++) {
                $pages[] = $i;
            }
        } else {
            // Show first page
            $pages[] = 1;
            
            // Show ellipsis if needed
            if ($currentPage - $surround > 2) {
                $pages[] = '...';
            }
            
            // Show pages around current
            $start = max(2, $currentPage - $surround);
            $end = min($totalPages - 1, $currentPage + $surround);
            
            for ($i = $start; $i <= $end; $i++) {
                $pages[] = $i;
            }
            
            // Show ellipsis if needed
            if ($currentPage + $surround < $totalPages - 1) {
                $pages[] = '...';
            }
            
            // Show last page
            $pages[] = $totalPages;
        }
        
        return $pages;
    }
    
    /**
     * Slice array for pagination
     * 
     * @param array $data Array to paginate
     * @param int $page Current page number
     * @param int $perPage Records per page
     * @return array Sliced array
     */
    public static function sliceArray($data, $page = 1, $perPage = 10)
    {
        $offset = self::getOffset($page, $perPage);
        
        return array_slice($data, $offset, $perPage);
    }
    
    /**
     * Get pagination metadata for API responses
     * 
     * @param int $total Total records
     * @param int $page Current page
     * @param int $perPage Records per page
     * @return array API pagination metadata
     */
    public static function getApiMetadata($total, $page = 1, $perPage = 10)
    {
        $pagination = self::getPagination($total, $page, $perPage);
        
        return [
            'total' => $pagination['total'],
            'count' => $pagination['to'] - $pagination['from'] + 1,
            'per_page' => $pagination['per_page'],
            'current_page' => $pagination['current_page'],
            'total_pages' => $pagination['total_pages'],
            'links' => [
                'first' => '?page=1',
                'last' => '?page=' . $pagination['total_pages'],
                'prev' => $pagination['prev_page'] ? '?page=' . $pagination['prev_page'] : null,
                'next' => $pagination['next_page'] ? '?page=' . $pagination['next_page'] : null
            ]
        ];
    }
}
