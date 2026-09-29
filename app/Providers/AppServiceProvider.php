<?php

namespace App\Providers;

use App\Models\StoreSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
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
        try {
            if (Schema::hasTable('store_settings')) {
                // Dynamically configure SMTP if saved in Store Settings
                $host = StoreSetting::getValue('smtp_host');
                if ($host) {
                    Config::set('mail.default', 'smtp');
                    Config::set('mail.mailers.smtp.host', $host);
                    Config::set('mail.mailers.smtp.port', StoreSetting::getValue('smtp_port') ?: 587);
                    Config::set('mail.mailers.smtp.encryption', StoreSetting::getValue('smtp_encryption') ?: null);
                    Config::set('mail.mailers.smtp.username', StoreSetting::getValue('smtp_username'));
                    Config::set('mail.mailers.smtp.password', StoreSetting::getValue('smtp_password'));

                    $fromEmail = StoreSetting::getValue('smtp_from_email');
                    if ($fromEmail) {
                        Config::set('mail.from.address', $fromEmail);
                        Config::set('mail.from.name', StoreSetting::getValue('smtp_from_name') ?: StoreSetting::getStoreName());
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore during setup or early migrations
        }

        View::composer('*', function ($view) {
            try {
                if (!isset($view->getData()['storeName'])) {
                    $view->with('storeName', StoreSetting::getStoreName());
                }
                if (!isset($view->getData()['storeLogo'])) {
                    $view->with('storeLogo', StoreSetting::getLogoUrl());
                }
                if (!isset($view->getData()['storeFavicon'])) {
                    $view->with('storeFavicon', StoreSetting::getFaviconUrl());
                }
                if (!isset($view->getData()['primaryColor'])) {
                    $view->with('primaryColor', StoreSetting::getPrimaryColor());
                }
                if (!isset($view->getData()['secondaryColor'])) {
                    $view->with('secondaryColor', StoreSetting::getSecondaryColor());
                }
                if (!isset($view->getData()['currencySymbol'])) {
                    $view->with('currencySymbol', StoreSetting::getCurrencySymbol());
                }
            } catch (\Throwable $e) {
                $view->with([
                    'storeName'      => config('app.name', 'Store'),
                    'storeLogo'      => null,
                    'storeFavicon'   => asset('favicon.ico'),
                    'primaryColor'   => '#FF9900',
                    'secondaryColor' => '#232F3E',
                    'currencySymbol' => '₹',
                ]);
            }
        });
    }
}
