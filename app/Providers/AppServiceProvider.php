<?php

namespace App\Providers;

use App\Models\Book;
use App\Models\Business;
use App\Models\Transaction;
use App\Policies\BookPolicy;
use App\Policies\BusinessPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Observers\TransactionObserver;
use Illuminate\Support\Facades\Schema;
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
        if (config('app.env') === 'production' || str_contains(request()->header('x-forwarded-proto', ''), 'https')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Prevent key length issues on older MySQL / MariaDB
        Schema::defaultStringLength(191);
        Gate::policy(Business::class, BusinessPolicy::class);
        Gate::policy(Book::class, BookPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);

        Transaction::observe(TransactionObserver::class);

        // Enforce custom styled pagination with explicit spacing across all pages
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.custom');
        \Illuminate\Pagination\Paginator::defaultSimpleView('vendor.pagination.custom');

        View::composer('*', function ($view) {
            $active = null;
            if (Auth::check()) {
                $active = Business::find(session('active_business_id'));
            }
            $view->with('activeBusiness', $active);
        });
    }
}
