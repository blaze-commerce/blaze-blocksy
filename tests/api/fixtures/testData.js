/**
 * API Test Fixtures and Mock Data
 * 
 * Centralized test data for consistent API testing
 * 
 * @package BlazeCommerce\Tests\API\Fixtures
 */

/**
 * Generate test product data
 */
const generateProductData = (overrides = {}) => {
  const baseProduct = {
    name: `Test Product ${global.testUtils.generateRandomString()}`,
    type: 'simple',
    regular_price: '29.99',
    description: 'This is a test product created for API testing purposes.',
    short_description: 'Test product for API testing.',
    status: 'publish',
    catalog_visibility: 'visible',
    featured: false,
    virtual: false,
    downloadable: false,
    manage_stock: true,
    stock_quantity: 100,
    stock_status: 'instock',
    backorders: 'no',
    weight: '1.5',
    dimensions: {
      length: '10',
      width: '8',
      height: '3'
    },
    categories: [
      {
        id: 1,
        name: 'Test Category'
      }
    ],
    tags: [
      {
        id: 1,
        name: 'test'
      }
    ],
    images: [
      {
        src: 'https://via.placeholder.com/300x300.png?text=Test+Product',
        alt: 'Test Product Image'
      }
    ],
    attributes: [],
    meta_data: [
      {
        key: '_test_product',
        value: 'true'
      }
    ]
  };
  
  return { ...baseProduct, ...overrides };
};

/**
 * Generate test customer data
 */
const generateCustomerData = (overrides = {}) => {
  const randomString = global.testUtils.generateRandomString();
  
  const baseCustomer = {
    email: `testcustomer${randomString}@blazecommerce.io`,
    first_name: 'Test',
    last_name: 'Customer',
    username: `testcustomer${randomString}`,
    password: 'TestPassword123!',
    billing: {
      first_name: 'Test',
      last_name: 'Customer',
      company: 'Test Company',
      address_1: '123 Test Street',
      address_2: 'Suite 100',
      city: 'Test City',
      state: 'CA',
      postcode: '90210',
      country: 'US',
      email: `testcustomer${randomString}@blazecommerce.io`,
      phone: '555-123-4567'
    },
    shipping: {
      first_name: 'Test',
      last_name: 'Customer',
      company: 'Test Company',
      address_1: '123 Test Street',
      address_2: 'Suite 100',
      city: 'Test City',
      state: 'CA',
      postcode: '90210',
      country: 'US'
    },
    meta_data: [
      {
        key: '_test_customer',
        value: 'true'
      }
    ]
  };
  
  return { ...baseCustomer, ...overrides };
};

/**
 * Generate test order data
 */
const generateOrderData = (productId, customerId = null, overrides = {}) => {
  const randomString = global.testUtils.generateRandomString();
  
  const baseOrder = {
    payment_method: 'bacs',
    payment_method_title: 'Direct Bank Transfer',
    set_paid: false,
    status: 'pending',
    currency: 'USD',
    customer_note: 'Test order created for API testing',
    billing: {
      first_name: 'John',
      last_name: 'Doe',
      company: 'Test Company Inc.',
      address_1: '456 Order Street',
      address_2: 'Floor 2',
      city: 'Order City',
      state: 'NY',
      postcode: '10001',
      country: 'US',
      email: `testorder${randomString}@blazecommerce.io`,
      phone: '555-987-6543'
    },
    shipping: {
      first_name: 'John',
      last_name: 'Doe',
      company: 'Test Company Inc.',
      address_1: '456 Order Street',
      address_2: 'Floor 2',
      city: 'Order City',
      state: 'NY',
      postcode: '10001',
      country: 'US'
    },
    line_items: [
      {
        product_id: productId,
        quantity: 2,
        price: 29.99
      }
    ],
    shipping_lines: [
      {
        method_id: 'flat_rate',
        method_title: 'Flat Rate',
        total: '5.00'
      }
    ],
    fee_lines: [],
    coupon_lines: [],
    meta_data: [
      {
        key: '_test_order',
        value: 'true'
      }
    ]
  };
  
  if (customerId) {
    baseOrder.customer_id = customerId;
  }
  
  return { ...baseOrder, ...overrides };
};

/**
 * Generate test coupon data
 */
const generateCouponData = (overrides = {}) => {
  const baseCoupon = {
    code: `TEST${global.testUtils.generateRandomString().toUpperCase()}`,
    discount_type: 'percent',
    amount: '10',
    description: 'Test coupon for API testing',
    date_expires: null,
    individual_use: false,
    product_ids: [],
    excluded_product_ids: [],
    usage_limit: 100,
    usage_limit_per_user: 1,
    limit_usage_to_x_items: null,
    free_shipping: false,
    product_categories: [],
    excluded_product_categories: [],
    exclude_sale_items: false,
    minimum_amount: '0',
    maximum_amount: '1000',
    email_restrictions: [],
    meta_data: [
      {
        key: '_test_coupon',
        value: 'true'
      }
    ]
  };
  
  return { ...baseCoupon, ...overrides };
};

