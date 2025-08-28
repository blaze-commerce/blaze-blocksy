module.exports = {
  env: {
    browser: true,
    es2021: true,
    node: true,
    jquery: true
  },
  extends: [
    'eslint:recommended'
  ],
  parserOptions: {
    ecmaVersion: 'latest',
    sourceType: 'module'
  },
  globals: {
    wp: 'readonly',
    jQuery: 'readonly',
    $: 'readonly',
    ajaxurl: 'readonly',
    wc_checkout_params: 'readonly',
    woocommerce_params: 'readonly'
  },
  rules: {
    // Code Quality Standards (Priority 2)
    'no-console': 'warn',
    'no-debugger': 'error',
    'no-alert': 'warn',
    'no-unused-vars': 'error',
    'no-undef': 'error',
    
    // Security Standards (Priority 1)
    'no-eval': 'error',
    'no-implied-eval': 'error',
    'no-new-func': 'error',
    'no-script-url': 'error',
    
    // Performance Standards
    'no-loop-func': 'warn',
    'no-inner-declarations': 'error',
    
    // Code Structure Standards
    'max-len': ['warn', { code: 120, ignoreUrls: true }],
    'max-lines-per-function': ['warn', { max: 30, skipBlankLines: true, skipComments: true }],
    'complexity': ['warn', { max: 10 }],
    'max-depth': ['warn', { max: 4 }],
    
    // WordPress Specific
    'camelcase': 'off', // WordPress uses snake_case
    'no-underscore-dangle': 'off'
  },
  overrides: [
    {
      files: ['**/*.test.js', '**/*.spec.js'],
      env: {
        jest: true
      },
      rules: {
        'no-console': 'off'
      }
    }
  ]
};
