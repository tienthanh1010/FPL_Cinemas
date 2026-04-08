<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AuditoriumController;
use App\Http\Controllers\Admin\AuthController;
<<<<<<< HEAD
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CinemaController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ContentPostController;
=======
<<<<<<< HEAD
use App\Http\Controllers\Admin\BookingController;
=======
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CinemaController;
use App\Http\Controllers\Admin\CouponController;
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EquipmentController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\MaintenanceRequestController;
<<<<<<< HEAD
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\Admin\MovieVersionController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PricingProfileController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ShowController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StaffShiftController;
use App\Http\Controllers\Admin\TicketController;
=======
<<<<<<< HEAD
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\Admin\MovieVersionController;
use App\Http\Controllers\Admin\PricingProfileController;
use App\Http\Controllers\Admin\RefundController;
=======
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\Admin\MovieVersionController;
use App\Http\Controllers\Admin\PricingProfileController;
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ShowController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StaffShiftController;
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AdminGuest;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware([AdminGuest::class])->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    });

    Route::post('logout', [AuthController::class, 'logout'])
        ->middleware([AdminAuth::class])
        ->name('logout');

    Route::middleware([AdminAuth::class])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])
            ->middleware('admin.can:dashboard.view')
            ->name('dashboard');

<<<<<<< HEAD
        Route::get('reports', [ReportController::class, 'index'])
            ->middleware('admin.can:reports.view')
            ->name('reports.index');

        Route::middleware('admin.can:catalog.manage')->group(function () {
            Route::resource('movies', MovieController::class);
            Route::resource('movie-versions', MovieVersionController::class)
                ->parameters(['movie-versions' => 'movieVersion'])
                ->names('movie_versions');
            Route::resource('categories', CategoryController::class);
        });

        Route::middleware('admin.can:showtimes.manage')->group(function () {
            Route::resource('cinemas', CinemaController::class);
            Route::resource('auditoriums', AuditoriumController::class);
            Route::resource('shows', ShowController::class);
            Route::post('shows/{show}/seats/block', [ShowController::class, 'blockSeat'])->name('shows.seats.block');
            Route::delete('shows/{show}/seats/{seatBlock}/unblock', [ShowController::class, 'unblockSeat'])->name('shows.seats.unblock');
            Route::resource('pricing-profiles', PricingProfileController::class)
                ->parameters(['pricing-profiles' => 'pricingProfile'])
                ->names('pricing_profiles');
        });

        Route::middleware('admin.can:bookings.manage')->group(function () {
            Route::resource('bookings', BookingController::class)->only(['index', 'show', 'update']);
            Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        });

        Route::middleware('admin.can:tickets.checkin')->group(function () {
            Route::resource('tickets', TicketController::class)->only(['index', 'show']);
            Route::post('tickets/quick-checkin', [TicketController::class, 'quickCheckIn'])->name('tickets.quick_checkin');
            Route::post('tickets/{ticket}/check-in', [TicketController::class, 'checkIn'])->name('tickets.checkin');
            Route::post('tickets/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');
        });

        Route::middleware('admin.can:payments.manage')->group(function () {
            Route::resource('payments', PaymentController::class)->only(['index', 'show', 'update']);
        });

        Route::middleware('admin.can:refunds.manage')->group(function () {
            Route::post('payments/{payment}/refunds', [RefundController::class, 'store'])->name('payments.refunds.store');
            Route::resource('refunds', RefundController::class)->only(['index', 'show', 'update']);
        });

        Route::middleware('admin.can:fnb.manage')->group(function () {
            Route::resource('products', ProductController::class);
            Route::resource('suppliers', SupplierController::class);
            Route::resource('purchase-orders', PurchaseOrderController::class)
                ->parameters(['purchase-orders' => 'purchaseOrder'])
                ->names('purchase_orders')
                ->only(['index', 'create', 'store', 'show', 'update']);
            Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase_orders.receive');
            Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
            Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
            Route::post('inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        });

        Route::middleware('admin.can:marketing.manage')->group(function () {
            Route::resource('promotions', PromotionController::class);
            Route::resource('coupons', CouponController::class)->only(['index', 'create', 'store']);
            Route::resource('content-posts', ContentPostController::class)
                ->parameters(['content-posts' => 'contentPost'])
                ->names('content_posts');
        });

        Route::middleware('admin.can:customers.manage')->group(function () {
            Route::resource('customers', CustomerController::class);
        });

        Route::middleware('admin.can:staff.manage')->group(function () {
            Route::resource('staff', StaffController::class);
            Route::resource('staff-shifts', StaffShiftController::class)
                ->parameters(['staff-shifts' => 'staffShift'])
                ->names('staff_shifts');
        });

        Route::middleware('admin.can:operations.manage')->group(function () {
            Route::resource('equipment', EquipmentController::class);
            Route::resource('maintenance-requests', MaintenanceRequestController::class)
                ->parameters(['maintenance-requests' => 'maintenanceRequest'])
                ->names('maintenance_requests');
        });

        Route::middleware('admin.can:admin_users.manage')->group(function () {
            Route::resource('admin-users', AdminUserController::class)
                ->parameters(['admin-users' => 'adminUser'])
                ->names('admin_users');
        });
=======
        Route::resource('movies', MovieController::class);
        Route::resource('movie-versions', MovieVersionController::class)
            ->parameters(['movie-versions' => 'movieVersion'])
            ->names('movie_versions');
        Route::resource('categories', CategoryController::class);
        Route::resource('cinemas', CinemaController::class);
        Route::resource('auditoriums', AuditoriumController::class);
        Route::resource('shows', ShowController::class);
        Route::post('shows/{show}/seats/block', [ShowController::class, 'blockSeat'])->name('shows.seats.block');
        Route::delete('shows/{show}/seats/{seatBlock}/unblock', [ShowController::class, 'unblockSeat'])->name('shows.seats.unblock');
        Route::resource('pricing-profiles', PricingProfileController::class)->parameters(['pricing-profiles' => 'pricingProfile'])->names('pricing_profiles');
        Route::resource('products', ProductController::class);
<<<<<<< HEAD
        Route::resource('bookings', BookingController::class)->only(['index', 'show', 'update']);
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::resource('payments', PaymentController::class)->only(['index', 'show', 'update']);
        Route::post('payments/{payment}/refunds', [RefundController::class, 'store'])->name('payments.refunds.store');
        Route::resource('refunds', RefundController::class)->only(['index', 'show', 'update']);
=======
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::resource('promotions', PromotionController::class);
        Route::resource('coupons', CouponController::class)->only(['index', 'create', 'store']);

        Route::resource('customers', CustomerController::class);
        Route::resource('staff', StaffController::class);
        Route::resource('staff-shifts', StaffShiftController::class)->parameters(['staff-shifts' => 'staffShift'])->names('staff_shifts');
        Route::resource('equipment', EquipmentController::class);
        Route::resource('maintenance-requests', MaintenanceRequestController::class)->parameters(['maintenance-requests' => 'maintenanceRequest'])->names('maintenance_requests');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
    });
});
