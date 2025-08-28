# Dynamic Pickup Locations Implementation

## Overview

The pickup location display in the WooCommerce thank you page has been enhanced to dynamically retrieve pickup address information from WooCommerce settings instead of using hardcoded values. This makes the system more maintainable and flexible for stores with changing pickup locations.

## Features

### ✅ Dynamic Data Sources
1. **Order-specific pickup location** - From shipping method instance
2. **Local Pickup shipping method settings** - WooCommerce shipping settings
3. **WooCommerce store address** - Fallback to store address settings
4. **Hardcoded values** - Final fallback for reliability

### ✅ Multiple Pickup Locations Support
- Handles multiple pickup locations if configured
- Displays each location with proper numbering
- Maintains consistent styling across all locations

### ✅ Robust Fallback System
- Graceful degradation when no dynamic data is available
- Maintains existing functionality and styling
- Preserves custom instructions and visual elements

## Implementation Details

### Core Functions

#### `blocksy_child_get_pickup_locations( $order )`
**Purpose**: Retrieves pickup location data from various WooCommerce sources

**Data Sources Priority**:
1. **Shipping Method Instance Settings**
   ```php
   $shipping_method_settings = get_option( 'woocommerce_local_pickup_' . $instance_id . '_settings' );
   ```

2. **Order Meta Data**
   ```php
   $pickup_location_meta = $order->get_meta( '_pickup_location' );
   ```

3. **WooCommerce Store Address**
   ```php
   $address_1 = get_option( 'woocommerce_store_address' );
   $city = get_option( 'woocommerce_store_city' );
   // ... other store address fields
   ```

4. **Hardcoded Fallback**
   ```php
   array(
       'name' => 'Main Warehouse',
       'address' => '23015 N 15th Ave. STE 106, PHOENIX, AZ 85027',
       'instructions' => ''
   )
   ```

**Return Format**:
```php
array(
    array(
        'name' => 'Location Name',
        'address' => 'Full Address String',
        'instructions' => 'Custom pickup instructions'
    )
    // ... additional locations
)
```

#### `blocksy_child_get_shipping_display( $order )`
**Enhanced**: Now uses dynamic pickup location names in shipping display

**Before**: `'Pickup (Main Warehouse) - Free'`
**After**: `'Pickup (' . $location_name . ') - Free'`

#### `blocksy_child_blaze_commerce_addresses_section( $order )`
**Enhanced**: Displays dynamic pickup locations with proper formatting

**Features**:
- Multiple location support with numbering
- Dynamic address formatting with line breaks
- Custom instructions or default message
- Maintains existing CSS styling

## Configuration Options

### WooCommerce Local Pickup Settings
Configure pickup locations in **WooCommerce > Settings > Shipping > Local Pickup**:

- **Title**: Location name (e.g., "Main Warehouse", "Downtown Store")
- **Address Fields**: Street address, city, state, postal code
- **Instructions**: Custom pickup instructions for customers

### Store Address Fallback
If no pickup-specific settings are found, the system uses **WooCommerce > Settings > General**:

- **Store Address**: Primary store address
- **City**: Store city
- **Country/State**: Store location
- **Postcode**: Store postal code

### Order Meta Support
The system supports custom order meta for pickup locations:

```php
// Example: Setting pickup location via order meta
$order->update_meta_data( '_pickup_location', array(
    'name' => 'Custom Pickup Point',
    'address' => '123 Custom Street, City, State 12345',
    'instructions' => 'Ring doorbell twice'
) );
```

## Visual Styling

### Pickup Location Block
- **Background**: Light gray (`#f8f9fa`)
- **Border**: Green left border (`#28a745`)
- **Icon**: 📍 location pin in top-right
- **Instructions**: Yellow warning box styling

### Multiple Locations
- Each location gets its own styled block
- Numbered titles: "Pickup Location 1", "Pickup Location 2", etc.
- Consistent spacing and visual hierarchy

### Responsive Design
- **Desktop**: Two-column layout (billing + pickup)
- **Mobile**: Stacked single-column layout
- **Tablet**: Responsive breakpoints maintained

## Testing Scenarios

### Test Case 1: Local Pickup Method Configured
**Setup**: Configure Local Pickup shipping method with address
**Expected**: Shows configured pickup location name and address
**Fallback**: If incomplete, uses store address

### Test Case 2: Store Address Only
**Setup**: No pickup method configured, store address set
**Expected**: Shows "[Store Name] Store" with store address
**Fallback**: Uses hardcoded values if store address incomplete

### Test Case 3: No Configuration
**Setup**: No pickup settings or store address
**Expected**: Shows hardcoded "Main Warehouse" location
**Behavior**: System remains functional with default values

### Test Case 4: Multiple Pickup Locations
**Setup**: Multiple Local Pickup shipping methods
**Expected**: Shows all locations with proper numbering
**Layout**: Each location in separate styled block

## Maintenance

### Updating Hardcoded Fallback
To change the default fallback location, modify the final fallback in `blocksy_child_get_pickup_locations()`:

```php
$pickup_locations[] = array(
    'name' => 'Your Location Name',
    'address' => 'Your Address Here',
    'instructions' => ''
);
```

### Adding Custom Data Sources
To add additional pickup location sources, extend the function with new data retrieval logic before the fallback section.

### Debugging
Enable WordPress debug logging to troubleshoot pickup location retrieval:

```php
error_log( 'Pickup locations found: ' . print_r( $pickup_locations, true ) );
```

## Compatibility

### WooCommerce Versions
- **Tested**: WooCommerce 8.0+
- **Minimum**: WooCommerce 6.0+
- **Dependencies**: WC_Order object methods

### Third-Party Plugins
- **Local Pickup Plus**: Compatible with extended settings
- **Pickup Location Plugins**: Supports custom meta fields
- **Multi-Vendor**: Works with vendor-specific pickup locations

### Theme Compatibility
- **Blocksy Theme**: Fully integrated
- **Other Themes**: CSS may need adjustment
- **Custom Themes**: Styling classes available for customization

## Future Enhancements

### Potential Improvements
1. **Admin Interface**: Settings page for pickup location management
2. **Google Maps Integration**: Interactive pickup location maps
3. **Pickup Time Slots**: Time-based pickup scheduling
4. **Inventory Integration**: Location-specific stock levels
5. **Notification System**: Pickup-ready email templates

### Plugin Development
Consider developing a dedicated pickup locations plugin for:
- Advanced location management
- Customer pickup preferences
- Location-based inventory
- Pickup analytics and reporting
