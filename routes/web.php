<?php
use App\Http\Controllers\{HomeController, BookController, BookReviewController, CartController, CheckoutController, AccountController, NewsletterController, PageController, ProfileController};
use App\Http\Controllers\Auth\{CustomerAuthController, PublisherAuthController, AdminAuthController};
use App\Http\Controllers\Publisher as Pub;
use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

//CUSTOMER ROUTES
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/bulk-orders', [PageController::class, 'bulkOrders'])->name('bulk-orders');
Route::get('/book-rights', [PageController::class, 'bookRights'])->name('book-rights');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

// Customer auth
Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('account.login');
Route::post('/login', [CustomerAuthController::class, 'login'])->name('account.login.submit');
Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('account.register.form');
Route::post('/register', [CustomerAuthController::class, 'register'])->name('account.register');
Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('account.logout');

// Customer-protected (cart, checkout, orders)
Route::middleware('role:customer')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
    Route::get('/account/profile', [ProfileController::class, 'customerEdit'])->name('account.profile');
    Route::put('/account/profile', [ProfileController::class, 'update'])->name('account.profile.update');
    Route::put('/account/password', [ProfileController::class, 'updatePassword'])->name('account.password.update');
    Route::post('/books/{book}/reviews', [BookReviewController::class, 'store'])->name('books.reviews.store');
});

//PUBLISHER ROUTES
Route::prefix('publisher')->name('publisher.')->group(function () {
    Route::get('/login', [PublisherAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [PublisherAuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [PublisherAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [PublisherAuthController::class, 'register'])->name('register.submit');
    Route::post('/logout', [PublisherAuthController::class, 'logout'])->name('logout');

    Route::middleware('role:publisher')->group(function () {
        Route::get('/dashboard', [Pub\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'publisherEdit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::resource('books', Pub\BookController::class)->except('show');
        Route::get('/inventory', [Pub\InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/{book}/restock', [Pub\InventoryController::class, 'restock'])->name('inventory.restock');
        Route::post('/inventory/{book}/reduce', [Pub\InventoryController::class, 'reduce'])->name('inventory.reduce');
        Route::post('/inventory/{book}/adjust', [Pub\InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::get('/orders', [Pub\OrderController::class, 'index'])->name('orders.index');
        Route::patch('/orders/items/{orderItem}/status', [Pub\OrderController::class, 'updateStatus'])->name('orders.items.status');
    });
});

//ADMIN ROUTES
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('role:admin')->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'adminEdit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::get('/analytics', [Admin\AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/payment-settings', [Admin\PaymentSettingController::class, 'edit'])->name('payment-settings.edit');
        Route::put('/payment-settings', [Admin\PaymentSettingController::class, 'update'])->name('payment-settings.update');

        Route::get('/publishers', [Admin\PublisherController::class, 'index'])->name('publishers.index');
        Route::get('/publishers/create', [Admin\PublisherController::class, 'create'])->name('publishers.create');
        Route::post('/publishers', [Admin\PublisherController::class, 'store'])->name('publishers.store');
        Route::get('/publishers/{publisher}/edit', [Admin\PublisherController::class, 'edit'])->name('publishers.edit');
        Route::put('/publishers/{publisher}', [Admin\PublisherController::class, 'update'])->name('publishers.update');
        Route::patch('/publishers/{publisher}/approval', [Admin\PublisherController::class, 'updateApproval'])->name('publishers.approval');
        Route::delete('/publishers/{publisher}', [Admin\PublisherController::class, 'destroy'])->name('publishers.destroy');

        Route::get('/books', [Admin\BookController::class, 'index'])->name('books.index');
        Route::get('/books/create', [Admin\BookController::class, 'create'])->name('books.create');
        Route::post('/books', [Admin\BookController::class, 'store'])->name('books.store');
        Route::get('/books/{book}/edit', [Admin\BookController::class, 'edit'])->name('books.edit');
        Route::put('/books/{book}', [Admin\BookController::class, 'update'])->name('books.update');
        Route::patch('/books/{book}/status', [Admin\BookController::class, 'updateStatus'])->name('books.status');
        Route::patch('/books/{id}/restore', [Admin\BookController::class, 'restore'])->name('books.restore');
        Route::delete('/books/{id}/force', [Admin\BookController::class, 'forceDestroy'])->name('books.force-destroy');
        Route::delete('/books/{book}', [Admin\BookController::class, 'destroy'])->name('books.destroy');

        Route::get('/categories', [Admin\CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [Admin\CategoryController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [Admin\CategoryController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [Admin\CategoryController::class, 'destroyCategory'])->name('categories.destroy');
        Route::post('/authors', [Admin\CategoryController::class, 'storeAuthor'])->name('authors.store');
        Route::put('/authors/{author}', [Admin\CategoryController::class, 'updateAuthor'])->name('authors.update');
        Route::delete('/authors/{author}', [Admin\CategoryController::class, 'destroyAuthor'])->name('authors.destroy');

        Route::get('/coupons', [Admin\CouponController::class, 'index'])->name('coupons.index');
        Route::post('/coupons', [Admin\CouponController::class, 'store'])->name('coupons.store');
        Route::delete('/coupons/{coupon}', [Admin\CouponController::class, 'destroy'])->name('coupons.destroy');

        Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.status');
        Route::patch('/payments/{payment}/verify', [Admin\OrderController::class, 'verifyPayment'])->name('payments.verify');
        Route::get('/payments/{payment}/proof', [Admin\OrderController::class, 'paymentProof'])->name('payments.proof');

        Route::get('/newsletter', [Admin\NewsletterController::class, 'index'])->name('newsletter.index');
        Route::post('/newsletter/send', [Admin\NewsletterController::class, 'send'])->name('newsletter.send');
        Route::get('/newsletter/export', [Admin\NewsletterController::class, 'export'])->name('newsletter.export');
    });
});
