[**Wersja polska**][ext0]
# Paynow WooCommerce Plugin

Paynow plugin adds quick bank transfers and BLIK payment to a WooCommerce shop.

This plugin supports WooCommerce 2.2 and higher.

## Installation
1. Download plugin from the [Github repository][ext1] to the local directory as zip
2. Unzip the downloaded file locally
3. Create a zip archive of `/woocommerce-gateway-paynow` folder
4. Go to the Wordpress administration page
5. Go to `Plugins` 
6. Use `Add new` option and point to the archive containing the plugin (created in step 3)
7. Activate the plugin

## Configuration
1. Go to the WooCommerce administration page
2. Go to `Settings > Payments`
3. Search and select `Paynow` and click `Manage`
4. Credential Keys can be found in `Settings > Shops and poses > Authentication data` in Paynow merchant panel
5. Type `Api-Key` and `Signature-Key` in proper fields

## FAQ
**How to configure return address?**

Return address will be set automatically for each order. There is no need to manually configure this address.

**How to configure a notification address?**

In the Paynow merchant panel go to the tab `Settings > Shops and poses`, in the field `Notification address` set the address: `https://your-domain.pl/?wc-api=WC_Gateway_Paynow`.

## Sandbox
To be able to test our Paynow Sandbox environment register [here][ext2]

## Support
If you have any questions or issues, please contact our support at support@paynow.pl.

## More info
If you wish to learn more about Paynow visit our website: https://www.paynow.pl/

## License
MIT license. For more information, see the LICENSE file.

[ext0]: README.md
[ext1]: https://github.com/pay-now/paynow-woocommerce/releases/latest
[ext2]: https://panel.sandbox.paynow.pl/auth/register