---
title: "Product Carousel Hook Fix"
description: "Fixed React Hook error in Product Carousel Gutenberg block by converting class component to functional component"
category: "guide"
last_updated: "2025-01-16"
tags: [fix, react, gutenberg, hooks]
---

# Overview

Fixed a critical React Hook error in the Product Carousel Gutenberg block that was preventing the block from working properly in the WordPress editor. The error occurred because `useBlockProps` hook was being used inside a class component, which violates React's Rules of Hooks.

# Problem

The original error was:
```
Error: Minified React error #321; visit https://reactjs.org/docs/error-decoder.html?invariant=321 for the full message or use the non-minified dev environment for full hook warnings.
```

This error occurred at line 154-156 in `assets/js/product-carousel-editor.js`:
```javascript
const blockProps = useBlockProps({
    className: 'wp-block-blaze-blocksy-product-carousel'
});
```

The issue was that `useBlockProps` is a React Hook that can only be used in functional components, but the code was using a class component (`ProductCarouselBlock extends Component`).

# Solution

Converted the entire `ProductCarouselBlock` class component to a functional component using React Hooks:

## Key Changes

1. **Component Structure**: Changed from class component to functional component
2. **State Management**: Replaced `this.state` with `useState` hooks
3. **Lifecycle Methods**: Replaced `componentDidMount` with `useEffect` hook
4. **Method Definitions**: Converted class methods to function declarations
5. **Method Calls**: Removed `this.` references for method calls
6. **Props Access**: Changed from `this.props` to direct `props` parameter

## Before (Class Component)
```javascript
class ProductCarouselBlock extends Component {
    constructor(props) {
        super(props);
        this.state = {
            categories: [],
            loading: true,
            error: null
        };
    }

    componentDidMount() {
        this.fetchCategories();
    }

    fetchCategories = async () => {
        // ... implementation
        this.setState({ ... });
    }

    render() {
        const { attributes, setAttributes } = this.props;
        const { error } = this.state;
        // ... rest of render method
    }
}
```

## After (Functional Component)
```javascript
function ProductCarouselBlock(props) {
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchCategories();
    }, []);

    const fetchCategories = async () => {
        // ... implementation
        setCategories(...);
        setLoading(false);
    };

    const { attributes, setAttributes } = props;
    // ... rest of component logic
}
```

# Dependencies

- WordPress Gutenberg block editor
- React Hooks (useState, useEffect, useBlockProps)
- WooCommerce REST API for product categories

# Testing

To verify the fix works:

1. **Editor Test**: Open WordPress admin and create/edit a post
2. **Block Test**: Add the "Product Carousel" block from the WooCommerce category
3. **Functionality Test**: Verify that:
   - Block loads without console errors
   - Category selection works in the sidebar
   - All carousel settings are functional
   - Block preview displays correctly

# Changelog

- **Fixed**: React Hook error #321 by converting class component to functional component
- **Updated**: State management to use React Hooks (useState, useEffect)
- **Improved**: Code follows modern React best practices
- **Maintained**: All existing functionality and features
