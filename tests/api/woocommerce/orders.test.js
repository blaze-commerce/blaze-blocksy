/**
 * WooCommerce Orders API Tests
 * 
 * Comprehensive testing for WooCommerce Orders REST API endpoints
 * 
 * @package BlazeCommerce\Tests\API\WooCommerce
 */

describe('WooCommerce Orders API', () => {
  let testOrderId;
  let testProductId;
  let testCustomerId;
  
  beforeAll(async () => {
    // Skip tests if WooCommerce credentials are not available
    if (!global.WC_CONFIG.consumer_key || !global.WC_CONFIG.consumer_secret) {
      console.warn('⚠️  Skipping WooCommerce Orders API tests - credentials not provided');
      return;
    }
    
    // Create a test product for order tests
    try {
      const productData = {
        name: `Test Product for Orders ${global.testUtils.generateRandomString()}`,
        type: 'simple',
        regular_price: '25.00',
        status: 'publish'
      };
      
      const productResponse = await global.wcClient.post('/products', productData);
      testProductId = productResponse.data.id;
      global.testUtils.cleanup.addProduct(testProductId);
    } catch (error) {
      console.warn('Failed to create test product for orders:', error.message);
    }
    
    // Create a test customer for order tests
    try {
      const customerData = {
        email: global.testUtils.generateRandomEmail(),
        first_name: 'Test',
        last_name: 'Customer',
        username: `testcustomer${global.testUtils.generateRandomString()}`,
        password: 'testpassword123'
      };
      
      const customerResponse = await global.wcClient.post('/customers', customerData);
      testCustomerId = customerResponse.data.id;
      global.testUtils.cleanup.addCustomer(testCustomerId);
    } catch (error) {
      console.warn('Failed to create test customer for orders:', error.message);
    }
  });
  
  afterEach(async () => {
    // Clean up created test orders
    if (testOrderId) {
      try {
        await global.wcClient.delete(`/orders/${testOrderId}`, { force: true });
        testOrderId = null;
      } catch (error) {
        console.warn(`Failed to clean up test order ${testOrderId}:`, error.message);
      }
    }
  });

  describe('GET /orders', () => {
    test('should retrieve orders list', async () => {
      if (!global.WC_CONFIG.consumer_key) {
        return expect(true).toBe(true); // Skip test
      }
      
      const response = await global.wcClient.get('/orders');
      
      expect(response.status).toBe(200);
      expect(Array.isArray(response.data)).toBe(true);
      
      if (response.data.length > 0) {
        const order = response.data[0];
        expect(order).toHaveValidAPIStructure([
          'id', 'status', 'total', 'date_created', 'billing', 'line_items'
        ]);
      }
    });
    
    test('should filter orders by status', async () => {
      if (!global.WC_CONFIG.consumer_key) {
        return expect(true).toBe(true); // Skip test
      }
      
      const response = await global.wcClient.get('/orders', {
        params: {
          status: 'completed'
        }
      });
      
      expect(response.status).toBe(200);
      expect(Array.isArray(response.data)).toBe(true);
      
      // All returned orders should have status 'completed'
      response.data.forEach(order => {
        expect(order.status).toBe('completed');
      });
    });
    
    test('should handle date range filtering', async () => {
      if (!global.WC_CONFIG.consumer_key) {
        return expect(true).toBe(true); // Skip test
      }
      
      const after = new Date();
      after.setDate(after.getDate() - 30); // 30 days ago
      
      const response = await global.wcClient.get('/orders', {
        params: {
          after: after.toISOString(),
          per_page: 10
        }
      });
      
      expect(response.status).toBe(200);
      expect(Array.isArray(response.data)).toBe(true);
      
      // Check that returned orders are within date range
      response.data.forEach(order => {
        const orderDate = new Date(order.date_created);
        expect(orderDate.getTime()).toBeGreaterThanOrEqual(after.getTime());
      });
    });
  });

  describe('POST /orders', () => {
    test('should create a new order', async () => {
      if (!global.WC_CONFIG.consumer_key || !testProductId) {
        return expect(true).toBe(true); // Skip test
      }
      
      const orderData = {
        payment_method: 'bacs',
        payment_method_title: 'Direct Bank Transfer',
        set_paid: false,
        billing: {
          first_name: 'John',
          last_name: 'Doe',
          address_1: '123 Test Street',
          address_2: '',
          city: 'Test City',
          state: 'CA',
          postcode: '12345',
          country: 'US',
          email: global.testUtils.generateRandomEmail(),
          phone: '555-123-4567'
        },
        shipping: {
          first_name: 'John',
          last_name: 'Doe',
          address_1: '123 Test Street',
          address_2: '',
          city: 'Test City',
          state: 'CA',
          postcode: '12345',
          country: 'US'
        },
        line_items: [
          {
            product_id: testProductId,
            quantity: 2
          }
        ]
      };
      
      const response = await global.wcClient.post('/orders', orderData);
      testOrderId = response.data.id;
      
      expect(response.status).toBe(201);
      expect(response.data).toBeValidWooCommerceResponse();
      expect(response.data.billing.first_name).toBe(orderData.billing.first_name);
      expect(response.data.line_items).toHaveLength(1);
      expect(response.data.line_items[0].product_id).toBe(testProductId);
      expect(response.data.line_items[0].quantity).toBe(2);
      
      // Add to cleanup
      global.testUtils.cleanup.addOrder(testOrderId);
    });
    
    test('should validate required billing information', async () => {
      if (!global.WC_CONFIG.consumer_key || !testProductId) {
        return expect(true).toBe(true); // Skip test
      }
      
      const invalidOrderData = {
        payment_method: 'bacs',
        line_items: [
          {
            product_id: testProductId,
            quantity: 1
          }
        ]
        // Missing required billing information
      };
      
      try {
        await global.wcClient.post('/orders', invalidOrderData);
        fail('Should have thrown validation error');
      } catch (error) {
        expect(error.response.status).toBe(400);
        expect(error.response.data.code).toBe('woocommerce_rest_invalid_order');
      }
    });
    
    test('should create order with customer ID', async () => {
      if (!global.WC_CONFIG.consumer_key || !testProductId || !testCustomerId) {
        return expect(true).toBe(true); // Skip test
      }
      
      const orderData = {
        customer_id: testCustomerId,
        payment_method: 'bacs',
        payment_method_title: 'Direct Bank Transfer',
        set_paid: false,
        billing: {
          first_name: 'Jane',
          last_name: 'Smith',
          address_1: '456 Customer Street',
          city: 'Customer City',
          state: 'NY',
          postcode: '67890',
          country: 'US',
          email: global.testUtils.generateRandomEmail(),
          phone: '555-987-6543'
        },
        line_items: [
          {
            product_id: testProductId,
            quantity: 1
          }
        ]
      };
      
      const response = await global.wcClient.post('/orders', orderData);
      testOrderId = response.data.id;
      
      expect(response.status).toBe(201);
      expect(response.data.customer_id).toBe(testCustomerId);
      
      // Add to cleanup
      global.testUtils.cleanup.addOrder(testOrderId);
    });
  });

  describe('GET /orders/{id}', () => {
    test('should retrieve specific order', async () => {
      if (!global.WC_CONFIG.consumer_key || !testProductId) {
        return expect(true).toBe(true); // Skip test
      }
      
      // First create a test order
      const orderData = {
        payment_method: 'bacs',
        billing: {
          first_name: 'Test',
          last_name: 'User',
          address_1: '789 Order Street',
          city: 'Order City',
          state: 'TX',
          postcode: '54321',
          country: 'US',
          email: global.testUtils.generateRandomEmail(),
          phone: '555-456-7890'
        },
        line_items: [
          {
            product_id: testProductId,
            quantity: 1
          }
        ]
      };
      
      const createResponse = await global.wcClient.post('/orders', orderData);
      testOrderId = createResponse.data.id;
      
      // Now retrieve it
      const response = await global.wcClient.get(`/orders/${testOrderId}`);
      
      expect(response.status).toBe(200);
      expect(response.data).toBeValidWooCommerceResponse();
      expect(response.data.id).toBe(testOrderId);
      expect(response.data.billing.first_name).toBe(orderData.billing.first_name);
      
      // Add to cleanup
      global.testUtils.cleanup.addOrder(testOrderId);
    });
    
    test('should return 404 for non-existent order', async () => {
      if (!global.WC_CONFIG.consumer_key) {
        return expect(true).toBe(true); // Skip test
      }
      
      const nonExistentId = 999999;
      
      try {
        await global.wcClient.get(`/orders/${nonExistentId}`);
        fail('Should have returned 404');
      } catch (error) {
        expect(error.response.status).toBe(404);
        expect(error.response.data.code).toBe('woocommerce_rest_order_invalid_id');
      }
    });
  });

  describe('PUT /orders/{id}', () => {
    test('should update order status', async () => {
      if (!global.WC_CONFIG.consumer_key || !testProductId) {
        return expect(true).toBe(true); // Skip test
      }
      
      // First create a test order
      const orderData = {
        payment_method: 'bacs',
        billing: {
          first_name: 'Update',
          last_name: 'Test',
          address_1: '321 Update Street',
          city: 'Update City',
          state: 'FL',
          postcode: '98765',
          country: 'US',
          email: global.testUtils.generateRandomEmail(),
          phone: '555-321-0987'
        },
        line_items: [
          {
            product_id: testProductId,
            quantity: 1
          }
        ]
      };
      
      const createResponse = await global.wcClient.post('/orders', orderData);
      testOrderId = createResponse.data.id;
      
      // Update the order status
      const updateData = {
        status: 'processing'
      };
      
      const response = await global.wcClient.put(`/orders/${testOrderId}`, updateData);
      
      expect(response.status).toBe(200);
      expect(response.data.id).toBe(testOrderId);
      expect(response.data.status).toBe('processing');
      
      // Add to cleanup
      global.testUtils.cleanup.addOrder(testOrderId);
    });
  });

  describe('Order Notes', () => {
    test('should add note to order', async () => {
      if (!global.WC_CONFIG.consumer_key || !testProductId) {
        return expect(true).toBe(true); // Skip test
      }
      
      // First create a test order
      const orderData = {
        payment_method: 'bacs',
        billing: {
          first_name: 'Note',
          last_name: 'Test',
          address_1: '654 Note Street',
          city: 'Note City',
          state: 'WA',
          postcode: '13579',
          country: 'US',
          email: global.testUtils.generateRandomEmail(),
          phone: '555-654-3210'
        },
        line_items: [
          {
            product_id: testProductId,
            quantity: 1
          }
        ]
      };
      
      const createResponse = await global.wcClient.post('/orders', orderData);
      testOrderId = createResponse.data.id;
      
      // Add a note to the order
      const noteData = {
        note: 'This is a test note for the order',
        customer_note: false
      };
      
      const response = await global.wcClient.post(`/orders/${testOrderId}/notes`, noteData);
      
      expect(response.status).toBe(201);
      expect(response.data.note).toBe(noteData.note);
      expect(response.data.customer_note).toBe(false);
      
      // Add to cleanup
      global.testUtils.cleanup.addOrder(testOrderId);
    });
  });
});
