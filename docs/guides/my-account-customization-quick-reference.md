---
title: "My Account Customization Quick Reference"
description: "Quick reference guide for all available My Account form customization options"
category: "guide"
last_updated: "2025-01-16"
framework: "wordpress"
domain: "user-management"
layer: "frontend"
tags: [quick-reference, customizer, my-account]
---

# Overview

Quick reference guide for all available customization options in the WooCommerce My Account form system.

# Accessing Customizer

**Path**: `WordPress Admin > Appearance > Customize > My Account Form`

# Available Sections

## 1. Template Selection
- **Default WooCommerce**: Standard WooCommerce forms
- **Template 1**: Side-by-side login/register layout
- **Template 2**: Centered layout with toggle

## 2. Typography Controls

### Heading Typography
- Font Family, Size, Color, Weight, Text Transform

### Body Text Typography  
- Font Family, Size, Color, Weight, Text Transform

### Placeholder Typography
- Font Family, Size, Color, Weight, Text Transform

### Button Typography
- Font Family, Size, Color, Weight, Text Transform

## 3. Color Customization

### Button Colors
- Background Color
- Text Color
- Hover Background Color
- Hover Text Color

### Input Colors
- Background Color
- Border Color
- Text Color

### Form Elements (NEW)
- Checkbox Border Color
- Required Field Asterisk Color

### Account Navigation (NEW)
- Border Color
- Text Color
- Active/Hover Background Color

## 4. Button Styling (Desktop)

### Padding Controls
- Top Padding
- Right Padding
- Bottom Padding
- Left Padding

### Border Radius Controls
- Button Border Radius
- Column Border Radius (All Templates)

## 5. Footer Text (NEW)
- Desktop Font Size
- Mobile Font Size

## 6. Responsive Controls

### Tablet (768px - 1023px)
- Font Size overrides for all elements
- Font Weight overrides for all elements

### Mobile (< 768px)
- Font Size overrides for all elements
- Font Weight overrides for all elements

# Default Values Reference

## Colors
| Element | Default Value |
|---------|---------------|
| Button Background | `#007cba` |
| Button Text | `#ffffff` |
| Button Hover Background | `#005a87` |
| Button Hover Text | `#ffffff` |
| Input Background | `#ffffff` |
| Input Border | `#dddddd` |
| Input Text | `#333333` |
| Checkbox Border | `#CDD1D4` |
| Required Field | `#ff0000` |
| Nav Border | `#CDD1D4` |
| Nav Text | `#242424` |
| Nav Active/Hover | `#be252f` |

## Spacing & Sizing
| Element | Default Value |
|---------|---------------|
| Button Top Padding | `12px` |
| Button Right Padding | `24px` |
| Button Bottom Padding | `12px` |
| Button Left Padding | `24px` |
| Button Border Radius | `3px` |
| Column Border Radius | `12px` |
| Footer Font Size (Desktop) | `14px` |
| Footer Font Size (Mobile) | `12px` |

## Typography Defaults
| Element | Font Family | Font Size | Color | Weight | Transform |
|---------|-------------|-----------|-------|---------|-----------|
| Heading | `inherit` | `24px` | `#333333` | `600` | `none` |
| Body | `inherit` | `16px` | `#666666` | `400` | `none` |
| Placeholder | `inherit` | `14px` | `#999999` | `400` | `none` |
| Button | `inherit` | `16px` | `#ffffff` | `500` | `none` |

# Live Preview Features

All customization options include real-time live preview:
- ✅ Instant visual updates
- ✅ No page refresh required
- ✅ Template-aware changes
- ✅ Responsive preview support

# CSS Units Supported

For size and spacing controls, you can use:
- `px` (pixels) - e.g., `16px`
- `rem` (relative to root) - e.g., `1rem`
- `em` (relative to parent) - e.g., `1.2em`
- `%` (percentage) - e.g., `100%`

# Template-Specific Features

## Template 1 (Side-by-Side)
- Column border radius control
- Responsive stacking on mobile
- Individual column styling

## Template 2 (Centered)
- Toggle between login/register
- Centered layout design
- Interactive form switching

## Default WooCommerce
- Standard WooCommerce styling
- No custom controls applied
- Fallback option

# Best Practices

## Color Accessibility
- Ensure sufficient contrast ratios
- Test with color blindness simulators
- Use WCAG AA compliant colors

## Typography
- Maintain readable font sizes (minimum 14px)
- Use web-safe font families
- Consider loading performance

## Responsive Design
- Test on multiple device sizes
- Use relative units when appropriate
- Ensure mobile usability

## Performance
- Minimize custom CSS output
- Use browser-compatible properties
- Test loading speeds

# Troubleshooting

## Common Issues

### Changes Not Appearing
1. Clear browser cache
2. Check if correct template is selected
3. Verify customizer preview is active

### Mobile Styles Not Working
1. Check responsive section settings
2. Verify media query support
3. Test on actual mobile devices

### Colors Not Updating
1. Ensure valid hex color format
2. Check for CSS conflicts
3. Verify live preview is enabled

# Related Documentation

- [My Account Customization Guide](MY-ACCOUNT-CUSTOMIZATION.md)
- [Advanced Customization Features](my-account-advanced-customization.md)
- [Button Border Radius Feature](my-account-button-border-radius.md)
