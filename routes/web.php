<?php

use Filament\Pages\Dashboard;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

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

Route::redirect('/', '/panel');

Route::get('/oauth/gh/redirect', function () {
    return Socialite::driver('github')
        ->scopes(['user:email', 'repo', 'write:repo_hook', 'read:repo_hook'])
        ->redirect();
})->name('oauth.gh.redirect');

Route::get('/oauth/callback/gh', function () {
    $ghUser = Socialite::driver('github')->user();

    $user = auth()->user();

    $team = $user->teams()->first();

    $team->update([
        'gh_api_key' => $ghUser->token,
    ]);

    return redirect(Dashboard::getUrl(tenant: $user->teams()->first()));
})->name('oauth.gh.callback');
