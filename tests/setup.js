/**
 * Jest Setup File for BlazeCommerce WordPress Child Theme
 * 
 * This file is loaded before any Jest tests are run and sets up the testing environment.
 * 
 * @package BlazeCommerce
 * @subpackage Tests
 */

// Mock WordPress globals
global.wp = {
  i18n: {
    __: jest.fn((text) => text),
    _x: jest.fn((text) => text),
    _n: jest.fn((single, plural, number) => number === 1 ? single : plural),
    sprintf: jest.fn((format, ...args) => format)
  },
  hooks: {
    addAction: jest.fn(),
    addFilter: jest.fn(),
    removeAction: jest.fn(),
    removeFilter: jest.fn(),
    doAction: jest.fn(),
    applyFilters: jest.fn((filter, value) => value)
  },
  data: {
    select: jest.fn(),
    dispatch: jest.fn(),
    subscribe: jest.fn()
  },
  element: {
    createElement: jest.fn(),
    Fragment: 'Fragment'
  },
  components: {},
  blocks: {
    registerBlockType: jest.fn(),
    unregisterBlockType: jest.fn()
  }
};

// Mock jQuery
global.jQuery = jest.fn(() => ({
  ready: jest.fn(),
  on: jest.fn(),
  off: jest.fn(),
  trigger: jest.fn(),
  find: jest.fn(),
  addClass: jest.fn(),
  removeClass: jest.fn(),
  toggleClass: jest.fn(),
  hasClass: jest.fn(),
  attr: jest.fn(),
  removeAttr: jest.fn(),
  prop: jest.fn(),
  removeProp: jest.fn(),
  val: jest.fn(),
  text: jest.fn(),
  html: jest.fn(),
  append: jest.fn(),
  prepend: jest.fn(),
  remove: jest.fn(),
  hide: jest.fn(),
  show: jest.fn(),
  toggle: jest.fn(),
  fadeIn: jest.fn(),
  fadeOut: jest.fn(),
  slideUp: jest.fn(),
  slideDown: jest.fn(),
  animate: jest.fn(),
  css: jest.fn(),
  width: jest.fn(),
  height: jest.fn(),
  offset: jest.fn(),
  position: jest.fn(),
  each: jest.fn(),
  map: jest.fn(),
  filter: jest.fn(),
  first: jest.fn(),
  last: jest.fn(),
  eq: jest.fn(),
  get: jest.fn(),
  ajax: jest.fn(),
  post: jest.fn(),
  getJSON: jest.fn(),
  load: jest.fn()
}));

global.$ = global.jQuery;

// Mock WordPress AJAX
global.ajaxurl = 'admin-ajax.php';

// Mock WooCommerce globals
global.wc_checkout_params = {
  ajax_url: 'admin-ajax.php',
  wc_ajax_url: '/?wc-ajax=%%endpoint%%',
  update_order_review_nonce: 'test-nonce',
  apply_coupon_nonce: 'test-nonce',
  remove_coupon_nonce: 'test-nonce',
  option_guest_checkout: 'yes',
  checkout_url: '/checkout/',
  is_checkout: '1',
  debug_mode: false,
  i18n_checkout_error: 'Error processing checkout. Please try again.'
};

global.woocommerce_params = {
  ajax_url: 'admin-ajax.php',
  wc_ajax_url: '/?wc-ajax=%%endpoint%%'
};

// Mock browser APIs
Object.defineProperty(window, 'localStorage', {
  value: {
    getItem: jest.fn(),
    setItem: jest.fn(),
    removeItem: jest.fn(),
    clear: jest.fn()
  },
  writable: true
});

Object.defineProperty(window, 'sessionStorage', {
  value: {
    getItem: jest.fn(),
    setItem: jest.fn(),
    removeItem: jest.fn(),
    clear: jest.fn()
  },
  writable: true
});

// Mock fetch API
global.fetch = jest.fn(() =>
  Promise.resolve({
    ok: true,
    status: 200,
    json: () => Promise.resolve({}),
    text: () => Promise.resolve('')
  })
);

// Mock console methods for cleaner test output
const originalConsole = global.console;
global.console = {
  ...originalConsole,
  log: jest.fn(),
  warn: jest.fn(),
  error: jest.fn(),
  info: jest.fn(),
  debug: jest.fn()
};

// Restore console for debugging when needed
global.console.restore = () => {
  global.console = originalConsole;
};

// Mock IntersectionObserver
global.IntersectionObserver = jest.fn(() => ({
  observe: jest.fn(),
  unobserve: jest.fn(),
  disconnect: jest.fn()
}));

// Mock ResizeObserver
global.ResizeObserver = jest.fn(() => ({
  observe: jest.fn(),
  unobserve: jest.fn(),
  disconnect: jest.fn()
}));

// Mock MutationObserver
global.MutationObserver = jest.fn(() => ({
  observe: jest.fn(),
  disconnect: jest.fn(),
  takeRecords: jest.fn()
}));

// Mock window.matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: jest.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: jest.fn(), // deprecated
    removeListener: jest.fn(), // deprecated
    addEventListener: jest.fn(),
    removeEventListener: jest.fn(),
    dispatchEvent: jest.fn(),
  })),
});

// Mock window.getComputedStyle
Object.defineProperty(window, 'getComputedStyle', {
  value: jest.fn(() => ({
    getPropertyValue: jest.fn(),
    setProperty: jest.fn(),
    removeProperty: jest.fn()
  }))
});

// Mock requestAnimationFrame
global.requestAnimationFrame = jest.fn(cb => setTimeout(cb, 0));
global.cancelAnimationFrame = jest.fn(id => clearTimeout(id));

// Setup test environment
beforeEach(() => {
  // Clear all mocks before each test
  jest.clearAllMocks();
  
  // Reset localStorage and sessionStorage
  localStorage.clear();
  sessionStorage.clear();
  
  // Reset DOM
  document.body.innerHTML = '';
  document.head.innerHTML = '';
});

afterEach(() => {
  // Clean up after each test
  jest.restoreAllMocks();
});

// Global test utilities
global.testUtils = {
  // Create a mock DOM element
  createElement: (tag, attributes = {}, children = []) => {
    const element = document.createElement(tag);
    
    Object.keys(attributes).forEach(key => {
      if (key === 'className') {
        element.className = attributes[key];
      } else if (key === 'innerHTML') {
        element.innerHTML = attributes[key];
      } else {
        element.setAttribute(key, attributes[key]);
      }
    });
    
    children.forEach(child => {
      if (typeof child === 'string') {
        element.appendChild(document.createTextNode(child));
      } else {
        element.appendChild(child);
      }
    });
    
    return element;
  },
  
  // Wait for async operations
  waitFor: (condition, timeout = 1000) => {
    return new Promise((resolve, reject) => {
      const startTime = Date.now();
      
      const check = () => {
        if (condition()) {
          resolve();
        } else if (Date.now() - startTime > timeout) {
          reject(new Error('Timeout waiting for condition'));
        } else {
          setTimeout(check, 10);
        }
      };
      
      check();
    });
  },
  
  // Trigger DOM events
  triggerEvent: (element, eventType, eventData = {}) => {
    const event = new Event(eventType, { bubbles: true, cancelable: true });
    Object.assign(event, eventData);
    element.dispatchEvent(event);
  }
};

console.log('Jest test environment initialized for BlazeCommerce WordPress Child Theme');
