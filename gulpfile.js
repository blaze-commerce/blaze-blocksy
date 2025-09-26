/**
 * Gulpfile for Blocksy Child Theme - Blaze Commerce Edition
 *
 * Build system for optimized CSS and JS bundles with conditional loading
 *
 * @author BlazeCommerce
 * @version 1.0.0
 */

const gulp = require("gulp");
const concat = require("gulp-concat");
const uglify = require("gulp-uglify");
const terser = require("gulp-terser");
const cleanCSS = require("gulp-clean-css");
const sourcemaps = require("gulp-sourcemaps");
const rename = require("gulp-rename");
const autoprefixer = require("gulp-autoprefixer");
const gulpif = require("gulp-if");
const plumber = require("gulp-plumber");
const notify = require("gulp-notify");
const del = require("del");
const browserSync = require("browser-sync").create();
const size = require("gulp-size");
const header = require("gulp-header");

// Environment detection
const isProduction = process.env.NODE_ENV === "production";

// JavaScript minification configuration helper
const getJSMinifyConfig = () => ({
  compress: {
    drop_console: isProduction,
  },
  mangle: {
    reserved: ["jQuery", "$", "window", "document"],
  },
  output: {
    comments: false,
  },
});

// Banner for minified files
const banner = [
  "/**",
  " * <%= pkg.name %> - <%= pkg.description %>",
  " * @version <%= pkg.version %>",
  " * @author <%= pkg.author %>",
  " * Built on <%= new Date() %>",
  " */",
  "",
].join("\n");

// Package info for banner
const pkg = require("./package.json");

// Error handler
const errorHandler = {
  errorHandler: notify.onError({
    title: "Gulp Error",
    message: "Error: <%= error.message %>",
  }),
};

// Paths configuration
const paths = {
  src: {
    css: {
      critical: ["assets/src/critical/css/**/*.css"],
      global: [
        "assets/src/frontend/css/components/footer.css",
        "assets/src/frontend/css/components/search.css",
        "assets/src/frontend/css/base/**/*.css",
      ],
      woocommerce: [
        "assets/src/frontend/css/pages/archive.css",
        "assets/src/frontend/css/pages/single-product.css",
        "assets/src/frontend/css/pages/checkout.css",
        "assets/src/frontend/css/pages/my-account.css",
        "assets/src/frontend/css/pages/thank-you.css",
        "assets/src/frontend/css/components/product-card.css",
        "assets/src/frontend/css/components/product-carousel.css",
        "assets/src/frontend/css/components/mini-cart.css",
      ],
      features: [
        "assets/src/frontend/css/features/wishlist.css",
        "assets/src/frontend/css/features/mix-match.css",
        "assets/src/frontend/css/features/custom.css",
      ],
      admin: ["assets/src/admin/css/**/*.css"],
    },
    js: {
      critical: ["assets/src/critical/js/**/*.js"],
      global: [
        "assets/src/frontend/js/components/general.js",
        "assets/src/frontend/js/components/minicart-control.js",
      ],
      woocommerce: [
        "assets/src/frontend/js/pages/archive.js",
        "assets/src/frontend/js/pages/single-product.js",
        "assets/src/frontend/js/pages/checkout.js",
        "assets/src/frontend/js/pages/my-account.js",
        "assets/src/frontend/js/pages/thank-you.js",
        "assets/src/frontend/js/pages/thank-you-inline.js",
        "assets/src/frontend/js/components/mini-cart.js",
      ],
      features: [
        "assets/src/frontend/js/features/wishlist-offcanvas.js",
        "assets/src/frontend/js/features/mix-and-match-products.js",
      ],
      admin: [
        "assets/src/admin/js/admin/my-account-admin.js",
        "assets/src/admin/js/editor/product-carousel-editor.js",
      ],
      customizer: [
        "assets/src/admin/js/customizer/customizer-preview.js",
        "assets/src/admin/js/customizer/my-account-customizer-preview.js",
        "assets/src/admin/js/customizer/wishlist-offcanvas-sync.js",
        "assets/src/admin/js/customizer/wishlist-offcanvas-variables.js",
      ],
    },
  },
  dist: {
    css: "assets/dist/css",
    js: "assets/dist/js",
    maps: "assets/maps",
  },
  watch: {
    css: "assets/src/**/*.css",
    js: "assets/src/**/*.js",
  },
};

// Clean task
gulp.task("clean", () => {
  return del(["assets/dist/**/*", "assets/maps/**/*"]);
});

