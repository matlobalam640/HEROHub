<?php

use App\Http\Controllers\Admin\PlanManageController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Business\BillingController;
use App\Http\Controllers\Business\EmployeeController;
use App\Http\Controllers\Business\PortalController as BusinessPortalController;
use App\Http\Controllers\Business\VisitorController as BusinessVisitorController;
use App\Http\Controllers\ComingSoonController;
use App\Http\Controllers\StaffMembershipController;
use App\Http\Controllers\Customer\MembershipController;
use App\Http\Controllers\Customer\MembershipStripePlanCheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Dispatch\VerificationController;
use App\Http\Controllers\Partner\CommissionReportController;
use App\Http\Controllers\Partner\EnrollmentController as PartnerEnrollmentController;
use App\Http\Controllers\Partner\PortalController as PartnerPortalController;
use App\Http\Controllers\Partner\SalesController as PartnerSalesController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeWebhookController;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->middleware('throttle:120,1')
    ->name('stripe.webhook');

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    $home = RouteServiceProvider::homeUrlFor(auth()->user());

    return redirect()->to($home);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/dispatch/verification', [VerificationController::class, 'index'])
        ->middleware(['verified', 'role:admin|dispatch'])
        ->name('dispatch.verification');

    Route::middleware('role:customer|business')->group(function () {
        Route::get('/my/membership', [MembershipController::class, 'show'])
            ->name('customer.membership');
        Route::get('/my/membership/plan', [MembershipController::class, 'plan'])
            ->name('customer.membership.plan');
        Route::post('/my/membership/plan', [MembershipController::class, 'updatePlan'])
            ->name('customer.membership.plan.update');
        Route::get('/my/membership/plan/subscribe/{plan}/{interval}', [MembershipController::class, 'subscribeFromCatalog'])
            ->whereIn('interval', ['monthly', 'yearly'])
            ->name('customer.membership.plan.subscribe');
        Route::get('/my/membership/plan/stripe-checkout/{token}', [MembershipStripePlanCheckoutController::class, 'showReview'])
            ->name('customer.membership.plan.stripe.review');
        Route::post('/my/membership/plan/stripe-checkout', [MembershipStripePlanCheckoutController::class, 'startCheckout'])
            ->name('customer.membership.plan.stripe.start');
        Route::get('/my/membership/plan/stripe/success', [MembershipStripePlanCheckoutController::class, 'success'])
            ->name('customer.membership.plan.stripe.success');
        Route::post('/my/membership/dependents', [MembershipController::class, 'storeDependent'])
            ->name('customer.membership.dependents.store');
        Route::delete('/my/membership/dependents/{dependentId}', [MembershipController::class, 'destroyDependent'])
            ->name('customer.membership.dependents.destroy');
        Route::post('/my/membership/visitors', [MembershipController::class, 'storeVisitor'])
            ->name('customer.membership.visitors.store');
        Route::delete('/my/membership/visitors/{dependentId}', [MembershipController::class, 'destroyVisitor'])
            ->name('customer.membership.visitors.destroy');
        Route::get('/my/membership/visitor-coverage', [MembershipController::class, 'visitorCoverage'])
            ->name('customer.membership.visitors');
        Route::get('/my/membership/family-members', [MembershipController::class, 'familyMembers'])
            ->name('customer.membership.family');
        Route::post('/my/membership/billing', [MembershipController::class, 'updateBilling'])
            ->name('customer.membership.billing.update');
        Route::get('/my/membership/payment-method', [MembershipController::class, 'paymentMethod'])
            ->name('customer.membership.billing');
        Route::post('/my/membership/auto-renew', [MembershipController::class, 'updateAutoRenew'])
            ->name('customer.membership.auto-renew.update');
        Route::get('/my/membership/card.pdf', [MembershipController::class, 'downloadCard'])
            ->name('customer.membership.card-pdf');
        Route::get('/my/membership/payments', [MembershipController::class, 'payments'])
            ->name('customer.membership.payments');
        Route::get('/my/membership/invoices/{invoiceRef}', [MembershipController::class, 'downloadInvoice'])
            ->name('customer.membership.invoices.download');
    });

    Route::middleware(['verified', 'role:partner'])->prefix('partner')->name('partner.')->group(function () {
        Route::get('/', [PartnerPortalController::class, 'index'])->name('portal');
        Route::get('/enroll', [PartnerEnrollmentController::class, 'create'])->name('enroll.create');
        Route::post('/enroll', [PartnerEnrollmentController::class, 'store'])->name('enroll.store');
        Route::get('/sales', [PartnerSalesController::class, 'index'])->name('sales.index');
        Route::get('/commissions', CommissionReportController::class)->name('commissions');
    });

    Route::middleware(['verified', 'role:business'])->prefix('company')->name('business.')->group(function () {
        Route::get('/', [BusinessPortalController::class, 'index'])->name('portal');
        Route::post('/current-company', [BusinessPortalController::class, 'switchCompany'])->name('company.switch');

        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::post('/employees/import', [EmployeeController::class, 'import'])->name('employees.import');
        Route::delete('/employees/{membership}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        Route::patch('/employees/{membership}/plan', [EmployeeController::class, 'updatePlan'])->name('employees.plan');
        Route::patch('/employees/{membership}/status', [EmployeeController::class, 'updateStatus'])->name('employees.status');

        Route::post('/employees/{membership}/visitors', [BusinessVisitorController::class, 'store'])->name('visitors.store');
        Route::delete('/visitors/{memberDependent}', [BusinessVisitorController::class, 'destroy'])->name('visitors.destroy');

        Route::get('/billing', [BillingController::class, 'edit'])->name('billing.edit');
        Route::patch('/billing', [BillingController::class, 'update'])->name('billing.update');
    });

    Route::redirect('/portal/plans', '/portal/plans/retail')->name('portal.plans');
    Route::get('/portal/plans/retail', [PlanController::class, 'retail'])->name('portal.plans.retail');
    Route::get('/portal/plans/small-business', [PlanController::class, 'smallBusiness'])->name('portal.plans.small-business');
    Route::get('/portal/plans/corporate', [PlanController::class, 'corporate'])->name('portal.plans.corporate');

    Route::get('/admin/plans/create', [PlanManageController::class, 'create'])->name('admin.plans.create');
    Route::post('/admin/plans', [PlanManageController::class, 'store'])->name('admin.plans.store');

    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])
        ->middleware(['verified', 'role:admin'])
        ->name('admin.users.destroy');

    Route::get('/portal/memberships/{membership}', [StaffMembershipController::class, 'show'])
        ->middleware(['verified', 'role:admin|dispatch'])
        ->name('portal.membership.show');

    // Placeholder routes for upcoming modules (template-like navigation without 404s)
    Route::get('/portal/{page}', ComingSoonController::class)
        ->whereIn('page', [
            'customers',
            'memberships',
            'companies',
            'partners',
            'reports',
            'settings',
        ])
        ->name('portal.coming-soon');
});

require __DIR__.'/auth.php';
