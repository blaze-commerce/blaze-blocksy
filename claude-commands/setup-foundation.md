# Setup Foundation

Apply the Figma handover design to the Blocksy Customizer. This command reads colors, typography, and layout from the Figma handover and applies them via WP-CLI theme mods.

## Prerequisites

- `/setup-project` must have been run first (credentials saved in memory)
- Blocksy theme + Companion Pro must be installed and activated
- Figma handover page must exist with color styles and typography specs

## What This Command Does

1. **Reads project config** from Claude Code memory
2. **Fetches Figma handover data** (colors, typography, layout)
3. **Applies to Blocksy Customizer** via `wp theme mod set`
4. **Updates CHANGELOG.md** with all changes
5. **Pushes to ClickUp** if configured

## Implementation

### Step 1: Load Config

Read the project config from Claude Code memory (`project-config.json`). Verify ClickUp and Figma credentials are present. If not, tell the user to run `/setup-project` first.

### Step 2: Fetch Figma Handover

Use the Figma API to fetch the handover page:

```python
import requests

token = config["figma"]["token"]
file_key = config["figma"]["file_key"]
headers = {"X-Figma-Token": token}

# Get file pages to find the handover page
resp = requests.get(f"https://api.figma.com/v1/files/{file_key}", headers=headers)
pages = resp.json()["document"]["children"]

# Find HANDOVER page
handover_page = None
for page in pages:
    if config["figma"]["handover_page"].lower() in page["name"].lower():
        handover_page = page
        break
```

### Step 3: Extract Colors

Find the color style frame on the handover page. Colors are typically organized as named swatches with hex values.

```python
# Fetch the specific node with color styles
node_id = config["figma"]["nodes"]["colors"]  # Set during first run, or auto-detect
resp = requests.get(
    f"https://api.figma.com/v1/files/{file_key}/nodes",
    headers=headers,
    params={"ids": node_id}
)
```

Parse the color nodes and build a palette array:

```python
colors = []
for child in color_frame["children"]:
    name = child["name"]
    fill = child["fills"][0]["color"]
    hex_color = "#{:02x}{:02x}{:02x}".format(
        int(fill["r"] * 255),
        int(fill["g"] * 255),
        int(fill["b"] * 255)
    ).upper()
    colors.append({"title": name, "color": hex_color})
```

### Step 4: Apply Color Palette

Blocksy supports unlimited palette colors (not just 8). Apply all colors from Figma:

```php
// Build palette array
$palette = ['current_palette' => 'palette-1', 'palettes' => [['id' => 'palette-1', 'color1' => [...], ...]]];
// Set via: wp theme mod set colorPalette '<json>'
```

Also set Global Colors:
- `fontColor` → body text color (reference palette variable)
- `linkColor` → link colors (initial + hover)
- `selectionColor` → text selection
- `border_color` → borders
- `headingColor` → all heading colors
- `site_background` → site background

### Step 5: Extract Typography

Find the typography frame on the handover page. Extract:
- **Body font**: family, weight, size, line-height
- **Heading font**: family, weight, sizes per level (H1-H6)
- **Button font**: family, weight, size

### Step 6: Register Custom Fonts

If the handover uses non-Google fonts (e.g., Museo Sans):

1. Check if font files exist on the site (`wp-content/uploads/` or `wp-content/fonts/`)
2. If found, register in Blocksy Custom Fonts via `blocksy_ext_custom_fonts_settings` option
3. If not found, inform user to upload font files

```python
# Register custom font
font_settings = {
    "fonts": [{
        "name": font_name,
        "variations": [{
            "variation": variation_code,  # e.g., "n5" for normal weight 500
            "url": font_url
        }]
    }],
    "stacks": []
}
# wp option update blocksy_ext_custom_fonts_settings '<json>' --format=json
```

### Step 7: Apply Typography

Set all typography theme mods:

```bash
# Body
wp theme mod set rootTypography '<json>'

# Headings H1-H6
wp theme mod set h1Typography '<json>'
wp theme mod set h2Typography '<json>'
# ... through h6Typography

# Buttons
wp theme mod set buttons '<json>'

# Additional (industry standard defaults if not in Figma)
wp theme mod set quote '<json>'
wp theme mod set pullquote '<json>'
wp theme mod set pre '<json>'
wp theme mod set figcaption '<json>'
```

Typography JSON structure:
```json
{
    "family": "ct_font_museo-sans",
    "variation": "n5",
    "size": {"desktop": "16px", "tablet": "15px", "mobile": "14px"},
    "line-height": {"desktop": "1.5", "tablet": "1.5", "mobile": "1.5"},
    "letter-spacing": "0em",
    "text-transform": "none",
    "text-decoration": "none"
}
```

### Step 8: Save Figma Node IDs

After the first successful run, save the discovered Figma node IDs back to the project config so future runs are faster:

```python
config["figma"]["nodes"]["colors"] = discovered_color_node_id
config["figma"]["nodes"]["typography"] = discovered_typography_node_id
# Save back to memory
```

### Step 9: Update CHANGELOG.md

Add a detailed entry documenting all applied settings with:
- Color palette values and names
- Typography families, weights, and sizes
- Any known issues or missing items

### Step 10: Push to ClickUp

If ClickUp is configured, push the updated CHANGELOG.md.

## Output

```
FOUNDATION APPLIED
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Colors:     {count} palette colors applied
Typography: Body ({body_font}), Headings ({heading_font}), Buttons ({button_font})
Custom Fonts: {count} registered

Changes saved to CHANGELOG.md
ClickUp synced: OK

Next steps:
  1. Review colors in Customizer → General → Colors
  2. Review typography in Customizer → General → Typography
  3. Proceed with header/footer/menu setup

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

## Important Notes

- **Blocksy supports unlimited palette colors** — not limited to 8. Colors 9+ are added via the "Add New Color" feature.
- **Custom fonts** use `ct_font_<name>` reference format in typography settings
- **Typography variation codes**: `n1`-`n9` (normal), `i1`-`i9` (italic). `n4` = regular, `n5` = medium, `n7` = bold.
- **Responsive sizes** use `{desktop, tablet, mobile}` object format
- **Buttons key** is `buttons` (NOT `buttonTypography`)
- **All changes are via Customizer/theme mods** — no custom CSS or PHP code needed
- **Always verify in wp-admin** Customizer after applying settings
