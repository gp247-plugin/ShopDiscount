<?php
use Illuminate\Support\Facades\Route;
use App\GP247\Plugins\ShopDiscount\Admin\Livewire\DiscountManager;

$config = file_get_contents(__DIR__.'/gp247.json');
$config = json_decode($config, true);

if(gp247_extension_check_active($config['configGroup'], $config['configKey'])) {

    Route::group(
        [
            'middleware' => GP247_FRONT_MIDDLEWARE,
            'prefix'    => 'plugin/discount',
            'namespace' => 'App\GP247\Plugins\ShopDiscount\Controllers',
        ],
        function () {
            Route::post('/discount_process', 'FrontController@useDiscount')
                ->name('discount.process');
            Route::post('/discount_remove', 'FrontController@removeDiscount')
                ->name('discount.remove');
        }
    );

    // v2 (Livewire + TailAdmin) — replaces the legacy AdminLTE controller, whose
    // views extended the now-removed `gp247-core::layout` / `gp247-core::screen.list`.
    // Route names are kept identical to v1 for back-compat: the AdminMenu row
    // installed by AppConfig::install() references `route_admin::admin_discount.index`.
    // The single DiscountManager component drives list + create + edit (two-panel).
    Route::group(
        [
            'prefix' => GP247_ADMIN_PREFIX.'/discount',
            'middleware' => GP247_ADMIN_MIDDLEWARE,
        ],
        function () {
            Route::get('/', DiscountManager::class)
                ->name('admin_discount.index');
            Route::get('/create', DiscountManager::class)
                ->name('admin_discount.create');
            Route::get('/edit/{id}', DiscountManager::class)
                ->name('admin_discount.edit');
        }
    );
}