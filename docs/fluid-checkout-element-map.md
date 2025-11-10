# Fluid Checkout Element Map

## Overview

This document provides a comprehensive map of all Fluid Checkout elements, their CSS selectors, and how they are targeted by the Blocksy Customizer integration.

## CSS Variables

Fluid Checkout uses CSS variables for theming. The customizer controls these variables:

| Variable | Description | Customizer Setting |
|----------|-------------|-------------------|
| `--fluidtheme--color--primary` | Primary brand color | Primary Color |
| `--fluidtheme--color--secondary` | Secondary accent color | Secondary Color |
| `--fluidtheme--color--tertiary` | Tertiary color | Not currently controlled |
| `--fluidtheme--color--body-text` | Body text color | Body Text Color |
| `--fluidtheme--color--heading` | Heading text color | Heading Color |
| `--fluidtheme--color--link` | Link color | Link Color |
| `--fluidtheme--color--link--hover` | Link hover color | Link Hover Color |
| `--fluidtheme--color--content-background` | Content area background | Content Background |
| `--fluidtheme--color--border` | Border color | Border Color |

## Typography Elements

### Headings

**Selectors:**
```css
.woocommerce-checkout h1
.woocommerce-checkout h2
.woocommerce-checkout h3
.fc-step__title
```

**Customizer Controls:**
- Font Family
- Font Size
- Font Color
- Font Weight

**Common Usage:**
- Step titles (e.g., "Contact", "Shipping", "Payment")
- Section headings
- Order summary title

### Body Text

**Selectors:**
```css
.woocommerce-checkout
.woocommerce-checkout p
.woocommerce-checkout span
```

**Customizer Controls:**
- Font Family
- Font Size
- Font Color
- Font Weight

**Common Usage:**
- General text content
- Descriptions
- Helper text

### Form Labels

**Selectors:**
```css
.woocommerce-checkout label
.form-row label
```

**Customizer Controls:**
- Font Family
- Font Size
- Font Color
- Font Weight

**Common Usage:**
- Field labels (e.g., "Email address", "First name")
- Checkbox labels
- Radio button labels

### Placeholder Text

**Selectors:**
```css
.woocommerce-checkout input::placeholder
.woocommerce-checkout textarea::placeholder
```

**Customizer Controls:**
- Font Family
- Font Size
- Font Color
- Font Weight

**Common Usage:**
- Input field placeholders
- Textarea placeholders

### Button Text

**Selectors:**
```css
.woocommerce-checkout button
.woocommerce-checkout .button
```

**Customizer Controls:**
- Font Family
- Font Size
- Font Color
- Font Weight

**Common Usage:**
- "Continue to shipping" button
- "Place order" button
- "Apply coupon" button

## Form Elements

### Text Inputs

**Selectors:**
```css
.woocommerce-checkout input[type="text"]
.woocommerce-checkout input[type="email"]
.woocommerce-checkout input[type="tel"]
.woocommerce-checkout input[type="password"]
```

**Customizer Controls:**
- Background Color
- Border Color
- Text Color
- Focus Border Color
- Padding
- Border Radius

**Common Usage:**
- Email address field
- Name fields
- Address fields
- Phone number field

### Textareas

**Selectors:**
```css
.woocommerce-checkout textarea
```

**Customizer Controls:**
- Background Color
- Border Color
- Text Color
- Focus Border Color
- Padding
- Border Radius

**Common Usage:**
- Order notes
- Additional information

### Select Dropdowns

**Selectors:**
```css
.woocommerce-checkout select
```

**Customizer Controls:**
- Background Color
- Border Color
- Text Color
- Focus Border Color
- Padding
- Border Radius

**Common Usage:**
- Country selector
- State/Province selector
- Shipping method selector

### Checkboxes

**Selectors:**
```css
.woocommerce-checkout input[type="checkbox"]
```

**Customizer Controls:**
- Currently inherits form element styles

**Common Usage:**
- "Create an account?" checkbox
- "Ship to a different address?" checkbox
- Terms and conditions checkbox

### Radio Buttons

**Selectors:**
```css
.woocommerce-checkout input[type="radio"]
```

**Customizer Controls:**
- Currently inherits form element styles

**Common Usage:**
- Payment method selection
- Shipping method selection

## Buttons

### Primary Buttons

