<?php

use App\Http\Controllers\Admin\AdminAddonController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminCouponController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDeliveryController;
use App\Http\Controllers\Admin\AdminFooterSettingsController;
use App\Http\Controllers\Admin\AdminHeroBannerController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Delivery\DeliveryAuthController;
use App\Http\Controllers\Delivery\DeliveryDashboardController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Customer Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
Route::get('/food/{slug}', [MenuController::class, 'show'])->name('food.show');

// Offers & Promotions (FoodKing style)
Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');
Route::get('/offers/{slug}', [OfferController::class, 'show'])->name('offers.show');

// Dedicated Search Page
Route::get('/search', [SearchController::class, 'index'])->name('search.index');

// Help & Support Pages
Route::get('/about', [PageController::class, 'about'])->name('page.about');
Route::get('/terms', [PageController::class, 'terms'])->name('page.terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('page.privacy');
Route::get('/refund-policy', [PageController::class, 'refund'])->name('page.refund');
Route::get('/faq', [PageController::class, 'faq'])->name('page.faq');
Route::get('/contact', [PageController::class, 'contact'])->name('page.contact');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');

// Newsletter Subscription
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

// Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::post('/cart/remove-coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');
Route::get('/cart/summary', [CartController::class, 'summary'])->name('cart.summary');

// Checkout
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
Route::post('/checkout/check-delivery', [CheckoutController::class, 'checkDelivery'])->name('checkout.check-delivery');
Route::post('/checkout/reverse-geocode', [CheckoutController::class, 'reverseGeocode'])->name('checkout.reverse-geocode');

// Order Public Tracking
Route::get('/order/track/{token}', [OrderTrackingController::class, 'show'])->name('order.track');
Route::get('/order/status/{token}', [OrderTrackingController::class, 'statusJson'])->name('order.status.json');

// Payment Gateway Verification
Route::get('/order/payment/{token}', [PaymentController::class, 'showPaymentPage'])->name('order.payment');
Route::post('/order/payment/{token}/verify', [PaymentController::class, 'verifyRazorpay'])->name('order.payment.verify');
Route::post('/order/payment/{token}/failed', [PaymentController::class, 'paymentFailed'])->name('order.payment.failed');
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Customer Account Area (Authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('account')->name('account.')->group(function () {
    Route::get('/profile', [CustomerAccountController::class, 'profile'])->name('profile');
    Route::post('/profile', [CustomerAccountController::class, 'updateProfile'])->name('profile.update');
    Route::get('/addresses', [CustomerAccountController::class, 'addresses'])->name('addresses');
    Route::post('/addresses', [CustomerAccountController::class, 'storeAddress'])->name('addresses.store');
    Route::delete('/addresses/{address}', [CustomerAccountController::class, 'destroyAddress'])->name('addresses.destroy');
    Route::get('/orders', [CustomerAccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{orderNumber}', [CustomerAccountController::class, 'orderDetails'])->name('orders.details');
    Route::post('/orders/{orderNumber}/cancel', [CustomerAccountController::class, 'cancelOrder'])->name('orders.cancel');
    Route::post('/orders/{orderNumber}/reorder', [CustomerAccountController::class, 'reorder'])->name('orders.reorder');
});

/*
|--------------------------------------------------------------------------
| Admin Panel Routes (Protected by Auth & Role)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin,admin,order_manager,kitchen_staff,delivery_manager'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Categories
        Route::resource('categories', AdminCategoryController::class);

        // Products & Variations
        Route::resource('products', AdminProductController::class);
        Route::post('products/{product}/toggle-availability', [AdminProductController::class, 'toggleAvailability'])->name('products.toggle');
        Route::delete('products/{product}/variations/{variation}', [AdminProductController::class, 'destroyVariation'])->name('products.variations.destroy');

        // Addon Groups & Addons
        Route::get('addons', [AdminAddonController::class, 'index'])->name('addons.index');
        Route::post('addons/groups', [AdminAddonController::class, 'storeGroup'])->name('addons.groups.store');
        Route::delete('addons/groups/{group}', [AdminAddonController::class, 'destroyGroup'])->name('addons.groups.destroy');
        Route::post('addons/groups/{group}/items', [AdminAddonController::class, 'storeAddon'])->name('addons.items.store');
        Route::delete('addons/items/{addon}', [AdminAddonController::class, 'destroyAddon'])->name('addons.items.destroy');

        // Orders
        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/live-alerts', [AdminOrderController::class, 'liveAlerts'])->name('orders.live-alerts');
        Route::get('orders/export', [AdminOrderController::class, 'exportCsv'])->name('orders.export');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('orders/{order}/cancel', [AdminOrderController::class, 'cancelOrder'])->name('orders.cancel');
        Route::post('orders/{order}/assign-rider', [AdminOrderController::class, 'assignRider'])->name('orders.assign-rider');
        Route::post('orders/{order}/notes', [AdminOrderController::class, 'updateInternalNotes'])->name('orders.notes');
        Route::get('orders/{order}/receipt', [AdminOrderController::class, 'printReceipt'])->name('orders.receipt');
        Route::get('orders/{order}/invoice', [AdminOrderController::class, 'printInvoice'])->name('orders.invoice');

        // Customers
        Route::get('customers', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');
        Route::post('customers/{customer}/toggle-status', [AdminCustomerController::class, 'toggleStatus'])->name('customers.toggle');

        // Coupons
        Route::resource('coupons', AdminCouponController::class);

        // Delivery Riders
        Route::get('delivery', [AdminDeliveryController::class, 'index'])->name('delivery.index');
        Route::post('delivery', [AdminDeliveryController::class, 'store'])->name('delivery.store');
        Route::put('delivery/{rider}', [AdminDeliveryController::class, 'update'])->name('delivery.update');
        Route::post('delivery/{rider}/toggle', [AdminDeliveryController::class, 'toggleStatus'])->name('delivery.toggle');

        // Payments
        Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::post('payments/{payment}/verify', [AdminPaymentController::class, 'markAsPaid'])->name('payments.verify');

        // Push Notifications (FCM) & History
        Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/send-test', [AdminNotificationController::class, 'sendTest'])->name('notifications.test');
        Route::post('notifications/validate-connection', [AdminNotificationController::class, 'validateConnection'])->name('notifications.validate');
        Route::post('notifications/{log}/retry', [AdminNotificationController::class, 'retry'])->name('notifications.retry');
        Route::post('notifications/retry-all', [AdminNotificationController::class, 'retryAllFailed'])->name('notifications.retry-all');
        Route::post('notifications/clear-logs', [AdminNotificationController::class, 'clearOldLogs'])->name('notifications.clear');
        Route::post('settings/notifications/firebase/test', [AdminNotificationController::class, 'sendTest'])->name('settings.notifications.test');
        Route::post('settings/notifications/firebase/validate', [AdminNotificationController::class, 'validateConnection'])->name('settings.notifications.validate');

        // Settings & Website
        Route::get('settings/receipt/test', [AdminSettingsController::class, 'testReceipt'])->name('settings.receipt.test');
        Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::post('settings', [AdminSettingsController::class, 'updateSettings'])->name('settings.update');
        Route::post('settings/banners', [AdminSettingsController::class, 'storeBanner'])->name('banners.store');
        Route::put('settings/banners/{banner}', [AdminSettingsController::class, 'updateBanner'])->name('banners.update');
        Route::patch('settings/banners/{banner}/toggle', [AdminSettingsController::class, 'toggleBanner'])->name('banners.toggle');
        Route::delete('settings/banners/{banner}', [AdminSettingsController::class, 'destroyBanner'])->name('banners.destroy');
        Route::post('settings/banners/store-alias', [AdminSettingsController::class, 'storeBanner'])->name('settings.banners.store');
        Route::put('settings/banners/{banner}/update-alias', [AdminSettingsController::class, 'updateBanner'])->name('settings.banners.update');
        Route::patch('settings/banners/{banner}/toggle-alias', [AdminSettingsController::class, 'toggleBanner'])->name('settings.banners.toggle');
        Route::delete('settings/banners/destroy-alias/{banner}', [AdminSettingsController::class, 'destroyBanner'])->name('settings.banners.destroy');

        // Website Management: Homepage Banners
        Route::get('banners', [AdminHeroBannerController::class, 'index'])->name('banners.index');
        Route::get('hero-banners', [AdminHeroBannerController::class, 'index'])->name('hero-banners.index');
        Route::get('hero-banners/create', [AdminHeroBannerController::class, 'create'])->name('hero-banners.create');
        Route::post('hero-banners', [AdminHeroBannerController::class, 'store'])->name('hero-banners.store');
        Route::get('hero-banners/{heroBanner}/edit', [AdminHeroBannerController::class, 'edit'])->name('hero-banners.edit');
        Route::put('hero-banners/{heroBanner}', [AdminHeroBannerController::class, 'update'])->name('hero-banners.update');
        Route::patch('hero-banners/{heroBanner}/toggle', [AdminHeroBannerController::class, 'toggle'])->name('hero-banners.toggle');
        Route::delete('hero-banners/{heroBanner}', [AdminHeroBannerController::class, 'destroy'])->name('hero-banners.destroy');

        // Offer Banner 1 (Left Promo Banner)
        Route::post('homepage-banners/offer-1', [AdminHeroBannerController::class, 'updateOffer1'])->name('banners.offer1.update');
        Route::patch('homepage-banners/offer-1/toggle', [AdminHeroBannerController::class, 'toggleOffer1'])->name('banners.offer1.toggle');
        Route::delete('homepage-banners/offer-1', [AdminHeroBannerController::class, 'destroyOffer1'])->name('banners.offer1.destroy');

        // Offer Banner 2 (Right Promo Banner)
        Route::post('homepage-banners/offer-2', [AdminHeroBannerController::class, 'updateOffer2'])->name('banners.offer2.update');
        Route::patch('homepage-banners/offer-2/toggle', [AdminHeroBannerController::class, 'toggleOffer2'])->name('banners.offer2.toggle');
        Route::delete('homepage-banners/offer-2', [AdminHeroBannerController::class, 'destroyOffer2'])->name('banners.offer2.destroy');

        // Website Management: Website Pages CMS
        Route::resource('pages', AdminPageController::class)->except(['show']);
        Route::post('pages/{page}/toggle', [AdminPageController::class, 'toggle'])->name('pages.toggle');

        // Website Management: Footer Settings & Links
        Route::get('footer-settings', [AdminFooterSettingsController::class, 'index'])->name('footer.index');
        Route::post('footer-settings/general', [AdminFooterSettingsController::class, 'updateGeneral'])->name('footer.general.update');
        Route::post('footer-settings/links', [AdminFooterSettingsController::class, 'storeLink'])->name('footer.links.store');
        Route::put('footer-settings/links/{link}', [AdminFooterSettingsController::class, 'updateLink'])->name('footer.links.update');
        Route::patch('footer-settings/links/{link}/toggle', [AdminFooterSettingsController::class, 'toggleLink'])->name('footer.links.toggle');
        Route::post('footer-settings/links/reorder', [AdminFooterSettingsController::class, 'reorderLinks'])->name('footer.links.reorder');
        Route::delete('footer-settings/links/{link}', [AdminFooterSettingsController::class, 'destroyLink'])->name('footer.links.destroy');

        // Reports
        Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [AdminReportController::class, 'exportCsv'])->name('reports.export');
    });

/*
|--------------------------------------------------------------------------
| Delivery Partner Portal Routes (Protected by Auth & Role)
|--------------------------------------------------------------------------
*/
Route::prefix('delivery')->name('delivery.')->group(function () {
    // Dedicated Delivery Partner Authentication
    Route::middleware('guest')->group(function () {
        Route::get('/login', [DeliveryAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [DeliveryAuthController::class, 'login'])->name('login.post');
    });
    Route::post('/logout', [DeliveryAuthController::class, 'logout'])->name('logout')->middleware('auth');

    // Authenticated Delivery Partner Area (Role Protected)
    Route::middleware(['auth', 'role:delivery_person'])->group(function () {
        Route::get('/dashboard', [DeliveryDashboardController::class, 'dashboard'])->name('dashboard');

        // Order Management
        Route::get('/orders', [DeliveryDashboardController::class, 'orders'])->name('orders.index');
        Route::get('/available', [DeliveryDashboardController::class, 'available'])->name('available.index');
        Route::post('/orders/{order}/claim', [DeliveryDashboardController::class, 'claim'])->name('orders.claim');
        Route::get('/orders/{order}', [DeliveryDashboardController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/status', [DeliveryDashboardController::class, 'updateStatus'])->name('orders.status');

        // History & Performance
        Route::get('/history', [DeliveryDashboardController::class, 'history'])->name('history');

        // Earnings
        Route::get('/earnings', [DeliveryDashboardController::class, 'earnings'])->name('earnings');

        // Profile & Duty Status
        Route::get('/profile', [DeliveryDashboardController::class, 'profile'])->name('profile');
        Route::post('/profile', [DeliveryDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::post('/toggle-duty', [DeliveryDashboardController::class, 'toggleDuty'])->name('duty.toggle');
    });
});

