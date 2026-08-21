# College Street Online — Laravel Build

An online bookstore & multi-vendor publisher marketplace, built per the
provided SRS on Laravel 11 + MySQL. This is the **~70% "core commerce
strong"** build described in the SRS, brought forward with the specific
fixes the SRS recommended for the known defects (see below).

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
# create a MySQL database named college_street_online, then:
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Demo accounts created by the seeder (password for all: `password`):
- Admin: `admin@collegestreetonline.com`
- Publisher: `ganguram@collegestreetonline.com` / `bengalacademic@collegestreetonline.com`
- Customer: `customer@collegestreetonline.com`

## What's implemented, and how it maps to the SRS fixes

- **FR-3 Inventory** — `InventoryService` funnels every stock change
  (sale/restock/cancel/return) through one `inventory_transactions`
  ledger with row-level locking (`lockForUpdate`), so `inventory.quantity`
  is always derived from the ledger rather than touched ad hoc — this is
  the exact root-cause fix the SRS calls for.
- **FR-6 Checkout** — `PricingService` is the single source of truth for
  shipping/platform-fee math, used by both the cart page and checkout, so
  the two can't drift out of sync.
- **FR-8 Order Management** — `Order::transitionTo()` writes every status
  change to a new `order_status_history` table (actor, from, to,
  timestamp) instead of only overwriting the status column.
- **FR-9 Coupons** — `Coupon::computeDiscount()` always clamps the
  discount to the order subtotal server-side, and validity is re-checked
  at order placement, not just when first applied to the cart.
- **FR-4 Search** — `TransliterationService` re-indexes the Bengali
  transliteration on every save (a `Book` model `saving` hook), not only
  at creation.
- **FR-1/Section 4** — role-based access via a single `role` enum column
  and one `RoleMiddleware` applied at the route-group level; ownership
  checks (publisher editing only their own books) happen in the
  controllers on top of that.

## Still pending (matches the SRS's own status — not silently dropped)

- **FR-7 Payment gateway** — still manual UTR-upload + admin verification
  only, as specified; no gateway integration (SRS Section 10.2).
- **FR-11 Reviews & Ratings** — `book_reviews` table and model exist;
  no submission UI yet (SRS marks this Pending).
- **Bulk Order pricing tiers** and **Book Rights** — pages exist as
  designed placeholders per SRS Section 5.1; business logic not built.
- Admin analytics dashboard beyond the current stat cards (SRS Section 10.5).

See `Features-Completion-Doc.docx` for the full page-by-page and
FR-by-FR status.

## Structure

Standard Laravel MVC. `app/Services/` holds the three services above.
`app/Http/Controllers/{Publisher,Admin}/` are role-scoped. Views are
Blade, styled with the same CSS as the UI/UX demo (`public/css/site.css`,
`public/css/admin.css`) — same navy/gold palette and Fraunces+Outfit type.
