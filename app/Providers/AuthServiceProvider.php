<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\License;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Auth::viaRequest('license-key', function (Request $request) {
            $key = $request->getPassword();
            $username = $request->getUser();

            $license = License::query()
                ->active()
                ->where('key', $key)
                ->first();

            abort_unless($license, 401, 'License key invalid');

            if (filled($license->username) && $username !== $license->username) {
                abort(401, 'License holder invalid');
            }

            if (! filled($license->username) && filled($username)) {
                $license->update([
                    'username' => $username,
                ]);
            }

            return $license;
        });
    }
}