/**
 * Generate test category data
 */
const generateCategoryData = (overrides = {}) => {
  const baseCategory = {
    name: `Test Category ${global.testUtils.generateRandomString()}`,
    slug: `test-category-${global.testUtils.generateRandomString()}`,
    description: 'Test category created for API testing',
    display: 'default',
    image: {
      src: 'https://via.placeholder.com/150x150.png?text=Category',
      alt: 'Test Category Image'
    },
    menu_order: 0,
    count: 0,
    meta_data: [
      {
        key: '_test_category',
        value: 'true'
      }
    ]
  };
  
  return { ...baseCategory, ...overrides };
};

/**
 * Generate variable product data with variations
 */
const generateVariableProductData = (overrides = {}) => {
  const baseVariableProduct = {
    name: `Variable Product ${global.testUtils.generateRandomString()}`,
    type: 'variable',
    description: 'Test variable product with multiple variations',
    short_description: 'Variable test product',
    status: 'publish',
    attributes: [
      {
        name: 'Size',
        options: ['Small', 'Medium', 'Large'],
        visible: true,
        variation: true
      },
      {
        name: 'Color',
        options: ['Red', 'Blue', 'Green'],
        visible: true,
        variation: true
      }
    ],
    default_attributes: [
      {
        name: 'Size',
        option: 'Medium'
      },
      {
        name: 'Color',
        option: 'Blue'
      }
    ],
    meta_data: [
      {
        key: '_test_variable_product',
        value: 'true'
      }
    ]
  };
  
  return { ...baseVariableProduct, ...overrides };
};

/**
 * Generate product variation data
 */
const generateVariationData = (parentId, overrides = {}) => {
  const baseVariation = {
    regular_price: '25.99',
    sale_price: '19.99',
    status: 'publish',
    stock_quantity: 50,
    manage_stock: true,
    stock_status: 'instock',
    attributes: [
      {
        name: 'Size',
        option: 'Large'
      },
      {
        name: 'Color',
        option: 'Red'
      }
    ],
    image: {
      src: 'https://via.placeholder.com/300x300.png?text=Variation',
      alt: 'Product Variation Image'
    },
    meta_data: [
      {
        key: '_test_variation',
        value: 'true'
      }
    ]
  };
  
  return { ...baseVariation, ...overrides };
};

/**
 * Common API response validation schemas
 */
const validationSchemas = {
  product: [
    'id', 'name', 'slug', 'status', 'type', 'description', 'short_description',
    'sku', 'price', 'regular_price', 'sale_price', 'date_created', 'date_modified',
    'stock_status', 'stock_quantity', 'manage_stock', 'categories', 'tags', 'images'
  ],
  
  order: [
    'id', 'parent_id', 'number', 'order_key', 'created_via', 'version', 'status',
    'currency', 'date_created', 'date_modified', 'discount_total', 'discount_tax',
    'shipping_total', 'shipping_tax', 'cart_tax', 'total', 'total_tax', 'prices_include_tax',
    'customer_id', 'customer_ip_address', 'customer_user_agent', 'customer_note',
    'billing', 'shipping', 'payment_method', 'payment_method_title', 'transaction_id',
    'date_paid', 'date_completed', 'cart_hash', 'line_items', 'tax_lines', 'shipping_lines',
    'fee_lines', 'coupon_lines', 'refunds'
  ],
  
  customer: [
    'id', 'date_created', 'date_modified', 'email', 'first_name', 'last_name',
    'role', 'username', 'billing', 'shipping', 'is_paying_customer',
    'avatar_url', 'meta_data'
  ],
  
  coupon: [
    'id', 'code', 'amount', 'date_created', 'date_modified', 'discount_type',
    'description', 'date_expires', 'usage_count', 'individual_use', 'product_ids',
    'excluded_product_ids', 'usage_limit', 'usage_limit_per_user', 'limit_usage_to_x_items',
    'free_shipping', 'product_categories', 'excluded_product_categories', 'exclude_sale_items',
    'minimum_amount', 'maximum_amount', 'email_restrictions', 'used_by', 'meta_data'
  ]
};

module.exports = {
  generateProductData,
  generateCustomerData,
  generateOrderData,
  generateCouponData,
  generateCategoryData,
  generateVariableProductData,
  generateVariationData,
  validationSchemas
};
