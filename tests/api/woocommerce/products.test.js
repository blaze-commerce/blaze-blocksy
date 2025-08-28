/**
 * WooCommerce Products API Tests
 * 
 * Comprehensive testing for WooCommerce Products REST API endpoints
 * 
 * @package BlazeCommerce\Tests\API\WooCommerce
 */

describe('WooCommerce Products API', () => {
  let testProductId;
  
  beforeAll(async () => {
    // Skip tests if WooCommerce credentials are not available
    if (!global.WC_CONFIG.consumer_key || !global.WC_CONFIG.consumer_secret) {
      console.warn('⚠️  Skipping WooCommerce Products API tests - credentials not provided');
      return;
    }
  });
  
  afterEach(async () => {
    // Clean up created test products
    if (testProductId) {
      try {
        await global.wcClient.delete(`/products/${testProductId}`, { force: true });
        testProductId = null;
      } catch (error) {
        console.warn(`Failed to clean up test product ${testProductId}:`, error.message);
      }
    }
  });

  describe('GET /products', () => {
    test('should retrieve products list', async () => {
      if (!global.WC_CONFIG.consumer_key) {
        return expect(true).toBe(true); // Skip test
      }
      
      const response = await global.wcClient.get('/products');
      
      expect(response.status).toBe(200);
      expect(Array.isArray(response.data)).toBe(true);
      
      if (response.data.length > 0) {
        const product = response.data[0];
        expect(product).toHaveValidAPIStructure([
          'id', 'name', 'slug', 'status', 'type', 'price'
        ]);
      }
    });
    
    test('should handle pagination parameters', async () => {
      if (!global.WC_CONFIG.consumer_key) {
        return expect(true).toBe(true); // Skip test
      }
      
      const response = await global.wcClient.get('/products', {
        params: {
          per_page: 5,
          page: 1
        }
      });
      
      expect(response.status).toBe(200);
      expect(Array.isArray(response.data)).toBe(true);
      expect(response.data.length).toBeLessThanOrEqual(5);
      
      // Check pagination headers
      expect(response.headers).toHaveProperty('x-wp-total');
      expect(response.headers).toHaveProperty('x-wp-totalpages');
    });
    
    test('should filter products by status', async () => {
      if (!global.WC_CONFIG.consumer_key) {
        return expect(true).toBe(true); // Skip test
      }
      
      const response = await global.wcClient.get('/products', {
        params: {
          status: 'publish'
        }
      });
      
      expect(response.status).toBe(200);
      expect(Array.isArray(response.data)).toBe(true);
      
      // All returned products should have status 'publish'
      response.data.forEach(product => {
        expect(product.status).toBe('publish');
      });
    });
  });

  describe('POST /products', () => {
    test('should create a new product', async () => {
      if (!global.WC_CONFIG.consumer_key) {
        return expect(true).toBe(true); // Skip test
      }
      
      const productData = {
        name: `Test Product ${global.testUtils.generateRandomString()}`,
        type: 'simple',
        regular_price: '19.99',
        description: 'Test product description',
        short_description: 'Test product short description',
        status: 'publish'
      };
      
      const response = await global.wcClient.post('/products', productData);
      testProductId = response.data.id;
      
      expect(response.status).toBe(201);
      expect(response.data).toBeValidWooCommerceResponse();
      expect(response.data.name).toBe(productData.name);
      expect(response.data.type).toBe(productData.type);
      expect(response.data.regular_price).toBe(productData.regular_price);
      expect(response.data.status).toBe(productData.status);
      
      // Add to cleanup
      global.testUtils.cleanup.addProduct(testProductId);
    });
    
    test('should validate required fields', async () => {
      if (!global.WC_CONFIG.consumer_key) {
        return expect(true).toBe(true); // Skip test
      }
      
      const invalidProductData = {
        type: 'simple',
        regular_price: '19.99'
        // Missing required 'name' field
      };
      
      try {
        await global.wcClient.post('/products', invalidProductData);
        fail('Should have thrown validation error');
      } catch (error) {
        expect(error.response.status).toBe(400);
        expect(error.response.data.code).toBe('woocommerce_rest_cannot_create');
      }
    });
    
    test('should handle product with variations', async () => {
      if (!global.WC_CONFIG.consumer_key) {
        return expect(true).toBe(true); // Skip test
      }
      
      const variableProductData = {
        name: `Variable Product ${global.testUtils.generateRandomString()}`,
        type: 'variable',
        description: 'Test variable product',
        status: 'publish',
        attributes: [
          {
            name: 'Size',
            options: ['Small', 'Medium', 'Large'],
            visible: true,
            variation: true
          }
        ]
      };
      
      const response = await global.wcClient.post('/products', variableProductData);
      testProductId = response.data.id;
      
      expect(response.status).toBe(201);
      expect(response.data.type).toBe('variable');
      expect(response.data.attributes).toHaveLength(1);
      expect(response.data.attributes[0].name).toBe('Size');
      
      // Add to cleanup
      global.testUtils.cleanup.addProduct(testProductId);
    });
  });

  describe('GET /products/{id}', () => {
    test('should retrieve specific product', async () => {
      if (!global.WC_CONFIG.consumer_key) {
        return expect(true).toBe(true); // Skip test
      }
      
      // First create a test product
      const productData = {
        name: `Test Product ${global.testUtils.generateRandomString()}`,
        type: 'simple',
        regular_price: '29.99',
        status: 'publish'
      };
      
      const createResponse = await global.wcClient.post('/products', productData);
      testProductId = createResponse.data.id;
      
      // Now retrieve it
      const response = await global.wcClient.get(`/products/${testProductId}`);
      
      expect(response.status).toBe(200);
      expect(response.data).toBeValidWooCommerceResponse();
      expect(response.data.id).toBe(testProductId);
      expect(response.data.name).toBe(productData.name);
      
      // Add to cleanup
      global.testUtils.cleanup.addProduct(testProductId);
    });
    
    test('should return 404 for non-existent product', async () => {
      if (!global.WC_CONFIG.consumer_key) {
        return expect(true).toBe(true); // Skip test
      }
      
      const nonExistentId = 999999;
      
      try {
        await global.wcClient.get(`/products/${nonExistentId}`);
        fail('Should have returned 404');
      } catch (error) {
        expect(error.response.status).toBe(404);
        expect(error.response.data.code).toBe('woocommerce_rest_product_invalid_id');
      }
    });
  });

  describe('PUT /products/{id}', () => {
    test('should update existing product', async () => {
      if (!global.WC_CONFIG.consumer_key) {
        return expect(true).toBe(true); // Skip test
      }
      
      // First create a test product
      const productData = {
        name: `Test Product ${global.testUtils.generateRandomString()}`,
        type: 'simple',
        regular_price: '39.99',
        status: 'publish'
      };
      
      const createResponse = await global.wcClient.post('/products', productData);
      testProductId = createResponse.data.id;
      
      // Update the product
      const updateData = {
        name: `Updated Product ${global.testUtils.generateRandomString()}`,
        regular_price: '49.99'
      };
      
      const response = await global.wcClient.put(`/products/${testProductId}`, updateData);
      
      expect(response.status).toBe(200);
      expect(response.data.id).toBe(testProductId);
      expect(response.data.name).toBe(updateData.name);
      expect(response.data.regular_price).toBe(updateData.regular_price);
      
      // Add to cleanup
      global.testUtils.cleanup.addProduct(testProductId);
    });
  });

  describe('DELETE /products/{id}', () => {
    test('should delete product', async () => {
      if (!global.WC_CONFIG.consumer_key) {
        return expect(true).toBe(true); // Skip test
      }
      
      // First create a test product
      const productData = {
        name: `Test Product ${global.testUtils.generateRandomString()}`,
        type: 'simple',
        regular_price: '59.99',
        status: 'publish'
      };
      
      const createResponse = await global.wcClient.post('/products', productData);
      const productId = createResponse.data.id;
      
      // Delete the product
      const response = await global.wcClient.delete(`/products/${productId}`, {
        force: true
      });
      
      expect(response.status).toBe(200);
      expect(response.data.id).toBe(productId);
      
      // Verify product is deleted
      try {
        await global.wcClient.get(`/products/${productId}`);
        fail('Product should have been deleted');
      } catch (error) {
        expect(error.response.status).toBe(404);
      }
    });
  });

  describe('Product Search and Filtering', () => {
    test('should search products by name', async () => {
      if (!global.WC_CONFIG.consumer_key) {
        return expect(true).toBe(true); // Skip test
      }
      
      const searchTerm = global.testUtils.generateRandomString();
      
      // Create a product with unique name
      const productData = {
        name: `Searchable Product ${searchTerm}`,
        type: 'simple',
        regular_price: '69.99',
        status: 'publish'
      };
      
      const createResponse = await global.wcClient.post('/products', productData);
      testProductId = createResponse.data.id;
      
      // Wait a moment for indexing
      await global.testUtils.wait(1000);
      
      // Search for the product
      const response = await global.wcClient.get('/products', {
        params: {
          search: searchTerm
        }
      });
      
      expect(response.status).toBe(200);
      expect(Array.isArray(response.data)).toBe(true);
      
      // Should find our test product
      const foundProduct = response.data.find(p => p.id === testProductId);
      expect(foundProduct).toBeDefined();
      expect(foundProduct.name).toContain(searchTerm);
      
      // Add to cleanup
      global.testUtils.cleanup.addProduct(testProductId);
    });
  });
});
