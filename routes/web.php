<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LeadChannelController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PaymentOptionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CustomerPriceListController;
use App\Http\Controllers\CustomerProductController;
use App\Http\Controllers\PriceListController;
use App\Http\Controllers\ProductLandingPageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('pricelist/{code}', [CustomerPriceListController::class, 'show'])->name('price-lists.public');
Route::get('product/{code}', [CustomerProductController::class, 'show'])->name('product-landing-pages.public');

Route::get('f/{code}', [App\Http\Controllers\PublicFormController::class, 'show'])->name('forms.public.show');
Route::post('f/{code}', [App\Http\Controllers\PublicFormController::class, 'submit'])->name('forms.public.submit');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');

Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
Route::post('calendar/reminders', [CalendarController::class, 'storeReminder'])->name('calendar.reminders.store');
Route::post('calendar/leads/{lead}/task', [CalendarController::class, 'storeLeadTask'])->name('calendar.leads.task');
Route::post('calendar/reminders/{reminder}/toggle-done', [CalendarController::class, 'toggleDone'])->name('calendar.reminders.toggle-done');
Route::delete('calendar/reminders/{reminder}', [CalendarController::class, 'destroyReminder'])->name('calendar.reminders.destroy');

Route::resource('contacts', ContactController::class);
Route::post('contacts/bulk-delete', [ContactController::class, 'bulkDelete'])->name('contacts.bulk-delete');
Route::get('contacts/{contact}/address-label', [ContactController::class, 'addressLabel'])->name('contacts.address-label');
Route::get('contacts/{contact}/receive-pay', [ContactController::class, 'showReceivePay'])->name('contacts.receive-pay');
Route::post('contacts/{contact}/receive-pay', [ContactController::class, 'submitReceivePay'])->name('contacts.receive-pay.submit');
Route::post('leads/{lead}/convert-to-contact', [LeadController::class, 'convertToContact'])->name('leads.convert-to-contact');
Route::get('leads/{lead}/create-invoice', [LeadController::class, 'createInvoiceFromLead'])->name('leads.create-invoice');
Route::post('leads/{lead}/assign', [LeadController::class, 'assignLead'])->name('leads.assign');
Route::post('leads/{lead}/tags', [LeadController::class, 'updateTags'])->name('leads.tags.update');
Route::post('leads/{lead}/comments', [LeadController::class, 'storeComment'])->name('leads.comments.store');
Route::get('leads/{lead}/change-status', [LeadController::class, 'showChangeStatus'])->name('leads.change-status');
Route::post('leads/{lead}/change-status', [LeadController::class, 'submitChangeStatus'])->name('leads.change-status.submit');
Route::post('leads/{lead}/attachments', [LeadController::class, 'storeAttachment'])->name('leads.attachments.store');
Route::delete('leads/{lead}/attachments/{attachment}', [LeadController::class, 'destroyAttachment'])->name('leads.attachments.destroy');
Route::resource('leads', LeadController::class);
Route::get('contacts-export/csv', [ContactController::class, 'exportCsv'])->name('contacts.export');
Route::get('contacts/import/form', [ContactController::class, 'importForm'])->name('contacts.import');
Route::post('contacts/import', [ContactController::class, 'import'])->name('contacts.import.store');
Route::get('api/contacts/search', [ContactController::class, 'searchApi'])->name('contacts.search.api');
Route::get('api/contacts/{contact}', [ContactController::class, 'showApi'])->name('contacts.show.api');

Route::resource('invoices', InvoiceController::class);
Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
// Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf'); // Disabled - fix PDF generation later
Route::post('invoices/{invoice}/mark-final', [InvoiceController::class, 'markFinal'])->name('invoices.mark-final');
Route::get('invoices/{invoice}/set-paid', [InvoiceController::class, 'showSetPaid'])->name('invoices.set-paid');
Route::post('invoices/{invoice}/set-paid', [InvoiceController::class, 'submitSetPaid'])->name('invoices.set-paid.submit');
Route::delete('invoices/{invoice}/payments/{payment}', [InvoiceController::class, 'destroyPayment'])->name('invoices.payments.destroy')->where(['invoice' => '[0-9]+', 'payment' => '[0-9]+']);
Route::put('invoices/{invoice}/payment-options', [InvoiceController::class, 'updatePaymentOptions'])->name('invoices.payment-options.update');
Route::post('invoices/{invoice}/attachments', [InvoiceController::class, 'storeAttachment'])->name('invoices.attachments.store');
Route::delete('invoices/{invoice}/attachments/{attachment}', [InvoiceController::class, 'destroyAttachment'])->name('invoices.attachments.destroy');

