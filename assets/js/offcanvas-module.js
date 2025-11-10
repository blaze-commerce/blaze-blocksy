/**
 * Generic Off-Canvas Module JavaScript - Blocksy Compatible
 *
 * Handles off-canvas panel interactions for Blocksy Child Theme.
 * Follows Blocksy's standard offcanvas implementation with proper state management.
 *
 * @package BlocksyChild
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  /**
   * Off-Canvas Module Manager
   */
  const OffcanvasModule = {
    /**
     * Currently active panel
     */
    activePanel: null,

    /**
     * Initialize all offcanvas instances
     */
    init: function () {
      this.setupTriggers();
      this.setupCloseHandlers();
      this.setupAjaxHandlers();
    },

    /**
     * Setup trigger click handlers - Blocksy Standard
     */
    setupTriggers: function () {
      // Blocksy standard: data-toggle-panel attribute
      $(document).on("click", "[data-toggle-panel]", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const panelSelector = $(this).data("toggle-panel");
        const panelId = panelSelector.replace("#", "");
        const panel = document.getElementById(panelId);

        if (panel && $(panel).hasClass("ct-panel")) {
          OffcanvasModule.openPanel(panelId, this);
        }

        return false;
      });

      // Setup custom triggers from config
      if (
        typeof offcanvasModuleConfig !== "undefined" &&
        offcanvasModuleConfig.instances
      ) {
        Object.keys(offcanvasModuleConfig.instances).forEach(function (
          instanceId
        ) {
          const instance = offcanvasModuleConfig.instances[instanceId];

          if (instance.triggerSelector) {
            $(document).on("click", instance.triggerSelector, function (e) {
              e.preventDefault();
              e.stopPropagation();
              OffcanvasModule.openPanel(instanceId + "-panel", this);
              return false;
            });
          }
        });
      }
    },

    /**
     * Setup close button handlers - Blocksy Standard
     */
    setupCloseHandlers: function () {
      // Close button click
      $(document).on("click", ".ct-panel .ct-toggle-close", function (e) {
        e.preventDefault();
        const panel = $(this).closest(".ct-panel");
        OffcanvasModule.closePanel(panel.attr("id"));
      });

      // ESC key to close
      $(document).on("keydown", function (e) {
        if (e.key === "Escape" || e.keyCode === 27) {
          if (OffcanvasModule.activePanel) {
            OffcanvasModule.closePanel(OffcanvasModule.activePanel);
          }
        }
      });

      // Click outside to close
      $(document).on("click", ".ct-panel.active", function (e) {
        if (e.target === this) {
          OffcanvasModule.closePanel($(this).attr("id"));
        }
      });
    },

    /**
     * Setup AJAX content loading handlers
     */
    setupAjaxHandlers: function () {
      if (typeof offcanvasModuleConfig === "undefined") {
        return;
      }

      // Listen for custom refresh events
      $(document).on("offcanvas:refresh", function (e, panelId) {
        OffcanvasModule.refreshContent(panelId);
      });
    },

    /**
     * Open an offcanvas panel - Blocksy Standard State Management
     *
     * @param {string} panelId - Panel element ID
     * @param {HTMLElement} trigger - Trigger element (optional)
     */
    openPanel: function (panelId, trigger) {
      const panel = document.getElementById(panelId);

      if (!panel) {
        console.warn("Offcanvas panel not found:", panelId);
        return;
      }

      // Close any currently open panel
      if (
        OffcanvasModule.activePanel &&
        OffcanvasModule.activePanel !== panelId
      ) {
        OffcanvasModule.closePanel(OffcanvasModule.activePanel);
      }

      // Load AJAX content if configured
      const instanceId = panelId.replace("-panel", "");
      if (
        typeof offcanvasModuleConfig !== "undefined" &&
        offcanvasModuleConfig.instances &&
        offcanvasModuleConfig.instances[instanceId] &&
        offcanvasModuleConfig.instances[instanceId].ajaxAction
      ) {
        this.loadAjaxContent(instanceId);
      }

      // Blocksy Standard: Set body data-panel attribute for state management
      const behaviour = panel.getAttribute("data-behaviour") || "right-side";
      const direction = behaviour.includes("left")
        ? ":left"
        : behaviour === "modal"
        ? ""
        : ":right";

      // Step 1: Set initial state (opening)
      document.body.setAttribute("data-panel", "");
      panel.classList.add("active");
      panel.removeAttribute("inert");

      // Step 2: Use requestAnimationFrame for smooth GPU-accelerated animation
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          // Set final state (opened)
          document.body.setAttribute("data-panel", "in" + direction);
        });
      });

      // Update ARIA attributes
      if (trigger) {
        trigger.setAttribute("aria-expanded", "true");
      }

      // Store active panel
      OffcanvasModule.activePanel = panelId;

      // Trigger custom event
      $(document).trigger("offcanvas:opened", [panelId]);
    },

    /**
     * Close an offcanvas panel - Blocksy Standard State Management
     *
     * @param {string} panelId - Panel element ID
     */
    closePanel: function (panelId) {
      const panel = document.getElementById(panelId);

      if (!panel) {
        return;
      }

      // Blocksy Standard: Set closing state
      document.body.setAttribute("data-panel", "out");

      // Wait for animation to complete (0.25s as per Blocksy standard)
      setTimeout(() => {
        // Remove state and clean up
        document.body.removeAttribute("data-panel");
        panel.classList.remove("active");
        panel.setAttribute("inert", "");

        // Update ARIA attributes on triggers
        const triggers = document.querySelectorAll(
          '[data-toggle-panel="#' + panelId + '"]'
        );
        triggers.forEach((trigger) => {
          trigger.setAttribute("aria-expanded", "false");
        });

        // Clear active panel
        if (OffcanvasModule.activePanel === panelId) {
          OffcanvasModule.activePanel = null;
        }

        // Trigger custom event
        $(document).trigger("offcanvas:closed", [panelId]);
      }, 250); // 0.25s animation duration
    },

    /**
     * Load content via AJAX
     *
     * @param {string} instanceId - Instance identifier
     */
    loadAjaxContent: function (instanceId) {
      if (typeof offcanvasModuleConfig === "undefined") {
        return;
      }

      const instance = offcanvasModuleConfig.instances[instanceId];
      if (!instance || !instance.ajaxAction) {
        return;
      }

      const panelId = instanceId + "-panel";
      const $panel = $("#" + panelId);
      const $content = $panel.find(".ct-panel-content-inner");

      // Add loading state
      $content.addClass("loading");

      $.ajax({
        url: offcanvasModuleConfig.ajaxUrl,
        type: "POST",
        data: {
          action: instance.ajaxAction,
          nonce: offcanvasModuleConfig.nonce,
        },
        success: function (response) {
          if (response.success && response.data.content) {
            $content.html(response.data.content);

            // Update count if provided
            if (typeof response.data.count !== "undefined") {
              $panel
                .find(".offcanvas-count")
                .text("(" + response.data.count + ")");
            }

            // Trigger custom event
            $(document).trigger("offcanvas:content-loaded", [
              panelId,
              response.data,
            ]);
          }
        },
        error: function (xhr, status, error) {
          console.error("Failed to load offcanvas content:", error);
          $content.html("<p>Failed to load content. Please try again.</p>");
        },
        complete: function () {
          $content.removeClass("loading");
        },
      });
    },

    /**
     * Refresh panel content
     *
     * @param {string} panelId - Panel element ID
     */
    refreshContent: function (panelId) {
      const instanceId = panelId.replace("-panel", "");
      this.loadAjaxContent(instanceId);
    },

    /**
     * Update panel count badge
     *
     * @param {string} panelId - Panel element ID
     * @param {number} count - New count value
     */
    updateCount: function (panelId, count) {
      const $panel = $("#" + panelId);
      const $countBadge = $panel.find(".offcanvas-count");

      if ($countBadge.length) {
        $countBadge.text("(" + count + ")");
      }
    },
  };

  /**
   * Initialize on document ready
   */
  $(document).ready(function () {
    OffcanvasModule.init();
  });

  /**
   * Expose to global scope for external access
   */
  window.OffcanvasModule = OffcanvasModule;
})(jQuery);
