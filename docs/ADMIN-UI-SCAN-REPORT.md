# CRO Toolkit – Admin UI Scan Report

**Generated:** Scan of `admin/class-cro-admin.php` and admin partials for pages, partials, form fields, and SelectWoo usage.

---

## 1) Admin pages (slugs) registered in `admin/class-cro-admin.php`

| Slug | Menu title | Callback / Rendered as |
|------|------------|-------------------------|
| `cro-toolkit` | CRO Toolkit / Dashboard | `render_dashboard` → onboarding or dashboard |
| `cro-presets` | Presets | `render_presets` |
| `cro-campaigns` | Campaigns | `render_campaigns` |
| `cro-campaign-edit` | Edit Campaign (hidden) | `render_campaign_builder` |
| `cro-boosters` | On-Page Boosters | `render_boosters` |
| `cro-cart` | Cart Optimizer | `render_cart_optimizer` |
| `cro-abandoned-carts` | Abandoned Carts | `render_abandoned_carts_list` |
| `cro-abandoned-cart` | Abandoned Cart Emails | `render_abandoned_cart_emails` |
| `cro-offers` | Offers | `render_offers` |
| `cro-checkout` | Checkout Optimizer | `render_checkout_optimizer` |
| `cro-ab-tests` | A/B Tests | `render_ab_tests` |
| `cro-ab-test-new` | Create A/B Test (hidden) | `render_ab_test_new` |
| `cro-ab-test-view` | View A/B Test (hidden) | `render_ab_test_view` |
| `cro-analytics` | Analytics | `render_analytics` |
| `cro-settings` | Settings | `render_settings` |
| `cro-system-status` | System Status | `render_system_status` |
| `cro-tools` | Tools (Import / Export) | `render_tools` |

---

## 2) Partials used to render those pages

| Page slug | Primary content partial(s) | Notes |
|-----------|----------------------------|--------|
| `cro-toolkit` | `cro-admin-onboarding.php` or `cro-admin-dashboard.php` | Depends on onboarding state |
| `cro-presets` | `cro-admin-presets.php` | Direct include |
| `cro-campaigns` | `cro-admin-campaigns.php` | Via `CRO_Admin_Layout::render_page` |
| `cro-campaign-edit` | `cro-admin-campaign-builder.php` | Includes builder sub-partials below |
| `cro-boosters` | `cro-admin-boosters.php` | Via layout |
| `cro-cart` | `cro-admin-cart.php` | Via layout |
| `cro-abandoned-carts` | `cro-admin-abandoned-carts-list.php` | Via layout |
| `cro-abandoned-cart` | `cro-admin-abandoned-cart.php` | Via layout |
| `cro-offers` | `cro-admin-offers.php` | Via layout |
| `cro-checkout` | `cro-admin-checkout.php` | Via layout |
| `cro-ab-tests` | `cro-admin-ab-tests.php` | Via layout |
| `cro-ab-test-new` | `cro-admin-ab-test-new.php` | Via layout |
| `cro-ab-test-view` | `cro-admin-ab-test-view.php` | Via layout |
| `cro-analytics` | `cro-admin-analytics.php` | Via layout |
| `cro-settings` | `cro-admin-settings.php` | Via layout |
| `cro-system-status` | `cro-admin-system-status.php` | Via layout |
| `cro-tools` | `cro-admin-tools.php` | Via layout |

**Builder sub-partials** (included by `cro-admin-campaign-builder.php`):

- `admin/partials/builder/design-controls.php`
- `admin/partials/builder/trigger-controls.php`
- `admin/partials/builder/targeting-controls.php`
- `admin/partials/builder/display-controls.php`

**Unused / legacy partial:** `cro-admin-campaign-edit.php` – not referenced by `class-cro-admin.php`; campaign edit page uses `cro-admin-campaign-builder.php`. Contains its own form (name, type, status, targeting). Consider removing or wiring if still needed.

---

## 3) All `<select>` and `<input>` fields in admin partials

### SelectWoo init class

- **Init class:** `cro-select-woo` (see `admin/js/cro-select-woo-init.js`).
- Selects with `cro-select-woo` are enhanced by SelectWoo when the script runs on CRO admin pages.

### By file

