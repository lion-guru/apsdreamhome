<?php

namespace App\Services;

use App\Core\Database\Database;

use \App\Traits\ServiceTenantTrait;

/**
 * Import/Export Service - Bulk Data Operations
 * CSV/Excel import and export with validation
 */
class ImportExportService
{
    private $database;
    private $importPath;
    private $exportPath;
    private $batchSize = 100;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->importPath = STORAGE_PATH . '/imports/';
        $this->exportPath = STORAGE_PATH . '/exports/';
        
        foreach ([$this->importPath, $this->exportPath] as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
        
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure import/export tables exist
     */
    private function ensureTablesExist(): void
    {
        // import_jobs table removed - queries wrapped in try/catch
    }
    
    /**
     * Import properties from CSV
     */
    public function importProperties(string $filePath, array $options = []): array
    {
        $result = [
            'total' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        try {
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new \Exception("Cannot open file: {$filePath}");
            }
            
            // Read headers
            $headers = fgetcsv($handle);
            if (!$headers) {
                throw new \Exception("Empty file or invalid CSV");
            }
            
            $batch = [];
            $rowNum = 0;
            
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                $result['total']++;
                
                if (count($row) !== count($headers)) {
                    $result['failed']++;
                    $result['errors'][] = "Row {$rowNum}: Column count mismatch";
                    continue;
                }
                
                $data = array_combine($headers, $row);
                
                // Validate and sanitize
                $validated = $this->validatePropertyData($data);
                
                if ($validated['valid']) {
                    $batch[] = $validated['data'];
                    
                    if (count($batch) >= $this->batchSize) {
                        $this->insertPropertyBatch($batch);
                        $result['successful'] += count($batch);
                        $batch = [];
                    }
                } else {
                    $result['failed']++;
                    $result['errors'][] = "Row {$rowNum}: " . $validated['error'];
                }
            }
            
            // Insert remaining
            if (!empty($batch)) {
                $this->insertPropertyBatch($batch);
                $result['successful'] += count($batch);
            }
            
            fclose($handle);
            
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Validate property data
     */
    private function validatePropertyData(array $data): array
    {
        $required = ['title', 'type', 'price', 'location'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['valid' => false, 'error' => "Missing required field: {$field}"];
            }
        }
        
        // Validate type
        $validTypes = ['plot', 'house', 'flat', 'shop', 'farmhouse'];
        if (!in_array(strtolower($data['type']), $validTypes)) {
            return ['valid' => false, 'error' => "Invalid property type: {$data['type']}"];
        }
        
        // Validate price
        if (!is_numeric($data['price']) || $data['price'] <= 0) {
            return ['valid' => false, 'error' => "Invalid price: {$data['price']}"];
        }
        
        // Sanitize
        $data['title'] = trim($data['title']);
        $data['location'] = trim($data['location']);
        $data['price'] = floatval($data['price']);
        $data['area'] = !empty($data['area']) ? floatval($data['area']) : null;
        
        return ['valid' => true, 'data' => $data];
    }
    
    /**
     * Insert property batch
     */
    private function insertPropertyBatch(array $batch): void
    {
        $sql = "INSERT INTO properties (title, type, price, area, location, address, 
                description, status, amenities, created_at) 
                VALUES ";
        
        $values = [];
        $params = [];
        
        foreach ($batch as $row) {
            $values[] = "(?, ?, ?, ?, ?, ?, ?, 'available', ?, NOW())";
            $params[] = $row['title'];
            $params[] = $row['type'];
            $params[] = $row['price'];
            $params[] = $row['area'] ?? null;
            $params[] = $row['location'];
            $params[] = $row['address'] ?? $row['location'];
            $params[] = $row['description'] ?? null;
            $params[] = !empty($row['amenities']) ? json_encode(explode(',', $row['amenities'])) : null;
        }
        
        $sql .= implode(', ', $values);
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
    }
    
