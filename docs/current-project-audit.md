# Existing Laravel Project Audit: "My 2nd Wife"

## 1. Project Overview & Environment
- **Framework**: Laravel 12.70.2
- **PHP**: 8.2.12 (ZTS x64, XAMPP)
- **Database**: MySQL 8.0 (`my_2nd_wife`)
- **Frontend Assets**: Tailwind CSS v4, `@tailwindcss/vite`, Alpine.js, Chart.js, Lucide Icons
- **Asset Pipeline**: Vite 7.3.7 compiling to `public/build/`
- **Application Port**: `http://localhost:8000`
- **Architecture**: Single-restaurant food delivery system with Customer Web Storefront, Admin Panel (`/admin`), and Versioned REST API (`/api/v1`).

---

## 2. Existing Database & Migrations (10 Migrations Applied)
1. `2026_10_28_060000_add_fields_to_users_table`: Phone, role (`super_admin`, `admin`, `order_manager`, `kitchen_staff`, `delivery_manager`, `delivery_person`, `customer`), avatar, `is_active`.
2. `2026_10_28_060001_create_addresses_table`: Customer saved addresses (Home, Work, Other) with landmark and instructions.
3. `2026_10_28_060002_create_categories_table`: Name, slug, description, image, display order, active status.
4. `2026_10_28_060003_create_products_table`: Regular price, discount price, `is_veg`, SKU, prep time, merchandising flags (`is_featured`, `is_popular`, `is_best_seller`), availability.
5. `2026_10_28_060004_create_product_variations_table`: Portion sizes (Regular, Medium, Large, Half, Full) with specific prices.
6. `2026_10_28_060005_create_addon_groups_and_addons_tables`: Add-on groups (single/multiple, required/optional) and individual add-on items with prices.
7. `2026_10_28_060006_create_coupons_and_usages_tables`: Percentage and fixed discounts with thresholds, caps, and expiry dates.
8. `2026_10_28_060007_create_orders_and_items_tables`: Orders, historical price snapshots (`order_items`, `order_item_addons`), and status history audit trails.
9. `2026_10_28_060008_create_payments_table`: Payment ledger (Cash on Delivery, Razorpay), status (`pending`, `completed`, `failed`), transaction IDs.
10. `2026_10_28_060009_create_restaurant_settings_and_banners_tables`: Key-value settings store, homepage banners, audit logs.

---

## 3. Existing Eloquent Models & Business Services
- **Models**: `User`, `Address`, `Category`, `Product`, `ProductImage`, `ProductVariation`, `AddonGroup`, `Addon`, `Coupon`, `CouponUsage`, `Order`, `OrderItem`, `OrderItemAddon`, `OrderStatusHistory`, `Payment`, `RestaurantSetting`, `HomepageBanner`, `AuditLog`.
- **Services**:
  - `CartService`: Session-based live database price recalculation, variation/addon validation, delivery charge threshold calculation, packaging fees, tax (GST) calculation.
  - `OrderService`: Transactional checkout (`DB::transaction`), historical name/price snapshot generation, status transition state machine, cancellation rules, rider assignment.
  - `PaymentService`: Razorpay API orders, HMAC SHA256 signature verification, webhook handler, sandbox simulation mode for offline test environments.

---

## 4. Existing Routes & Controllers
- **Customer Controllers**:
  - `HomeController.php`: Homepage (`/`)
  - `MenuController.php`: Food menu listing (`/menu`) and food details (`/food/{slug}`)
  - `CartController.php`: Cart listing (`/cart`), add item (`/cart/add`), update quantity (`/cart/update`), remove item (`/cart/remove`), apply/remove coupon (`/cart/apply-coupon`, `/cart/remove-coupon`), summary JSON (`/cart/summary`)
  - `CheckoutController.php`: Checkout view (`/checkout`) and order submission (`/checkout`)
  - `OrderTrackingController.php`: Public order tracking (`/order/track/{token}`) and live status JSON (`/order/status/{token}`)
  - `PaymentController.php`: Payment landing (`/order/payment/{token}`) and Razorpay verification (`/order/payment/{token}/verify`)
  - `CustomerAccountController.php`: Profile (`/account/profile`), Address book (`/account/addresses`), Orders list (`/account/orders`), Order details (`/account/orders/{orderNumber}`), Order cancellation (`/account/orders/{orderNumber}/cancel`), Reorder (`/account/orders/{orderNumber}/reorder`)
  - `AuthController.php`: Login (`/login`), Register (`/register`), Logout (`/logout`)
