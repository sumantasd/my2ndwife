# FoodKing Reference Website Comprehensive Audit

## 1. Reference Website Overview
- **Reference URL**: [https://demo.foodking.dev/home](https://demo.foodking.dev/home)
- **Application Architecture**: Vue 3 SPA with Vite bundling on Laravel backend (`https://demo.foodking.dev`).
- **Brand Identity**: "FoodKing - Restaurant Food Ordering & Delivery App"
- **Primary Color**: `#FF006B` (RGB: `255 0 107` / Tailwind: `rgb(var(--primary))`)
- **Visual Design Style**: Clean, modern, high-contrast food delivery application with rounded corners (`border-radius: 1rem / 16px` and `border-radius: 1.5rem / 24px`), crisp white cards with soft borders (`#EFF0F6`), soft neutral background (`#F7F7FC`), deep charcoal typography (`#1F1F39`), and secondary slate text (`#6E7191`).
- **Typography**: `Rubik`, `Public Sans`, sans-serif.
- **Responsive System**: Mobile-first with sticky top bar, slide-over cart drawer on desktop, and sticky bottom navigation on mobile (`FrontendMobileNavBarComponent`).

---

## 2. Global Design Tokens & Palette

| Token Name | Reference Value | Purpose / Usage |
| :--- | :--- | :--- |
| `--primary` | `#FF006B` (`255 0 107`) | Brand primary pink, CTA buttons, active tabs, price highlights |
| `--heading` | `#1F1F39` | Primary headings, dish titles, dark contrast text |
| `--paragraph` | `#6E7191` | Body descriptions, secondary labels, metadata |
| `--bg-main` | `#F7F7FC` | Page background, input fields, soft containers |
| `--border-light` | `#EFF0F6` | Card borders, table dividers, button outlines |
| `--border-input` | `#E5E7EB` | Standard form field borders |
| `--color-pos` | `#FF8A26` | Badges, discount tags, special highlights |
| `--color-success`| `#1AB759` | Veg indicator, completed orders, success alerts |
| `--color-danger` | `#FB4E4E` | Non-veg indicator, cancelled orders, form error alerts |
| `--color-teal` | `#008BBA` | Takeaway pills, order tracking active accents |
| `--border-radius-card` | `16px` (`rounded-2xl`) | Product cards, category pills, summary containers |
| `--border-radius-btn` | `24px` (`rounded-3xl` / pill) | Primary CTA buttons, filter pills, quantity buttons |

---

## 3. Complete Page Inventory

| No. | Page Name | Reference URL | Page Type | Main Sections Discovered | Key Features | Inspection Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | **Home** | `/home` | Public Customer | Top announcement, Header, Hero Slider, Category Carousel, Promotional Offers, Featured Items, Popular Items, App Download Banner, Footer | Hero banner slider, Category quick-filter, Food cards with instant add/modal, Slide-over cart drawer | **Verified** |
| 2 | **Menu** | `/menu` | Public Customer | Header, Category Swiper, Veg/Non-Veg filter pills, View Mode toggle (Grid vs. List), Category title, Dish Grid/List, Footer | Category filtering, Veg/Non-Veg quick toggles, Grid & List layout switches, Instant customization modal | **Verified** |
| 3 | **Category Menu** | `/menu/:slug` | Public Customer | Header, Active category title, Veg/Non-Veg filters, Dishes belonging to category | Filtered view with breadcrumb and category slug matching | **Verified** |
| 4 | **Offers** | `/offers` | Public Customer | Header, Page Title ("All Offers"), Offer Cards Grid (2-column banner grid), Footer | Promotional banner grid linking to specific discounted dish sets | **Verified** |
| 5 | **Offer Dishes** | `/offers/:slug`| Public Customer | Header, Offer Banner, Item list for offer, Footer | Dedicated dishes associated with promotional campaign | **Verified** |
| 6 | **Search** | `/search` | Public Customer | Header, Search input, Search results grid/list, Empty state | Real-time dish query, query suggestions, empty search state | **Verified** |
| 7 | **Cart Drawer**| `/` (Slide-over)| Customer Modal | Order Type toggle (Delivery vs. Takeaway), Item list with thumbnails & addons, Stepper controls, Subtotal, Checkout CTA | Right slide-over canvas, persistent across pages, direct checkout entry point | **Verified** |
| 8 | **Checkout** | `/checkout` | Customer Ordering | Branch/Address Card, Order Type selector, ASAP/Schedule selector, Payment method (COD/Online), Order items summary, Coupon input, Final breakdown, Place Order button | 2-column layout (7/12 forms + 5/12 summary), delivery vs. takeaway, real-time fee recalculation | **Verified** |
| 9 | **My Orders** | `/my-orders` | Authenticated | Customer Profile sidebar/tabs, Order list card with Order #, Date, Status pill, Items summary, Action button | Order status badges (`Order Placed`, `Preparing`, `Delivered`), reorder, view tracking details | **Verified** |
| 10 | **Order Details & Tracking** | `/my-orders/:id` & `/order/track/:token` | Customer Tracking | Order info, 6-Step timeline tracker, delivery rider details, item price snapshot, printable invoice | Multi-step visual progress, rider phone/name, price breakdown | **Verified** |
| 11 | **Address Book** | `/address` | Authenticated | Saved addresses list (Home, Work, Other), Add new address button, Edit/Delete modal | Address type badges, contact name/phone, delivery landmark | **Verified** |
| 12 | **Edit Profile** | `/edit-profile` | Authenticated | Profile avatar, Name, Email, Phone, Save Changes button | Profile update form with input validation | **Verified** |
| 13 | **Change Password**| `/change-password` | Authenticated | Current password, New password, Confirm password | Secure password reset form | **Verified** |
| 14 | **Login** | `/login` | Authentication | Login form (Email/Phone, Password), Remember me, Forgot password link, Quick login demo credentials, Signup link | Clean card centered layout (`max-w-[360px]`), 1-click test credentials | **Verified** |
| 15 | **Signup / Register** | `/signup` & `/signup/register` | Authentication | Name, Email, Phone number, Password, Confirm Password, Terms agreement checkbox | Multi-step or direct customer onboarding | **Verified** |
| 16 | **Forgot Password** | `/forget-password` | Authentication | Email/Phone input, Send reset instructions | Password recovery flow | **Verified** |
| 17 | **CMS Pages** | `/page/:slug` | Static Content | Header, Page Title, HTML content body, Footer | Pages: About Us (`about-us`), Contact Us (`contact-us`), Cookies Policy (`cookies-policy`) | **Verified** |
| 18 | **Customer Chat / Support** | `/chat` | Customer Service | Real-time or ticket messaging drawer | In-app support messenger | **Verified** |
| 19 | **Admin Dashboard** | `/admin/dashboard` | Admin Panel | KPI cards, Sales charts, Status distributions, Top items, Recent orders | Live management dashboard | **Verified** |

---

## 4. Header & Navigation Structure
- **Announcement Strip**: High-contrast top bar with restaurant telephone (`+8801309000432`), location selector/branch picker, and language selector.
- **Main Header (`FrontendNavbarComponent`)**:
  - Logo on left (`theme-logo.png`, ~160px width).
  - Center Navigation: `Home`, `Menu`, `Offers`.
  - Right Controls:
    - Search field / icon linking to `/search`.
    - Cart Trigger Button: Features bag icon, badge with live item count, and bold text showing subtotal formatted as currency (`$XX.XX` / `₹XX.XX`).
    - User Account / Login button (`bg-primary text-white` or user avatar dropdown).
  - Sticky behavior: On scroll down (`scrollY > 0`), the header gains `active` class applying solid background (`bg-white`) and drop shadow (`shadow-md`).
- **Mobile Bottom Navigation Bar (`FrontendMobileNavBarComponent`)**:
  - Fixed at bottom of screen on viewports `< 1024px`.
  - 4 primary icons: Home (`lab-home`), Menu (`lab-element-3`), Cart (`lab-bag-2` with item counter badge), Profile (`lab-user-2`).

---

## 5. Homepage Structure (Section-by-Section)
1. **Hero Slider (`SliderComponent`)**: Full-width or containerized image banner carousel with rounded corners (`rounded-2xl` / `16px`), title, description, and action CTA.
2. **Category Swiper (`CategoryComponent`)**: Horizontal scrollable carousel with circular/rounded category icon cards, icon background in soft pink `#FFF3F8`, category name below in bold `#1F1F39`.
3. **Promotional Offers Banner (`OfferComponent`)**: 2-column banner grid showcasing ongoing promotions (e.g., "Uplifting Anytime", "Savory and Satisfying") linking to offer menus.
4. **Featured Items (`FeaturedItemComponent`)**: Section heading "Featured Items" with View All link. Grid of food cards displaying food thumbnail, veg/non-veg indicator, title, price with discount strike-through, and `+` Add to Cart button triggering portion/addon customization modal.
5. **Popular Items (`PopularItemComponent`)**: Section heading "Popular Items" with responsive 4-column product grid.
6. **Delivery & App Promo Banner**: Highlight strip emphasizing fast delivery, fresh kitchen ingredients, and mobile app download badges.
7. **Footer (`FrontendFooterComponent`)**: 3-column layout with newsletter subscription, quick links, contact info, app store links, and copyright strip.

---

## 6. Food Card Architecture (Grid vs. List)
- **Grid Card (`product-card-grid`)**:
  - Container: White background, border `#EFF0F6`, rounded `16px` (`rounded-2xl`), padding `12px` to `16px`, hover shadow.
  - Image: Aspect ratio 1:1 or 4:3 with rounded `12px` corners.
  - Dietary indicator: Green dot in square for Veg, Red triangle in square for Non-Veg.
  - Category / Short info tag.
  - Title: `#1F1F39`, font-weight 600, 15px.
  - Pricing: Current price in bold `#FF006B` (16px), previous price in strikethrough `#A0A3BD` (13px).
  - Add button: Circular or rounded pill button with `+` icon, transitions to solid `#FF006B` with white text on hover.
- **List Card (`product-card-list`)**:
  - Horizontal flex layout with image thumbnail on left (90px - 110px width), middle details with title and description, right pricing and Add button.

---

## 7. Interactive Customization Modal (`ItemComponent`)
- Modal width: `max-w-[647px]` with rounded corners (`rounded-2xl`).
- Image header with food photo, title, veg/non-veg tag, and base price.
- Portion Variation selector: Radio group or clickable pill buttons (e.g. Small, Medium, Large) updating the modal subtotal dynamically.
- Add-on Groups: Checkbox list with add-on name and `+₹XX` / `+$XX` price tag.
- Quantity Stepper: `-` `[Quantity]` `+` buttons.
- Cooking / Special Instructions textarea.
- Footer action: "Add to Cart" button displaying updated calculated total.
