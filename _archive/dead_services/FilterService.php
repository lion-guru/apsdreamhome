<?php

namespace App\Services;

/**
 * FilterService
 * 
 * Provides common methods to apply filters to database queries
 * Eliminates repetitive code for building WHERE clauses and filters
 */
class FilterService
{
    /**
     * Apply search filters to a query
     * 
     * @param array $where Existing WHERE conditions
     * @param array $params Existing parameters
     * @param string $searchTerm Search term
     * @param array $columns Columns to search in
     * @param string $paramName Parameter name for search term
     * @return array Updated where and params arrays
     */
    public static function applySearchFilters($where, $params, $searchTerm, $columns, $paramName = 'search')
    {
        if (!empty($searchTerm)) {
            $searchConditions = [];
            foreach ($columns as $column) {
                $searchConditions[] = "$column LIKE :{$paramName}";
            }
            $where[] = '(' . implode(' OR ', $searchConditions) . ')';
            $params[$paramName] = '%' . $searchTerm . '%';
        }
        
        return ['where' => $where, 'params' => $params];
    }
    
    /**
     * Apply date range filters
     * 
     * @param array $where Existing WHERE conditions
     * @param array $params Existing parameters
     * @param string $dateFrom Start date
     * @param string $dateTo End date
     * @param string $column Date column name (default: created_at)
     * @return array Updated where and params arrays
     */
    public static function applyDateFilters($where, $params, $dateFrom, $dateTo, $column = 'created_at')
    {
        if (!empty($dateFrom)) {
            $where[] = "$column >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        
        if (!empty($dateTo)) {
            $where[] = "$column <= :date_to";
            $params['date_to'] = $dateTo;
        }
        
        return ['where' => $where, 'params' => $params];
    }
    
    /**
     * Apply status filter
     * 
     * @param array $where Existing WHERE conditions
     * @param array $params Existing parameters
     * @param string $status Status value
     * @param string $column Status column name (default: status)
     * @return array Updated where and params arrays
     */
    public static function applyStatusFilter($where, $params, $status, $column = 'status')
    {
        if (!empty($status) && $status !== 'all') {
            $where[] = "$column = :status";
            $params['status'] = $status;
        }
        
        return ['where' => $where, 'params' => $params];
    }
    
    /**
     * Apply multiple status filters (IN clause)
     * 
     * @param array $where Existing WHERE conditions
     * @param array $params Existing parameters
     * @param array $statuses Status values
     * @param string $column Status column name (default: status)
     * @return array Updated where and params arrays
     */
    public static function applyStatusInFilter($where, $params, $statuses, $column = 'status')
    {
        if (!empty($statuses) && is_array($statuses)) {
            $placeholders = implode(',', array_fill(0, count($statuses), '?'));
            $where[] = "$column IN ($placeholders)";
            $params = array_merge($params, $statuses);
        }
        
        return ['where' => $where, 'params' => $params];
    }
    
    /**
     * Apply role filter
     * 
     * @param array $where Existing WHERE conditions
     * @param array $params Existing parameters
     * @param string|array $roles Role(s) to filter by
     * @param string $column Role column name (default: role)
     * @return array Updated where and params arrays
     */
    public static function applyRoleFilter($where, $params, $roles, $column = 'role')
    {
        if (!empty($roles)) {
            $roleArray = is_array($roles) ? $roles : [$roles];
            $placeholders = implode(',', array_fill(0, count($roleArray), '?'));
            $where[] = "$column IN ($placeholders)";
            $params = array_merge($params, $roleArray);
        }
        
        return ['where' => $where, 'params' => $params];
    }
    
    /**
     * Apply active status filter (is_active = 1)
     * 
     * @param array $where Existing WHERE conditions
     * @param bool $isActive Whether to filter by active status
     * @param string $column Active column name (default: is_active)
     * @return array Updated where array
     */
    public static function applyActiveFilter($where, $isActive = true, $column = 'is_active')
    {
        if ($isActive) {
            $where[] = "$column = 1";
        }
        
        return ['where' => $where];
    }
    
    /**
     * Apply ID filter
     * 
     * @param array $where Existing WHERE conditions
     * @param array $params Existing parameters
     * @param mixed $id ID value
     * @param string $column ID column name (default: id)
     * @return array Updated where and params arrays
     */
    public static function applyIdFilter($where, $params, $id, $column = 'id')
    {
        if (!empty($id)) {
            $where[] = "$column = :id";
            $params['id'] = $id;
        }
        
        return ['where' => $where, 'params' => $params];
    }
    
    /**
     * Apply foreign key filter (e.g., user_id, property_id)
     * 
     * @param array $where Existing WHERE conditions
     * @param array $params Existing parameters
     * @param mixed $value Foreign key value
     * @param string $column Foreign key column name
     * @param string $paramName Parameter name (default: column name)
     * @return array Updated where and params arrays
     */
    public static function applyForeignKeyFilter($where, $params, $value, $column, $paramName = null)
    {
        if (!empty($value)) {
            $paramName = $paramName ?: $column;
            $where[] = "$column = :$paramName";
            $params[$paramName] = $value;
        }
        
        return ['where' => $where, 'params' => $params];
    }
    
    /**
     * Apply numeric range filter (e.g., price, area)
     * 
     * @param array $where Existing WHERE conditions
     * @param array $params Existing parameters
     * @param float $min Minimum value
     * @param float $max Maximum value
     * @param string $column Column name
     * @return array Updated where and params arrays
     */
    public static function applyRangeFilter($where, $params, $min, $max, $column)
    {
        if (!empty($min)) {
            $where[] = "$column >= :{$column}_min";
            $params["{$column}_min"] = $min;
        }
        
        if (!empty($max)) {
            $where[] = "$column <= :{$column}_max";
            $params["{$column}_max"] = $max;
        }
        
        return ['where' => $where, 'params' => $params];
    }
    
    /**
     * Apply price range filter
     * 
     * @param array $where Existing WHERE conditions
     * @param array $params Existing parameters
     * @param float $minPrice Minimum price
     * @param float $maxPrice Maximum price
     * @param string $column Price column name (default: price)
     * @return array Updated where and params arrays
     */
    public static function applyPriceRangeFilter($where, $params, $minPrice, $maxPrice, $column = 'price')
    {
        return self::applyRangeFilter($where, $params, $minPrice, $maxPrice, $column);
    }
    
    /**
     * Apply featured filter
     * 
     * @param array $where Existing WHERE conditions
     * @param array $params Existing parameters
     * @param mixed $featured Featured value (0, 1, or null)
     * @param string $column Featured column name (default: featured)
     * @return array Updated where and params arrays
     */
    public static function applyFeaturedFilter($where, $params, $featured, $column = 'featured')
    {
        if (isset($featured) && $featured !== '') {
            $where[] = "$column = :featured";
            $params['featured'] = (int)$featured;
        }
        
        return ['where' => $where, 'params' => $params];
    }
    
    /**
     * Build WHERE clause from conditions array
     * 
     * @param array $where WHERE conditions
     * @return string WHERE clause string
     */
    public static function buildWhereClause($where)
    {
        if (empty($where)) {
            return '';
        }
        
        return 'WHERE ' . implode(' AND ', $where);
    }
    
    /**
     * Build ORDER BY clause
     * 
     * @param array $filters Filter array with sort and order keys
     * @param array $allowedSorts Allowed sort columns
     * @param string $defaultSort Default sort column
     * @param string $defaultOrder Default sort order
     * @param string $tableAlias Table alias (default: empty)
     * @return string ORDER BY clause
     */
    public static function buildOrderByClause($filters, $allowedSorts, $defaultSort = 'created_at', $defaultOrder = 'DESC', $tableAlias = '')
    {
        $sort = in_array($filters['sort'] ?? '', $allowedSorts) ? $filters['sort'] : $defaultSort;
        $order = strtoupper($filters['order'] ?? '') === 'ASC' ? 'ASC' : 'DESC';
        
        $column = $tableAlias ? "{$tableAlias}.{$sort}" : $sort;
        
        return "ORDER BY {$column} {$order}";
    }
    
    /**
     * Apply all common filters (search, date, status)
     * 
     * @param array $filters Filter array
     * @param array $searchColumns Columns to search in
     * @param string $dateColumn Date column name (default: created_at)
     * @param string $statusColumn Status column name (default: status)
     * @return array Array with where conditions and params
     */
    public static function applyCommonFilters($filters, $searchColumns = [], $dateColumn = 'created_at', $statusColumn = 'status')
    {
        $where = [];
        $params = [];
        
        // Apply search filter
        if (!empty($filters['search']) && !empty($searchColumns)) {
            $result = self::applySearchFilters($where, $params, $filters['search'], $searchColumns);
            $where = $result['where'];
            $params = $result['params'];
        }
        
        // Apply date filters
        $result = self::applyDateFilters($where, $params, $filters['date_from'] ?? '', $filters['date_to'] ?? '', $dateColumn);
        $where = $result['where'];
        $params = $result['params'];
        
        // Apply status filter
        $result = self::applyStatusFilter($where, $params, $filters['status'] ?? '', $statusColumn);
        $where = $result['where'];
        $params = $result['params'];
        
        return ['where' => $where, 'params' => $params];
    }
    
    /**
     * Sanitize and prepare filter values
     * 
     * @param array $filters Raw filter array
     * @param array $defaults Default values
     * @return array Sanitized filter array
     */
    public static function sanitizeFilters($filters, $defaults = [])
    {
        $sanitized = [];
        
        foreach ($filters as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim($value);
            } elseif (is_numeric($value)) {
                $sanitized[$key] = $value;
            } elseif (is_array($value)) {
                $sanitized[$key] = $value;
            }
        }
        
        // Apply defaults
        foreach ($defaults as $key => $value) {
            if (!isset($sanitized[$key])) {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
}
