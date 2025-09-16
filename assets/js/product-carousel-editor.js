/**
 * Product Carousel Gutenberg Block Editor
 *
 * JavaScript for the block editor interface
 */

(function () {
  "use strict";

  const { registerBlockType } = wp.blocks;
  const { createElement: el, Fragment, useState, useEffect } = wp.element;
  const { InspectorControls, BlockControls, useBlockProps } = wp.blockEditor;
  const {
    PanelBody,
    SelectControl,
    RangeControl,
    ToggleControl,
    CheckboxControl,
    Spinner,
    Notice,
  } = wp.components;
  const { __ } = wp.i18n;
  const { apiFetch } = wp;

  /**
   * Product Carousel Block Component
   */
  function ProductCarouselBlock(props) {
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
      fetchCategories();
    }, []);

    /**
     * Fetch product categories from WordPress API
     */
    const fetchCategories = async () => {
      try {
        const categoriesData = await apiFetch({
          path: "/wp/v2/product_cat?per_page=100&hide_empty=false",
        });

        setCategories(
          categoriesData.map((cat) => ({
            value: cat.id,
            label: cat.name,
          }))
        );
        setLoading(false);
      } catch (error) {
        setError(error.message);
        setLoading(false);
      }
    };

    /**
     * Handle category selection change
     */
    const onCategoryChange = (categoryId, checked) => {
      const { attributes, setAttributes } = props;
      const { selectedCategories } = attributes;

      let newCategories;
      if (checked) {
        newCategories = [...selectedCategories, categoryId];
      } else {
        newCategories = selectedCategories.filter((id) => id !== categoryId);
      }

      setAttributes({ selectedCategories: newCategories });
    };

    /**
     * Handle responsive settings change
     */
    const onResponsiveChange = (device, value) => {
      const { attributes, setAttributes } = props;
      const { productsPerSlide } = attributes;

      setAttributes({
        productsPerSlide: {
          ...productsPerSlide,
          [device]: value,
        },
      });
    };

    /**
     * Render category checkboxes
     */
    const renderCategoryControls = () => {
      const { attributes } = props;
      const { selectedCategories } = attributes;

      if (loading) {
        return el(Spinner);
      }

      return el(
        "div",
        { className: "blaze-category-checkboxes" },
        categories.map((category) =>
          el(CheckboxControl, {
            key: category.value,
            label: category.label,
            checked: selectedCategories.includes(category.value),
            onChange: (checked) => onCategoryChange(category.value, checked),
          })
        )
      );
    };

    /**
     * Render block preview
     */
    const renderPreview = () => {
      return el(
        "div",
        { className: "blaze-product-carousel-preview" },
        el(
          "div",
          { className: "blaze-preview-content" },
          __(
            "Product carousel will appear here. Configure settings in the sidebar.",
            "blaze-blocksy"
          )
        )
      );
    };

    // Main component render
    const { attributes, setAttributes } = props;
    const {
      saleAttribute,
      orderBy,
      productsPerSlide,
      showNavigation,
      showDots,
      autoplay,
      autoplayTimeout,
      loop,
      margin,
      productsLimit,
    } = attributes;

    const blockProps = useBlockProps({
      className: "wp-block-blaze-blocksy-product-carousel",
    });

    return el(
      Fragment,
      {},
      // Block Controls (toolbar)
      el(BlockControls, {}),

      // Inspector Controls (sidebar)
      el(
        InspectorControls,
        {},
        // Product Selection Panel
        el(
          PanelBody,
          {
            title: __("Product Selection", "blaze-blocksy"),
            initialOpen: true,
          },
          // Category Selection
          el(
            "div",
            { className: "blaze-control-group" },
            el(
              "h4",
              { className: "blaze-control-group-title" },
              __("Product Categories", "blaze-blocksy")
            ),
            renderCategoryControls(),
            el(
              "p",
              { className: "blaze-help-text" },
              __(
                "Leave empty to show products from all categories",
                "blaze-blocksy"
              )
            )
          ),

          // Sale Attribute
          el(SelectControl, {
            label: __("Sale Attribute", "blaze-blocksy"),
            value: saleAttribute,
            options: [
              { value: "all", label: __("All Products", "blaze-blocksy") },
              {
                value: "featured",
                label: __("Featured Products", "blaze-blocksy"),
              },
              {
                value: "on_sale",
                label: __("On Sale Products", "blaze-blocksy"),
              },
              {
                value: "new",
                label: __("New Products (Last 30 days)", "blaze-blocksy"),
              },
              {
                value: "in_stock",
                label: __("In Stock Products", "blaze-blocksy"),
              },
              {
                value: "out_of_stock",
                label: __("Out of Stock Products", "blaze-blocksy"),
              },
            ],
            onChange: (value) => setAttributes({ saleAttribute: value }),
          }),

          // Order By
          el(SelectControl, {
            label: __("Order By", "blaze-blocksy"),
            value: orderBy || "date",
            options: [
              { value: "date", label: __("Newest Product", "blaze-blocksy") },
              { value: "name", label: __("Name", "blaze-blocksy") },
              {
                value: "most_selling",
                label: __("Most Selling", "blaze-blocksy"),
              },
              {
                value: "most_popular",
                label: __("Most Popular (by reviews)", "blaze-blocksy"),
              },
            ],
            onChange: (value) => setAttributes({ orderBy: value }),
          }),

          // Products Limit
          el(RangeControl, {
            label: __("Maximum Products", "blaze-blocksy"),
            value: productsLimit,
            onChange: (value) => setAttributes({ productsLimit: value }),
            min: 1,
            max: 50,
            step: 1,
          })
        ),

        // Carousel Settings Panel
        el(
          PanelBody,
          {
            title: __("Carousel Settings", "blaze-blocksy"),
            initialOpen: false,
          },
          // Responsive Settings
          el(
            "div",
            { className: "blaze-control-group" },
            el(
              "h4",
              { className: "blaze-control-group-title" },
              __("Products Per Slide", "blaze-blocksy")
            ),
            el(
              "div",
              { className: "blaze-responsive-controls" },
              el(RangeControl, {
                label: __("Desktop", "blaze-blocksy"),
                value: productsPerSlide.desktop,
                onChange: (value) => onResponsiveChange("desktop", value),
                min: 1,
                max: 8,
                step: 1,
              }),
              el(RangeControl, {
                label: __("Tablet", "blaze-blocksy"),
                value: productsPerSlide.tablet,
                onChange: (value) => onResponsiveChange("tablet", value),
                min: 1,
                max: 6,
                step: 1,
              }),
              el(RangeControl, {
                label: __("Mobile", "blaze-blocksy"),
                value: productsPerSlide.mobile,
                onChange: (value) => onResponsiveChange("mobile", value),
                min: 1,
                max: 4,
                step: 1,
              })
            )
          ),

          // Navigation Controls
          el(ToggleControl, {
            label: __("Show Navigation Arrows", "blaze-blocksy"),
            checked: showNavigation,
            onChange: (value) => setAttributes({ showNavigation: value }),
          }),

          el(ToggleControl, {
            label: __("Show Dots Pagination", "blaze-blocksy"),
            checked: showDots,
            onChange: (value) => setAttributes({ showDots: value }),
          }),

          el(ToggleControl, {
            label: __("Enable Loop", "blaze-blocksy"),
            checked: loop,
            onChange: (value) => setAttributes({ loop: value }),
          }),

          // Autoplay Settings
          el(ToggleControl, {
            label: __("Enable Autoplay", "blaze-blocksy"),
            checked: autoplay,
            onChange: (value) => setAttributes({ autoplay: value }),
          }),

          autoplay &&
            el(RangeControl, {
              label: __("Autoplay Timeout (ms)", "blaze-blocksy"),
              value: autoplayTimeout,
              onChange: (value) => setAttributes({ autoplayTimeout: value }),
              min: 1000,
              max: 10000,
              step: 500,
            }),

          // Spacing
          el(RangeControl, {
            label: __("Margin Between Items (px)", "blaze-blocksy"),
            value: margin,
            onChange: (value) => setAttributes({ margin: value }),
            min: 0,
            max: 50,
            step: 2,
          })
        )
      ),

      // Block Content
      el(
        "div",
        blockProps,
        error &&
          el(
            Notice,
            {
              status: "error",
              isDismissible: false,
            },
            error
          ),

        el(
          "div",
          { className: "blaze-product-carousel-editor-header" },
          el(
            "h3",
            { className: "blaze-product-carousel-editor-title" },
            __("Product Carousel", "blaze-blocksy")
          )
        ),

        renderPreview()
      )
    );
  }

  /**
   * Register the block
   */
  registerBlockType("blaze-blocksy/product-carousel", {
    title: __("Product Carousel", "blaze-blocksy"),
    description: __(
      "Display WooCommerce products in a responsive carousel slider.",
      "blaze-blocksy"
    ),
    icon: "products",
    category: "woocommerce",
    keywords: [
      __("products", "blaze-blocksy"),
      __("carousel", "blaze-blocksy"),
      __("slider", "blaze-blocksy"),
      __("woocommerce", "blaze-blocksy"),
    ],
    supports: {
      align: ["wide", "full"],
      html: false,
    },
    attributes: {
      selectedCategories: {
        type: "array",
        default: [],
      },
      saleAttribute: {
        type: "string",
        default: "all",
      },
      orderBy: {
        type: "string",
        default: "date",
      },
      productsPerSlide: {
        type: "object",
        default: {
          desktop: 4,
          tablet: 3,
          mobile: 2,
        },
      },
      showNavigation: {
        type: "boolean",
        default: true,
      },
      showDots: {
        type: "boolean",
        default: true,
      },
      autoplay: {
        type: "boolean",
        default: false,
      },
      autoplayTimeout: {
        type: "number",
        default: 5000,
      },
      loop: {
        type: "boolean",
        default: false,
      },
      margin: {
        type: "number",
        default: 24,
      },
      productsLimit: {
        type: "number",
        default: 12,
      },
    },
    edit: ProductCarouselBlock,
    save: function () {
      // Server-side rendering, so return null
      return null;
    },
  });
})();
