# WooCommerce API Credentials Setup Guide

## 🎯 OBJECTIVE
Configure WooCommerce REST API credentials to enable all 34 API tests (currently 4 are skipping due to missing credentials).

## 📋 CURRENT STATUS
- **Total API Tests**: 34
- **Passing**: 30
- **Skipping**: 4 (due to missing WC_CONSUMER_KEY and WC_CONSUMER_SECRET)
- **Target**: 34/34 passing

## 🔑 REQUIRED CREDENTIALS

### Environment Variables Needed:
```bash
WC_CONSUMER_KEY=ck_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
WC_CONSUMER_SECRET=cs_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
API_BASE_URL=https://stg-infinitytargetscom-sitebuild.kinsta.cloud
TEST_USER_EMAIL=hello@blazecommerce.io
TEST_USER_PASSWORD=nx$9G2AG1zu2x&d4
```

## 🛠️ STEP-BY-STEP SETUP

### Step 1: Generate WooCommerce API Keys

1. **Access WordPress Admin:**
   - URL: `https://stg-infinitytargetscom-sitebuild.kinsta.cloud/wp-admin/`
   - Username: `hello@blazecommerce.io`
   - Password: `nx$9G2AG1zu2x&d4`

2. **Navigate to WooCommerce API Settings:**
   - Go to: **WooCommerce → Settings → Advanced → REST API**
   - Click: **Add Key**

3. **Create API Key:**
   - **Description**: `BlazeCommerce API Testing`
   - **User**: Select admin user
   - **Permissions**: `Read/Write`
   - Click: **Generate API Key**

4. **Copy Credentials:**
   - **Consumer Key**: `ck_...` (copy this value)
   - **Consumer Secret**: `cs_...` (copy this value)
   - ⚠️ **Important**: Save these immediately - they won't be shown again!

### Step 2: Configure Local Environment

1. **Create .env file in project root:**
   ```bash
   # Copy from .env.example
   cp .env.example .env
   ```

2. **Add API credentials to .env:**
   ```bash
   # WooCommerce API Credentials
   WC_CONSUMER_KEY=ck_your_consumer_key_here
   WC_CONSUMER_SECRET=cs_your_consumer_secret_here
   
   # Test Environment
   API_BASE_URL=https://stg-infinitytargetscom-sitebuild.kinsta.cloud
   TEST_USER_EMAIL=hello@blazecommerce.io
   TEST_USER_PASSWORD=nx$9G2AG1zu2x&d4
   ```

### Step 3: Verify API Access

1. **Test API connectivity:**
   ```bash
   curl -u "ck_your_key:cs_your_secret" \
   https://stg-infinitytargetscom-sitebuild.kinsta.cloud/wp-json/wc/v3/system_status
   ```

2. **Expected response:**
   ```json
   {
     "environment": {
       "home_url": "https://stg-infinitytargetscom-sitebuild.kinsta.cloud",
       "site_url": "https://stg-infinitytargetscom-sitebuild.kinsta.cloud",
       "version": "8.4.0"
     }
   }
   ```

### Step 4: Run API Tests

1. **Run complete API test suite:**
   ```bash
   npm run test:api:rest
   ```

2. **Expected results:**
   ```
   ✅ 34 tests passing
   ❌ 0 tests skipping
   ⏱️ Test duration: ~30-45 seconds
   ```

## 🔍 TROUBLESHOOTING

### Issue: "Authentication failed"
**Cause**: Invalid API credentials
**Solution**: 
1. Regenerate API keys in WooCommerce admin
2. Update .env file with new credentials
3. Restart test suite

### Issue: "SSL certificate problem"
**Cause**: SSL verification issues on staging
**Solution**: API tests are configured with `verify_ssl: false` for staging

### Issue: "Rate limiting"
**Cause**: Too many API requests
**Solution**: Tests include automatic retry logic with exponential backoff

### Issue: "Permission denied"
**Cause**: API key permissions insufficient
**Solution**: Ensure API key has "Read/Write" permissions

## 📊 API ENDPOINTS TESTED

### Products API:
- `GET /wp-json/wc/v3/products` - List products
- `POST /wp-json/wc/v3/products` - Create product
- `GET /wp-json/wc/v3/products/{id}` - Get product
- `PUT /wp-json/wc/v3/products/{id}` - Update product
- `DELETE /wp-json/wc/v3/products/{id}` - Delete product

### Orders API:
- `GET /wp-json/wc/v3/orders` - List orders
- `POST /wp-json/wc/v3/orders` - Create order
- `GET /wp-json/wc/v3/orders/{id}` - Get order
- `PUT /wp-json/wc/v3/orders/{id}` - Update order
- `DELETE /wp-json/wc/v3/orders/{id}` - Delete order

### Customers API:
- `GET /wp-json/wc/v3/customers` - List customers
- `POST /wp-json/wc/v3/customers` - Create customer
- `GET /wp-json/wc/v3/customers/{id}` - Get customer

### System API:
- `GET /wp-json/wc/v3/system_status` - System status

## 🔒 SECURITY CONSIDERATIONS

### API Key Security:
- ✅ Never commit API keys to version control
- ✅ Use environment variables for credentials
- ✅ Rotate API keys regularly (monthly)
- ✅ Use separate keys for testing vs production
- ✅ Monitor API usage for suspicious activity

### Test Data Security:
- ✅ Tests use temporary test data
- ✅ Automatic cleanup after test completion
- ✅ No real customer data in tests
- ✅ Staging environment isolation

## 📈 SUCCESS METRICS

### Before Setup:
- **API Tests**: 30/34 passing (4 skipping)
- **Coverage**: 88% (missing WooCommerce API coverage)
- **CI/CD**: Incomplete due to skipped tests

### After Setup:
- **API Tests**: 34/34 passing (0 skipping)
- **Coverage**: 100% API endpoint coverage
- **CI/CD**: Full automated testing pipeline
- **Confidence**: High reliability for WooCommerce integrations

## 🚀 NEXT STEPS

1. **Generate API keys** using the guide above
2. **Configure .env file** with credentials
3. **Run API tests** to verify setup: `npm run test:api:rest`
4. **Commit .env.example** (without actual credentials)
5. **Update CI/CD pipeline** with encrypted environment variables
6. **Document API usage** for development team

## 📞 SUPPORT

If you encounter issues:
1. **Check WooCommerce logs**: WooCommerce → Status → Logs
2. **Verify API permissions**: Ensure "Read/Write" access
3. **Test manual API calls**: Use curl or Postman
4. **Contact team**: security@blazecommerce.io

---

**Last Updated**: 2025-08-28  
**Version**: 1.0.0  
**Status**: ⏳ PENDING API KEY GENERATION
