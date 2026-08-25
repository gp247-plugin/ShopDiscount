<?php

namespace App\GP247\Plugins\ShopDiscount\Admin\Livewire;

use App\GP247\Plugins\ShopDiscount\Models\ShopDiscount;
use GP247\Core\AdminShell\Infrastructure\ResourcePanel;

/**
 * Discount/coupon manager — v2 port of the legacy AdminLTE AdminController
 * (which rendered the now-removed `gp247-core::screen.list` and a
 * `gp247-core::layout` form). Two-panel TailAdmin screen (add/edit form left,
 * list right) on the core ResourcePanel base. Business rules and persistence
 * reuse the untouched ShopDiscount model; multi-store scoping mirrors the legacy
 * behaviour (pivot table shop_discount_store). Gated by `admin_discount`.
 *
 * @aidlc-unit plugin-shop-discount
 * @aidlc-story GP247-v2-compat
 * @aidlc-adr ADR-001, ADR-005, ADR-007
 */
class DiscountManager extends ResourcePanel
{
    /** @var string|null Layer-2 permission slug gating this screen. */
    protected ?string $permission = 'admin_discount';

    /**
     * Keep list state (page/keyword/sort) and the edited record on screen when
     * editing/saving, instead of remounting via route navigation.
     *
     * @var bool
     * @aidlc-story US-AUI-two-panel-state-preservation
     * @aidlc-adr ADR-admin-shell-rbac-two-panel-state-preservation
     */
    protected bool $keepStateOnSave = true;

    /**
     * Base query for the list, scoped to the current admin store on multi-store /
     * multi-partner installs (root sees all), matching the legacy screen.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function baseQuery()
    {
        $query = ShopDiscount::query();

        // WHY: legacy screen restricted non-root admins to discounts attached to
        // their own store via the shop_discount_store pivot; keep that scoping.
        if ($this->isMultiStore() && session('adminStoreId') != GP247_STORE_ID_ROOT) {
            $storeId = session('adminStoreId');
            $query->whereHas('stores', function ($w) use ($storeId): void {
                $w->where('id', $storeId);
            });
        }

        return $query;
    }

    /**
     * @return array<int, string>
     */
    protected function searchable(): array
    {
        return ['code'];
    }

    /**
     * @return array<int, string>
     */
    protected function sortableColumns(): array
    {
        return ['id', 'code'];
    }

    /**
     * @return array<int, string>
     */
    protected function defaultSort(): array
    {
        return ['id', 'desc'];
    }

    /**
     * @return string
     */
    protected function panelView(): string
    {
        return 'Plugins/ShopDiscount::Admin.discount';
    }

    /**
     * @return string
     */
    protected function pageTitle(): string
    {
        return gp247_language_render('Plugins/ShopDiscount::lang.title');
    }

    /**
     * @return string
     */
    protected function baseRoute(): string
    {
        // WHY: keep the legacy route name — the AdminMenu row installed by
        // AppConfig::install() references `route_admin::admin_discount.index`.
        return 'admin_discount.index';
    }

    /**
     * @return array<string, mixed>
     */
    protected function formDefaults(): array
    {
        return [
            'code'       => '',
            'reward'     => '',
            'type'       => 'point',
            'data'       => '',
            'limit'      => 1,
            'login'      => 0,
            'status'     => 0,
            'expires_at' => '',
            'shop_store' => [],
        ];
    }

    /**
     * @param ShopDiscount $model
     * @return array<string, mixed>
     */
    protected function fillForm($model): array
    {
        return [
            'code'       => (string) $model->code,
            'reward'     => (string) $model->reward,
            'type'       => (string) $model->type,
            'data'       => (string) $model->data,
            'limit'      => (int) $model->limit,
            'login'      => (int) $model->login,
            'status'     => (int) $model->status,
            'expires_at' => $model->expires_at ? \Illuminate\Support\Carbon::parse($model->expires_at)->format('Y-m-d') : '',
            'shop_store' => $model->stores->pluck('id')->map(fn ($id) => (string) $id)->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'form.code'   => 'required|regex:/(^([0-9A-Za-z\-\._]+)$)/|string|max:50|discount_unique:' . ($this->editingId ?? ''),
            'form.limit'  => 'required|numeric|min:1',
            'form.reward' => 'required|numeric|min:0',
            'form.type'   => 'required',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'form.code.regex'           => gp247_language_render('Plugins/ShopDiscount::lang.admin.code_validate'),
            'form.code.discount_unique' => gp247_language_render('Plugins/ShopDiscount::lang.discount_unique'),
        ];
    }

    /**
     * Persist the sanitised form (insert when editingId is null, else update),
     * then sync the store pivot exactly as the legacy controller did.
     *
     * @param array<string, mixed> $data
     * @return void
     */
    protected function persist(array $data): void
    {
        $attributes = [
            'code'       => $data['code'],
            'reward'     => (float) $data['reward'],
            'limit'      => (int) $data['limit'],
            'type'       => $data['type'],
            'data'       => $data['data'] ?? '',
            'login'      => empty($data['login']) ? 0 : 1,
            'status'     => empty($data['status']) ? 0 : 1,
            'expires_at' => empty($data['expires_at']) ? null : $data['expires_at'],
        ];

        if ($this->editingId !== null) {
            $discount = ShopDiscount::findOrFail($this->editingId);
            $discount->update($attributes);
        } else {
            $discount = ShopDiscount::createDiscountAdmin($attributes);
        }

        // WHY: legacy default — with no explicit store selection the discount is
        // attached to the acting admin's store (single-store installs too).
        $shopStore = !empty($data['shop_store']) ? (array) $data['shop_store'] : [session('adminStoreId')];
        $discount->stores()->sync($shopStore);
    }

    /**
     * Delete a discount. The model's deleting hook detaches stores and users.
     *
     * @param int|string $id
     * @return void
     */
    protected function deleteModel($id): void
    {
        $model = $this->baseQuery()->find($id);
        if ($model !== null) {
            $model->delete();
        }
    }

    /**
     * Whether this install runs in multi-store / multi-partner mode.
     *
     * @return bool
     */
    public function isMultiStore(): bool
    {
        return gp247_store_check_multi_partner_installed() || gp247_store_check_multi_store_installed();
    }

    /**
     * Store options ([{id,label}]) for the multi-store selector; empty unless
     * multi-store is active.
     *
     * @return array<int, array<string, string>>
     */
    public function storeOptions(): array
    {
        if (!$this->isMultiStore() || !function_exists('gp247_store_get_list_code_store')) {
            return [];
        }

        $options = [];
        foreach (gp247_store_get_list_code_store() as $id => $code) {
            $options[] = ['id' => (string) $id, 'label' => (string) $code];
        }

        return $options;
    }
}
