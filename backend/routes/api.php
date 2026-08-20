<?php

use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\AuditController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\IncomeController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\MeasurementController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PayrollController;
use App\Http\Controllers\Api\V1\PdfController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\ShopController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WishlistController;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

Route::get('health', fn () => response()->json(['success' => true, 'message' => 'OK', 'data' => null]));

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
    Route::get('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('verify-email/send', [AuthController::class, 'sendVerificationEmail']);
    });

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);

    Route::apiResource('shops', ShopController::class);
    Route::get('shops/{shop}/performance', [ShopController::class, 'performance']);

    Route::apiResource('categories', CategoryController::class)->except(['destroy']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

    Route::apiResource('products', ProductController::class);
    Route::get('products/{slugOrId}', [ProductController::class, 'show'])->where('slugOrId', '.*');

    Route::get('wishlist', [WishlistController::class, 'index']);
    Route::post('wishlist/{product}/toggle', [WishlistController::class, 'toggle']);

    Route::get('measurements', [MeasurementController::class, 'index']);
    Route::post('measurements', [MeasurementController::class, 'store']);
    Route::get('measurements/{profile}', [MeasurementController::class, 'show']);
    Route::put('measurements/{profile}', [MeasurementController::class, 'update']);
    Route::delete('measurements/{profile}', [MeasurementController::class, 'destroy']);

    Route::get('orders/dashboard', [OrderController::class, 'dashboard']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::put('orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::post('orders/{order}/assign-tailor', [OrderController::class, 'assignTailor']);
    Route::post('orders/{order}/payments', [PaymentController::class, 'store']);

    Route::get('orders/{order}/pdf/invoice', [PdfController::class, 'invoice']);
    Route::get('orders/{order}/pdf/measurements', [PdfController::class, 'measurementSheet']);
    Route::get('employees/{employee}/payroll/{payroll}/pdf', [PdfController::class, 'payrollSlip']);

    Route::get('inventory', [InventoryController::class, 'index']);
    Route::post('inventory', [InventoryController::class, 'store']);
    Route::get('inventory/{item}', [InventoryController::class, 'show']);
    Route::put('inventory/{item}', [InventoryController::class, 'update']);
    Route::post('inventory/{item}/adjust', [InventoryController::class, 'adjust']);
    Route::delete('inventory/{item}', [InventoryController::class, 'destroy']);
    Route::get('stock-movements', [InventoryController::class, 'movements']);

    Route::apiResource('suppliers', SupplierController::class);

    Route::get('income', [IncomeController::class, 'index']);
    Route::post('income', [IncomeController::class, 'store']);
    Route::put('income/{income}', [IncomeController::class, 'update']);
    Route::delete('income/{income}', [IncomeController::class, 'destroy']);

    Route::get('expenses', [ExpenseController::class, 'index']);
    Route::post('expenses', [ExpenseController::class, 'store']);
    Route::put('expenses/{expense}', [ExpenseController::class, 'update']);
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy']);

    Route::get('employees', [EmployeeController::class, 'index']);
    Route::post('employees', [EmployeeController::class, 'store']);
    Route::get('employees/{employee}', [EmployeeController::class, 'show']);
    Route::put('employees/{employee}', [EmployeeController::class, 'update']);
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy']);
    Route::get('tailors', [EmployeeController::class, 'tailors']);

    Route::get('attendance', [AttendanceController::class, 'index']);
    Route::post('attendance', [AttendanceController::class, 'store']);
    Route::get('attendance/monthly', [AttendanceController::class, 'monthly']);
    Route::put('attendance/{attendance}', [AttendanceController::class, 'update']);

    Route::get('payroll', [PayrollController::class, 'index']);
    Route::post('payroll', [PayrollController::class, 'store']);
    Route::delete('payroll/{payroll}', [PayrollController::class, 'destroy']);

    Route::get('customers', [CustomerController::class, 'index']);
    Route::get('customers/{user}', [CustomerController::class, 'show']);

    Route::get('staff', [UserController::class, 'index']);
    Route::post('staff', [UserController::class, 'store']);
    Route::put('staff/{user}', [UserController::class, 'update']);
    Route::delete('staff/{user}', [UserController::class, 'destroy']);

    Route::get('dashboard', [DashboardController::class, 'admin']);
    Route::get('tailor/dashboard', [DashboardController::class, 'tailor']);

    Route::get('reports/summary', [ReportController::class, 'summary']);
    Route::get('reports/trend', [ReportController::class, 'trend']);
    Route::get('reports/sales', [ReportController::class, 'sales']);
    Route::get('reports/orders', [ReportController::class, 'orders']);
    Route::get('reports/tailors', [ReportController::class, 'tailors']);
    Route::get('reports/low-stock', [ReportController::class, 'lowStock']);

    Route::get('settings', [SettingController::class, 'business']);
    Route::put('settings', [SettingController::class, 'updateBusiness']);

    Route::get('audit-logs', [AuditController::class, 'index']);
});

Route::get('public/products', [ProductController::class, 'index']);
Route::get('public/settings', [SettingController::class, 'publicSettings']);
Route::get('public/shops', [ShopController::class, 'index']);
Route::get('public/categories', [CategoryController::class, 'index']);
Route::get('public/products/{slugOrId}', [ProductController::class, 'show'])->where('slugOrId', '.*');

Route::get('track-order/{order_number}', function (string $order_number) {
    $order = \App\Models\Order::with(['items', 'shop', 'statusHistory.changedBy'])
        ->where('order_number', $order_number)
        ->firstOrFail();

    return response()->json([
        'success' => true,
        'message' => 'Success',
        'data' => new \App\Http\Resources\OrderResource($order),
    ]);
});