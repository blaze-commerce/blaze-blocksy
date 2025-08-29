const ctProductInformationOffCanvas = document.getElementById(
  "ct-product-information-offcanvas"
);
const ctProductInformationOffCanvasOverlay = document.getElementById(
  "ct-product-information-offcanvas-overlay"
);

function openOffcanvas(tab) {
  const overlay = ctProductInformationOffCanvasOverlay;
  const offcanvas = ctProductInformationOffCanvas;

  overlay.classList.add("active");
  offcanvas.classList.add("active");

  // Switch to the appropriate tab
  switchTab(tab);
}

function closeOffcanvas() {
  const overlay = ctProductInformationOffCanvasOverlay;
  const offcanvas = ctProductInformationOffCanvas;

  overlay.classList.remove("active");
  offcanvas.classList.remove("active");
}

function switchTab(tabName) {
  // Remove active class from all tabs and contents
  const tabs = document.querySelectorAll(".offcanvas-tab");
  const contents = document.querySelectorAll(".tab-content");

  tabs.forEach((tab) => tab.classList.remove("active"));
  contents.forEach((content) => content.classList.remove("active"));

  // Add active class to selected tab and content
  const selectedTab = document.querySelector(`[data-tab="${tabName}"]`);
  const selectedContent = document.getElementById(`${tabName}-content`);

  if (selectedTab) selectedTab.classList.add("active");
  if (selectedContent) selectedContent.classList.add("active");
}

// Add click event listeners to tabs
document.querySelectorAll(".offcanvas-tab").forEach((tab) => {
  tab.addEventListener("click", function () {
    const tabName = this.getAttribute("data-tab");
    switchTab(tabName);
  });
});

// Close offcanvas when pressing Escape key
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") {
    closeOffcanvas();
  }
});
