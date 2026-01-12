<?php

namespace App\Providers;

use App\Support\Format;
use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
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
        Carbon::setLocale(config('app.locale', 'es'));

        // Best-effort locale for strftime / Intl fallbacks.
        // On Windows, locale names differ; we try a few common variants.
        @setlocale(LC_ALL, 'es_CO.UTF-8', 'es_CO', 'Spanish_Colombia.1252', 'Spanish_Colombia', 'es_ES.UTF-8', 'es_ES', 'Spanish');

        Blade::directive('money', function ($expression) {
            return "<?php echo \\App\\Support\\Format::money($expression); ?>";
        });

        Blade::directive('dateCo', function ($expression) {
            return "<?php echo \\App\\Support\\Format::date($expression); ?>";
        });

        Blade::directive('datetimeCo', function ($expression) {
            return "<?php echo \\App\\Support\\Format::datetime($expression); ?>";
        });

        Blade::directive('dateLongCo', function ($expression) {
            return "<?php echo \\App\\Support\\Format::dateLong($expression); ?>";
        });

        Blade::directive('datetimeLongCo', function ($expression) {
            return "<?php echo \\App\\Support\\Format::datetimeLong($expression); ?>";
        });

        Blade::directive('monthCo', function ($expression) {
            return "<?php echo \\App\\Support\\Format::monthName($expression); ?>";
        });

        Blade::directive('monthYearCo', function ($expression) {
            return "<?php echo \\App\\Support\\Format::monthYear($expression); ?>";
        });
    }
}