**`admin/partials/cro-admin-offers.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input hidden | `name="cro_toggle_offer"`, `name="cro_offer_index"` | — |
| input checkbox | toggle offer | — |
| input hidden | `#cro-drawer-offer-index` | — |
| input text | `#cro-drawer-headline`, `.regular-text` | — |
| input checkbox | `#cro-drawer-enabled` | — |
| input number | `#cro-drawer-priority` | — |
| input number | `#cro-drawer-min-cart-total`, `#cro-drawer-max-cart-total`, `#cro-drawer-min-items` | — |
| input checkbox | `#cro-drawer-exclude-sale-items` | — |
| **select** | `#cro-drawer-include-categories`, `.cro-drawer-select.cro-select-woo` | ✅ Yes |
| **select** | `#cro-drawer-exclude-categories`, `.cro-drawer-select.cro-select-woo` | ✅ Yes |
| **select** | `#cro-drawer-include-products`, `.cro-select-woo.cro-select-products` | ✅ Yes |
| **select** | `#cro-drawer-exclude-products`, `.cro-select-woo.cro-select-products` | ✅ Yes |
| **select** | `#cro-drawer-cart-contains-category`, `.cro-select-woo` | ✅ Yes |
| input checkbox | `#cro-drawer-first-time`, `#cro-drawer-returning-toggle` | — |
| input number | `#cro-drawer-returning-min-orders`, `#cro-drawer-lifetime-spend` | — |
| **select** | `#cro-drawer-allowed-roles`, `.cro-select-woo` | ✅ Yes |
| **select** | `#cro-drawer-excluded-roles`, `.cro-select-woo` | ✅ Yes |
| **select** | `#cro-drawer-reward-type` | ❌ No |
| input number | `#cro-drawer-reward-amount`, `#cro-drawer-coupon-ttl` | — |
| input checkbox | `#cro-drawer-individual-use` | — |
| **select** | `#cro-drawer-apply-to-categories`, `.cro-select-woo` | ✅ Yes |
| **select** | `#cro-drawer-apply-to-products`, `.cro-select-woo.cro-select-products` | ✅ Yes |
| input number | `#cro-drawer-rate-limit-hours`, `#cro-drawer-max-coupons-per-visitor` | — |
| input number | `#cro-test-cart-total`, `#cro-test-items-count`, etc. | — |
| **select** | `#cro-test-is-logged-in` | ❌ No |
| **select** | `#cro-test-user-role` | ❌ No |

**`admin/partials/cro-admin-analytics.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input hidden | `name="page"` | — |
| input date | `name="from"`, `name="to"` | — |
| **select** | `#cro-campaign-filter`, `name="campaign_id"` | ❌ No |

**`admin/partials/cro-admin-dashboard.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input hidden | `name="cro_quick_launch"` | — |

**`admin/partials/cro-admin-system-status.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input hidden | `name="cro_verify_installation"`, `name="cro_repair_tables"` | — |

**`admin/partials/cro-admin-campaigns.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input hidden | `name="cro_action"`, `name="campaign_id"` | — |

**`admin/partials/cro-admin-abandoned-carts-list.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input hidden | `name="page"`, `name="status_filter"` | — |
| input search | `#cro-ac-search`, `name="search"` | — |

**`admin/partials/cro-admin-campaign-edit.php`** (not used by main menu; may be legacy)

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input text | `#campaign_name`, `.regular-text` | — |
| **select** | `#campaign_type`, `name="campaign_type"` | ❌ No |
| **select** | `#campaign_status`, `name="campaign_status"` | ❌ No |
| input number | `targeting[behavior][min_time_on_page]`, etc. | — |
| input checkbox | `targeting[behavior][require_interaction]`, device toggles | — |
| **select** | `#cart_status`, `name="targeting[behavior][cart_status]"` | ❌ No |
| **select** | `#visitor_type`, `name="targeting[visitor][type]"` | ❌ No |

