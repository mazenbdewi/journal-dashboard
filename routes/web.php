<?php

use App\Http\Controllers\Auth\PasskeyLoginController;
// routes/web.php
use App\Models\Notification;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/notifications/{notification}/read', function (Notification $notification) {
    if ($notification->user_id === auth()->id()) {
        $notification->update(['is_read' => true]);
    }

    return redirect($notification->link ?? '/');
})->name('notifications.read');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify', function () {
    return view('auth.verify-email'); // أنشئ هذه الصفحة إذا لم تكن موجودة
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/adminpanel'); // غير الرابط حسب الحاجة
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/resend', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.resend');

Route::get('/adminpanel/passkey-login', [PasskeyLoginController::class, 'show'])->name('passkey.login');
Route::post('/adminpanel/passkey-login', [PasskeyLoginController::class, 'authenticate']);
