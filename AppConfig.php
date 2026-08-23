<?php
/**
 * Plugin format 2.0
 */
#App\GP247\Plugins\ShopDiscount\AppConfig.php
namespace App\GP247\Plugins\ShopDiscount;

use App\GP247\Plugins\ShopDiscount\Models\ExtensionModel;
use App\GP247\Plugins\ShopDiscount\Controllers\FrontController;
use GP247\Core\Models\AdminConfig;
use GP247\Core\Models\AdminHome;
use GP247\Core\ExtensionConfigDefault;
use GP247\Shop\Models\ShopCurrency;
use GP247\Core\Models\AdminMenu;
use GP247\Shop\Front\Contracts\CheckoutTotalMethod;
use Illuminate\Support\Facades\DB;
class AppConfig extends ExtensionConfigDefault implements CheckoutTotalMethod
{
    public function __construct()
    {
        //Read config from gp247.json
        $config = file_get_contents(__DIR__.'/gp247.json');
        $config = json_decode($config, true);
    	$this->configGroup = $config['configGroup'];
        $this->configKey = $config['configKey'];
        $this->configCode = $config['configCode'] ?? $this->configKey;
        $this->requireCore = $config['requireCore'] ?? [];
        $this->requireComposerPackages = $config['requireComposerPackages'] ?? [];
        $this->requireGp247Extensions = $config['requireGp247Extensions'] ?? [];
        //Path
        $this->appPath = $this->configGroup . '/' . $this->configKey;
        //Language
        $this->title = trans($this->appPath.'::lang.title');
        //Image logo or thumb
        $this->image = $this->appPath.'/'.$config['image'];
        //
        $this->version = $config['version'];
        $this->auth = $config['auth'];
        $this->link = $config['link'];
    }

    public function install()
    {
        $checkPluginPro = AdminConfig::where('key', 'ShopDiscountPro')->first();
        if ($checkPluginPro) {
            return ['error' => 1, 'msg' =>  gp247_language_render($this->appPath.'::lang.plugin_action.plugin_discount_pro_exist')];
        }

        $check = AdminConfig::where('key', $this->configKey)
            ->where('group', $this->configGroup)->first();
        if ($check) {
            //Check Plugin key exist
            $return = ['error' => 1, 'msg' =>  gp247_language_render('admin.extension.plugin_exist')];
        } else {
            //Insert plugin to config
            $dataInsert = [
                [
                    'group'  => $this->configGroup,
                    'code'    => $this->configCode,
                    'key'    => $this->configKey,
                    'sort'   => 0,
                    'store_id' => GP247_STORE_ID_GLOBAL,
                    'value'  => self::ON, //Enable extension
                    'detail' => $this->appPath.'::lang.title',
                ],
            ];
            try {
                AdminConfig::insert(
                    $dataInsert
                );

                $blockMenu = AdminMenu::where('key','ADMIN_SHOP_ORDER')->first();
                AdminMenu::insert([
                    'sort' => 30,
                    'parent_id' => $blockMenu->id ?? 0,
                    'title' => $this->appPath.'::lang.title',
                    'icon' => 'fas fa-tags',
                    'uri' => 'route_admin::admin_discount.index',
                    'key' => $this->configKey,
                    ]);

                (new ExtensionModel)->installExtension();
                $return = ['error' => 0, 'msg' => gp247_language_render('admin.extension.install_success')];
            } catch (\Throwable $e) {
                $return = ['error' => 1, 'msg' => $e->getMessage()];
            }
        }

        return $return;
    }

    public function uninstall()
    {
        //Please delete all values inserted in the installation step
        try {
            (new AdminConfig)
            ->where('key', $this->configKey)
            ->orWhere('code', $this->configKey.'_config')
            ->delete();

            //Admin config home
            AdminHome::where('extension', $this->appPath)->delete();
            AdminMenu::where('key', $this->configKey)->delete();

            (new ExtensionModel)->uninstallExtension();

            $return = ['error' => 0, 'msg' => gp247_language_render('admin.extension.uninstall_success')];
        } catch (\Throwable $e) {
            $return = ['error' => 1, 'msg' => $e->getMessage()];
        }

        //Delete menu
        AdminMenu::where('uri', 'route::admin_discount.index')->delete();

        return $return;
    }
    
    public function enable()
    {
        $process = (new AdminConfig)
            ->where('group', $this->configGroup)
            ->where('key', $this->configKey)
            ->update(['value' => self::ON]);
        //Admin config home
        AdminHome::where('extension', $this->appPath)->update(['status' => 1]);

        if (!$process) {
            $return = ['error' => 1, 'msg' => gp247_language_render('admin.extension.action_error', ['action' => 'Enable'])];
        }
        $return = ['error' => 0, 'msg' => gp247_language_render('admin.extension.enable_success')];
        return $return;
    }

