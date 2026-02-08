# Goods & Services + Price Lists — Implementation Plan

> Similar to the ordering project's menu system: catalog of goods/services, price lists with sections, public URL for customers, styling options.

---

## 1. Overview

| Component | Description |
|-----------|-------------|
| **Product Catalog** | List of goods and services the company offers. Tags, smart autocomplete. |
| **Invoice Integration** | Invoice item description uses smart autocomplete from products. Optional `product_id` link. |
| **لیست قیمت** | Manager creates price lists from products. Sections, show/hide prices, duplicate. Optional templates with product photos. |
| **Public View** | Unique URL per price list. Customer sees it without login. Styling like menu customizations. |

---

## 2. Database Schema

### 2.1 Products (Goods & Services)

```
products
├── id
├── name               (string, required)
├── description        (text, nullable)
├── default_unit_price (decimal 15,0, nullable) — suggested price
├── unit               (string, nullable) — e.g. عدد، کیلو، متر
├── photo_path         (string, nullable) — product image
├── user_id            (foreign, nullable) — owner
├── is_active          (boolean, default true)
├── sort_order         (integer, default 0)
├── timestamps
```

**Tags**: Use existing polymorphic `taggables` table: `taggable_type = 'App\Models\Product'`, `taggable_id = products.id`.

### 2.2 Price Lists (لیست قیمت)

```
price_lists
├── id
├── name               (string, required)
├── code               (string, unique, nullable) — for public URL, e.g. "abc123"
├── template           (string, nullable) — e.g. 'simple', 'with_photos', 'grid'
├── show_prices        (boolean, default true)
├── show_photos        (boolean, default false) — show product photos in list
├── user_id            (foreign, nullable)
├── title_text         (string, nullable) — display title on public view
├── primary_color      (string, nullable) — hex
├── font_family        (string, nullable)
├── is_active          (boolean, default true)
├── timestamps
```

**Templates**: `simple` (text only), `with_photos` (product photos in list), `grid` (card layout with photos)

### 2.3 Price List Sections

```
price_list_sections
├── id
├── price_list_id      (foreign)
├── name               (string, required)
├── sort_order         (integer, default 0)
├── timestamps
```

### 2.4 Price List Items

```
price_list_items
├── id
├── price_list_section_id (foreign)
├── product_id         (foreign, nullable) — link to product
├── custom_name        (string, nullable) — override product name
├── custom_description (text, nullable)
├── unit_price         (decimal 15,0, nullable) — override product default
├── unit               (string, nullable)
├── sort_order         (integer, default 0)
├── timestamps
```

### 2.5 Product Landing Pages (صفحه فرود محصول)

Manager can create a standalone landing page for a single product.

```
product_landing_pages
├── id
├── product_id         (foreign, required)
├── code               (string, unique, nullable) — for public URL
├── title_text         (string, nullable) — override page title
├── headline           (string, nullable) — CTA headline
├── cta_type           (string, nullable) — 'purchase', 'call', 'whatsapp', 'link'
├── cta_url            (string, nullable) — external link (for purchase/link)
├── cta_phone          (string, nullable) — for call/whatsapp
├── cta_button_text    (string, nullable) — e.g. سفارش، تماس، خرید
├── primary_color      (string, nullable)
├── font_family        (string, nullable)
├── is_active          (boolean, default true)
├── timestamps
```

**Public route**: `/product/{code}` — shows product photo, description, price, CTA button (خرید / تماس / واتساپ / لینک).

### 2.6 Invoice Items (Update)

Add optional `product_id` to `invoice_items`:

```
invoice_items
├── ...existing columns...
├── product_id         (foreign, nullable) — optional link to product
```

---

## 3. Smart Autocomplete for Invoice Items

**Requirement**: Not sequential word match — smarter search.

**Options**:

1. **Word-split match**: Split query by spaces, match each word in `name` OR `description` OR tag names.
   - `WHERE (name LIKE '%word1%' OR description LIKE '%word1%' OR tags.name LIKE '%word1%') AND ...`
   - Order by: exact name match > name starts with > name contains > tag match.

2. **FULLTEXT** (MySQL): Add FULLTEXT index on `products.name`, `products.description`. Use `MATCH ... AGAINST`.

3. **Trigram / similarity** (if available): For typo tolerance.

**Recommended (simple)**: Word-split + tag search. Query `q` → split → build dynamic WHERE. Include tag names via join on taggables.

```php
// Product::scopeSearch($query, $q)
// 1. Split $q into words (Persian + English)
// 2. For each word: (name LIKE %w% OR description LIKE %w% OR tag in tags)
// 3. Order by: exact match > starts with > contains
```