    /**
     * Import leads from CSV
     */
    public function importLeads(string $filePath, array $options = []): array
    {
        $result = ['total' => 0, 'successful' => 0, 'failed' => 0, 'errors' => []];
        
        try {
            $handle = fopen($filePath, 'r');
            $headers = fgetcsv($handle);
            $rowNum = 0;
            $batch = [];
            
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                $result['total']++;
                
                $data = array_combine($headers, $row);
                
                // Validate
                if (empty($data['name']) || empty($data['phone'])) {
                    $result['failed']++;
                    $result['errors'][] = "Row {$rowNum}: Name and phone required";
                    continue;
                }
                
                // Sanitize with middleware
                $data = $this->validator->process($data);
                
                $batch[] = [
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'],
                    'source' => $data['source'] ?? 'import',
                    'status' => $data['status'] ?? 'new',
                    'budget' => !empty($data['budget']) ? floatval($data['budget']) : null,
                    'property_type' => $data['property_type'] ?? null,
                    'location' => $data['location'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'assigned_to' => $options['default_assignee'] ?? null
                ];
                
                if (count($batch) >= $this->batchSize) {
                    $this->insertLeadsBatch($batch);
                    $result['successful'] += count($batch);
                    $batch = [];
                }
            }
            
            if (!empty($batch)) {
                $this->insertLeadsBatch($batch);
                $result['successful'] += count($batch);
            }
            
            fclose($handle);
            
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Insert leads batch
     */
    private function insertLeadsBatch(array $batch): void
    {
        $sql = "INSERT INTO leads (name, email, phone, source, status, budget, 
                property_type, location, notes, assigned_to, created_at) VALUES ";
        
        $values = [];
        $params = [];
        
        foreach ($batch as $row) {
            $values[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            array_push($params, 
                $row['name'], $row['email'], $row['phone'], $row['source'],
                $row['status'], $row['budget'], $row['property_type'],
                $row['location'], $row['notes'], $row['assigned_to']
            );
        }
        
        $sql .= implode(', ', $values);
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
    }
    
    /**
     * Export properties to CSV
     */
    public function exportProperties(array $filters = []): array
    {
        $filename = 'properties_export_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = $this->exportPath . $filename;
        
        // Build query
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['type'])) {
            $where[] = 'type = ?';
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['location'])) {
            $where[] = 'location LIKE ?';
            $params[] = '%' . $filters['location'] . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT * FROM properties WHERE {$whereClause} ORDER BY id DESC";
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Write to CSV
        $handle = fopen($filepath, 'w');
        
        if (!empty($properties)) {
            // Headers
            fputcsv($handle, array_keys($properties[0]));
            
            // Data
            foreach ($properties as $property) {
                fputcsv($handle, $property);
            }
        }
        
        fclose($handle);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'records' => count($properties),
            'size' => filesize($filepath)
        ];
    }
    
    /**
     * Export leads to CSV
     */
    public function exportLeads(array $filters = []): array
    {
        $filename = 'leads_export_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = $this->exportPath . $filename;
        
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['source'])) {
            $where[] = 'source = ?';
            $params[] = $filters['source'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(created_at) <= ?';
            $params[] = $filters['date_to'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT * FROM leads WHERE {$whereClause} ORDER BY created_at DESC";
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        $leads = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $handle = fopen($filepath, 'w');
        
        if (!empty($leads)) {
            fputcsv($handle, array_keys($leads[0]));
            foreach ($leads as $lead) {
                fputcsv($handle, $lead);
            }
        }
        
        fclose($handle);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'records' => count($leads),
            'size' => filesize($filepath)
        ];
    }
    
    /**
     * Import khatabook sales data from CSV
     */
    public function importSales(string $filePath, array $options = []): array
    {
        $result = ['total' => 0, 'successful' => 0, 'failed' => 0, 'errors' => []];
        $batchNo = date('YmdHis') . '_' . uniqid();

        try {
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new \Exception("Cannot open file: {$filePath}");
            }

            $headers = fgetcsv($handle);
            if (!$headers) {
                throw new \Exception("Empty file or invalid CSV");
            }

            // Normalize headers
            $headers = array_map('trim', $headers);
            $headers = array_map('strtolower', $headers);
            $headers = array_map(function($h) {
                $h = preg_replace('/[^a-z0-9_]/', '_', $h);
                $h = trim($h, '_');
                return $h;
            }, $headers);

            // Map common khatabook column names
            $columnMap = [
                'date' => 'transaction_date',
                'transaction_date' => 'transaction_date',
                'entry_date' => 'transaction_date',
                'customer' => 'customer_name',
                'customer_name' => 'customer_name',
                'name' => 'customer_name',
                'client_name' => 'customer_name',
                'party_name' => 'customer_name',
                'phone' => 'customer_phone',
                'mobile' => 'customer_phone',
                'contact' => 'customer_phone',
                'customer_phone' => 'customer_phone',
                'address' => 'customer_address',
                'customer_address' => 'customer_address',
                'item' => 'item_description',
                'description' => 'item_description',
                'particulars' => 'item_description',
                'item_description' => 'item_description',
                'product' => 'item_description',
                'qty' => 'quantity',
                'quantity' => 'quantity',
                'rate' => 'rate',
                'unit_price' => 'rate',
                'price' => 'rate',
                'amount' => 'amount',
                'total' => 'amount',
                'value' => 'amount',
                'payment_mode' => 'payment_method',
                'payment_method' => 'payment_method',
                'mode' => 'payment_method',
                'reference' => 'reference_no',
                'ref_no' => 'reference_no',
                'bill_no' => 'reference_no',
                'invoice_no' => 'reference_no',
                'reference_no' => 'reference_no',
                'remarks' => 'notes',
                'notes' => 'notes',
                'note' => 'notes',
            ];

            $batch = [];
            $rowNum = 0;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                $result['total']++;

                if (count($row) !== count($headers)) {
                    $result['failed']++;
                    $result['errors'][] = "Row {$rowNum}: Column count mismatch";
                    continue;
                }

                $rawData = array_combine($headers, $row);

                // Map columns
                $data = [];
                foreach ($rawData as $col => $val) {
                    $mappedCol = $columnMap[$col] ?? $col;
                    $data[$mappedCol] = trim($val);
                }

                // Required: at least customer_name or amount
                if (empty($data['customer_name']) && empty($data['amount'])) {
                    $result['failed']++;
                    $result['errors'][] = "Row {$rowNum}: Missing customer name or amount";
                    continue;
                }

                // Parse date
                if (!empty($data['transaction_date'])) {
                    $ts = strtotime($data['transaction_date']);
                    if ($ts === false) {
                        $data['transaction_date'] = date('Y-m-d');
                    } else {
                        $data['transaction_date'] = date('Y-m-d', $ts);
                    }
                } else {
                    $data['transaction_date'] = date('Y-m-d');
                }

                // Parse amount
                $data['amount'] = !empty($data['amount']) ? floatval(preg_replace('/[^0-9.]/', '', $data['amount'])) : 0;
                $data['rate'] = !empty($data['rate']) ? floatval(preg_replace('/[^0-9.]/', '', $data['rate'])) : 0;
                $data['quantity'] = !empty($data['quantity']) ? floatval(preg_replace('/[^0-9.]/', '', $data['quantity'])) : 1;

                $batch[] = [
                    'transaction_date' => $data['transaction_date'],
                    'customer_name' => $data['customer_name'] ?? 'Unknown',
                    'customer_phone' => $data['customer_phone'] ?? null,
                    'customer_address' => $data['customer_address'] ?? null,
                    'item_description' => $data['item_description'] ?? null,
                    'quantity' => $data['quantity'],
                    'rate' => $data['rate'],
                    'amount' => $data['amount'],
                    'payment_method' => $data['payment_method'] ?? null,
                    'reference_no' => $data['reference_no'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'import_batch' => $batchNo,
                ];

                if (count($batch) >= $this->batchSize) {
                    $this->insertSalesBatch($batch);
                    $result['successful'] += count($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                $this->insertSalesBatch($batch);
                $result['successful'] += count($batch);
            }

            fclose($handle);

        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Insert sales batch
     */
    private function insertSalesBatch(array $batch): void
    {
        $sql = "INSERT INTO khatabook_sales (transaction_date, customer_name, customer_phone, customer_address,
                item_description, quantity, rate, amount, payment_method, reference_no, notes, import_batch, imported_by, imported_at) VALUES ";

        $values = [];
        $params = [];

        foreach ($batch as $row) {
            $values[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            array_push($params,
                $row['transaction_date'], $row['customer_name'], $row['customer_phone'],
                $row['customer_address'], $row['item_description'], $row['quantity'],
                $row['rate'], $row['amount'], $row['payment_method'], $row['reference_no'],
                $row['notes'], $row['import_batch'], $_SESSION['admin_id'] ?? null
            );
        }

        $sql .= implode(', ', $values);

        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Get import templates
     */
    public function getImportTemplate(string $type): array
    {
        $templates = [
            'properties' => [
                'title' => 'Property Title (Required)',
                'type' => 'Type: plot/house/flat/shop/farmhouse (Required)',
                'price' => 'Price in INR (Required)',
                'area' => 'Area in sqft',
                'location' => 'Location/City (Required)',
                'address' => 'Full Address',
                'description' => 'Property Description',
                'amenities' => 'Comma-separated amenities'
            ],
            'leads' => [
                'name' => 'Lead Name (Required)',
                'email' => 'Email Address',
                'phone' => 'Phone Number (Required)',
                'source' => 'Lead Source',
                'status' => 'Status: new/contacted/qualified/converted/lost',
                'budget' => 'Budget Amount',
                'property_type' => 'Preferred Property Type',
                'location' => 'Preferred Location',
                'notes' => 'Additional Notes'
            ],
            'khatabook_sales' => [
                'date' => 'Transaction Date (YYYY-MM-DD)',
                'customer_name' => 'Customer/Party Name (Required)',
                'customer_phone' => 'Phone Number',
                'customer_address' => 'Address',
                'item_description' => 'Item/Service Description',
                'quantity' => 'Quantity (default: 1)',
                'rate' => 'Rate per unit',
                'amount' => 'Total Amount (Required)',
                'payment_method' => 'Payment Mode (cash/online/bank)',
                'reference_no' => 'Bill/Invoice/Reference Number',
                'notes' => 'Additional Notes'
            ]
        ];
        
        return $templates[$type] ?? [];
    }
    
    /**
     * Download import template
     */
    public function downloadTemplate(string $type): array
    {
        $template = $this->getImportTemplate($type);
        
        if (empty($template)) {
            return ['success' => false, 'error' => 'Template not found'];
        }
        
        $filename = "{$type}_import_template.csv";
        $filepath = $this->exportPath . $filename;
        
        $handle = fopen($filepath, 'w');
        fputcsv($handle, array_keys($template));
        fputcsv($handle, array_values($template)); // Sample data row
        fclose($handle);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath
        ];
    }
    
    /**
     * Validate import file
     */
    public function validateImportFile(string $filepath, string $type): array
    {
        $result = ['valid' => false, 'errors' => []];
        
        if (!file_exists($filepath)) {
            $result['errors'][] = 'File not found';
            return $result;
        }
        
        $handle = fopen($filepath, 'r');
        $headers = fgetcsv($handle);
        fclose($handle);
        
        if (!$headers) {
            $result['errors'][] = 'Empty file or invalid CSV';
            return $result;
        }
        
        $template = $this->getImportTemplate($type);
        $required = array_keys(array_filter($template, function($v) {
            return strpos($v, 'Required') !== false;
        }));
        
        $missing = array_diff($required, $headers);
        
        if (!empty($missing)) {
            $result['errors'][] = 'Missing required columns: ' . implode(', ', $missing);
            return $result;
        }
        
        $result['valid'] = true;
        $result['columns'] = count($headers);
        
        return $result;
    }
}
