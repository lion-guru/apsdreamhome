<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

class LocationApiController extends BaseController
{
    public function index()
    {
        header('Content-Type: application/json');
        try {
            $db = \App\Core\Database\Database::getInstance();
            $query = $_GET['q'] ?? '';
            $type = $_GET['type'] ?? 'all';

            $results = [];

            if ($type === 'all' || $type === 'states') {
                $stmt = $db->query("SELECT id, name, 'state' as type FROM states ORDER BY name");
                $results = array_merge($results, $stmt->fetchAll(\PDO::FETCH_ASSOC));
            }

            if ($type === 'all' || $type === 'districts') {
                $sql = "SELECT d.id, d.name, d.state_id, s.name as state_name, 'district' as type 
                        FROM districts d 
                        LEFT JOIN states s ON d.state_id = s.id 
                        ORDER BY d.name";
                $params = [];
                if ($query) {
                    $sql = "SELECT d.id, d.name, d.state_id, s.name as state_name, 'district' as type 
                            FROM districts d 
                            LEFT JOIN states s ON d.state_id = s.id 
                            WHERE d.name LIKE ? OR s.name LIKE ?
                            ORDER BY d.name";
                    $params = ['%' . $query . '%', '%' . $query . '%'];
                }
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $results = array_merge($results, $stmt->fetchAll(\PDO::FETCH_ASSOC));
            }

            if ($type === 'all' || $type === 'cities') {
                $sql = "SELECT c.id, c.name, c.district_id, d.name as district_name, s.name as state_name, 'city' as type 
                        FROM cities c 
                        LEFT JOIN districts d ON c.district_id = d.id 
                        LEFT JOIN states s ON c.state_id = s.id 
                        ORDER BY c.name";
                $params = [];
                if ($query) {
                    $sql = "SELECT c.id, c.name, c.district_id, d.name as district_name, s.name as state_name, 'city' as type 
                            FROM cities c 
                            LEFT JOIN districts d ON c.district_id = d.id 
                            LEFT JOIN states s ON c.state_id = s.id 
                            WHERE c.name LIKE ? OR d.name LIKE ? OR s.name LIKE ?
                            ORDER BY c.name";
                    $params = ['%' . $query . '%', '%' . $query . '%', '%' . $query . '%'];
                }
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $results = array_merge($results, $stmt->fetchAll(\PDO::FETCH_ASSOC));
            }

            // Default locations if DB is empty
            if (empty($results)) {
                $results = [
                    ['id' => 1, 'name' => 'Gorakhpur', 'state_name' => 'Uttar Pradesh', 'type' => 'city'],
                    ['id' => 2, 'name' => 'Lucknow', 'state_name' => 'Uttar Pradesh', 'type' => 'city'],
                    ['id' => 3, 'name' => 'Kushinagar', 'state_name' => 'Uttar Pradesh', 'type' => 'city'],
                    ['id' => 4, 'name' => 'Varanasi', 'state_name' => 'Uttar Pradesh', 'type' => 'city'],
                    ['id' => 5, 'name' => 'Ayodhya', 'state_name' => 'Uttar Pradesh', 'type' => 'city'],
                ];
            }

            echo json_encode(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function byState($stateId)
    {
        header('Content-Type: application/json');
        try {
            $db = \App\Core\Database\Database::getInstance();
            $stmt = $db->prepare("
                SELECT d.id, d.name, d.state_id, s.name as state_name, 'district' as type 
                FROM districts d 
                LEFT JOIN states s ON d.state_id = s.id 
                WHERE d.state_id = ?
                ORDER BY d.name
            ");
            $stmt->execute([$stateId]);
            $districts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $districts]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function byDistrict($districtId)
    {
        header('Content-Type: application/json');
        try {
            $db = \App\Core\Database\Database::getInstance();
            $stmt = $db->prepare("
                SELECT c.id, c.name, c.district_id, d.name as district_name, s.name as state_name, 'city' as type 
                FROM cities c 
                LEFT JOIN districts d ON c.district_id = d.id 
                LEFT JOIN states s ON c.state_id = s.id 
                WHERE c.district_id = ?
                ORDER BY c.name
            ");
            $stmt->execute([$districtId]);
            $cities = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $cities]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>
