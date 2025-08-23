# Blaze Commerce Checkout Block

A fully customizable Gutenberg block that converts the Blaze Commerce checkout plugin into a reusable block template with comprehensive editor controls and accordion functionality.

## Features

- **Gutenberg Block Integration**: Available in Block Inserter under "Blaze Commerce" category
- **Pixel-Perfect Design**: Maintains original plugin styling with full customization options
- **Comprehensive Editor Controls**: Typography, colors, spacing, layout, and content settings
- **Responsive Accordion**: Configurable per viewport (desktop, tablet, mobile)
- **JavaScript Functionality**: All original checkout features preserved
- **Real-time Preview**: See changes instantly in the editor
- **AJAX Integration**: Login, registration, and coupon functionality
- **WooCommerce Compatible**: Full integration with WooCommerce checkout process

## Installation & Setup

### 1. Install Dependencies

Navigate to the block directory and install dependencies:

```bash
cd /path/to/blocksy-child/blocks/blaze-checkout
npm install
```

### 2. Build the Block

Build the block for production:

```bash
npm run build
```

For development with watch mode:

```bash
npm run start
```

### 3. Block Registration

The block is automatically registered through the child theme's `functions.php`. No additional setup required.

## Block Structure

```
blaze-checkout/
├── src/
│   ├── index.js              # Main block registration
│   ├── edit.js               # Editor component with controls
│   ├── save.js               # Save component (returns null for PHP render)
│   ├── style.scss            # Editor styles
│   └── components/
│       └── CheckoutPreview.js # Preview component for editor
├── assets/
│   ├── css/
│   │   └── frontend.css      # Frontend styles
│   └── js/
│       └── frontend.js       # Frontend JavaScript functionality
├── includes/
│   └── helper-functions.php  # PHP helper functions
├── build/                    # Built files (auto-generated)
├── block.json               # Block configuration
├── render.php               # PHP render template
├── package.json             # Dependencies and scripts
└── webpack.config.js        # Build configuration
```

## Block Attributes

### Content Settings
- Main Heading
- Recipients Details Heading
- Order Summary Heading
- Edit Button Text
- Create Account Heading
- Create Account Text
- Optional Text
- Subscription Warning

### Accordion Settings
- Desktop: Enable/Disable, Default Open/Closed
- Tablet: Enable/Disable, Default Open/Closed
- Mobile: Enable/Disable, Default Open/Closed

### Typography Settings
- Main Heading: Font size, weight, line height, color
- Section Heading: Font size, weight, line height, color
- Body Text: Font size, weight, line height, color
- Labels: Font size, weight, line height, color

### Color Settings
- Primary Color
- Secondary Color
- Accent Color
- Error Color
- Success Color
- Border Color
- Background Color

### Spacing Settings
- Section Padding
- Element Margin
- Button Padding
- Input Padding

### Layout Settings
- Max Width
- Column Gap
- Alignment

## Usage

1. **Add Block**: In the Gutenberg editor, click the "+" button and search for "Blaze Commerce Checkout"
2. **Customize**: Use the Inspector Controls in the sidebar to customize all aspects of the checkout
3. **Preview**: See real-time changes in the editor preview
4. **Publish**: The block will render the full checkout functionality on the frontend

## Accordion Functionality

The Order Summary section includes responsive accordion behavior:

- **Desktop**: Open by default (configurable)
- **Tablet**: Closed by default (configurable)
- **Mobile**: Closed by default (configurable)

Each viewport can be configured independently through the block settings.

## JavaScript Features

All original checkout functionality is preserved:

- Email validation
- Guest checkout flow
- User login/registration
- Coupon code application
- Form validation
- Accordion interactions
- Responsive behavior
- WooCommerce integration

## Styling

The block uses CSS custom properties for easy customization:

```css
.blaze-checkout-block {
  --blaze-primary-color: #040711;
  --blaze-accent-color: #007cba;
  --blaze-main-heading-font-size: 48px;
  /* ... and many more */
}
```

All styles can be overridden through the block editor controls or custom CSS.

## PHP Integration

The block integrates seamlessly with WooCommerce:

- Uses WooCommerce checkout object
- Renders actual cart items
- Processes real payments
- Handles shipping methods
- Manages user accounts
- Applies coupons

## AJAX Endpoints

Custom AJAX handlers for enhanced functionality:

- `blaze_checkout_login`: User login
- `blaze_checkout_register`: User registration

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- IE11+ (with polyfills)
- Mobile browsers
- Responsive design for all screen sizes

## Development

### File Structure
- `src/`: Source files for the block editor
- `assets/`: Frontend assets (CSS, JS)
- `includes/`: PHP helper functions
- `build/`: Compiled files (auto-generated)

### Build Commands
- `npm run build`: Production build
- `npm run start`: Development build with watch
- `npm run lint:js`: JavaScript linting
- `npm run lint:css`: CSS linting
- `npm run format`: Code formatting

### Customization
All aspects of the block can be customized through:
1. Block editor controls (recommended)
2. CSS custom properties
3. PHP filters and actions
4. JavaScript hooks

## Troubleshooting

### Block Not Appearing
- Ensure the block is built: `npm run build`
- Check that WooCommerce is active
- Verify the block category is registered

### Styles Not Loading
- Clear any caching plugins
- Check that CSS files are enqueued
- Verify file permissions

### JavaScript Errors
- Check browser console for errors
- Ensure jQuery is loaded
- Verify AJAX endpoints are working

## Support

For support and customization requests, contact the Blaze Commerce team.

## License

GPL-2.0-or-later