    public function disable()
    {
        $return = ['error' => 0, 'msg' => ''];
        $process = (new AdminConfig)
            ->where('group', $this->configGroup)
            ->where('key', $this->configKey)
            ->update(['value' => self::OFF]);
        if (!$process) {
            $return = ['error' => 1, 'msg' => gp247_language_render('admin.extension.action_error', ['action' => 'Disable'])];
        }

        //Admin config home
        AdminHome::where('extension', $this->appPath)->update(['status' => 0]);

        return $return;
    }


    // Remove setup for store

    public function removeStore($storeId = null)
    {
        // code here
    }

    // Setup for store

    public function setupStore($storeId = null)
    {
       // code here
    }


    // Process when click button plugin in admin

    public function clickApp()
    {
        return redirect()->route('admin_discount.index');
    }

    /**
     * Apply a coupon at checkout (CheckoutTotalMethod contract, L2).
     * Reuses FrontController::check() for validation, then registers the code in
     * session('totalMethod') so ShopOrderTotal/getInfo() add the discount line.
     * Returns the result instead of throwing so the wizard can show feedback.
     *
     * @param array<string, mixed> $payload Sanitised input, expects ['code' => string].
     * @return array{error: int, msg?: string}
     *
     * @aidlc-unit storefront
     * @aidlc-story US-LW-006
     * @aidlc-adr ADR-storefront-checkout-total-method-contract
     */
    public function checkoutApply(array $payload): array
    {
        $code = trim((string) ($payload['code'] ?? ''));
        if ($code === '') {
            return ['error' => 1, 'msg' => gp247_language_render('cart.coupon_empty')];
        }

        $uID   = customer()->id ?? 0;
        $check = (new FrontController)->check($code, $uID);
        if (!empty($check['error'])) {
            return ['error' => 1, 'msg' => $check['msg']];
        }

        // WHY: getInfo() computes the value for both 'point' and 'percent' types from
        // the stored code; registering the key is enough — recompute happens in the wizard.
        $totalMethod = session('totalMethod', []);
        $totalMethod[$this->configKey] = $code;
        session(['totalMethod' => $totalMethod]);

        return ['error' => 0, 'msg' => gp247_language_render($this->appPath.'::lang.process.completed')];
    }

    /**
     * Remove the applied coupon from the order (CheckoutTotalMethod contract, L2).
     *
     * @return void
     */
    public function checkoutRemove(): void
    {
        $totalMethod = session('totalMethod', []);
        unset($totalMethod[$this->configKey]);
        session(['totalMethod' => $totalMethod]);
    }

    /**
     * Storefront fragment rendered inside the checkout total-method zone
     * (CheckoutTotalMethod contract, L3).
     *
     * @return string|null
     */
    public function checkoutView(): ?string
    {
        return $this->appPath.'::checkout';
    }

    /**
     * Get info plugin
     *
     * @return  [type]  [return description]
     */
    public function getInfo()
    {
        $uID = customer()->id ?? 0;
        $dataStore = [];
        $arrData = [
            'title' => $this->title,
            'key' => $this->configKey,
            'code' => $this->configCode,
            'image' => $this->image,
            'permission' => self::ALLOW,
            'version' => $this->version,
            'auth' => $this->auth,
            'link' => $this->link,
            'value' => 0, // this return need for plugin shipping
            'appPath' => $this->appPath,
            'store' => $dataStore
        ];

        
        $totalMethod = session('totalMethod', []);
        $discount = $totalMethod[$this->configKey]??'';

        $check = (new FrontController)->check($discount, $uID);

        if (!empty($discount) && !$check['error']) {
            $subtotalWithTax = ShopCurrency::sumCartCheckout()['subTotalWithTax'] ?? null;
            if (!$subtotalWithTax) {
                return $arrData;
            }
            if ($check['content']['type'] == 'percent') {
                $value = floor($subtotalWithTax * $check['content']['reward'] / 100);
            } else {
                $value = gp247_currency_value($check['content']['reward']);
            }
            if (gp247_store_check_multi_partner_installed() ||  gp247_store_check_multi_store_installed()) {
                //Add info for earch store
                $storeID = $check['content']['store_id'];
                $dataStore[$storeID]['value'] = $value;
            } else {
                $dataStore = [];
            }

            $arrData = array(
                'title' => '<b>' . $this->title . ':</b> ' . $discount . '',
                'key' => $this->configKey,
                'code' => $this->configCode,
                'image' => $this->image,
                'permission' => self::ALLOW,
                'version' => $this->version,
                'auth' => $this->auth,
                'link' => $this->link,
                'value' => ($value > $subtotalWithTax) ? -$subtotalWithTax : -$value,
                'appPath' => $this->appPath,
                'store' => $dataStore
            );
        }
        return $arrData;
    }
}
