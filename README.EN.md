[**Wersja polska**][ext0]

# Paynow WooCommerce Plugin

The Paynow plugin adds quick bank transfers and BLIK payments to a WooCommerce shop.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [FAQ](#FAQ)
- [Sandbox](#sandbox)
- [Support](#support)
- [License](#license)

## Prerequisites

- PHP since 7.1
- WooCommerce 2.2 or higher

## Installation

See also [instructional video][ext12].

1. Download the pay-by-paynow-pl.zip file from [Github repository][ext1] to a local directory
2. Go to the Wordpress administration page
3. Go to `Plugins`

![Installation step 3][ext3]

4. Use the `Add new` option

![Installation step 4][ext4]

5. Use the `Upload plugin` option and point to the archive containing the plugin (downloaded in the 1st step)

![Installation step 5][ext5]

6. Activate the plugin

![Installation step 6][ext6]

## Configuration

1. Go to the WooCommerce administration page
2. Go to `Settings > Payments`
3. Search and select `Paynow` and click `Manage`

![Configuration step 3][ext7]

4. Production credential keys can be found in the tab `My business > Paynow > Settings > Shops and poses > Authentication data` in the mBank's online banking.

   Sandbox credential keys can be found in `Settings > Shops and poses > Authentication data` in the [sandbox panel][ext11].

![Configuration step 4a][ext8]
![Configuration step 4b][ext13]

5. Depending on the environment you want to connect to go to the `Production configuration` section or the `Sandbox configuration` section and type `Api Key` and `Signature Key` in the proper fields.

![Configuration step 5][ext9]

## FAQ

**How to configure the return address?**

The return address will be set automatically for each order. There is no need to manually configure this address.

**How to configure the notification address?**

In the Paynow merchant panel go to the tab `Settings > Shops and poses`, in the field `Notification address` set the address: `https://your-domain.pl/?wc-api=WC_Gateway_Paynow`.

![Configuration of the notifiction address][ext10]

## Sandbox

To be able to test our Paynow Sandbox environment, register [here][ext2].

## Support

If you have any questions or issues, please contact our support at support@paynow.pl.

If you wish to learn more about Paynow visit our website: https://www.paynow.pl/.

## License

GPL license. For more information, see the LICENSE file.

[ext0]: README.md
[ext1]: https://github.com/pay-now/paynow-woocommerce/releases/latest/download/pay-by-paynow-pl.zip
[ext2]: https://panel.sandbox.paynow.pl/auth/register
[ext3]: instruction/step1_EN.png
[ext4]: instruction/step2_EN.png
[ext5]: instruction/step3_EN.png
[ext6]: instruction/step4_EN.png
[ext7]: instruction/step5_EN.png
[ext8]: instruction/step6a.png
[ext9]: instruction/step7_EN.png
[ext10]: instruction/step8.png
[ext11]: https://panel.sandbox.paynow.pl/merchant/payments
[ext12]: https://paynow.wistia.com/medias/g62mlym13x
[ext13]: instruction/step6b.png