**Selectors:**
```css
.woocommerce-checkout button.button
.woocommerce-checkout .button
.woocommerce-checkout input[type="submit"]
.woocommerce-checkout #place_order
```

**Customizer Controls:**
- Background Color
- Text Color
- Hover Background Color
- Hover Text Color
- Padding (Top, Right, Bottom, Left)
- Border Radius

**Common Usage:**
- "Continue to shipping" button
- "Continue to payment" button
- "Place order" button
- "Apply coupon" button

### Secondary Buttons

**Selectors:**
```css
.woocommerce-checkout .button.alt
.woocommerce-checkout .button--secondary
```

**Customizer Controls:**
- Currently uses primary button styles

**Common Usage:**
- "Return to cart" link
- "Edit" buttons

## Layout Sections

### Checkout Steps

**Selectors:**
```css
.woocommerce-checkout .fc-step
```

**Customizer Controls:**
- Padding (Top, Right, Bottom, Left)
- Margin Bottom
- Border Width
- Border Color
- Border Style
- Border Radius

**Common Usage:**
- Contact information step
- Shipping address step
- Shipping method step
- Payment method step

### Cart Section

**Selectors:**
```css
.woocommerce-checkout .fc-cart-section
```

**Customizer Controls:**
- Padding (Top, Right, Bottom, Left)
- Margin Bottom
- Border Width
- Border Color
- Border Style
- Border Radius

**Common Usage:**
- Cart items display
- Order summary sidebar

### Order Review

**Selectors:**
```css
.woocommerce-checkout .woocommerce-checkout-review-order
```

**Customizer Controls:**
- Padding (Top, Right, Bottom, Left)
- Border Width
- Border Color
- Border Style
- Border Radius

**Common Usage:**
- Order totals
- Subtotal, shipping, tax breakdown
- Total amount

## Spacing Elements

### Form Rows

**Selectors:**
```css
.woocommerce-checkout .form-row
```

**Customizer Controls:**
- Margin Bottom (Field Gap)

**Common Usage:**
- Individual form field containers
- Spacing between fields

### Section Spacing

**Selectors:**
```css
.woocommerce-checkout .fc-step
.woocommerce-checkout .fc-cart-section
```

**Customizer Controls:**
- Margin Bottom (Section Margin Bottom)

**Common Usage:**
- Space between checkout steps
- Space between major sections

## Additional Elements

### Progress Bar

**Selectors:**
```css
.fc-progress-bar
.fc-progress-bar__step
.fc-progress-bar__step--completed
.fc-progress-bar__step--current
```

**Customizer Controls:**
- Not currently controlled (future enhancement)

**Common Usage:**
- Visual progress indicator
- Step completion status

### Error Messages

**Selectors:**
```css
.woocommerce-error
.woocommerce-checkout .woocommerce-error
```

**Customizer Controls:**
- Not currently controlled (future enhancement)

**Common Usage:**
- Validation errors
- Payment errors
- Shipping errors

### Success Messages

**Selectors:**
```css
.woocommerce-message
.woocommerce-checkout .woocommerce-message
```

**Customizer Controls:**
- Not currently controlled (future enhancement)

**Common Usage:**
- Coupon applied successfully
- Account created successfully

### Info Messages

**Selectors:**
```css
.woocommerce-info
.woocommerce-checkout .woocommerce-info
```

**Customizer Controls:**
- Not currently controlled (future enhancement)

**Common Usage:**
- Login prompt
- Returning customer notice

## Future Enhancements

The following elements could be added to the customizer in future versions:

1. **Progress Bar Styling**
   - Step indicator colors
   - Completed step color
   - Current step color
   - Progress bar height

2. **Message Styling**
   - Error message colors
   - Success message colors
   - Info message colors
   - Message padding and borders

3. **Advanced Form Elements**
   - Checkbox custom styling
   - Radio button custom styling
   - Select dropdown arrow styling

4. **Responsive Spacing**
   - Mobile-specific padding
   - Tablet-specific spacing
   - Desktop-specific layout

5. **Animation Controls**
   - Transition speeds
   - Hover effects
   - Focus effects

## Notes

- All selectors use `.woocommerce-checkout` as a prefix to ensure styles only apply to the checkout page
- The `!important` flag is used to override theme defaults
- CSS variables are set on `:root` for global application
- Live preview is supported for most settings via postMessage transport

