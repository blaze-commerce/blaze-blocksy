# Pattern: Account Benefits (My Account Register Form)

Renders a marketing bullet list inside the WooCommerce Register form on the My Account page, positioned between the password field and the Register button.

## Implementation

**No code. DB-only. Client-editable.**

Uses a Blocksy Pro **Content Block** (template_type = `hook`) attached to the WooCommerce `woocommerce_register_form` action.

## Why this approach

- Client edits bullets in Gutenberg (WP Admin -> Content Blocks -> Account Benefits) without developer involvement.
- No WooCommerce template override -> survives WC version upgrades cleanly.
- No hardcoded strings in the child theme -> benefits copy is per-site, not per-client-fork.
- Feature-flag friendly: delete the Content Block to disable.

## Content Block config

| Field | Value |
|---|---|
| Post type | `ct_content_block` |
| `template_type` meta | `hook` |
| Location | `woocommerce_register_form` |
| Priority | `5` |
| Display Conditions | Include -> Page: My account (page_ids rule, post_id 13) |

The `woocommerce_register_form` hook only fires inside the register form (rendered on `/my-account` for guests), so "Everywhere" is sufficient scoping — the hook itself is the scope.

## Gutenberg content

Paragraph block ("Create an account and you will get:") + List block with 5 list items. Both wrapped in a Group block with className `bc-account-benefits` so per-client CSS can target if needed.

## Alternative hooks (if position needs to change)

| Hook | Position in form |
|---|---|
| `woocommerce_register_form_start` | Above all fields |
| `woocommerce_register_form` | Between password and privacy/submit (current) |
| `woocommerce_register_form_end` | After Register button |

Edit the Content Block's Hook location field to switch.

## Go-Live migration

Blocksy Content Blocks live in the DB, not in the child-theme git repo. Export steps live in `GO-LIVE.md`.

## Files

- No child-theme files. Pattern is DB-driven.
- CSS (optional, only if per-client brand styling needed): `clients/{client}/{client}.css` targeting `.bc-account-benefits`.

## Client replication (new site)

1. WP Admin -> Content Blocks -> Add New
2. Title: "Account Benefits — Register Form"
3. Template type: Hook
4. Location: `woocommerce_register_form` (use Custom Hook field if not in dropdown)
5. Priority: 5
6. Display Conditions: Include -> Page: My account (page_ids rule, post_id 13)
7. Content: paragraph + list block (copy per-client wording)
8. Publish
