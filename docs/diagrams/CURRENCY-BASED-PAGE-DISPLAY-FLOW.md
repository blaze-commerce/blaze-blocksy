# Currency-Based Page Display - Flow Diagrams

## 1. High-Level Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    Visitor Arrives                           │
│                  (with currency USD)                         │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│              WordPress Loads Page Template                   │
│                                                              │
│         template_include filter (priority 999)              │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│    BlazeCommerceCurrencyPageDisplay::                        │
│    maybe_redirect_to_related_page()                          │
└────────────────────────┬────────────────────────────────────┘
                         │
        ┌────────────────┼────────────────┐
        │                │                │
        ▼                ▼                ▼
   Is Singular?   Check Metadata   Get Current Region
   (is_page)      (region + page)   (USD → US)
        │                │                │
        └────────────────┼────────────────┘
                         │
                         ▼
            ┌─────────────────────────┐
            │  Region Match Check     │
            │  page_region ==         │
            │  current_region?        │
            │  (US == US) ✓           │
            └────────┬────────────────┘
                     │
         ┌───────────┴───────────┐
         │                       │
        YES                      NO
         │                       │
         ▼                       ▼
    Display Related         Return Original
    Page Content            Template
    (URL unchanged)         (No change)
         │                       │
         └───────────┬───────────┘
                     │
                     ▼
        ┌─────────────────────────┐
        │   Page Rendered to      │
        │   Visitor              │
        └─────────────────────────┘
```

## 2. Detailed Decision Tree

```
START: Page Request
│
├─ Is Admin? ──YES──> SKIP (return original template)
│
├─ Is Singular Page? ──NO──> SKIP (return original template)
│
├─ Get Post ID ──FAIL──> SKIP (return original template)
│
├─ Get Metadata
│  ├─ blaze_page_region ──EMPTY──> SKIP
│  └─ blaze_related_page ──EMPTY──> SKIP
│
├─ Get Current Currency
│  └─ WooCommerce active? ──NO──> SKIP
│
├─ Get Current Region
│  ├─ Aelia active? ──NO──> SKIP
│  ├─ Aelia options exist? ──NO──> SKIP
│  └─ Currency mapped? ──NO──> SKIP
│
├─ Compare Regions
│  └─ page_region == current_region? ──NO──> SKIP
│
├─ Verify Related Page
│  ├─ Page exists? ──NO──> SKIP
│  └─ Is published? ──NO──> SKIP
│
└─ DISPLAY RELATED PAGE
   ├─ Set global $post
   ├─ Call setup_postdata()
   └─ Return page template
```

## 3. Data Flow

```
┌──────────────────────────────────────────────────────────────┐
│                    WordPress Database                         │
├──────────────────────────────────────────────────────────────┤
│                                                               │
│  Posts Table                    Post Meta Table              │
│  ┌──────────────┐              ┌──────────────────────┐     │
│  │ ID: 1        │              │ post_id: 1           │     │
│  │ title: Page A│              │ meta_key: blaze_page │     │
│  │ status: pub  │              │ meta_value: US       │     │
│  └──────────────┘              └──────────────────────┘     │
│                                                               │
│  ┌──────────────┐              ┌──────────────────────┐     │
│  │ ID: 2        │              │ post_id: 1           │     │
│  │ title: Page B│              │ meta_key: blaze_rel  │     │
│  │ status: pub  │              │ meta_value: 2        │     │
│  └──────────────┘              └──────────────────────┘     │
│                                                               │
│  Options Table                                               │
│  ┌──────────────────────────────────────────────────┐       │
│  │ option_name: wc_aelia_currency_switcher         │       │
│  │ option_value: {                                  │       │
│  │   "currency_countries_mappings": {              │       │
│  │     "USD": {"countries": ["US", "PR", "VI"]},  │       │
│  │     "CAD": {"countries": ["CA"]}                │       │
│  │   }                                              │       │
│  │ }                                                │       │
│  └──────────────────────────────────────────────────┘       │
│                                                               │
└──────────────────────────────────────────────────────────────┘
                         │
                         │ Queries
                         ▼
┌──────────────────────────────────────────────────────────────┐
│              BlazeCommerceCurrencyPageDisplay                │
├──────────────────────────────────────────────────────────────┤
│                                                               │
│  get_current_currency()                                      │
│  └─> get_woocommerce_currency() ──> "USD"                   │
│                                                               │
│  get_current_region()                                        │
│  ├─> get_option('wc_aelia_currency_switcher')               │
│  ├─> currency_countries_mappings['USD']                     │
│  └─> return "US"                                             │
│                                                               │
│  should_redirect_to_related_page()                           │
│  ├─> get_post_meta(1, 'blaze_page_region') ──> "US"        │
│  ├─> get_post_meta(1, 'blaze_related_page') ──> 2          │
│  ├─> Compare: "US" == "US" ✓                                │
│  ├─> get_post(2) ──> verify published                       │
│  └─> return true                                             │
│                                                               │
│  maybe_redirect_to_related_page()                            │
│  ├─> Set global $post = get_post(2)                         │
│  ├─> setup_postdata($post)                                  │
│  └─> return get_page_template()                             │
│                                                               │
└──────────────────────────────────────────────────────────────┘
                         │
                         │ Returns
                         ▼
