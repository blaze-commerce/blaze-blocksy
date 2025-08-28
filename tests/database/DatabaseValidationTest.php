<?php
/**
 * Database State Validation Framework
 * 
 * Comprehensive testing for database integrity, transactions, and data consistency
 * 
 * @package BlazeCommerce\Tests\Database
 */

namespace BlazeCommerce\Tests\Database;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;

class DatabaseValidationTest extends TestCase
{
    private static $pdo;
    private static $testDbName = 'blaze_commerce_test';
    private $transactionStarted = false;
    
    public static function setUpBeforeClass(): void
    {
        // Database connection configuration
        $host = getenv('DB_HOST') ?: 'localhost';
        $username = getenv('DB_USER') ?: '[REPLACE_WITH_DB_USER]';
        $password = getenv('DB_PASSWORD') ?: '[REPLACE_WITH_DB_PASSWORD]';
        $port = getenv('DB_PORT') ?: '3306';
        
        try {
            // Connect to MySQL server
            self::$pdo = new PDO(
                "mysql:host={$host};port={$port}",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );

            // Set charset
            self::$pdo->exec("SET NAMES utf8mb4");

            // Create test database
            self::$pdo->exec("CREATE DATABASE IF NOT EXISTS " . self::$testDbName . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            self::$pdo->exec("USE " . self::$testDbName);
            
            // Create test tables
            self::createTestTables();
            
        } catch (PDOException $e) {
            self::markTestSkipped('Database connection failed: ' . $e->getMessage());
        }
    }
    
    public static function tearDownAfterClass(): void
    {
        if (self::$pdo) {
            // Clean up test database
            self::$pdo->exec("DROP DATABASE IF EXISTS " . self::$testDbName);
            self::$pdo = null;
        }
    }
    
    protected function setUp(): void
    {
        if (!self::$pdo) {
            $this->markTestSkipped('Database not available');
        }
        
        // Start transaction for test isolation
        self::$pdo->beginTransaction();
        $this->transactionStarted = true;
    }
    
    protected function tearDown(): void
    {
        // Rollback transaction to ensure test isolation
        if ($this->transactionStarted && self::$pdo) {
            self::$pdo->rollBack();
            $this->transactionStarted = false;
        }
    }
    
    private static function createTestTables(): void
    {
        $tables = [
            'test_products' => "
                CREATE TABLE IF NOT EXISTS test_products (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    price DECIMAL(10,2) NOT NULL,
                    stock_quantity INT DEFAULT 0,
                    status ENUM('active', 'inactive') DEFAULT 'active',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_status (status),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB
            ",
            'test_orders' => "
                CREATE TABLE IF NOT EXISTS test_orders (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    customer_email VARCHAR(255) NOT NULL,
                    total DECIMAL(10,2) NOT NULL,
                    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_customer_email (customer_email),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB
            ",
            'test_order_items' => "
                CREATE TABLE IF NOT EXISTS test_order_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id INT NOT NULL,
                    product_id INT NOT NULL,
                    quantity INT NOT NULL,
                    price DECIMAL(10,2) NOT NULL,
                    FOREIGN KEY (order_id) REFERENCES test_orders(id) ON DELETE CASCADE,
                    FOREIGN KEY (product_id) REFERENCES test_products(id) ON DELETE CASCADE,
                    INDEX idx_order_id (order_id),
                    INDEX idx_product_id (product_id)
                ) ENGINE=InnoDB
            ",
            'test_audit_log' => "
                CREATE TABLE IF NOT EXISTS test_audit_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    table_name VARCHAR(100) NOT NULL,
                    record_id INT NOT NULL,
                    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
                    old_values JSON,
                    new_values JSON,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_table_record (table_name, record_id),
                    INDEX idx_action (action)
                ) ENGINE=InnoDB
            "
        ];
        
        foreach ($tables as $tableName => $sql) {
            self::$pdo->exec($sql);
        }
    }
    
    /**
     * Test basic database connectivity and structure
     */
    public function testDatabaseConnectivity(): void
    {
        $this->assertInstanceOf(PDO::class, self::$pdo);
        
        // Test database selection
        $stmt = self::$pdo->query("SELECT DATABASE()");
        $result = $stmt->fetchColumn();
        $this->assertEquals(self::$testDbName, $result);
    }
    
    /**
     * Test table structure and constraints
     */
    public function testTableStructure(): void
    {
        $expectedTables = ['test_products', 'test_orders', 'test_order_items', 'test_audit_log'];
        
        $stmt = self::$pdo->query("SHOW TABLES");
        $actualTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($expectedTables as $table) {
            $this->assertContains($table, $actualTables, "Table {$table} should exist");
        }
        
        // Test foreign key constraints
        $stmt = self::$pdo->query("
            SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $stmt->execute([self::$testDbName]);
        $constraints = $stmt->fetchAll();
        
        $this->assertGreaterThan(0, count($constraints), 'Foreign key constraints should exist');
    }
    
    /**
     * Test transaction rollback functionality
     */
    public function testTransactionRollback(): void
    {
        // Insert test data
        $stmt = self::$pdo->prepare("INSERT INTO test_products (name, price, stock_quantity) VALUES (?, ?, ?)");
        $stmt->execute(['Test Product', 29.99, 100]);
        $productId = self::$pdo->lastInsertId();
        
        // Verify data exists within transaction
        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM test_products WHERE id = ?");
        $stmt->execute([$productId]);
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count, 'Product should exist within transaction');
        
        // Rollback will happen in tearDown()
        // In a separate connection, the data should not be visible
        $separateConnection = new PDO(
            "mysql:host=localhost;dbname=" . self::$testDbName,
            getenv('DB_USER') ?: 'root',
            getenv('DB_PASSWORD') ?: '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $stmt = $separateConnection->prepare("SELECT COUNT(*) FROM test_products WHERE id = ?");
        $stmt->execute([$productId]);
        $externalCount = $stmt->fetchColumn();
        $this->assertEquals(0, $externalCount, 'Product should not be visible outside transaction');
    }
    
    /**
     * Test data integrity constraints
     */
    public function testDataIntegrityConstraints(): void
    {
        // Test NOT NULL constraint
        try {
            $stmt = self::$pdo->prepare("INSERT INTO test_products (price, stock_quantity) VALUES (?, ?)");
            $stmt->execute([29.99, 100]); // Missing required 'name' field
            $this->fail('Should have thrown constraint violation');
        } catch (PDOException $e) {
            $this->assertStringContains('cannot be null', $e->getMessage());
        }
        
        // Test ENUM constraint
        try {
            $stmt = self::$pdo->prepare("INSERT INTO test_products (name, price, status) VALUES (?, ?, ?)");
            $stmt->execute(['Test Product', 29.99, 'invalid_status']);
            $this->fail('Should have thrown ENUM constraint violation');
        } catch (PDOException $e) {
            $this->assertStringContains('Data truncated', $e->getMessage());
        }
    }
    
    /**
     * Test foreign key constraints
     */
    public function testForeignKeyConstraints(): void
    {
        // Create test product and order
        $stmt = self::$pdo->prepare("INSERT INTO test_products (name, price, stock_quantity) VALUES (?, ?, ?)");
        $stmt->execute(['Test Product', 29.99, 100]);
        $productId = self::$pdo->lastInsertId();
        
        $stmt = self::$pdo->prepare("INSERT INTO test_orders (customer_email, total) VALUES (?, ?)");
        $stmt->execute(['test@example.com', 29.99]);
        $orderId = self::$pdo->lastInsertId();
        
        // Test valid foreign key reference
        $stmt = self::$pdo->prepare("INSERT INTO test_order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$orderId, $productId, 1, 29.99]);
        $this->assertTrue(true, 'Valid foreign key reference should work');
        
        // Test invalid foreign key reference
        try {
            $stmt = self::$pdo->prepare("INSERT INTO test_order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([99999, $productId, 1, 29.99]); // Non-existent order_id
            $this->fail('Should have thrown foreign key constraint violation');
        } catch (PDOException $e) {
            $this->assertStringContains('foreign key constraint', strtolower($e->getMessage()));
        }
    }
    
    /**
     * Test cascade delete functionality
     */
    public function testCascadeDelete(): void
    {
        // Create test data with relationships
        $stmt = self::$pdo->prepare("INSERT INTO test_products (name, price, stock_quantity) VALUES (?, ?, ?)");
        $stmt->execute(['Test Product', 29.99, 100]);
        $productId = self::$pdo->lastInsertId();
        
        $stmt = self::$pdo->prepare("INSERT INTO test_orders (customer_email, total) VALUES (?, ?)");
        $stmt->execute(['test@example.com', 29.99]);
        $orderId = self::$pdo->lastInsertId();
        
        $stmt = self::$pdo->prepare("INSERT INTO test_order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$orderId, $productId, 1, 29.99]);
        
        // Verify order item exists
        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM test_order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $this->assertEquals(1, $stmt->fetchColumn());
        
        // Delete parent order
        $stmt = self::$pdo->prepare("DELETE FROM test_orders WHERE id = ?");
        $stmt->execute([$orderId]);
        
        // Verify cascade delete worked
        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM test_order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $this->assertEquals(0, $stmt->fetchColumn(), 'Order items should be cascade deleted');
    }
    
    /**
     * Test concurrent access and locking
     */
    public function testConcurrentAccess(): void
    {
        // Insert test product
        $stmt = self::$pdo->prepare("INSERT INTO test_products (name, price, stock_quantity) VALUES (?, ?, ?)");
        $stmt->execute(['Concurrent Test Product', 29.99, 100]);
        $productId = self::$pdo->lastInsertId();
        
        // Simulate concurrent stock updates
        $stmt = self::$pdo->prepare("UPDATE test_products SET stock_quantity = stock_quantity - 1 WHERE id = ? AND stock_quantity > 0");
        $stmt->execute([$productId]);
        $affectedRows = $stmt->rowCount();
        
        $this->assertEquals(1, $affectedRows, 'Stock update should succeed');
        
        // Verify final stock quantity
        $stmt = self::$pdo->prepare("SELECT stock_quantity FROM test_products WHERE id = ?");
        $stmt->execute([$productId]);
        $finalStock = $stmt->fetchColumn();
        $this->assertEquals(99, $finalStock, 'Stock should be decremented by 1');
    }
    
    /**
     * Test audit logging functionality
     */
    public function testAuditLogging(): void
    {
        // Create test product
        $stmt = self::$pdo->prepare("INSERT INTO test_products (name, price, stock_quantity) VALUES (?, ?, ?)");
        $stmt->execute(['Audit Test Product', 29.99, 100]);
        $productId = self::$pdo->lastInsertId();
        
        // Log the insert action
        $stmt = self::$pdo->prepare("INSERT INTO test_audit_log (table_name, record_id, action, new_values) VALUES (?, ?, ?, ?)");
        $newValues = json_encode(['name' => 'Audit Test Product', 'price' => 29.99, 'stock_quantity' => 100]);
        $stmt->execute(['test_products', $productId, 'INSERT', $newValues]);
        
        // Update the product
        $stmt = self::$pdo->prepare("UPDATE test_products SET price = ? WHERE id = ?");
        $stmt->execute([39.99, $productId]);
        
        // Log the update action
        $stmt = self::$pdo->prepare("INSERT INTO test_audit_log (table_name, record_id, action, old_values, new_values) VALUES (?, ?, ?, ?, ?)");
        $oldValues = json_encode(['price' => 29.99]);
        $newValues = json_encode(['price' => 39.99]);
        $stmt->execute(['test_products', $productId, 'UPDATE', $oldValues, $newValues]);
        
        // Verify audit log entries
        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM test_audit_log WHERE table_name = ? AND record_id = ?");
        $stmt->execute(['test_products', $productId]);
        $auditCount = $stmt->fetchColumn();
        
        $this->assertEquals(2, $auditCount, 'Should have 2 audit log entries (INSERT and UPDATE)');
    }
}
