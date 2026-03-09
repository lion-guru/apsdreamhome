<?php

namespace App\Services\Business;

use App\Models\Associate;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Config;

/**
 * Associate Business Service - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class AssociateService
{
    private $database;
    private $logger;
    private $config;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = new Logger();
        $this->config = Config::getInstance();
    }
    
    /**
     * Get all associates with pagination
     */
    public function getAllAssociates($page = 1, $limit = 20, $filters = [])
    {
        try {
            $offset = ($page - 1) * $limit;
            $where = ["a.status != 'deleted'"];
            $params = [];
            
            // Apply filters
            if (!empty($filters['status'])) {
                $where[] = "a.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['search'])) {
                $where[] = "(a.name LIKE ? OR a.email LIKE ? OR a.phone LIKE ?)";
                $params[] = "%{$filters['search']}%";
                $params[] = "%{$filters['search']}%";
                $params[] = "%{$filters['search']}%";
            }
            
            $whereClause = implode(' AND ', $where);
            
            // Get associates
            $associates = $this->database->select(
                "SELECT a.*, 
                        COUNT(s.id) as sales_count,
                        SUM(s.sale_amount) as total_sales_amount,
                        SUM(s.commission_amount) as total_commission
                 FROM associates a
                 LEFT JOIN sales s ON a.id = s.associate_id AND s.status = 'completed'
                 WHERE $whereClause
                 GROUP BY a.id
                 ORDER BY a.name ASC
                 LIMIT ? OFFSET ?",
                array_merge($params, [$limit, $offset])
            );
            
            // Get total count
            $total = $this->database->selectOne(
                "SELECT COUNT(*) as count FROM associates a WHERE $whereClause",
                $params
            )['count'];
            
            return [
                'data' => $associates,
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => ceil($total / $limit)
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get associates', [
                'error' => $e->getMessage(),
                'page' => $page,
                'limit' => $limit
            ]);
            
            throw new \Exception('Failed to retrieve associates');
        }
    }
    
    /**
     * Create new associate
     */
    public function createAssociate(array $data)
    {
        try {
            // Validate data
            $errors = Associate::validate($data);
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'errors' => $errors
                ];
            }
            
            // Check if email already exists
            if (Associate::findByEmail($data['email'])) {
                return [
                    'success' => false,
                    'message' => 'Email already exists'
                ];
            }
            
            $associate = new Associate();
            $associate->create($data);
            
            $this->logger->info('Associate created', [
                'associate_id' => $associate->id,
                'name' => $associate->name,
                'email' => $associate->email
            ]);
            
            return [
                'success' => true,
                'data' => $associate->toArray(),
                'message' => 'Associate created successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to create associate', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to create associate: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update associate
     */
    public function updateAssociate($id, array $data)
    {
        try {
            $associate = Associate::find($id);
            if (!$associate) {
                return [
                    'success' => false,
                    'message' => 'Associate not found'
                ];
            }
            
            // Validate data
            $errors = Associate::validate($data);
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'errors' => $errors
                ];
            }
            
            $associate->update($data);
            
            $this->logger->info('Associate updated', [
                'associate_id' => $associate->id,
                'updated_fields' => array_keys($data)
            ]);
            
            return [
                'success' => true,
                'data' => $associate->toArray(),
                'message' => 'Associate updated successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to update associate', [
                'error' => $e->getMessage(),
                'associate_id' => $id
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to update associate: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete associate
     */
    public function deleteAssociate($id)
    {
        try {
            $associate = Associate::find($id);
            if (!$associate) {
                return [
                    'success' => false,
                    'message' => 'Associate not found'
                ];
            }
            
            // Check if associate has active sales
            $activeSales = $this->database->selectOne(
                "SELECT COUNT(*) as count FROM sales 
                 WHERE associate_id = ? AND status IN ('pending', 'processing')",
                [$id]
            );
            
            if ($activeSales && $activeSales['count'] > 0) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete associate with active sales'
                ];
            }
            
            $associate->delete();
            
            $this->logger->info('Associate deleted', [
                'associate_id' => $id,
                'name' => $associate->name
            ]);
            
            return [
                'success' => true,
                'message' => 'Associate deleted successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete associate', [
                'error' => $e->getMessage(),
                'associate_id' => $id
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to delete associate: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get associate details
     */
    public function getAssociateDetails($id)
    {
        try {
            $associate = Associate::find($id);
            if (!$associate) {
                return [
                    'success' => false,
                    'message' => 'Associate not found'
                ];
            }
            
            // Get associate's recent sales
            $recentSales = $this->database->select(
                "SELECT s.*, p.name as property_name 
                 FROM sales s 
                 LEFT JOIN properties p ON s.property_id = p.id 
                 WHERE s.associate_id = ? 
                 ORDER BY s.created_at DESC 
                 LIMIT 10",
                [$id]
            );
            
            // Get performance metrics
            $metrics = $this->database->selectOne(
                "SELECT COUNT(s.id) as total_sales,
                        SUM(s.sale_amount) as total_sales_amount,
                        SUM(s.commission_amount) as total_commission,
                        AVG(s.sale_amount) as avg_sale_amount
                 FROM sales s 
                 WHERE s.associate_id = ? AND s.status = 'completed'",
                [$id]
            );
            
            // Get monthly performance
            $monthlyPerformance = $this->database->select(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                        COUNT(*) as sales_count,
                        SUM(sale_amount) as total_sales
                 FROM sales 
                 WHERE associate_id = ? AND status = 'completed'
                 AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY month DESC",
                [$id]
            );
            
            return [
                'success' => true,
                'data' => [
                    'associate' => $associate->toArray(),
                    'recent_sales' => $recentSales,
                    'metrics' => $metrics,
                    'monthly_performance' => $monthlyPerformance
                ]
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get associate details', [
                'error' => $e->getMessage(),
                'associate_id' => $id
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to get associate details'
            ];
        }
    }
    
    /**
     * Get associate performance report
     */
    public function getPerformanceReport($filters = [])
    {
        try {
            $where = ["a.status = 'active'"];
            $params = [];
            
            // Apply date filters
            if (!empty($filters['start_date'])) {
                $where[] = "s.created_at >= ?";
                $params[] = $filters['start_date'];
            }
            
            if (!empty($filters['end_date'])) {
                $where[] = "s.created_at <= ?";
                $params[] = $filters['end_date'];
            }
            
            $whereClause = implode(' AND ', $where);
            
            // Get performance data
            $performance = $this->database->select(
                "SELECT a.id, a.name, a.email,
                        COUNT(s.id) as sales_count,
                        SUM(s.sale_amount) as total_sales_amount,
                        SUM(s.commission_amount) as total_commission,
                        AVG(s.sale_amount) as avg_sale_amount,
                        MAX(s.created_at) as last_sale_date
                 FROM associates a
                 LEFT JOIN sales s ON a.id = s.associate_id AND s.status = 'completed'
                 WHERE $whereClause
                 GROUP BY a.id
                 ORDER BY total_sales_amount DESC
                 LIMIT 50",
                $params
            );
            
            // Get summary statistics
            $summary = $this->database->selectOne(
                "SELECT COUNT(DISTINCT a.id) as total_associates,
                        COUNT(s.id) as total_sales,
                        SUM(s.sale_amount) as total_sales_amount,
                        SUM(s.commission_amount) as total_commission
                 FROM associates a
                 LEFT JOIN sales s ON a.id = s.associate_id AND s.status = 'completed'
                 WHERE $whereClause",
                $params
            );
            
            return [
                'success' => true,
                'data' => [
                    'performance' => $performance,
                    'summary' => $summary
                ]
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get performance report', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to get performance report'
            ];
        }
    }
    
    /**
     * Update commission rate
     */
    public function updateCommissionRate($id, $rate)
    {
        try {
            $associate = Associate::find($id);
            if (!$associate) {
                return [
                    'success' => false,
                    'message' => 'Associate not found'
                ];
            }
            
            if ($rate < 0 || $rate > 100) {
                return [
                    'success' => false,
                    'message' => 'Commission rate must be between 0 and 100'
                ];
            }
            
            $associate->updateCommissionRate($rate);
            
            $this->logger->info('Commission rate updated', [
                'associate_id' => $id,
                'new_rate' => $rate
            ]);
            
            return [
                'success' => true,
                'message' => 'Commission rate updated successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to update commission rate', [
                'error' => $e->getMessage(),
                'associate_id' => $id,
                'rate' => $rate
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to update commission rate'
            ];
        }
    }
    
    /**
     * Get top performers
     */
    public function getTopPerformers($limit = 10, $period = 'month')
    {
        try {
            $dateCondition = '';
            if ($period === 'month') {
                $dateCondition = "AND s.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            } elseif ($period === 'quarter') {
                $dateCondition = "AND s.created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
            } elseif ($period === 'year') {
                $dateCondition = "AND s.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            }
            
            $performers = $this->database->select(
                "SELECT a.id, a.name, a.email, a.commission_rate,
                        COUNT(s.id) as sales_count,
                        SUM(s.sale_amount) as total_sales_amount,
                        SUM(s.commission_amount) as total_commission,
                        AVG(s.sale_amount) as avg_sale_amount
                 FROM associates a
                 LEFT JOIN sales s ON a.id = s.associate_id AND s.status = 'completed' $dateCondition
                 WHERE a.status = 'active'
                 GROUP BY a.id
                 HAVING sales_count > 0
                 ORDER BY total_sales_amount DESC
                 LIMIT ?",
                [$limit]
            );
            
            return [
                'success' => true,
                'data' => $performers
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get top performers', [
                'error' => $e->getMessage(),
                'limit' => $limit,
                'period' => $period
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to get top performers'
            ];
        }
    }
    
    /**
     * Export associates data
     */
    public function exportAssociates($format = 'csv', $filters = [])
    {
        try {
            $where = ["a.status != 'deleted'"];
            $params = [];
            
            // Apply filters
            if (!empty($filters['status'])) {
                $where[] = "a.status = ?";
                $params[] = $filters['status'];
            }
            
            $whereClause = implode(' AND ', $where);
            
            $associates = $this->database->select(
                "SELECT a.*, 
                        COUNT(s.id) as sales_count,
                        SUM(s.sale_amount) as total_sales_amount
                 FROM associates a
                 LEFT JOIN sales s ON a.id = s.associate_id AND s.status = 'completed'
                 WHERE $whereClause
                 GROUP BY a.id
                 ORDER BY a.name ASC",
                $params
            );
            
            if ($format === 'csv') {
                $filename = 'associates_export_' . date('Y-m-d_H-i-s') . '.csv';
                $filepath = STORAGE_PATH . '/exports/' . $filename;
                
                // Ensure export directory exists
                $exportDir = dirname($filepath);
                if (!is_dir($exportDir)) {
                    mkdir($exportDir, 0755, true);
                }
                
                $handle = fopen($filepath, 'w');
                
                // Header
                fputcsv($handle, [
                    'ID', 'Name', 'Email', 'Phone', 'Address', 'Joining Date',
                    'Status', 'Commission Rate', 'Total Sales', 'Sales Count', 'Created At'
                ]);
                
                // Data
                foreach ($associates as $associate) {
                    fputcsv($handle, [
                        $associate['id'],
                        $associate['name'],
                        $associate['email'],
                        $associate['phone'],
                        $associate['address'],
                        $associate['joining_date'],
                        $associate['status'],
                        $associate['commission_rate'],
                        $associate['total_sales_amount'],
                        $associate['sales_count'],
                        $associate['created_at']
                    ]);
                }
                
                fclose($handle);
                
                return [
                    'success' => true,
                    'file' => $filename,
                    'path' => $filepath,
                    'count' => count($associates)
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Unsupported format'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to export associates', [
                'error' => $e->getMessage(),
                'format' => $format
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to export associates'
            ];
        }
    }
}