┌──────────────────────────────────────────────────────────────┐
│                  Page Template Rendered                       │
│                  (Page B content displayed)                   │
└──────────────────────────────────────────────────────────────┘
```

## 4. Configuration Workflow

```
┌─────────────────────────────────────────────────────────────┐
│              WordPress Admin - Edit Page                     │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│         Blaze Commerce Settings Meta Box                     │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │ Page Region: [Dropdown]                              │  │
│  │ ├─ Select a region...                                │  │
│  │ ├─ United States (USD)                               │  │
│  │ ├─ Canada (CAD)                                      │  │
│  │ └─ United Kingdom (GBP)                              │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │ Related Page: [Search Box]                            │  │
│  │ ├─ Search for a page...                              │  │
│  │ ├─ [AJAX Search Results]                             │  │
│  │ └─ [Select Related Page]                             │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
│  [Update Button]                                             │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│              Save Post Meta Data                             │
│                                                              │
│  save_page_meta()                                            │
│  ├─ Verify nonce                                            │
│  ├─ Check permissions                                       │
│  ├─ Sanitize page_region                                    │
│  ├─ Sanitize related_page (absint)                          │
│  └─ update_post_meta()                                      │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│              Metadata Stored in Database                     │
│                                                              │
│  post_meta:                                                  │
│  ├─ blaze_page_region = "US"                                │
│  └─ blaze_related_page = 2                                  │
└─────────────────────────────────────────────────────────────┘
```

## 5. Multi-Region Setup

```
┌─────────────────────────────────────────────────────────────┐
│                    Store Regions                             │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Region 1: US (USD)          Region 2: Canada (CAD)        │
│  ┌──────────────────┐        ┌──────────────────┐          │
│  │ Page A           │        │ Page B           │          │
│  │ About Us - US    │◄──────►│ About Us - Canada│          │
│  │ Region: US       │        │ Region: CA       │          │
│  │ Related: Page B  │        │ Related: Page A  │          │
│  └──────────────────┘        └──────────────────┘          │
│         ▲                              ▲                    │
│         │                              │                    │
│    USD Visitor                    CAD Visitor               │
│    Sees Page B                    Sees Page A               │
│    Content                        Content                   │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

## 6. Request Routing

```
Request 1: USD Visitor → /about-us-us/
│
├─ Get Post ID: 1 (Page A)
├─ Get Region: US
├─ Get Related: 2 (Page B)
├─ Current Currency: USD
├─ Current Region: US
├─ Match? YES (US == US)
│
└─> Display Page B Content
    (URL: /about-us-us/)
    (Title: About Us - Canada)
    (Content: Canadian content)


Request 2: CAD Visitor → /about-us-us/
│
├─ Get Post ID: 1 (Page A)
├─ Get Region: US
├─ Get Related: 2 (Page B)
├─ Current Currency: CAD
├─ Current Region: CA
├─ Match? NO (US != CA)
│
└─> Display Page A Content
    (URL: /about-us-us/)
    (Title: About Us - US)
    (Content: US content)


Request 3: CAD Visitor → /about-us-canada/
│
├─ Get Post ID: 2 (Page B)
├─ Get Region: CA
├─ Get Related: 1 (Page A)
├─ Current Currency: CAD
├─ Current Region: CA
├─ Match? YES (CA == CA)
│
└─> Display Page A Content
    (URL: /about-us-canada/)
    (Title: About Us - US)
    (Content: US content)
```

## 7. Performance Timeline

```
0ms   ├─ Page Request Received
      │
5ms   ├─ WordPress Loads
      │
10ms  ├─ Plugins Initialize
      │
15ms  ├─ template_include Filter (Priority 999)
      │  ├─ Check is_singular() ─────────── 0.1ms
      │  ├─ Get Post ID ─────────────────── 0.05ms
      │  ├─ Get Metadata (2 queries) ────── 0.5ms
      │  ├─ Get Currency ────────────────── 0.1ms
      │  ├─ Get Aelia Options ──────────── 0.3ms
      │  ├─ Map Currency to Region ─────── 0.1ms
      │  ├─ Compare Regions ────────────── 0.05ms
      │  ├─ Verify Related Page ────────── 0.2ms
      │  └─ Set Global Post ────────────── 0.05ms
      │  Total: ~1.5ms
      │
16.5ms├─ Template Rendering
      │
100ms ├─ Page Fully Loaded
      │
```

## 8. Error Handling Flow

```
START: maybe_redirect_to_related_page()
│
├─ Is Singular Page?
│  └─ NO ──> Return original template
│
├─ Get Post ID
│  └─ FAIL ──> Return original template
│
├─ Get Metadata
│  ├─ Region empty? ──> Return original template
│  └─ Related empty? ──> Return original template
│
├─ Get Current Currency
│  └─ Empty? ──> Return original template
│
├─ Get Current Region
│  └─ Empty? ──> Return original template
│
├─ Compare Regions
│  └─ No match? ──> Return original template
│
├─ Get Related Page
│  └─ Not found? ──> Return original template
│
├─ Check Published Status
│  └─ Not published? ──> Return original template
│
└─ SUCCESS: Display Related Page
```

---

These diagrams illustrate the complete flow of the Currency-Based Page Display feature from request to response.

