(function ($) {
  $(document).ready(function () {
    // check if element .product[data-products] exists
    if ($(".products[data-products]").length) {
      // find all elements with class .product then loop
      $(".products[data-products] .product").each(function () {
        // check if element .ct-variation-swatches exists
        if ($(this).find(".ct-variation-swatches").length) {
          $(this)
            .find(".ct-variation-swatches")
            .each(function () {
              // count how many .ct-swatch-container exists
              var count = $(this).find(".ct-swatch-container").length;
              // if count is more than 3 then add button "view more" inside .ct-variation-swatches and hide the rest;
              if (count > 3) {
                $(this).append(
                  `<button class="view-more-variations">${count - 3}+</button>`
                );
                // $(this).find(".ct-swatch-container:gt(2)").hide();
              }
            });
        }
      });
    }

    $(document).on("click", ".view-more-variations", function () {
      $(this).hide();
      $(this)
        .parent()
        .find(".ct-swatch-container:gt(2)")
        .css("display", "flex");
    });
  });
})(jQuery);