- **Admin Controllers**:
  - `AdminDashboardController.php`: Live metrics & Chart.js (`/admin/dashboard`)
  - `AdminCategoryController.php`: Category CRUD (`/admin/categories`)
  - `AdminProductController.php`: Product CRUD (`/admin/products`)
  - `AdminAddonController.php`: Add-on groups & items (`/admin/addons`)
  - `AdminOrderController.php`: Order management, rider assignment, invoice printing (`/admin/orders`)
  - `AdminCustomerController.php`: Customer management (`/admin/customers`)
  - `AdminCouponController.php`: Coupon management (`/admin/coupons`)
  - `AdminDeliveryController.php`: Delivery riders (`/admin/delivery`)
  - `AdminPaymentController.php`: Payment ledger (`/admin/payments`)
  - `AdminSettingsController.php`: Operational & website settings (`/admin/settings`)
  - `AdminReportController.php`: Financial reports & CSV export (`/admin/reports`)
- **API v1 Controllers**:
  - `ApiAuthController.php`, `ApiMenuController.php`, `ApiOrderController.php`, `ApiSettingsController.php` (under `/api/v1/*`)

---

## 5. Existing Blade Templates & Views
- Layouts: `layouts/app.blade.php`, `layouts/admin.blade.php`
- Components: `components/food-card.blade.php`, `components/category-card.blade.php`
- Customer Views: `customer/home.blade.php`, `customer/menu.blade.php`, `customer/product-details.blade.php`, `customer/cart.blade.php`, `customer/checkout.blade.php`, `customer/payment.blade.php`, `customer/track-order.blade.php`, `customer/account/*`
- Admin Views: `admin/dashboard.blade.php`, `admin/categories/*`, `admin/products/*`, `admin/addons/*`, `admin/orders/*`, `admin/customers/*`, `admin/coupons/*`, `admin/delivery/*`, `admin/payments/*`, `admin/settings/*`, `admin/reports/*`

---

## 6. What Needs Enhancement to Match FoodKing Reference
1. **Primary Color Harmonization**: Update CSS variables to exact FoodKing primary pink `#FF006B` (`rgb(255 0 107)`), deep heading text `#1F1F39`, and paragraph text `#6E7191`.
2. **FoodKing Header & Navbar**:
   - Add the persistent top announcement bar with restaurant phone, location badge, and language indicator.
   - Refine main navigation to include `Home`, `Menu`, `Offers`.
   - Implement the interactive **Slide-over Cart Drawer (`FrontendCartComponent`)** with order type toggle (Delivery vs. Takeaway), item steppers, and subtotal summary so customers can review cart from any page without leaving their flow.
   - Add real-time live search dropdown / page linking.
3. **FoodKing Homepage Layout**:
   - Hero Slider with navigation controls and rounded corners (`rounded-2xl`).
   - Category Swiper with circular icons and active highlight.
   - 2-Column Promotional Offers Banner Grid (`OfferComponent`) matching FoodKing's banner style.
   - "Featured Items" and "Popular Items" sections with exact FoodKing card proportions.
   - Delivery / App download highlight section.
   - 3-Column FoodKing Footer with newsletter subscription, quick links, app store badges, and contact details.
4. **FoodKing Menu Experience**:
   - Category swiper bar on top of the menu page.
   - Veg / Non-Veg filter pills with exact FoodKing pill styling.
   - Grid View vs. List View toggle buttons (`lab lab-element-3` / `lab lab-row-vertical`).
   - Interactive Food Details & Customization Modal (`ItemComponent` style) enabling 1-click portion and addon selection directly from the menu grid.
5. **Dedicated Offers Page (`/offers`) & Search Page (`/search`)**:
   - Create route and view for `/offers` matching FoodKing's `OffersComponent`.
   - Create route and view for `/search` matching FoodKing's `SearchItemComponent`.
6. **Checkout View Refinements**:
   - Match FoodKing's 2-column container (`max-w-[965px]`) with left branch/address/schedule/payment cards and right order summary card with item quantity counter badges.
7. **Customer Account & Order Tracking**:
   - Align order cards with FoodKing's `MyOrderComponent` styling.
   - Multi-step order timeline visualizer matching FoodKing's tracking step design.
