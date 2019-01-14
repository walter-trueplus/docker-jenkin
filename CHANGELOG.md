## Bug Fixed

3.0.4
=============

* [#17](https://github.com/Magestore/pos-standard/issues/17) -- Rounding issue when take payment (fixed in [Magestore/pos-standard#50](https://github.com/Magestore/pos-standard/pull/50))
* [#20](https://github.com/Magestore/pos-standard/issues/20) -- Fix Problem data synchronization makes POS and Server slow (fixed in [Magestore/pos-standard#50](https://github.com/Magestore/pos-standard/pull/50))
* [#21](https://github.com/Magestore/pos-standard/issues/21) -- Update settings for testing API Config of Authorize Net and Stripe (fixed in [Magestore/pos-standard#50](https://github.com/Magestore/pos-standard/pull/50))

3.0.3
=============

* [#19](https://github.com/Magestore/pos-standard/issues/19) -- Fix hold order problem when the product has Qty = 1 (fixed in [Magestore/pos-standard#37](https://github.com/Magestore/pos-standard/pull/37))

3.0.2
=============

* [#1](https://github.com/Magestore/pos-standard/issues/1) -- Fix Bug POS switch to the checkout page even if the request has an error (by refreshing page).
* [#2](https://github.com/Magestore/pos-standard/issues/2) -- Log error to indexed DB when API request has errors. (fixed in [Magestore/pos-standard#16](https://github.com/Magestore/pos-standard/pull/16))
* [#22](https://github.com/Magestore/pos-standard/issues/22) -- Session timeout would unlimited if the session timeout config was 0 or null. (fixed in [Magestore/pos-standard#24](https://github.com/Magestore/pos-standard/pull/24))

3.0.1
=============

- Can not close category popup when there is no root category in POS.
- Do not show all POS menu items in backend when user doesn't have all permissions.
- Correct the message when log-in on another device at the same time.
- Fix UI/UX of virtual NumPad of adjustment refund & adjustment fee.
- Error message: fixQuantity() is undefined when choose a shipping method in POS.
- Subtotal, Product Price in receipt are different from order detail in POS.
- Can not calculate tax if post code is not used in tax rate.
- Fix bug "unable to save POS" in backend.
- Wrong shipping fee calculation when current currency is different than base currency.
- Show wrong order status when current currency is different than base currency.
- Rounding issue in Total Paid, Grand Total, Total Due, Subtotal & Discount calculation.
- Do not enable Clear Cart button after selected customer without item in cart.
- Can not create custom sale product automatically if there are more required product attributes.