**`admin/partials/cro-admin-presets.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input hidden | `name="cro_apply_preset"`, `name="preset_id"` | — |

**`admin/partials/cro-admin-campaign-builder.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input text | `#campaign-name`, `.cro-campaign-name-input` | — |
| **select** | `#campaign-status` | ❌ No |
| input hidden | `#content-image`, `#campaign-id`, `#campaign-data` | — |
| **select** | `#content-tone` | ❌ No |
| input text/url/checkbox/number | content fields (headline, CTA, countdown, etc.) | — |
| **select** | `#content-cta-action` | ❌ No |
| **select** | `#content-countdown-type` | ❌ No |

**`admin/partials/cro-admin-ab-test-new.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input text | campaign name field | — |
| **select** | `#campaign_id`, `name="campaign_id"` | ❌ No |
| **select** | `#metric`, `name="metric"` | ❌ No |
| input number | sample size | — |
| **select** | `#confidence_level`, `name="confidence_level"` | ❌ No |
| input checkbox | `name="auto_apply_winner"` | — |

**`admin/partials/cro-admin-boosters.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input checkbox | `sticky_cart_enabled`, `sticky_cart_mobile_only`, etc. | — |
| input number | `#sticky_cart_scroll` | — |
| **select** | `#sticky_cart_tone`, `name="sticky_cart_tone"` | ❌ No |
| input text | `#sticky_cart_button_text`, colors | — |
| **select** | `#shipping_bar_tone`, `name="shipping_bar_tone"` | ❌ No |
| input number | `shipping_bar_threshold` | — |
| input text | `#shipping_bar_message_progress`, `#shipping_bar_message_achieved` | — |
| **select** | `#shipping_bar_position`, `name="shipping_bar_position"` | ❌ No |
| **select** | `#stock_urgency_tone`, `name="stock_urgency_tone"` | ❌ No |
| input text | `#stock_urgency_message` | — |
| input checkbox | `trust_badges_enabled` | — |

**`admin/partials/cro-admin-checkout.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input checkbox | `checkout_enabled`, `remove_company`, etc. | — |
| input text | `name="trust_message"`, `name="guarantee_text"` | — |

**`admin/partials/cro-admin-abandoned-cart.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input checkbox | `cro_abandoned_cart_enabled`, `cro_abandoned_cart_require_opt_in` | — |
| input number | `cro_email_1_delay_hours`, etc. | — |
| input text | `#cro_email_subject_template`, `.large-text` | — |
| input email | `#cro_test_email_to`, `.regular-text` | — |

**`admin/partials/cro-admin-cart.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input checkbox | `cart_enabled`, `show_trust`, etc. | — |
| input text | `#trust_message`, `#urgency_message`, `#checkout_text` | — |
| **select** | `#urgency_type`, `name="urgency_type"` | ❌ No |
| **select** | `name="cro_discount_type"` | ❌ No |
| **select** | `name="cro_include_categories[]"`, `.cro-select-woo` | ✅ Yes |
| **select** | `name="cro_exclude_categories[]"`, `.cro-select-woo` | ✅ Yes |
| **select** | `name="cro_include_products[]"`, `.cro-select-woo.cro-select-products` | ✅ Yes |
| **select** | `name="cro_exclude_products[]"`, `.cro-select-woo.cro-select-products` | ✅ Yes |
| **select** | `name="cro_per_category_discount_cat[]"`, `.cro-select-woo.cro-per-cat-select` | ✅ Yes |
| **select** | `name="cro_generate_coupon_for_email"` | ❌ No |
| **select** | `#offer_banner_position`, `name="offer_banner_position"` | ❌ No |

**`admin/partials/cro-admin-tools.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input hidden | verify, page, action, cro_import | — |
| input file | `#cro-import-file`, `name="import_file"` | — |
| **select** | `#cro-export-campaign`, `name="campaign_id"`, `.regular-text` | ❌ No |

**`admin/partials/cro-admin-settings.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input checkbox | `enable_analytics`, `debug_mode`, etc. | — |
| **select** | `#cro-font-size-scale`, `name="font_size_scale"` | ❌ No |
| **select** | `#cro-font-family`, `name="font_family"` | ❌ No |
| **select** | `#cro-animation-speed`, `name="animation_speed"` | ❌ No |

