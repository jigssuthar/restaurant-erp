
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebsiteController;

// PUBLIC WEBSITE
Route::get('/', [WebsiteController::class, 'home'])
    ->name('home');

Route::get('/about', [WebsiteController::class, 'page'])
    ->defaults('slug', 'about')
    ->name('about');

Route::get('/features', [WebsiteController::class, 'page'])
    ->defaults('slug', 'features')
    ->name('features');

// SOLUTIONS
Route::get('/solutions/restaurants', [WebsiteController::class, 'page'])
    ->defaults('slug', 'restaurants')
    ->name('solutions.restaurants');

Route::get('/solutions/cafes', [WebsiteController::class, 'page'])
    ->defaults('slug', 'cafes')
    ->name('solutions.cafes');
use App\Http\Controllers\ContactController;

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');
Route::get('/solutions/cloud-kitchens', [WebsiteController::class, 'page'])
    ->defaults('slug', 'cloud-kitchens')
    ->name('solutions.cloud-kitchens');

// PRODUCTS
Route::get('/products/pos', [WebsiteController::class, 'page'])
    ->defaults('slug', 'pos')
    ->name('products.pos');

Route::get('/products/inventory', [WebsiteController::class, 'page'])
    ->defaults('slug', 'inventory')
    ->name('products.inventory');

Route::get('/products/multi-branch', [WebsiteController::class, 'page'])
    ->defaults('slug', 'multi-branch')
    ->name('products.multi-branch');

// OTHER PAGES
Route::get('/pricing', function () {
    $plans = \App\Models\SaasPlan::query()
        ->where('is_active', true)
        ->whereNotNull('price')
        ->whereNotNull('razorpay_plan_id')
        ->orderBy('sort_order')
        ->get();

    return view('website.pricing', compact('plans'));
})->name('pricing');

Route::get('/contact', function () {
    return view('website.contact');
})->name('contact');

Route::get('/faq', [WebsiteController::class, 'page'])
    ->defaults('slug', 'faq')
    ->name('faq');

// LEGAL
Route::get('/privacy-policy', [WebsiteController::class, 'page'])
    ->defaults('slug', 'privacy')
    ->name('privacy');

Route::get('/terms-and-conditions', [WebsiteController::class, 'page'])
    ->defaults('slug', 'terms')
    ->name('terms');


// AUTHENTICATION
Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.store');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])
        ->name('password.request');

    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])
        ->name('password.reset');

    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.update');
});

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\DiningTableController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SaasPlanController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\CustomerTableOrderController;

Route::get('/table/{token}', [CustomerTableOrderController::class, 'menu'])
    ->middleware('subscription')
    ->name('qr.menu');
Route::post('/table/{token}/orders', [CustomerTableOrderController::class, 'store'])
    ->middleware(['throttle:10,1', 'subscription'])
    ->name('qr.orders.store');
Route::post('/billing/razorpay/webhook', [BillingController::class, 'webhook'])
    ->middleware('throttle:120,1')
    ->name('razorpay.webhook');

Route::middleware(['auth', 'subscription'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware('role:admin,manager,staff')
            ->name('dashboard');

        Route::middleware('platform-admin')->group(function () {
            Route::resource('organizations', OrganizationController::class);
            Route::resource('saas-plans', SaasPlanController::class)->except(['show']);
        });

        Route::middleware('role:admin')->group(function () {
            Route::resource('staff', StaffController::class)->middleware('plan-limit:staff');
            Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
            Route::post('/billing/plans/{plan}/subscribe', [BillingController::class, 'subscribe'])
                ->name('billing.subscribe');
            Route::post('/billing/verify', [BillingController::class, 'verify'])->name('billing.verify');
            Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
        });

        Route::middleware('role:admin,manager')->group(function () {
            Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
            Route::resource('branches', BranchController::class)->middleware('plan-limit:branches');
            Route::resource('categories', CategoryController::class)->except(['index', 'show']);
            Route::resource('products', ProductController::class)->except(['index', 'show'])->middleware('plan-limit:products');
            Route::resource('dining-tables', DiningTableController::class)
                ->except(['index', 'show'])
                ->middleware('plan-limit:tables')
                ->parameters(['dining-tables' => 'diningTable']);
            Route::post('/dining-tables/{diningTable}/qr/rotate', [DiningTableController::class, 'rotateQr'])
                ->name('dining-tables.qr.rotate');
        });

        Route::middleware('role:admin,manager,staff')->group(function () {
            Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
            Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
            Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
            Route::patch('/orders/{order}/payment', [OrderController::class, 'markPaid'])->name('orders.payment');
            Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
            Route::get('/products', [ProductController::class, 'index'])->name('products.index');
            Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
            Route::get('/dining-tables', [DiningTableController::class, 'index'])->name('dining-tables.index');
            Route::get('/dining-tables/{diningTable}', [DiningTableController::class, 'show'])
                ->name('dining-tables.show');
        });

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    });
