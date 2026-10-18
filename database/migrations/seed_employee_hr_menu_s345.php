<?php
/**
 * Seed Employee HR Menu Items
 * Adds missing HR features to employee sidebar
 * Idempotent - skips existing entries
 */

namespace App\Database\Migrations;

use App\Core\Database\Database;

class seed_employee_hr_menu_s345
{
    public function up(): void
    {
        $db = Database::getInstance();
        
        // Get the maximum order_index for employee section
        $maxOrder = $db->fetchOne("SELECT COALESCE(MAX(order_index), 0) FROM admin_menu_items WHERE section = 'employee'");
        $baseOrder = (int)$maxOrder;
        
        $menuItems = [
            // Self-Service Core (Phase 1)
            [
                'name' => 'Salary Advances & Loans',
                'url' => '/employee/self-service/advances',
                'icon' => 'fas fa-hand-holding-usd',
                'section' => 'employee',
                'is_active' => 1,
                'parent_id' => null,
                'order_index' => $baseOrder + 1,
            ],
            [
                'name' => 'Leave Encashment',
                'url' => '/employee/self-service/encashment',
                'icon' => 'fas fa-money-bill-wave',
                'section' => 'employee',
                'is_active' => 1,
                'parent_id' => null,
                'order_index' => $baseOrder + 2,
            ],
            [
                'name' => 'Overtime Management',
                'url' => '/employee/self-service/overtime',
                'icon' => 'fas fa-clock',
                'section' => 'employee',
                'is_active' => 1,
                'parent_id' => null,
                'order_index' => $baseOrder + 3,
            ],
            
            // Employee Experience (Phase 2)
            [
                'name' => 'My Asset Assignments',
                'url' => '/employee/self-service/assets',
                'icon' => 'fas fa-laptop',
                'section' => 'employee',
                'is_active' => 1,
                'parent_id' => null,
                'order_index' => $baseOrder + 4,
            ],
            [
                'name' => 'Company Announcements',
                'url' => '/employee/self-service/announcements',
                'icon' => 'fas fa-bullhorn',
                'section' => 'employee',
                'is_active' => 1,
                'parent_id' => null,
                'order_index' => $baseOrder + 5,
            ],
            [
                'name' => 'Shift Swaps',
                'url' => '/employee/self-service/shift-swaps',
                'icon' => 'fas fa-exchange-alt',
                'section' => 'employee',
                'is_active' => 1,
                'parent_id' => null,
                'order_index' => $baseOrder + 6,
            ],
            [
                'name' => 'Helpdesk & Support',
                'url' => '/employee/self-service/helpdesk',
                'icon' => 'fas fa-headset',
                'section' => 'employee',
                'is_active' => 1,
                'parent_id' => null,
                'order_index' => $baseOrder + 7,
            ],
            
            // Self-Service Parent (existing Quality Control -> Self-Service)
            // The existing item at id=215 points to /employee/self-service
            // We'll add sub-items under it
        ];
        
        // Check if Self-Service parent exists, if not create it
        $selfServiceId = $db->fetchOne("SELECT id FROM admin_menu_items WHERE url = '/employee/self-service' AND section = 'employee'");
        if (!$selfServiceId) {
            $selfServiceId = $db->fetchOne("SELECT id FROM admin_menu_items WHERE name = 'Self Service' AND section = 'employee'");
        }
        
        if ($selfServiceId) {
            // Add sub-items under Self-Service parent
            $subItems = [
                [
                    'name' => 'Salary Advances & Loans',
                    'url' => '/employee/self-service/advances',
                    'icon' => 'fas fa-hand-holding-usd',
                    'section' => 'employee',
                    'is_active' => 1,
                    'parent_id' => (int)$selfServiceId,
                    'order_index' => 1,
                ],
                [
                    'name' => 'Leave Encashment',
                    'url' => '/employee/self-service/encashment',
                    'icon' => 'fas fa-money-bill-wave',
                    'section' => 'employee',
                    'is_active' => 1,
                    'parent_id' => (int)$selfServiceId,
                    'order_index' => 2,
                ],
                [
                    'name' => 'Overtime Management',
                    'url' => '/employee/self-service/overtime',
                    'icon' => 'fas fa-clock',
                    'section' => 'employee',
                    'is_active' => 1,
                    'parent_id' => (int)$selfServiceId,
                    'order_index' => 3,
                ],
                [
                    'name' => 'My Asset Assignments',
                    'url' => '/employee/self-service/assets',
                    'icon' => 'fas fa-laptop',
                    'section' => 'employee',
                    'is_active' => 1,
                    'parent_id' => (int)$selfServiceId,
                    'order_index' => 4,
                ],
                [
                    'name' => 'Company Announcements',
                    'url' => '/employee/self-service/announcements',
                    'icon' => 'fas fa-bullhorn',
                    'section' => 'employee',
                    'is_active' => 1,
                    'parent_id' => (int)$selfServiceId,
                    'order_index' => 5,
                ],
                [
                    'name' => 'Shift Swaps',
                    'url' => '/employee/self-service/shift-swaps',
                    'icon' => 'fas fa-exchange-alt',
                    'section' => 'employee',
                    'is_active' => 1,
                    'parent_id' => (int)$selfServiceId,
                    'order_index' => 6,
                ],
                [
                    'name' => 'Helpdesk & Support',
                    'url' => '/employee/self-service/helpdesk',
                    'icon' => 'fas fa-headset',
                    'section' => 'employee',
                    'is_active' => 1,
                    'parent_id' => (int)$selfServiceId,
                    'order_index' => 7,
                ],
            ];
            
            foreach ($subItems as $item) {
                $exists = $db->fetchOne(
                    "SELECT id FROM admin_menu_items WHERE name = ? AND url = ? AND parent_id = ?",
                    [$item['name'], $item['url'], $item['parent_id']]
                );
                
                if (!$exists) {
                    $db->insert('admin_menu_items', $item);
                    echo "Inserted sub-item: {$item['name']}\n";
                } else {
                    echo "Sub-item already exists: {$item['name']}\n";
                }
            }
        } else {
            // Fallback: create top-level items
            foreach ($menuItems as $item) {
                $exists = $db->fetchOne(
                    "SELECT id FROM admin_menu_items WHERE name = ? AND url = ? AND section = 'employee'",
                    [$item['name'], $item['url']]
                );
                
                if (!$exists) {
                    $db->insert('admin_menu_items', $item);
                    echo "Inserted: {$item['name']}\n";
                } else {
                    echo "Already exists: {$item['name']}\n";
                }
            }
        }
        
        // Add role permissions for employee/telecaller roles
        $this->addRolePermissions($db);
        
        echo "Employee HR menu seeding completed.\n";
    }
    
