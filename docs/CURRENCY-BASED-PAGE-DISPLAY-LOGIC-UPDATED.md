# Currency-Based Page Display - Logic Updated

## 🔄 What Changed

The logic has been **reversed** to work the opposite way:

### ❌ Old Logic (Incorrect)
```
Current Page (ID 123)
    ↓
Check: Does this page have blaze_related_page metadata?
    ↓
If yes, display that page
```

### ✅ New Logic (Correct)
```
Current Page (ID 123)
    ↓
Query: Find ANY page where blaze_related_page = 123
    ↓
Check: Does that page's region match current currency?
    ↓
If yes, display that page
```

## 📋 Example Scenario

### Setup
- **Page A** (ID: 1) - "About Us - US"
  - `blaze_page_region` = "US"
  - `blaze_related_page` = 2

- **Page B** (ID: 2) - "About Us - Canada"
  - `blaze_page_region` = "CA"
  - `blaze_related_page` = 1

### Visitor Flow

#### USD Visitor visits Page A (ID: 1)
```
1. Current page ID = 1
2. Query: Find pages where blaze_related_page = 1
3. Found: Page B (ID: 2)
4. Check: Page B's region = "CA"
5. Current currency USD → Region "US"
6. Match? NO (CA ≠ US)
7. Result: Display Page A (original)
```

#### CAD Visitor visits Page A (ID: 1)
```
1. Current page ID = 1
2. Query: Find pages where blaze_related_page = 1
3. Found: Page B (ID: 2)
4. Check: Page B's region = "CA"
5. Current currency CAD → Region "CA"
6. Match? YES (CA = CA)
7. Result: Display Page B (related page)
```

#### USD Visitor visits Page B (ID: 2)
```
1. Current page ID = 2
2. Query: Find pages where blaze_related_page = 2
3. Found: Page A (ID: 1)
4. Check: Page A's region = "US"
5. Current currency USD → Region "US"
6. Match? YES (US = US)
7. Result: Display Page A (related page)
```

#### CAD Visitor visits Page B (ID: 2)
```
1. Current page ID = 2
2. Query: Find pages where blaze_related_page = 2
3. Found: Page A (ID: 1)
4. Check: Page A's region = "US"
5. Current currency CAD → Region "CA"
6. Match? NO (US ≠ CA)
7. Result: Display Page B (original)
```

## 🔍 How It Works (Technical)

### Step 1: Get Current Page ID
```php
$current_post_id = get_the_ID();  // e.g., 123
```

### Step 2: Get Current Region
```php
$current_region = $this->get_current_region();  // e.g., "US"
```

### Step 3: Query for Related Pages
```php
$args = array(
    'post_type'      => 'page',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => array(
        array(
            'key'   => 'blaze_related_page',
            'value' => $current_post_id,  // Find pages linking to current page
            'type'  => 'NUMERIC',
        ),
    ),
);

$related_pages = get_posts( $args );
```

### Step 4: Check Region Match
```php
foreach ( $related_pages as $page ) {
    $page_region = get_post_meta( $page->ID, 'blaze_page_region', true );
    
    // If this page's region matches current region, use it
    if ( $page_region === $current_region ) {
        return $page->ID;  // Display this page
    }
}
```

## 🎯 Key Differences

| Aspect | Old Logic | New Logic |
|--------|-----------|-----------|
| **Direction** | Current page → Related page | Any page → Current page |
| **Query** | Get metadata from current page | Query for pages linking to current page |
| **Metadata Used** | `blaze_related_page` on current page | `blaze_related_page` on OTHER pages |
| **Region Check** | Current page's region | Related page's region |
| **Result** | Display page from metadata | Display page that links to current page |

## 📊 Database Query

### Old Logic
```sql
SELECT post_meta.meta_value
FROM wp_postmeta
WHERE post_id = 123
AND meta_key = 'blaze_related_page'
```

### New Logic
```sql
SELECT posts.ID
FROM wp_posts
INNER JOIN wp_postmeta ON posts.ID = wp_postmeta.post_id
WHERE wp_postmeta.meta_key = 'blaze_related_page'
AND wp_postmeta.meta_value = 123
AND posts.post_status = 'publish'
AND posts.post_type = 'page'
```

## ✅ Configuration Example

### Setup Two Pages

**Page A: "About Us - US"**
- ID: 1
- `blaze_page_region` = "US"
- `blaze_related_page` = 2 (links to Page B)

**Page B: "About Us - Canada"**
- ID: 2
- `blaze_page_region` = "CA"
- `blaze_related_page` = 1 (links to Page A)

### Result

| Visitor | Visits | Sees |
|---------|--------|------|
| USD | Page A | Page A (no match) |
| USD | Page B | Page A (match!) |
| CAD | Page A | Page B (match!) |
| CAD | Page B | Page B (no match) |

## 🔄 Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    Visitor Arrives                           │
│                  (with currency USD)                         │
│                  (visits page ID 123)                        │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│              Get Current Page ID: 123                        │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│         Get Current Region: USD → "US"                       │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│    Query: Find pages where blaze_related_page = 123         │
│    Result: Found Page ID 456                                │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│    Get Page 456's region: "CA"                              │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│         Compare: "US" == "CA"?                              │
│         Result: NO                                           │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│         Display Original Page (ID 123)                       │
└─────────────────────────────────────────────────────────────┘
```

## 🚀 Implementation Details

### Method: `get_related_page_id()`
- Gets current page ID
- Gets current region from currency
- Queries for pages where `blaze_related_page = current_page_id`
- Checks each found page's region
- Returns first page with matching region
- Returns `false` if no match found

### Method: `maybe_redirect_to_related_page()`
- Calls `get_related_page_id()`
- If related page found, displays it
- If not found, displays original page
- Supports optional 301 redirect mode

## 💡 Why This Logic?

This approach is better because:

1. **Bidirectional Links**: Each page only needs to link to ONE other page
2. **Cleaner Setup**: No need to update both pages
3. **Flexible**: Can have multiple pages linking to the same page
4. **Intuitive**: "Show me the page that wants to be shown for my currency"

## 🧪 Testing

### Test Case 1: USD Visitor on US Page
- Visit: Page A (US)
- Expected: See Page A
- Reason: No page links to Page A with USD region

### Test Case 2: CAD Visitor on US Page
- Visit: Page A (US)
- Expected: See Page B (Canada)
- Reason: Page B links to Page A with CA region

### Test Case 3: USD Visitor on Canada Page
- Visit: Page B (Canada)
- Expected: See Page A (US)
- Reason: Page A links to Page B with US region

### Test Case 4: CAD Visitor on Canada Page
- Visit: Page B (Canada)
- Expected: See Page B
- Reason: No page links to Page B with CAD region

## 📝 Summary

The logic has been completely reversed to:
1. Take the current page ID
2. Find ANY page that has this page as its related page
3. Check if that page's region matches the current currency
4. Display that page if there's a match

This is more intuitive and flexible than the previous approach!

---

**Updated**: 2025-10-17
**Version**: 2.0.0
**Status**: ✅ Ready to Test