Route::get('settings/payment-options', [PaymentOptionController::class, 'index'])->name('settings.payment-options');
Route::post('settings/payment-options', [PaymentOptionController::class, 'store'])->name('settings.payment-options.store');
Route::get('settings/payment-options/{payment_option}/edit', [PaymentOptionController::class, 'edit'])->name('settings.payment-options.edit');
Route::put('settings/payment-options/{payment_option}', [PaymentOptionController::class, 'update'])->name('settings.payment-options.update');
Route::delete('settings/payment-options/{payment_option}', [PaymentOptionController::class, 'destroy'])->name('settings.payment-options.destroy');

Route::get('settings/lead-channels', [LeadChannelController::class, 'index'])->name('settings.lead-channels');
Route::post('settings/lead-channels', [LeadChannelController::class, 'store'])->name('settings.lead-channels.store');
Route::delete('settings/lead-channels/{lead_channel}', [LeadChannelController::class, 'destroy'])->name('settings.lead-channels.destroy');
Route::get('settings/company', [SettingController::class, 'companyIndex'])->name('settings.company');
Route::get('settings/company/address', [SettingController::class, 'companyAddress'])->name('settings.company.address');
Route::post('settings/company/address', [SettingController::class, 'updateCompany'])->name('settings.company.update');

Route::get('products/import/form', [ProductController::class, 'importForm'])->name('products.import');
Route::post('products/import', [ProductController::class, 'import'])->name('products.import.store');
Route::resource('products', ProductController::class);
Route::post('price-lists/{priceList}/duplicate', [PriceListController::class, 'duplicate'])->name('price-lists.duplicate');
Route::post('price-lists/{priceList}/generate-code', [PriceListController::class, 'generateCode'])->name('price-lists.generate-code');
Route::get('price-lists/{priceList}/links', [PriceListController::class, 'links'])->name('price-lists.links');
Route::resource('price-lists', PriceListController::class);
Route::get('product-landing-pages/{productLandingPage}/links', [ProductLandingPageController::class, 'links'])->name('product-landing-pages.links');
Route::post('product-landing-pages/{productLandingPage}/generate-code', [ProductLandingPageController::class, 'generateCode'])->name('product-landing-pages.generate-code');
Route::resource('product-landing-pages', ProductLandingPageController::class)->except(['show']);
Route::get('api/products/search', [ProductController::class, 'searchApi'])->name('products.search.api');
Route::resource('tags', TagController::class);

Route::get('forms/inbox', [App\Http\Controllers\FormController::class, 'inbox'])->name('forms.inbox');
Route::get('forms/submissions/{submission}', [App\Http\Controllers\FormController::class, 'showSubmission'])->name('forms.submissions.show');
Route::post('forms/{form}/modules', [App\Http\Controllers\FormController::class, 'storeModule'])->name('forms.modules.store');
Route::put('forms/{form}/modules/{module}', [App\Http\Controllers\FormController::class, 'updateModule'])->name('forms.modules.update');
Route::delete('forms/{form}/modules/{module}', [App\Http\Controllers\FormController::class, 'destroyModule'])->name('forms.modules.destroy');
Route::post('forms/{form}/links', [App\Http\Controllers\FormController::class, 'createLink'])->name('forms.links.store');
Route::resource('forms', App\Http\Controllers\FormController::class);

Route::redirect('bank-accounts', '/settings/payment-options');
Route::redirect('bank-accounts/create', '/settings/payment-options');
Route::get('bank-accounts/{path}', function () {
    return redirect()->route('settings.payment-options');
})->where('path', '.+');

Route::get('api/tasks/search-taskable', [TaskController::class, 'searchTaskableApi'])->name('tasks.search-taskable.api');
Route::resource('tasks', TaskController::class);
Route::post('tasks/{task}/notes', [TaskController::class, 'storeNote'])->name('tasks.notes.store');
Route::post('tasks/{task}/attachments', [TaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
Route::delete('tasks/{task}/attachments/{attachment}', [TaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');
Route::post('tasks/{task}/change-status', [TaskController::class, 'changeStatus'])->name('tasks.change-status');
Route::post('tasks/{task}/assign', [TaskController::class, 'assign'])->name('tasks.assign');

Route::get('transactions/by-date', [TransactionController::class, 'byDate'])->name('transactions.by-date');
Route::get('transactions/by-contact', [TransactionController::class, 'byContact'])->name('transactions.by-contact');
Route::get('transactions/contact/{contact}', [TransactionController::class, 'contactTransactions'])->name('transactions.contact-detail')->where(['contact' => '[0-9]+']);

Route::get('users', [UserController::class, 'index'])->name('users.index');
Route::get('users/create', [UserController::class, 'create'])->name('users.create');
Route::post('users', [UserController::class, 'store'])->name('users.store');
Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
});
