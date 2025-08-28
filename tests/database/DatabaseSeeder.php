<?php
/**
 * Database Seeder and Fixture Management
 * 
 * Utilities for creating consistent test data and managing database fixtures
 * 
 * @package BlazeCommerce\Tests\Database
 */

namespace BlazeCommerce\Tests\Database;

use PDO;
use PDOException;

class DatabaseSeeder
{
    private $pdo;
    private $fixtures = [];
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Seed the database with test data
     */
    public function seed(): array
    {
        $this->fixtures = [];
        
        try {
            $this->pdo->beginTransaction();
            
            // Seed products
            $this->fixtures['products'] = $this->seedProducts();
            
            // Seed customers/orders
            $this->fixtures['orders'] = $this->seedOrders();
            
            // Seed order items
            $this->fixtures['order_items'] = $this->seedOrderItems();
            
            // Seed categories (if table exists)
            if ($this->tableExists('test_categories')) {
                $this->fixtures['categories'] = $this->seedCategories();
            }
            
            $this->pdo->commit();
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new \Exception('Database seeding failed: ' . $e->getMessage());
        }
        
        return $this->fixtures;
    }
    
    /**
     * Clean up all test data
     */
    public function cleanup(): void
    {
        $tables = [
            'test_order_items',
            'test_orders', 
            'test_products',
            'test_categories',
            'test_audit_log'
        ];
        
        try {
            $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
            
            foreach ($tables as $table) {
                if ($this->tableExists($table)) {
                    $this->pdo->exec("TRUNCATE TABLE {$table}");
                }
            }
            
            $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            
        } catch (PDOException $e) {
            throw new \Exception('Database cleanup failed: ' . $e->getMessage());
        }
        
        $this->fixtures = [];
    }
    
