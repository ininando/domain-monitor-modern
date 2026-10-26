<?php

namespace App\Providers;

use App\Enums\LevelExpired;
use App\Services\StatistikDomain;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Carbon::setLocale(config('app.locale'));

        Paginator::defaultView('components.pagination');
        Paginator::defaultSimpleView('components.pagination');

        // Data lonceng notifikasi (domain kritis) untuk topbar di setiap halaman aplikasi.
        View::composer('components.layouts.app', function ($view): void {
            if (! auth()->check()) {
                return;
            }

            $statistik = app(StatistikDomain::class);

            $view->with('notifikasi', [
                'jumlah' => $statistik->jumlahKritis(),
                'daftar' => $statistik->perluPerhatian(LevelExpired::BATAS_CRITICAL, 5),
            ]);
        });
    }
}