---

## 4. Routes

### Admin (auth required)

| Method | Route | Action |
|--------|-------|--------|
| GET | /products | Product catalog index |
| GET | /products/create | Create product |
| POST | /products | Store product |
| GET | /products/{product}/edit | Edit product |
| PUT | /products/{product} | Update product |
| DELETE | /products/{product} | Delete product |
| GET | /api/products/search | Autocomplete API (for invoice form) |

| Method | Route | Action |
|--------|-------|--------|
| GET | /price-lists | لیست قیمت — index |
| GET | /price-lists/create | Create price list |
| POST | /price-lists | Store |
| GET | /price-lists/{priceList} | Show (admin) |
| GET | /price-lists/{priceList}/edit | Edit |
| PUT | /price-lists/{priceList} | Update |
| DELETE | /price-lists/{priceList} | Delete |
| GET | /price-lists/{priceList}/duplicate | Duplicate → create new |
| POST | /price-lists/{priceList}/generate-code | Generate unique code for URL |
| GET | /price-lists/{priceList}/links | Show share links (like menu customer-links) |

### Public (no auth)

| Method | Route | Action |
|--------|-------|--------|
| GET | /pricelist/{code} | لیست قیمت — customer view (no login) |
| GET | /product/{code} | Product landing page — customer view (no login) |

---

## 5. Controllers & Models

- `ProductController` — CRUD for products
- `PriceListController` — CRUD for price lists, duplicate, generate code
- `PriceListSectionController` — nested under price list
- `PriceListItemController` — nested under section
- `ProductLandingPageController` — CRUD for single-product landing pages
- `CustomerPriceListController` — public view لیست قیمت
- `CustomerProductController` — public view single product landing

Models: `Product`, `PriceList`, `PriceListSection`, `PriceListItem`, `ProductLandingPage`

---

## 6. Public Price List View (لیست قیمت)

- Layout: standalone page, no app nav
- Options from `price_lists`: title_text, show_prices, show_photos, template, primary_color, font_family
- **Templates**: `simple` (text only), `with_photos` (product photo + name + price), `grid` (card layout)
- Sections with items; each item: name, description, price (if show_prices), photo (if show_photos and product has photo)
- RTL, Vazirmatn, Persian digits

---

## 6b. Product Landing Page (صفحه فرود محصول)

- Manager creates a landing page for a single product
- Layout: standalone page, no app nav
- Content: product photo, name, description, price, CTA button
- **CTA options**:
  - `purchase` — link to external URL (e.g. shop, order form)
  - `call` — tel: link
  - `whatsapp` — WhatsApp link
  - `link` — custom URL
- Styling: title_text, headline, primary_color, font_family, cta_button_text
- Public route: `/product/{code}`

---

## 7. Invoice Form Changes

- Description input: add autocomplete from products
- On select: fill description, unit_price (from product.default_unit_price)
- Optional: store product_id for analytics / reporting
- User can still type freely (not forced to pick from catalog)

---

## 8. Implementation Order

1. **Phase 1**: Products catalog
   - Migration, model, controller, views
   - Tags (polymorphic)
   - API `/api/products/search` with smart search

2. **Phase 2**: Invoice autocomplete
   - Wire invoice form description to products search API
   - On select: fill description + unit_price
   - Optional: add product_id to invoice_items

3. **Phase 3**: Price lists
   - Migrations: price_lists, price_list_sections, price_list_items
   - CRUD for price lists
   - Sections CRUD
   - Add products to sections (price list items)
   - Duplicate price list
   - Generate code, show share links

4. **Phase 4**: Public customer view
   - Route /pricelist/{code}
   - CustomerPriceListController
   - View: layout + sections + items, show/hide prices, basic styling

5. **Phase 5**: Price list templates with photos
   - Templates: simple, with_photos, grid
   - Product photos in price list items

6. **Phase 6**: Product landing pages
   - CRUD for product_landing_pages
   - Public route /product/{code}
   - CTA: purchase URL, call, WhatsApp, custom link
   - Styling: headline, button text, colors, fonts

7. **Phase 7** (optional): Advanced styling
   - Similar to menu customizations: colors, fonts, footer, etc.

---

## 9. Nav / Settings

- Add "کالاها و خدمات" under settings or main nav
- Add "لیست قیمت" next to it

---

## 10. References

- Ordering project: `CustomerMenuController`, `MenuCustomization`, `/menu/{branch}/{code}`
- Ordering project: `resources/views/customer/menu.blade.php`
- Pocket-business: `InvoiceItem`, `resources/views/invoices/_form.blade.php`
- Pocket-business: `Tag` model (polymorphic taggable)
