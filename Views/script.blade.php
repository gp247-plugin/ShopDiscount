{{--
    Coupon apply/remove — v2 port: vanilla fetch instead of jQuery $.ajax.
    GP247 2.0 does not load jQuery on the storefront, so the legacy jQuery version
    silently failed. Same DOM ids and endpoints (discount.process / discount.remove)
    so any checkout view that includes render.blade.php + this script keeps working.

    NOTE: gp247/shop 2.0's Livewire checkout no longer includes this widget by
    default; surfacing the coupon box in the wizard is a shop/template concern
    (separate repo). The backend routes + ShopDiscount::getInfo() total line are intact.

    @aidlc-unit plugin-shop-discount
    @aidlc-story GP247-v2-compat
--}}
<script type="text/javascript">
(function () {
    var applyUrl  = "{{ gp247_route_front('discount.process') }}";
    var removeUrl = "{{ gp247_route_front('discount.remove') }}";
    var token     = "{{ csrf_token() }}";
    var uID       = {{ session('customer')->id ?? 0 }};

    var couponBtn   = document.getElementById('coupon-button');
    var couponInput = document.getElementById('coupon-value');
    var couponGroup = document.getElementById('coupon-group');
    var couponMsg   = document.querySelector('.coupon-msg');
    var removeBtn   = document.getElementById('removeCoupon');
    var totalBox    = document.getElementById('gp247_showTotal');

    /**
     * POST a form-encoded payload to the given coupon endpoint.
     * @param {string} url
     * @param {Object} payload
     * @returns {Promise<Object>} Parsed JSON response.
     */
    function postCoupon(url, payload) {
        var body = new URLSearchParams(payload);
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: body.toString()
        }).then(function (res) { return res.json(); });
    }

    if (couponBtn) {
        couponBtn.addEventListener('click', function () {
            var coupon = couponInput ? couponInput.value.trim() : '';
            if (coupon === '') {
                if (couponGroup) { couponGroup.classList.add('has-error'); }
                if (couponMsg) {
                    couponMsg.innerHTML = "{{ gp247_language_render('cart.coupon_empty') }}";
                    couponMsg.classList.add('text-danger');
                    couponMsg.style.display = '';
                }
                return;
            }

            couponBtn.setAttribute('disabled', 'disabled');
            postCoupon(applyUrl, { code: coupon, uID: uID, _token: token })
                .then(function (result) {
                    if (couponInput) { couponInput.value = ''; }
                    if (couponMsg) { couponMsg.classList.remove('text-danger', 'text-success'); couponMsg.style.display = 'none'; }
                    if (couponGroup) { couponGroup.classList.remove('has-error'); }

                    if (result.error == 1) {
                        if (couponGroup) { couponGroup.classList.add('has-error'); }
                        if (couponMsg) { couponMsg.innerHTML = result.msg; couponMsg.classList.add('text-danger'); couponMsg.style.display = ''; }
                    } else {
                        if (removeBtn) { removeBtn.style.display = ''; }
                        if (couponMsg) { couponMsg.innerHTML = result.msg; couponMsg.classList.add('text-success'); couponMsg.style.display = ''; }
                        if (totalBox && result.html) { totalBox.innerHTML = result.html; }
                    }
                })
                .catch(function () { console.log('error'); })
                .finally(function () { couponBtn.removeAttribute('disabled'); });
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', function () {
            postCoupon(removeUrl, { _token: token })
                .then(function (result) {
                    if (removeBtn) { removeBtn.style.display = 'none'; }
                    if (couponInput) { couponInput.value = ''; }
                    if (couponMsg) { couponMsg.classList.remove('text-danger', 'text-success'); couponMsg.style.display = 'none'; }
                    if (totalBox && result.html) { totalBox.innerHTML = result.html; }
                })
                .catch(function () { console.log('error'); });
        });
    }
})();
</script>