// CSS Tasks
gulp.task("css:critical", () => {
  return gulp
    .src(paths.src.css.critical)
    .pipe(plumber(errorHandler))
    .pipe(gulpif(!isProduction, sourcemaps.init()))
    .pipe(concat("critical.css"))
    .pipe(
      autoprefixer({
        cascade: false,
      })
    )
    .pipe(gulpif(!isProduction, sourcemaps.write("../maps/css")))
    .pipe(gulp.dest(paths.dist.css))
    .pipe(
      cleanCSS({
        level: 2,
        compatibility: "ie8",
      })
    )
    .pipe(header(banner, { pkg: pkg }))
    .pipe(rename({ suffix: ".min" }))
    .pipe(size({ showFiles: true, title: "Critical CSS" }))
    .pipe(gulp.dest(paths.dist.css))
    .pipe(browserSync.stream());
});

gulp.task("css:global", () => {
  return gulp
    .src(paths.src.css.global)
    .pipe(plumber(errorHandler))
    .pipe(gulpif(!isProduction, sourcemaps.init()))
    .pipe(concat("global.css"))
    .pipe(
      autoprefixer({
        cascade: false,
      })
    )
    .pipe(gulpif(!isProduction, sourcemaps.write("../maps/css")))
    .pipe(gulp.dest(paths.dist.css))
    .pipe(
      cleanCSS({
        level: 2,
        compatibility: "ie8",
      })
    )
    .pipe(header(banner, { pkg: pkg }))
    .pipe(rename({ suffix: ".min" }))
    .pipe(size({ showFiles: true, title: "Global CSS" }))
    .pipe(gulp.dest(paths.dist.css))
    .pipe(browserSync.stream());
});

gulp.task("css:woocommerce", () => {
  return gulp
    .src(paths.src.css.woocommerce)
    .pipe(plumber(errorHandler))
    .pipe(gulpif(!isProduction, sourcemaps.init()))
    .pipe(concat("woocommerce.css"))
    .pipe(
      autoprefixer({
        cascade: false,
      })
    )
    .pipe(gulpif(!isProduction, sourcemaps.write("../maps/css")))
    .pipe(gulp.dest(paths.dist.css))
    .pipe(
      cleanCSS({
        level: 2,
        compatibility: "ie8",
      })
    )
    .pipe(header(banner, { pkg: pkg }))
    .pipe(rename({ suffix: ".min" }))
    .pipe(size({ showFiles: true, title: "WooCommerce CSS" }))
    .pipe(gulp.dest(paths.dist.css))
    .pipe(browserSync.stream());
});

gulp.task("css:features", () => {
  return gulp
    .src(paths.src.css.features)
    .pipe(plumber(errorHandler))
    .pipe(gulpif(!isProduction, sourcemaps.init()))
    .pipe(concat("features.css"))
    .pipe(
      autoprefixer({
        cascade: false,
      })
    )
    .pipe(gulpif(!isProduction, sourcemaps.write("../maps/css")))
    .pipe(gulp.dest(paths.dist.css))
    .pipe(
      cleanCSS({
        level: 2,
        compatibility: "ie8",
      })
    )
    .pipe(header(banner, { pkg: pkg }))
    .pipe(rename({ suffix: ".min" }))
    .pipe(size({ showFiles: true, title: "Features CSS" }))
    .pipe(gulp.dest(paths.dist.css))
    .pipe(browserSync.stream());
});

gulp.task("css:admin", () => {
  return gulp
    .src(paths.src.css.admin)
    .pipe(plumber(errorHandler))
    .pipe(gulpif(!isProduction, sourcemaps.init()))
    .pipe(concat("admin.css"))
    .pipe(
      autoprefixer({
        cascade: false,
      })
    )
    .pipe(gulpif(!isProduction, sourcemaps.write("../maps/css")))
    .pipe(gulp.dest(paths.dist.css))
    .pipe(
      cleanCSS({
        level: 2,
        compatibility: "ie8",
      })
    )
    .pipe(header(banner, { pkg: pkg }))
    .pipe(rename({ suffix: ".min" }))
    .pipe(size({ showFiles: true, title: "Admin CSS" }))
    .pipe(gulp.dest(paths.dist.css))
    .pipe(browserSync.stream());
});

// Combine all CSS tasks
gulp.task(
  "css",
  gulp.parallel(
    "css:critical",
    "css:global",
    "css:woocommerce",
    "css:features",
    "css:admin"
  )
);

