=== Pay by paynow.pl ===
Tags: payment, payment gateway, paynow, woocommerce, płatności, payments, bramka płatności
Tested up to: 6.4
Requires PHP: 7.2
Stable tag: 2.5.7
License: GPLv3

**pay**now is a secure online payment by bank transfers, BLIK and card.

== Description ==
**pay**now gives you a free registration and only 0.95% commission.

Simple configuration for BLIK, quick transfers and cards payments. Pay-out immediately on the bank account. To use paynow, you need to have a business account in mBank.

If you do not have an account in the Paynow system yet, register in the [Production](https://paynow.pl/boarding) or [Sandbox environment](https://panel.sandbox.paynow.pl/auth/register).

== Installation ==
1. Go to the WooCommerce administration page
2. Go to `Settings > Payments`
3. Search and select `paynow.pl` and click `Manage`
4. Production credential keys can be found in the tab `My business > Paynow > Settings > Shops and poses > Authentication data` in the mBank's online banking.
**Sandbox credential keys can be found in `Settings > Shops and poses > Authentication data` in the [sandbox panel](https://panel.sandbox.paynow.pl/auth/login).
5. Depending on the environment you want to connect to go to the `Production configuration` section or the `Sandbox configuration` section and type `Api Key` and `Signature Key` in the proper fields.


== Frequently Asked Questions ==
**How to configure the return address?**

The return address will be set automatically for each order. There is no need to manually configure this address.

**How to configure the notification address?**

In the Paynow merchant panel go to the tab `Settings > Shops and poses`, in the field `Notification address` set the address: `https://your-domain.pl/?wc-api=WC_Gateway_Pay_By_Paynow_PL`.
