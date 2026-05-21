# Go-Live Checklist — Byron Bay Candles

## Pre-Launch

- [ ] All pages built and reviewed
- [ ] Mobile responsive check complete
- [ ] Forms tested (contact, newsletter, etc.)
- [ ] WooCommerce checkout flow tested (if applicable)
- [ ] SEO meta titles and descriptions set
- [ ] Favicon and social sharing images uploaded
- [ ] 301 redirects configured (if migrating)
- [ ] Analytics/tracking codes installed
- [ ] Cookie consent / privacy policy in place

## Performance

- [ ] Page speed score acceptable (Core Web Vitals)
- [ ] Images optimized
- [ ] Caching configured
- [ ] CDN enabled

## Data Migration (Run on LIVE after push)

- [ ] **Product descriptions:** Run short-desc → description migration on LIVE (112 products on staging had empty `post_content`, migrated from `post_excerpt`). Script: `wp eval` — copy from staging session notes or re-run:
  ```
  wp eval '$products = get_posts(["post_type"=>"product","posts_per_page"=>-1,"post_status"=>"publish"]);
  $m=0; foreach($products as $p) {
    if(strlen($p->post_content)>0 || empty($p->post_excerpt)) continue;
    wp_update_post(["ID"=>$p->ID,"post_content"=>$p->post_excerpt]); $m++;
  } echo "Migrated: $m";'
  ```
- [ ] Verify Description tab shows on product pages after migration
- [ ] Verify ACF tabs (Sizing, Wax details, Candle jar refills) render correctly
n- [ ] **Smart Coupon Pro BOGO notice:** The "Woohoo! Add any product from Candle Refills" message is hidden on blog posts/pages via CSS. Check if the BOGO coupon display settings need adjustment on LIVE. WP Admin → WooCommerce → Coupons → find the BOGO coupon → configure message display.
n- [ ] **B2B Bundle Pricing:** Confirm with client — twin gift set bundle (ID 8259) had $3.10 wholesale prices for Distributor/Wholesale/Local Wholesale groups (was it intentional or should it be $31.00?). Cleared on staging — re-enter correct values on LIVE if needed. Groups: 631578, 631619, 632083.

## DNS & Hosting

- [ ] SSL certificate active
- [ ] DNS records updated
- [ ] Staging → Production migration complete
- [ ] Email deliverability tested

## Cleanup Before Go-Live

- [ ] **Delete duplicate homepage:** Post ID 645428 — "[DUPLICATE - DELETE BEFORE GO-LIVE] Home (Original Backup 2026-03-26)" (draft)
- [ ] **Delete duplicate checkout:** Already deleted (ID 638910)

## Post-Launch

- [ ] Verify all pages load correctly on production
- [ ] Test all forms on production
- [ ] Monitor error logs for 48 hours
- [ ] Client handover / training complete
