{{--
    Coupon fragment for the checkout total-method zone (CheckoutTotalMethod contract, L3).
    Rendered by gp247-shop-front::partials.checkout_total_methods INSIDE the CheckoutWizard
    Livewire component, so wire:model / wire:click bind straight to the wizard — no jQuery,
    no fetch, no DOM swap. Uses only storefront UI tokens so it works on any template
    that provides them (rule gp247.md §3b).

    @aidlc-unit storefront
    @aidlc-story US-LW-006
    @aidlc-adr ADR-storefront-checkout-total-method-contract

    Variables (from the zone partial): $pluginKey (e.g. 'ShopDiscount'); $plugin (getInfo array);
    $message (['error'=>int,'msg'=>string] or null). Component state: $totalPayload.
--}}
@php($appliedCode = session('totalMethod')[$pluginKey] ?? null)
<div class="card p-5" wire:key="total-method-{{ $pluginKey }}">
    <label class="block text-sm font-medium text-ink-700 mb-2">
        <i class="fa fa-tag"></i> {{ gp247_language_render('cart.coupon') }}
    </label>

    @if ($appliedCode)
        <div class="flex items-center justify-between gap-3">
            <span class="text-sm font-semibold text-emerald-600">{{ $appliedCode }}</span>
            <button type="button" class="btn-ghost text-red-500"
                    wire:click="removeTotal('{{ $pluginKey }}')" wire:loading.attr="disabled">
                {{ gp247_language_render('cart.remove_coupon') }} <i class="fa fa-times"></i>
            </button>
        </div>
    @else
        <div class="flex items-center gap-2">
            <input type="text" class="input flex-1"
                   wire:model="totalPayload.{{ $pluginKey }}.code"
                   wire:keydown.enter.prevent="applyTotal('{{ $pluginKey }}')"
                   placeholder="{{ gp247_language_render('cart.coupon') }}">
            <button type="button" class="btn-primary"
                    wire:click="applyTotal('{{ $pluginKey }}')" wire:loading.attr="disabled">
                {{ gp247_language_render('cart.apply') }}
            </button>
        </div>
    @endif

    @if (!empty($message) && !empty($message['msg']))
        <p class="mt-2 text-sm {{ empty($message['error']) ? 'text-emerald-600' : 'text-red-500' }}">{{ $message['msg'] }}</p>
    @endif
</div>