**`admin/partials/cro-admin-ab-test-view.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input text | view/detail fields | — |
| input number | view/detail fields | — |

**`admin/partials/builder/targeting-controls.php`** (Select2, not SelectWoo)

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| **select** | `#targeting-page-mode` | ❌ No (uses Select2 in JS) |
| **select** | `#targeting-specific-pages`, `.cro-select2` | ❌ No (Select2) |
| **select** | `#targeting-visitor-type` | ❌ No |
| **select** | `#targeting-cart-status` | ❌ No |
| **select** | `#targeting-cart-contains`, `#targeting-cart-category`, etc., `.cro-select2` | ❌ No (Select2) |
| **select** | `.cro-rule-field`, `.cro-rule-operator` | ❌ No |

**`admin/partials/builder/design-controls.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| **select** | `#design-animation` | ❌ No |

**`admin/partials/builder/display-controls.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| **select** | `#display-frequency` | ❌ No |
| **select** | `#display-frequency-period-unit` | ❌ No |
| **select** | `#display-brand-font-scale` | ❌ No |
| **select** | `#display-auto-pause` | ❌ No |
| **select** | `#display-after-conversion` | ❌ No |
| **select** | `#display-followup-campaign` | ❌ No |

**`admin/partials/builder/trigger-controls.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| **select** | `#trigger-sensitivity` | ❌ No |

**`admin/partials/cro-admin-onboarding.php`**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| input hidden | `name="cro_onboarding_checklist"` | — |
| input checkbox | `feature_shipping_bar`, `feature_sticky_cart` | — |

**Classic editor modal (in `class-cro-admin.php`, not a partial)**

| Type | Selector / ID / class | SelectWoo? |
|------|------------------------|------------|
| **select** | `#cro-campaign-select` (Insert CRO Campaign) | ❌ No |

---

## 4) Selects: already have SelectWoo vs not

### Already using SelectWoo (class `cro-select-woo`)

- **cro-admin-offers.php:**  
  `#cro-drawer-include-categories`, `#cro-drawer-exclude-categories`, `#cro-drawer-include-products`, `#cro-drawer-exclude-products`, `#cro-drawer-cart-contains-category`, `#cro-drawer-allowed-roles`, `#cro-drawer-excluded-roles`, `#cro-drawer-apply-to-categories`, `#cro-drawer-apply-to-products`.
- **cro-admin-cart.php:**  
  `cro_include_categories[]`, `cro_exclude_categories[]`, `cro_include_products[]`, `cro_exclude_products[]`, `cro_per_category_discount_cat[]` (`.cro-select-woo`).

### Selects without SelectWoo (candidates for conversion or consistency)

- **cro-admin-offers.php:**  
  `#cro-drawer-reward-type`, `#cro-test-is-logged-in`, `#cro-test-user-role`
- **cro-admin-analytics.php:**  
  `#cro-campaign-filter`
- **cro-admin-campaign-edit.php:**  
  `#campaign_type`, `#campaign_status`, `#cart_status`, `#visitor_type`
- **cro-admin-campaign-builder.php:**  
  `#campaign-status`, `#content-tone`, `#content-cta-action`, `#content-countdown-type`
- **cro-admin-ab-test-new.php:**  
  `#campaign_id`, `#metric`, `#confidence_level`
- **cro-admin-boosters.php:**  
  `#sticky_cart_tone`, `#shipping_bar_tone`, `#shipping_bar_position`, `#stock_urgency_tone`
- **cro-admin-cart.php:**  
  `#urgency_type`, `cro_discount_type`, `cro_generate_coupon_for_email`, `#offer_banner_position`
- **cro-admin-tools.php:**  
  `#cro-export-campaign`
- **cro-admin-settings.php:**  
  `#cro-font-size-scale`, `#cro-font-family`, `#cro-animation-speed`
- **Builder partials:**  
  All selects in `targeting-controls.php`, `design-controls.php`, `display-controls.php`, `trigger-controls.php` use **Select2** (`.cro-select2` / `$().select2()` in `cro-campaign-builder.js`), not SelectWoo. Unify only if you standardize on SelectWoo for the whole admin.
- **class-cro-admin.php (modal):**  
  `#cro-campaign-select` (Add CRO Campaign in classic editor).

---

## 5) Checklist: files to update for UI consistency and SelectWoo conversion

