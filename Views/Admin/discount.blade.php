{{--
    Discount/coupon manager — v2 port (Livewire + TailAdmin, two-panel: add/edit
    form left, list right) on the core ResourcePanel base. Replaces the legacy
    AdminLTE views (form extended the removed `gp247-core::layout`; list used the
    removed `gp247-core::screen.list`). UI text via gp247_language_render; the
    date field uses the TailAdmin flatpickr (<x-gp247::input type="date">) instead
    of the old jQuery `.date_time`, and the store selector uses the Livewire
    <x-gp247::searchable-select multiple> instead of jQuery select2.

    @aidlc-unit plugin-shop-discount
    @aidlc-story GP247-v2-compat

    Variables: $rows (ShopDiscount paginator); $form, $editingId, $keyword, $sortField, $sortDir (state).
--}}
@php($appPath = 'Plugins/ShopDiscount')

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

    {{-- Left: add / edit form --}}
    <x-gp247::card :title="gp247_language_render($editingId ? 'action.edit' : $appPath.'::lang.admin.add_new_title')">
        <form wire:submit="save" class="space-y-4">

            <x-gp247::input :label="gp247_language_render($appPath.'::lang.code')" name="code"
                wire:model="form.code" :error="$errors->first('form.code')"
                :help="gp247_language_render($appPath.'::lang.admin.code_helper')" required />

            {{-- WHY: reward is a money amount for "point" coupons (stored in the base
                 currency) but a plain percentage for "percent" coupons, so the helper
                 hint switches unit accordingly. The type radios below use wire:model.live
                 so this server-rendered hint updates the moment the type changes. --}}
            <x-gp247::input type="number" step="0.01" min="0" :label="gp247_language_render($appPath.'::lang.reward')"
                name="reward" wire:model="form.reward" :error="$errors->first('form.reward')"
                :help="($form['type'] ?? 'point') === 'percent' ? '%' : gp247_money_hint()" required />

            {{-- type: point / percent --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ gp247_language_render($appPath.'::lang.type') }}</label>
                <div class="flex items-center gap-6">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                        <input type="radio" value="point" wire:model.live="form.type" class="text-blue-600 focus:ring-blue-500"> Point
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                        <input type="radio" value="percent" wire:model.live="form.type" class="text-blue-600 focus:ring-blue-500"> Percent (%)
                    </label>
                </div>
                @error('form.type')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <x-gp247::input :label="gp247_language_render($appPath.'::lang.data')" name="data"
                wire:model="form.data" :error="$errors->first('form.data')" />

            <x-gp247::input type="number" min="1" :label="gp247_language_render($appPath.'::lang.limit')"
                name="limit" wire:model="form.limit" :error="$errors->first('form.limit')" required />

            <x-gp247::input type="date" :label="gp247_language_render($appPath.'::lang.expires_at')"
                name="expires_at" wire:model="form.expires_at" :error="$errors->first('form.expires_at')" />

            @if ($this->isMultiStore())
                <x-gp247::searchable-select
                    multiple
                    model="form.shop_store"
                    :label="gp247_language_render('admin.select_store')"
                    :options="$this->storeOptions()"
                />
            @endif

            <x-gp247::checkbox :label="gp247_language_render($appPath.'::lang.login')" wire:model="form.login" value="1" />
            <x-gp247::checkbox :label="gp247_language_render($appPath.'::lang.status')" wire:model="form.status" value="1" />

            <div class="flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-700">
                <x-gp247::button variant="secondary" href="{{ gp247_route_admin('admin_discount.index') }}" wire:navigate>
                    {{ gp247_language_render($editingId ? 'admin.cancel' : 'admin.reset') }}
                </x-gp247::button>
                <x-gp247::button type="submit" wire:loading.attr="disabled">
                    <i class="fas fa-save"></i> {{ gp247_language_render($editingId ? 'admin.update' : 'admin.submit') }}
                </x-gp247::button>
            </div>
        </form>
    </x-gp247::card>

    {{-- Right: list --}}
    <x-gp247::card :title="gp247_language_render($appPath.'::lang.admin.list')">
        <div class="mb-3">
            <input type="search" wire:model.live.debounce.300ms="keyword"
                placeholder="{{ gp247_language_render($appPath.'::lang.admin.search_place') }}"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
        </div>

        <x-gp247::table :empty="$rows->isEmpty() ? gp247_language_render('admin.no_records') : null">
            <x-slot:head>
                <tr>
                    <th class="cursor-pointer px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400" wire:click="setSort('code')">
                        {{ gp247_language_render($appPath.'::lang.code') }} @if ($sortField === 'code')<span class="text-[10px]">{{ $sortDir === 'asc' ? '▲' : '▼' }}</span>@endif
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ gp247_language_render($appPath.'::lang.reward') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ gp247_language_render($appPath.'::lang.type') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ gp247_language_render($appPath.'::lang.used') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ gp247_language_render($appPath.'::lang.expires_at') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ gp247_language_render($appPath.'::lang.status') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ gp247_language_render($appPath.'::lang.admin.action') }}</th>
                </tr>
            </x-slot:head>

            @foreach ($rows as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ (string) $row->id === (string) $editingId ? 'bg-blue-50 dark:bg-blue-900/30' : '' }}" wire:key="discount-{{ $row->id }}">
                    <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $row->code }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                        @php($baseCode = gp247_base_currency_code())
                        @if ($row->type === 'percent')
                            {{ $row->reward }}%
                        @elseif ($baseCode)
                            {{-- Point reward is a fixed amount stored in the base currency; render
                                 with the base currency's symbol/format without converting it. --}}
                            {{ gp247_currency_render_symbol((float) $row->reward, $baseCode) }}
                        @else
                            {{ $row->reward }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $row->type === 'point' ? 'Point' : '%' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $row->used }}/{{ $row->limit }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $row->expires_at ? \Illuminate\Support\Carbon::parse($row->expires_at)->format('Y-m-d') : '—' }}</td>
                    <td class="px-4 py-3"><x-gp247::badge :color="$row->status ? 'green' : 'gray'">{{ $row->status ? gp247_language_render('admin.active') : gp247_language_render('admin.inactive') }}</x-gp247::badge></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <x-gp247::button size="sm" variant="ghost" href="{{ gp247_route_admin('admin_discount.edit', $row->id) }}" wire:navigate><i class="fas fa-edit"></i></x-gp247::button>
                            <x-gp247::button size="sm" variant="ghost" wire:click="delete('{{ $row->id }}')" wire:confirm="{{ gp247_language_render('action.delete_confirm') }}"><i class="fas fa-trash-alt text-red-600"></i></x-gp247::button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-gp247::table>

        <div class="mt-4">{{ $rows->links('gp247-admin::partials.pagination') }}</div>
    </x-gp247::card>
</div>