// JavaScript Tasks
gulp.task("js:critical", () => {
  return gulp
    .src(paths.src.js.critical)
    .pipe(plumber(errorHandler))
    .pipe(gulpif(!isProduction, sourcemaps.init()))
    .pipe(concat("critical.js"))
    .pipe(gulpif(!isProduction, sourcemaps.write("../maps/js")))
    .pipe(gulp.dest(paths.dist.js))
    .pipe(
      gulpif(
        isProduction,
        uglify(getJSMinifyConfig()).on("error", function (err) {
          console.error("Uglify error:", err.message);
          this.emit("end");
        })
      )
    )
    .pipe(header(banner, { pkg: pkg }))
    .pipe(rename({ suffix: ".min" }))
    .pipe(size({ showFiles: true, title: "Critical JS" }))
    .pipe(gulp.dest(paths.dist.js))
    .pipe(browserSync.stream());
});

gulp.task("js:global", () => {
  return gulp
    .src(paths.src.js.global)
    .pipe(plumber(errorHandler))
    .pipe(gulpif(!isProduction, sourcemaps.init()))
    .pipe(concat("global.js"))
    .pipe(gulpif(!isProduction, sourcemaps.write("../maps/js")))
    .pipe(gulp.dest(paths.dist.js))
    .pipe(
      gulpif(
        isProduction,
        uglify(getJSMinifyConfig()).on("error", function (err) {
          console.error("Uglify error:", err.message);
          this.emit("end");
        })
      )
    )
    .pipe(header(banner, { pkg: pkg }))
    .pipe(rename({ suffix: ".min" }))
    .pipe(size({ showFiles: true, title: "Global JS" }))
    .pipe(gulp.dest(paths.dist.js))
    .pipe(browserSync.stream());
});

gulp.task("js:woocommerce", () => {
  return gulp
    .src(paths.src.js.woocommerce)
    .pipe(plumber(errorHandler))
    .pipe(gulpif(!isProduction, sourcemaps.init()))
    .pipe(concat("woocommerce.js"))
    .pipe(gulpif(!isProduction, sourcemaps.write("../maps/js")))
    .pipe(gulp.dest(paths.dist.js))
    .pipe(
      gulpif(
        isProduction,
        uglify(getJSMinifyConfig()).on("error", function (err) {
          console.error("Uglify error:", err.message);
          this.emit("end");
        })
      )
    )
    .pipe(header(banner, { pkg: pkg }))
    .pipe(rename({ suffix: ".min" }))
    .pipe(size({ showFiles: true, title: "WooCommerce JS" }))
    .pipe(gulp.dest(paths.dist.js))
    .pipe(browserSync.stream());
});

gulp.task("js:features", () => {
  return gulp
    .src(paths.src.js.features)
    .pipe(plumber(errorHandler))
    .pipe(gulpif(!isProduction, sourcemaps.init()))
    .pipe(concat("features.js"))
    .pipe(gulpif(!isProduction, sourcemaps.write("../maps/js")))
    .pipe(gulp.dest(paths.dist.js))
    .pipe(
      gulpif(
        isProduction,
        uglify(getJSMinifyConfig()).on("error", function (err) {
          console.error("Uglify error:", err.message);
          this.emit("end");
        })
      )
    )
    .pipe(header(banner, { pkg: pkg }))
    .pipe(rename({ suffix: ".min" }))
    .pipe(size({ showFiles: true, title: "Features JS" }))
    .pipe(gulp.dest(paths.dist.js))
    .pipe(browserSync.stream());
});

gulp.task("js:admin", () => {
  return gulp
    .src(paths.src.js.admin)
    .pipe(plumber(errorHandler))
    .pipe(gulpif(!isProduction, sourcemaps.init()))
    .pipe(concat("admin.js"))
    .pipe(gulpif(!isProduction, sourcemaps.write("../maps/js")))
    .pipe(gulp.dest(paths.dist.js))
    .pipe(
      gulpif(
        isProduction,
        uglify(getJSMinifyConfig()).on("error", function (err) {
          console.error("Uglify error:", err.message);
          this.emit("end");
        })
      )
    )
    .pipe(header(banner, { pkg: pkg }))
    .pipe(rename({ suffix: ".min" }))
    .pipe(size({ showFiles: true, title: "Admin JS" }))
    .pipe(gulp.dest(paths.dist.js))
    .pipe(browserSync.stream());
});