    private function addRolePermissions(\App\Core\Database\Database $db): void
    {
        $roles = ['employee', 'telecaller'];
        $menuNames = [
            'Salary Advances & Loans',
            'Leave Encashment',
            'Overtime Management',
            'My Asset Assignments',
            'Company Announcements',
            'Shift Swaps',
            'Helpdesk & Support',
        ];
        
        foreach ($menuNames as $name) {
            $menuId = $db->fetchOne("SELECT id FROM admin_menu_items WHERE name = ? AND section = 'employee'", [$name]);
            if (!$menuId) continue;
            
            foreach ($roles as $role) {
                $exists = $db->fetchOne(
                    "SELECT id FROM admin_role_menu_permissions WHERE menu_item_id = ? AND role = ?",
                    [$menuId, $role]
                );
                
                if (!$exists) {
                    $db->insert('admin_role_menu_permissions', [
                        'menu_item_id' => (int)$menuId,
                        'role' => $role,
                        'can_view' => 1,
                        'can_edit' => 0,
                        'can_delete' => 0,
                        'can_approve' => 0,
                    ]);
                    echo "Added permission for role '$role' on '$name'\n";
                } else {
                    echo "Permission exists for role '$role' on '$name'\n";
                }
            }
        }
        
        echo "Role permissions added.\n";
    }
    
    public function down(): void
    {
        $db = Database::getInstance();
        $menuNames = [
            'Salary Advances & Loans',
            'Leave Encashment',
            'Overtime Management',
            'My Asset Assignments',
            'Company Announcements',
            'Shift Swaps',
            'Helpdesk & Support',
        ];
        
        foreach ($menuNames as $name) {
            $menuId = $db->fetchOne("SELECT id FROM admin_menu_items WHERE name = ? AND section = 'employee'", [$name]);
            if ($menuId) {
                $db->execute("DELETE FROM admin_role_menu_permissions WHERE menu_item_id = ?", [(int)$menuId]);
                $db->execute("DELETE FROM admin_menu_items WHERE id = ?", [(int)$menuId]);
                echo "Deleted: $name\n";
            }
        }
    }
}