Use this as a working checklist. “Add SelectWoo” = add class `cro-select-woo` and optional `data-placeholder` where appropriate; ensure page is under a CRO admin hook so `cro-select-woo-init.js` runs.

### High impact (single/dropdown selects that would benefit from SelectWoo)

- [ ] **admin/partials/cro-admin-offers.php**  
  - Add `cro-select-woo` (and placeholder) to: `#cro-drawer-reward-type`, `#cro-test-is-logged-in`, `#cro-test-user-role`.
- [ ] **admin/partials/cro-admin-analytics.php**  
  - Add `cro-select-woo` to `#cro-campaign-filter`.
- [ ] **admin/partials/cro-admin-tools.php**  
  - Add `cro-select-woo` to `#cro-export-campaign`.
- [ ] **admin/partials/cro-admin-settings.php**  
  - Add `cro-select-woo` to `#cro-font-size-scale`, `#cro-font-family`, `#cro-animation-speed`.
- [ ] **admin/class-cro-admin.php**  
  - Add `cro-select-woo` to `#cro-campaign-select` in the “Add CRO Campaign” thickbox modal (ensure SelectWoo is enqueued on post edit screen if you use it there, or keep native select).

### Medium impact (forms with several dropdowns)

- [ ] **admin/partials/cro-admin-boosters.php**  
  - Add `cro-select-woo` to: `#sticky_cart_tone`, `#shipping_bar_tone`, `#shipping_bar_position`, `#stock_urgency_tone`.
- [ ] **admin/partials/cro-admin-cart.php**  
  - Add `cro-select-woo` to: `#urgency_type`, `name="cro_discount_type"`, `cro_generate_coupon_for_email`, `#offer_banner_position`.
- [ ] **admin/partials/cro-admin-ab-test-new.php**  
  - Add `cro-select-woo` to: `#campaign_id`, `#metric`, `#confidence_level`.

### Lower priority / different context

- [ ] **admin/partials/cro-admin-campaign-builder.php**  
  - Add `cro-select-woo` to: `#campaign-status`, `#content-tone`, `#content-cta-action`, `#content-countdown-type` (only if you want SelectWoo in the visual builder; currently no SelectWoo there).
- [ ] **admin/partials/cro-admin-campaign-edit.php**  
  - If this partial is ever used again: add `cro-select-woo` to `#campaign_type`, `#campaign_status`, `#cart_status`, `#visitor_type`.

### Builder: Select2 vs SelectWoo

- [ ] **admin/partials/builder/*.php** and **admin/js/cro-campaign-builder.js**  
  - Decide: keep Select2 for builder only, or migrate `.cro-select2` to SelectWoo (class `cro-select-woo`, and swap `cro-campaign-builder.js` from `select2` to `selectWoo` and ensure SelectWoo is enqueued on `cro-campaign-edit`).  
  - If migrating: update `admin/partials/builder/targeting-controls.php`, `design-controls.php`, `display-controls.php`, `trigger-controls.php` and JS init.

### General UI consistency

- [ ] **admin/css/cro-admin-ui.css** (or relevant CSS)  
  - Ensure native `<select>` and SelectWoo-enhanced selects share consistent width, spacing, and focus styles where appropriate.
- [ ] **admin/js/cro-select-woo-init.js**  
  - No change needed for the above; already inits `.cro-select-woo`. Optionally add `data-placeholder` for new selects.
- [ ] **Enqueue**  
  - SelectWoo is enqueued for hooks containing `cro-`; post.php/post-new.php (classic editor modal) do not load it by default—add enqueue for that context if `#cro-campaign-select` gets `cro-select-woo`.

---

## Summary

- **17 admin slugs**; **16+ primary partials** (+ builder sub-partials; `cro-admin-campaign-edit.php` exists but is not used by the main menu).
- **Selects with SelectWoo:** Offers drawer (9) and Cart optimizer (5 multi-select + per-category row).
- **Selects without SelectWoo:** ~30+ across analytics, tools, settings, boosters, cart (single selects), campaign edit/builder, A/B test, and classic editor modal.
- **Builder:** Uses Select2 (`.cro-select2`); standardizing on SelectWoo would require JS and partial updates in builder partials.

Use the checklist above to add `cro-select-woo` and shared styling for a consistent admin UI and SelectWoo conversion.
