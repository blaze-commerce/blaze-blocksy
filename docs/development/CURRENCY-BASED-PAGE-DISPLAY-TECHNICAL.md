# Currency-Based Page Display - Technical Implementation Guide

## Architecture Overview

### Component Diagram
```
┌─────────────────────────────────────────────────────────────┐
│                    Page Request                              │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│         template_include Filter (Priority 999)               │
│    BlazeCommerceCurrencyPageDisplay::                        │
│    maybe_redirect_to_related_page()                          │
└────────────────────────┬────────────────────────────────────┘
                         │
        ┌────────────────┼────────────────┐
        │                │                │
        ▼                ▼                ▼
   Is Singular?   Check Metadata   Get Current Region
        │                │                │
        └────────────────┼────────────────┘
                         │
                         ▼
            ┌─────────────────────────┐
            │  Region Match Check     │
            │  page_region ==         │
            │  current_region?        │
            └────────┬────────────────┘
                     │
         ┌───────────┴───────────┐
         │                       │
        YES                      NO
         │                       │
         ▼                       ▼
    Display Related         Return Original
    Page Content            Template
```

## Class Structure

### BlazeCommerceCurrencyPageDisplay

**Singleton Pattern**: Ensures only one instance exists

```php
class BlazeCommerceCurrencyPageDisplay {
    private static $instance = null;
    
    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
```

## Method Reference

### `__construct()`
Initializes the class and hooks into WordPress

```php
public function __construct() {
    add_filter( 'template_include', array( $this, 'maybe_redirect_to_related_page' ), 999 );
}
```

**Hook Details**:
- **Hook**: `template_include`
- **Priority**: 999 (very high, runs near the end)
- **Reason**: Ensures other template filters run first

### `get_current_currency()`
Returns the current WooCommerce currency

```php
private function get_current_currency() {
    if ( function_exists( 'get_woocommerce_currency' ) ) {
        return get_woocommerce_currency();
    }
    return '';
}
```

**Returns**: String (e.g., 'USD', 'EUR', 'CAD')

### `get_current_region()`
Maps currency to region using Aelia settings

```php
private function get_current_region() {
    // 1. Get current currency
    // 2. Check Aelia Currency Switcher is active
    // 3. Get Aelia options from database
    // 4. Map currency to first country code
    // 5. Return country code
}
```

**Process**:
1. Gets currency from `get_current_currency()`
2. Checks if Aelia plugin is active
3. Retrieves Aelia options: `wc_aelia_currency_switcher`
4. Accesses `currency_countries_mappings` array
5. Returns first country code for the currency

**Example**:
```
Currency: USD
Aelia Mapping: USD → [US, PR, VI]
Returns: US
```

### `should_redirect_to_related_page()`
Determines if related page should be displayed

```php
private function should_redirect_to_related_page() {
    // Check 1: Is singular page (not admin)
    // Check 2: Get post ID
    // Check 3: Get metadata (region + related page)
    // Check 4: Both metadata fields exist
    // Check 5: Region matches current region
    // Check 6: Related page exists and is published
    // Return: true/false
}
```

**Validation Checks**:
1. `is_singular( 'page' )` - Only pages, not archives
2. `! is_admin()` - Not in admin area
3. `get_the_ID()` - Valid post ID exists
4. Metadata exists and not empty
5. `$page_region === $current_region` - Region match
6. Related page exists and `post_status === 'publish'`

### `get_related_page_id()`
Retrieves the related page ID from metadata

```php
private function get_related_page_id() {
    $post_id = get_the_ID();
    return absint( get_post_meta( $post_id, 'blaze_related_page', true ) );
}
```

### `maybe_redirect_to_related_page( $template )`
Main filter that processes template display

```php
public function maybe_redirect_to_related_page( $template ) {
    if ( ! $this->should_redirect_to_related_page() ) {
        return $template;
    }
    
    $related_page_id = $this->get_related_page_id();
    
    // Option 1: Redirect (commented out)
    // Option 2: Display content (current)
    
    global $post;
    $post = get_post( $related_page_id );
    setup_postdata( $post );
    
    return get_page_template();
}
```

**Flow**:
1. Check if should redirect
2. If not, return original template
3. If yes, get related page ID
4. Set global `$post` to related page
5. Call `setup_postdata()` to update post context
6. Return page template for related page

## Database Queries

