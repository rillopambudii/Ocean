<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super Admin selalu lolos semua gate di bawah.
        $roles = fn (string ...$roles) => fn ($user) => in_array($user->role, ['Super Admin', ...$roles]);

        Gate::define('super-admin', $roles());

        // Admin departemen: kelola seluruh modulnya, termasuk dokumen & data kantor.
        Gate::define('qhse-access', $roles('Admin QHSE'));
        Gate::define('operation-access', $roles('Admin Operation'));
        Gate::define('technical-access', $roles('Admin Technical'));
        Gate::define('crewing-access', $roles('Admin Crewing'));
        Gate::define('purchasing-access', $roles('Admin Purchasing'));

        // Admin Kapal: register per kapal di semua modul, data tersaring ke kapalnya.
        Gate::define('vessel-admin', fn ($user) => $user->role === 'Admin Kapal');

        // Management (Manager/GM/BOD): hanya melihat dashboard.
        Gate::define('management', fn ($user) => $user->role === 'Management');

        // Semua akun yang boleh mengisi data (selain Management).
        Gate::define('workspace', fn ($user) => $user->role !== 'Management');
    }
}