gulp.task("js:customizer", () => {
  return gulp
    .src(paths.src.js.customizer)
    .pipe(plumber(errorHandler))
    .pipe(gulpif(!isProduction, sourcemaps.init()))
    .pipe(concat("customizer.js"))
    .pipe(gulpif(!isProduction, sourcemaps.write("../maps/js")))
    .pipe(gulp.dest(paths.dist.js))
    .pipe(
      gulpif(
        isProduction,
        uglify(getJSMinifyConfig()).on("error", function (err) {
          console.error("Uglify error:", err.message);
          this.emit("end");
        })
      )
    )
    .pipe(header(banner, { pkg: pkg }))
    .pipe(rename({ suffix: ".min" }))
    .pipe(size({ showFiles: true, title: "Customizer JS" }))
    .pipe(gulp.dest(paths.dist.js))
    .pipe(browserSync.stream());
});

// Combine all JS tasks
gulp.task(
  "js",
  gulp.parallel(
    "js:critical",
    "js:global",
    "js:woocommerce",
    "js:features",
    "js:admin",
    "js:customizer"
  )
);

// Watch tasks
gulp.task("watch", () => {
  // Initialize BrowserSync
  browserSync.init({
    proxy: "localhost", // Change this to your local development URL
    notify: false,
    open: false,
  });

  // Watch CSS files
  gulp.watch(paths.watch.css, gulp.series("css")).on("change", () => {
    console.log("CSS files changed, rebuilding...");
  });

  // Watch JS files
  gulp.watch(paths.watch.js, gulp.series("js")).on("change", () => {
    console.log("JS files changed, rebuilding...");
    browserSync.reload();
  });

  // Watch PHP files for BrowserSync reload
  gulp.watch(["**/*.php", "!vendor/**/*.php"]).on("change", () => {
    console.log("PHP files changed, reloading browser...");
    browserSync.reload();
  });
});

// BrowserSync serve task
gulp.task("serve", () => {
  browserSync.init({
    proxy: "localhost", // Change this to your local development URL
    notify: false,
    open: true,
    ui: {
      port: 8080,
    },
  });
});

// Build task for production
gulp.task(
  "build",
  gulp.series("clean", gulp.parallel("css", "js"), (done) => {
    console.log("🎉 Build completed successfully!");
    console.log("📦 Assets have been optimized and minified");
    console.log("📁 Check assets/dist/ for compiled files");
    done();
  })
);

// Development task
gulp.task("dev", gulp.series("clean", gulp.parallel("css", "js"), "watch"));

// Default task
gulp.task("default", gulp.series("build"));

// Individual bundle tasks for selective building
gulp.task("build:critical", gulp.parallel("css:critical", "js:critical"));
gulp.task("build:global", gulp.parallel("css:global", "js:global"));
gulp.task(
  "build:woocommerce",
  gulp.parallel("css:woocommerce", "js:woocommerce")
);
gulp.task("build:features", gulp.parallel("css:features", "js:features"));
gulp.task("build:admin", gulp.parallel("css:admin", "js:admin"));
gulp.task("build:customizer", gulp.task("js:customizer"));

// Help task
gulp.task("help", (done) => {
  console.log("");
  console.log("🚀 Available Gulp Tasks:");
  console.log("");
  console.log("  gulp build          - Build all assets for production");
  console.log(
    "  gulp dev            - Build assets and start watching for changes"
  );
  console.log("  gulp watch          - Watch files and rebuild on changes");
  console.log("  gulp serve          - Start BrowserSync server");
  console.log("  gulp clean          - Clean dist and maps directories");
  console.log("  gulp css            - Build all CSS bundles");
  console.log("  gulp js             - Build all JS bundles");
  console.log("");
  console.log("🎯 Individual Bundle Tasks:");
  console.log("");
  console.log("  gulp build:critical     - Build critical CSS/JS");
  console.log("  gulp build:global       - Build global CSS/JS");
  console.log("  gulp build:woocommerce  - Build WooCommerce CSS/JS");
  console.log("  gulp build:features     - Build features CSS/JS");
  console.log("  gulp build:admin        - Build admin CSS/JS");
  console.log("  gulp build:customizer   - Build customizer JS");
  console.log("");
  console.log("💡 Environment Variables:");
  console.log("");
  console.log("  NODE_ENV=production     - Enable production optimizations");
  console.log("");
  done();
});