### Metadata Retrieval
```php
get_post_meta( $post_id, 'blaze_page_region', true );
get_post_meta( $post_id, 'blaze_related_page', true );
```

**Queries**: 2 per page load (if singular page)

### Aelia Options
```php
get_option( 'wc_aelia_currency_switcher', false );
```

**Queries**: 1 per page load (cached by WordPress)

### Related Page Verification
```php
get_post( $related_page_id );
```

**Queries**: 1 per page load (if metadata exists)

**Total**: ~4 queries maximum per page load

## Integration Points

### With Page Meta Fields
- Depends on: `custom/page-meta-fields.php`
- Uses metadata: `blaze_page_region`, `blaze_related_page`
- No direct code dependency

### With Aelia Currency Switcher
- Reads: `wc_aelia_currency_switcher` option
- Accesses: `currency_countries_mappings` array
- Requires: Plugin to be active

### With WooCommerce
- Uses: `get_woocommerce_currency()` function
- Requires: WooCommerce to be active

## Extending the Feature

### Add Custom Filters

```php
// Allow filtering of current region
add_filter( 'blaze_current_region', function( $region ) {
    // Custom logic
    return $region;
} );

// Allow filtering of should redirect decision
add_filter( 'blaze_should_redirect_to_related_page', function( $should_redirect, $post_id ) {
    // Custom logic
    return $should_redirect;
}, 10, 2 );
```

### Override Currency Detection

```php
add_filter( 'blaze_current_currency', function( $currency ) {
    // Use custom currency detection
    return $currency;
} );
```

### Add Logging

```php
add_action( 'blaze_page_display_debug', function( $data ) {
    error_log( 'Currency Page Display: ' . json_encode( $data ) );
} );
```

## Performance Optimization

### Caching Strategy
- Metadata is cached by WordPress post meta cache
- Aelia options are cached by WordPress options cache
- No additional caching needed

### Query Optimization
- Uses `get_post()` which is cached
- Metadata queries are minimal
- No expensive joins or complex queries

### Hook Priority
- Priority 999 ensures other filters run first
- Runs late in template loading process
- Minimal impact on page load time

## Testing Checklist

### Unit Tests
- [ ] `get_current_currency()` returns correct currency
- [ ] `get_current_region()` maps currency to region correctly
- [ ] `should_redirect_to_related_page()` validates all conditions
- [ ] `maybe_redirect_to_related_page()` sets correct post

### Integration Tests
- [ ] Page displays related content when region matches
- [ ] Page displays original content when region doesn't match
- [ ] Works with different currencies
- [ ] Handles missing metadata gracefully
- [ ] Handles missing related page gracefully

### Edge Cases
- [ ] Related page doesn't exist
- [ ] Related page is not published
- [ ] Metadata is empty
- [ ] Aelia plugin is not active
- [ ] WooCommerce is not active
- [ ] Currency is not set

## Debugging

### Enable Debug Output
```php
add_action( 'wp_footer', function() {
    if ( is_singular( 'page' ) && current_user_can( 'manage_options' ) ) {
        $post_id = get_the_ID();
        echo '<!-- DEBUG: Currency Page Display -->';
        echo '<!-- Post ID: ' . $post_id . ' -->';
        echo '<!-- Region: ' . get_post_meta( $post_id, 'blaze_page_region', true ) . ' -->';
        echo '<!-- Related: ' . get_post_meta( $post_id, 'blaze_related_page', true ) . ' -->';
    }
} );
```

### Check Aelia Mapping
```php
$aelia_options = get_option( 'wc_aelia_currency_switcher', false );
var_dump( $aelia_options['currency_countries_mappings'] );
```

## Security Considerations

### Input Validation
- Post IDs are validated with `absint()`
- Metadata is sanitized on save (via page-meta-fields.php)
- No user input is directly used

### Access Control
- Only processes on frontend (checks `is_admin()`)
- Respects post publish status
- No capability checks needed (public pages)

### Data Integrity
- Uses WordPress post functions
- Respects post status
- No direct database queries

## Performance Metrics

### Expected Impact
- **Page Load Time**: < 5ms additional
- **Database Queries**: +4 queries (cached)
- **Memory Usage**: < 1MB additional

### Optimization Tips
1. Use page caching plugins
2. Enable object caching
3. Monitor with Query Monitor plugin
4. Profile with Xdebug if needed

