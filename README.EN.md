[**Wersja polska**][ext0]
# Paynow WooCommerce Plugin

Paynow plugin adds quick bank transfers and BLIK payment to a WooCommerce shop.

This plugin supports WooCommerce 2.2 and higher.

## Table of Contents

* [Installation](#installation)
* [Configuration](#configuration)
* [FAQ](#FAQ)
* [Sandbox](#sandbox)
* [Support](#support)
* [License](#license)

## Installation
1. Download plugin from the [Github repository][ext1] to the local directory as zip
2. Go to the Wordpress administration page
3. Go to `Plugins` 

![Installation step 3][ext3]

4. Use `Add new` option

![Installation step 4][ext4]

5. Use `Upload plugin` option and point to the archive containing the plugin (downloaded in the 1st step)

![Installation step 5][ext5]

6. Activate the plugin

![Installation step 6][ext6]


## Configuration
1. Go to the WooCommerce administration page
2. Go to `Settings > Payments`
3. Search and select `Paynow` and click `Manage`

![Configuration step 3][ext7]

4. Credential Keys can be found in `Settings > Shops and poses > Authentication data` in Paynow merchant panel

![Configuration step 4][ext8]

5. Depending on the environment you want to connect with go to section `Production configuration` or `Sandbox configuration` and type `Api-Key` and `Signature-Key` in proper fields

![Configuration step 5][ext9]


## FAQ
**How to configure return address?**

Return address will be set automatically for each order. There is no need to manually configure this address.

**How to configure a notification address?**

In the Paynow merchant panel go to the tab `Settings > Shops and poses`, in the field `Notification address` set the address: `https://your-domain.pl/?wc-api=WC_Gateway_Paynow`.

![Configuration of the notifiction address][ext10]

## Sandbox
To be able to test our Paynow Sandbox environment register [here][ext2]

## Support
If you have any questions or issues, please contact our support at support@paynow.pl.

If you wish to learn more about Paynow visit our website: https://www.paynow.pl/

## License
MIT license. For more information, see the LICENSE file.

[ext0]: README.md
[ext1]: https://github.com/pay-now/paynow-woocommerce/releases/latest
[ext2]: https://panel.sandbox.paynow.pl/auth/register
[ext3]: instruction/step1_EN.png
[ext4]: instruction/step2_EN.png
[ext5]: instruction/step3_EN.png
[ext6]: instruction/step4_EN.png
[ext7]: instruction/step5_EN.png
[ext8]: instruction/step6.png
[ext9]: instruction/step7_EN.png
[ext10]: instruction/step8.png