"use strict";

let InstafoodCustomJSHooksClass = function() {

    // override formatted price displa
    // https://refreshless.com/wnumb/ library is already loaded (check out the docs)
    this.onPriceFormat = function(price, is_frontend_enabled_decimal_dot) {

        // return null if don't want to alter the frontend price format
        return null;

        if (!wNumb) {
            return null;
        }

        const moneyFormat = wNumb({
            decimals: 2, // decimals number
            mark: ',', // decimals separator
            thousand: '.', // thousand separator
        });
        return moneyFormat.to(price);
    }
}

window.instafoodCustomJSHooks = new InstafoodCustomJSHooksClass();