    /**
     * Seed test products
     */
    private function seedProducts(): array
    {
        $products = [
            [
                'name' => 'Test Product 1',
                'price' => 29.99,
                'stock_quantity' => 100,
                'status' => 'active'
            ],
            [
                'name' => 'Test Product 2', 
                'price' => 49.99,
                'stock_quantity' => 50,
                'status' => 'active'
            ],
            [
                'name' => 'Test Product 3',
                'price' => 19.99,
                'stock_quantity' => 0,
                'status' => 'inactive'
            ],
            [
                'name' => 'Premium Product',
                'price' => 99.99,
                'stock_quantity' => 25,
                'status' => 'active'
            ],
            [
                'name' => 'Sale Product',
                'price' => 15.99,
                'stock_quantity' => 200,
                'status' => 'active'
            ]
        ];
        
        $productIds = [];
        $stmt = $this->pdo->prepare("
            INSERT INTO test_products (name, price, stock_quantity, status) 
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($products as $product) {
            $stmt->execute([
                $product['name'],
                $product['price'],
                $product['stock_quantity'],
                $product['status']
            ]);
            
            $productIds[] = [
                'id' => $this->pdo->lastInsertId(),
                'name' => $product['name'],
                'price' => $product['price'],
                'stock_quantity' => $product['stock_quantity'],
                'status' => $product['status']
            ];
        }
        
        return $productIds;
    }
    
    /**
     * Seed test orders
     */
    private function seedOrders(): array
    {
        $orders = [
            [
                'customer_email' => 'customer1@example.com',
                'total' => 79.98,
                'status' => 'completed'
            ],
            [
                'customer_email' => 'customer2@example.com',
                'total' => 49.99,
                'status' => 'processing'
            ],
            [
                'customer_email' => 'customer3@example.com',
                'total' => 129.97,
                'status' => 'pending'
            ],
            [
                'customer_email' => 'customer1@example.com',
                'total' => 99.99,
                'status' => 'completed'
            ],
            [
                'customer_email' => 'customer4@example.com',
                'total' => 35.98,
                'status' => 'cancelled'
            ]
        ];
        
        $orderIds = [];
        $stmt = $this->pdo->prepare("
            INSERT INTO test_orders (customer_email, total, status) 
            VALUES (?, ?, ?)
        ");
        
        foreach ($orders as $order) {
            $stmt->execute([
                $order['customer_email'],
                $order['total'],
                $order['status']
            ]);
            
            $orderIds[] = [
                'id' => $this->pdo->lastInsertId(),
                'customer_email' => $order['customer_email'],
                'total' => $order['total'],
                'status' => $order['status']
            ];
        }
        
        return $orderIds;
    }
    
    /**
     * Seed test order items
     */
    private function seedOrderItems(): array
    {
        if (empty($this->fixtures['products']) || empty($this->fixtures['orders'])) {
            throw new \Exception('Products and orders must be seeded before order items');
        }
        
        $orderItems = [];
        $stmt = $this->pdo->prepare("
            INSERT INTO test_order_items (order_id, product_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");
        
        // Create order items for each order
        foreach ($this->fixtures['orders'] as $order) {
            $numItems = rand(1, 3); // 1-3 items per order
            $usedProducts = [];
            
            for ($i = 0; $i < $numItems; $i++) {
                // Select a random product that hasn't been used in this order
                do {
                    $productIndex = array_rand($this->fixtures['products']);
                    $product = $this->fixtures['products'][$productIndex];
                } while (in_array($product['id'], $usedProducts) && count($usedProducts) < count($this->fixtures['products']));
                
                $usedProducts[] = $product['id'];
                $quantity = rand(1, 3);
                
                $stmt->execute([
                    $order['id'],
                    $product['id'],
                    $quantity,
                    $product['price']
                ]);
                
                $orderItems[] = [
                    'id' => $this->pdo->lastInsertId(),
                    'order_id' => $order['id'],
                    'product_id' => $product['id'],
                    'quantity' => $quantity,
                    'price' => $product['price']
                ];
            }
        }
        
        return $orderItems;
    }
    
    /**
     * Seed test categories
     */
    private function seedCategories(): array
    {
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics'],
            ['name' => 'Clothing', 'slug' => 'clothing'],
            ['name' => 'Books', 'slug' => 'books'],
            ['name' => 'Home & Garden', 'slug' => 'home-garden'],
            ['name' => 'Sports', 'slug' => 'sports']
        ];
        
        $categoryIds = [];
        $stmt = $this->pdo->prepare("
            INSERT INTO test_categories (name, slug) 
            VALUES (?, ?)
        ");
        
        foreach ($categories as $category) {
            $stmt->execute([
                $category['name'],
                $category['slug']
            ]);
            
            $categoryIds[] = [
                'id' => $this->pdo->lastInsertId(),
                'name' => $category['name'],
                'slug' => $category['slug']
            ];
        }
        
        return $categoryIds;
    }
    
    /**
     * Get specific fixture data
     */
    public function getFixture(string $type, int $index = 0): ?array
    {
        return $this->fixtures[$type][$index] ?? null;
    }
    
    /**
     * Get all fixtures of a specific type
     */
    public function getFixtures(string $type): array
    {
        return $this->fixtures[$type] ?? [];
    }
    
    /**
     * Get random fixture of a specific type
     */
    public function getRandomFixture(string $type): ?array
    {
        $fixtures = $this->getFixtures($type);
        return empty($fixtures) ? null : $fixtures[array_rand($fixtures)];
    }
    
    /**
     * Check if table exists
     */
    private function tableExists(string $tableName): bool
    {
        try {
            $stmt = $this->pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$tableName]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Create sample data for performance testing
     */
    public function seedLargeDataset(int $productCount = 1000, int $orderCount = 500): array
    {
        $this->pdo->beginTransaction();
        
        try {
            // Seed large number of products
            $stmt = $this->pdo->prepare("
                INSERT INTO test_products (name, price, stock_quantity, status) 
                VALUES (?, ?, ?, ?)
            ");
            
            for ($i = 1; $i <= $productCount; $i++) {
                $stmt->execute([
                    "Bulk Product {$i}",
                    rand(10, 200) + (rand(0, 99) / 100), // Random price between 10.00 and 200.99
                    rand(0, 500),
                    rand(0, 10) > 1 ? 'active' : 'inactive' // 90% active, 10% inactive
                ]);
            }
            
            // Seed large number of orders
            $stmt = $this->pdo->prepare("
                INSERT INTO test_orders (customer_email, total, status) 
                VALUES (?, ?, ?)
            ");
            
            $statuses = ['pending', 'processing', 'completed', 'cancelled'];
            
            for ($i = 1; $i <= $orderCount; $i++) {
                $stmt->execute([
                    "bulkcustomer{$i}@example.com",
                    rand(20, 500) + (rand(0, 99) / 100),
                    $statuses[array_rand($statuses)]
                ]);
            }
            
            $this->pdo->commit();
            
            return [
                'products_created' => $productCount,
                'orders_created' => $orderCount
            ];
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new \Exception('Large dataset seeding failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate data integrity after seeding
     */
    public function validateIntegrity(): array
    {
        $results = [];
        
        // Check product counts
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM test_products");
        $results['product_count'] = $stmt->fetchColumn();
        
        // Check order counts
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM test_orders");
        $results['order_count'] = $stmt->fetchColumn();
        
        // Check order items counts
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM test_order_items");
        $results['order_item_count'] = $stmt->fetchColumn();
        
        // Check foreign key integrity
        $stmt = $this->pdo->query("
            SELECT COUNT(*) FROM test_order_items oi
            LEFT JOIN test_orders o ON oi.order_id = o.id
            LEFT JOIN test_products p ON oi.product_id = p.id
            WHERE o.id IS NULL OR p.id IS NULL
        ");
        $results['orphaned_order_items'] = $stmt->fetchColumn();
        
        // Check data consistency
        $stmt = $this->pdo->query("
            SELECT o.id, o.total, SUM(oi.quantity * oi.price) as calculated_total
            FROM test_orders o
            LEFT JOIN test_order_items oi ON o.id = oi.order_id
            GROUP BY o.id
            HAVING ABS(o.total - COALESCE(calculated_total, 0)) > 0.01
        ");
        $results['inconsistent_order_totals'] = $stmt->rowCount();
        
        return $results;
    }
}
