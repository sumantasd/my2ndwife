# FoodKing Page Coverage & Implementation Plan

## 1. Page & Feature Comparison Matrix

| FoodKing Reference Page / Feature | Existing Laravel Page | Missing Elements | Required Changes | Status |
| :--- | :--- | :--- | :--- | :--- |
| **Global Theme & Tokens** | `resources/css/app.css` & `app.blade.php` | FoodKing exact primary pink (`#FF006B`), Rubik typography, `#1F1F39` headings, `#6E7191` body text | Update CSS custom properties, import Google Fonts `Rubik` & `Public Sans`, refine button & card tokens | **Implemented but needs design improvements** |
| **Top Announcement Strip** | `layouts/app.blade.php` | Phone contact, branch/address indicator, language selector | Add FoodKing-style top utility bar above main header | **Partially implemented** |
| **Main Header & Sticky Nav** | `layouts/app.blade.php` | Direct links to `Home`, `Menu`, `Offers`, active scroll detection, slide-over cart trigger with subtotal price display | Refine header layout, add active scroll shadow effect, display live subtotal in cart button | **Implemented but needs design improvements** |
| **Slide-over Cart Drawer** | *None* (only dedicated `/cart` page existed) | FoodKing's slide-over right canvas with delivery/takeaway toggle and instant checkout trigger | Create reusable Blade cart drawer component with Alpine.js controls, allowing customers to view and update cart from any page | **Missing** |
| **Mobile Bottom Navigation** | `layouts/app.blade.php` | FoodKing mobile bottom bar icons (`Home`, `Menu`, `Cart`, `Profile`) | Refine mobile bottom navigation styling and active route indicators | **Partially implemented** |
| **Homepage Hero Slider** | `customer/home.blade.php` | Multi-slide carousel with indicator dots, rounded-2xl banner cards | Upgrade hero section to responsive banner slider with FoodKing styling | **Implemented but needs design improvements** |
| **Category Swiper Carousel** | `customer/home.blade.php` & `components/category-card.blade.php` | Circular icon cards in soft pink background, bold titles | Match FoodKing `CategoryComponent` visual styling | **Implemented but needs design improvements** |
| **Promotional Offers Banner** | `customer/home.blade.php` | 2-Column banner grid (`OfferComponent`) highlighting promotions | Add FoodKing-style promotional offer banner section linking to offer dishes | **Missing** |
| **Featured & Popular Food Sections** | `customer/home.blade.php` & `components/food-card.blade.php` | Exact FoodKing card proportions, veg/non-veg dot badges, current/previous price formatting, `+` Add button | Update `food-card.blade.php` to match FoodKing grid card design | **Implemented but needs design improvements** |
| **Food Customization Modal** | *None* (only dedicated `/food/{slug}` existed) | In-page customization modal (`ItemComponent`) allowing instant portion/addon selection without leaving page | Create interactive modal component with portion picker, addon checkboxes, instructions, and add to cart action | **Missing** |
| **Food Menu Page** | `customer/menu.blade.php` | Category swiper bar on top, Veg/Non-Veg filter pills, Grid vs. List view toggle | Implement top category bar, pill filters, and Grid vs. List view modes | **Implemented but needs design improvements** |
| **Dedicated Offers Page** | *None* | Dedicated `/offers` view showcasing promotional campaigns | Add route `offers`, controller method, and view matching FoodKing `OffersComponent` | **Missing** |
| **Dedicated Search Page** | *None* (only in-menu filter existed) | Dedicated `/search` view with keyword input and live results | Add route `search`, controller method, and view matching FoodKing `SearchItemComponent` | **Missing** |
| **Checkout Flow** | `customer/checkout.blade.php` | 2-column container (`max-w-[965px]`), left cards (Branch, Address, Schedule, Payment), right summary card with quantity badges | Restyle checkout page to match FoodKing's `CheckoutComponent` | **Implemented but needs design improvements** |
| **Customer Orders List** | `customer/account/orders.blade.php` | FoodKing order card styling (`w-full rounded-lg py-2 px-3 border border-[#EFF0F6]`), Rubik typography, status pills | Refine customer order list cards to match FoodKing `MyOrderComponent` | **Implemented but needs design improvements** |
| **Order Tracking Timeline** | `customer/track-order.blade.php` | FoodKing 6-step progress step bar, rider info badge, live JSON status | Update timeline visualizer to match FoodKing order tracking step design | **Implemented but needs design improvements** |
| **Customer Profile & Address Book** | `customer/account/profile.blade.php`, `addresses.blade.php` | FoodKing centered cards (`max-w-[550px]`), clean form inputs | Restyle profile and address pages to match FoodKing `EditProfileComponent` and `AddressComponent` | **Implemented but needs design improvements** |
| **Login & Register Views** | `auth/login.blade.php`, `register.blade.php` | FoodKing compact centered card (`max-w-[360px]`), rounded-3xl primary buttons | Restyle auth cards to match FoodKing `LoginComponent` | **Implemented but needs design improvements** |
| **Footer Component** | `layouts/app.blade.php` | 3-column layout, newsletter subscription form, quick links, app store download badges | Rebuild shared footer to match FoodKing `FrontendFooterComponent` | **Implemented but needs design improvements** |

---

## 2. Prioritized Implementation Sequence

1. **Step 1: Design Tokens & Typography**: Update CSS variables in `resources/css/app.css` to exact FoodKing primary pink (`#FF006B`), headings (`#1F1F39`), paragraphs (`#6E7191`), backgrounds (`#F7F7FC`), and import Google Fonts `Rubik` and `Public Sans`.
2. **Step 2: Shared Header, Navigation & Slide-over Cart Drawer**:
   - Rebuild shared navigation in `layouts/app.blade.php` with top announcement bar, brand logo, menu links (`Home`, `Menu`, `Offers`), search trigger, live subtotal cart button, and mobile bottom nav.
   - Build slide-over Cart Drawer (`FrontendCartComponent`) with delivery/takeaway toggle, quantity stepper, coupon, and checkout button.
3. **Step 3: Food Card & Modal Components**:
   - Refine `components/food-card.blade.php` to match FoodKing grid card design with veg/non-veg indicator, price strike-through, and `+` add button.
   - Build interactive Food Customization Modal (`ItemComponent`) with portion variations, add-on groups, quantity stepper, and instructions.
4. **Step 4: Homepage Transformation**:
   - Update `customer/home.blade.php` with Hero banner slider, Category carousel, 2-column Promotional Offers banner, Featured Dishes section, Popular Dishes section, Delivery app promo strip, and FoodKing 3-column footer.
5. **Step 5: Food Menu Page Transformation**:
   - Update `customer/menu.blade.php` with top category swiper, Veg / Non-Veg filter pills, and Grid vs. List view toggle.
6. **Step 6: Dedicated Offers Page & Search Page**:
   - Add routes and views for `/offers` and `/search`.
7. **Step 7: Checkout & Cart Page Refinements**:
   - Update `customer/checkout.blade.php` and `customer/cart.blade.php` to match FoodKing's 2-column container.
8. **Step 8: Customer Account & Order Tracking**:
   - Update `customer/account/*` and `customer/track-order.blade.php` to match FoodKing's order cards and 6-step tracking timeline.
9. **Step 9: Asset Compilation & Automated Testing**:
   - Compile assets with `npm run build` and run `php artisan test` to verify all 33+ tests pass.
10. **Step 10: Final Verification & Completion Report**.
