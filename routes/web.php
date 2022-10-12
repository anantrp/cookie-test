<?php

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
    [Notes]
    - I don't code with these many comments, most of the code is very obvious
    - I figured that you might be interested in examining my thought process

    [Thoughts]
    - This should be a post request (following REST standards)
    - Typically this should be a controller action with a form request class
    - For the brevity of the example let's continue here only
*/

// Since we only allow this to be done by an authenticated user,
// and also possible more actions on our wallet let's use route group

Route::group(['middleware' => 'auth'], function() {
    Route::get('buy/{cookies}', function ($cookies) {

        // Time for some constants
        $COOKIES_PER_DOLLAR = 1;

        // It would be easier to use validation, but I am assuming
        // that the signature of the problem should not be changed

        // Let's first make sure that the cookies is a numeric value
        // Any non-numeric or decimal value should be rejected

        // We can also use is_numeric. but the problem is :
        // If the input was a decimal (10.5) it will pass
        // and then the user think that the input valid
        // but the amount baught / deducted was wrong (integer)

        if(!filter_var($cookies, FILTER_VALIDATE_INT)) {
            return "Invalid cookie amount, please provide a whole number of cookies";
        }

        // Here we are sure that $cookies is indeed an integer,
        // let's convert that to a number first
        $cookieAmount = (int)$cookies;

        // Since we are going to use User at multiple location,
        // It's a good idea to retrive the model for once
        // the Auth::user does not exactly provide us with the user model
        $user = User::find(Auth::user()->id);

        // I don't see why we need to waste more memory
        // Since we have the User model already, why to use it's reference
        // $wallet = Auth::user()->wallet;

        // We don't want to allow the user to buy 0 or negetive cookies, right?
        if($cookieAmount < 1) {
            return "You can not buy $cookieAmount cookies";
        }

        // Now let's check that the user has enough balance
        if($cookieAmount > $user->wallet) {
            return "You do not have enough wallet balance to buy $cookieAmount cookies";
        }

        // I've added `wallet` to fillable in User model
        $user->update(['wallet' => $user->wallet - ($cookieAmount * $COOKIES_PER_DOLLAR)]);
        Log::info('User ' . $user->email . ' have bought ' . $cookieAmount . ' cookies');
        return 'Success, you have bought ' . $cookieAmount . ' cookies!';

    })->name('buy-cookies');
});

require __DIR__.'/auth.php';
