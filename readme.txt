=== Pay by paynow.pl ===
Tags: payment, payment gateway, paynow, woocommerce, płatności
Requires at least: 4.4
Tested up to: 5.53
Requires PHP: 7.1
Stable tag: 1.0.14
License: GPLv3

paynow is a secure online payment by bank transfers, BLIK and card.

== Description ==
paynow is a secure online payment by bank transfers, BLIK and card.

== Installation ==
1. Go to the WooCommerce administration page
2. Go to `Settings > Payments`
3. Search and select `Paynow` and click `Manage`
4. Production credential keys can be found in the tab `My business > Paynow > Settings > Shops and poses > Authentication data` in the mBank\'s online banking.
Sandbox credential keys can be found in `Settings > Shops and poses > Authentication data` in the [sandbox panel][ext0].
[ext0]: https://panel.sandbox.paynow.pl/auth/register
5. Depending on the environment you want to connect to go to the `Production configuration` section or the `Sandbox configuration` section and type `Api Key` and `Signature Key` in the proper fields.


== Frequently Asked Questions ==
**How to configure the return address?**

The return address will be set automatically for each order. There is no need to manually configure this address.

**How to configure the notification address?**

In the Paynow merchant panel go to the tab `Settings > Shops and poses`, in the field `Notification address` set the address: `https://your-domain.pl/?wc-api=WC_Gateway_Paynow`.