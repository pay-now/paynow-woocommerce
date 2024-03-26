---
title: Plugin package building
---

# Leaselink integration documentation

## Menu

1. General
2. Settings
3. Api connector
4. Payment method
5. Notifications API
6. Widget
7. Translations

## General

The plugin main file is `src/includes/class-wc-leaselink-plugin-pl-manager.php`. This is the place where we should look for including plugin dependencies, setting plugin up, attaching important wordpress hooks. At the end of the file there is a function which creates and returns the plugin main object. You can access settings and leaselink objects by the object.

- Settings: `wc_leaselink_plugin()->settings()`
- Leaselink: `wc_leaselink_plugin()->leaselink()`

! Be careful of using these functions in the code outside hooks - the objects can not be initialized yet.

Global variables are declared at `src/leaselink-plugin-pl.php`

## Settings

Settings page in wordpress admin panel is created by `Leaselink_Settings_Manager` object in `src/includes/class-leaselink-settings-manager.php`. It uses default wordpress mechanism to generate the page. All the fields definitions are in `get_sections_definition` method, the settings are stored in database by key `leaselink_global_settings_option`. You can access the settings simply using `get_option` function, but I recommend to use global settings object.

Important method of the class is `field_callback` - it is generates the fields on the page. You can change the design or behaviour of the fields.

The object provides also list of getters for all defined settings.

Read more: https://codex.wordpress.org/Creating_Options_Pages

## Api connector

Api connector is constructed from three classes: `Leaselink_Client`, `Leaselink_HTTP_Client`, `Leaselink_Configuration`

- `Leaselink_Configuration` - `src/includes/leaselink/class-leaselink-configuration.php` - The class is used to store and provide configuration for Leaselink api client. It is also resolving the api url based on current env.
- `Leaselink_HTTP_Client` - `src/includes/leaselink/class-leaselink-http-client.php` - The class is used to provide basic functionality like calling to clint api. The class uses guzzle http client to make call, build payloads and resolve responses. You can easily change a http client by editing the class.
- `Leaselink_Client` - `src/includes/leaselink/class-leaselink-client.php` - The class is a main class of api client, it is creating payloads from wordpress data, provides concrete function which are strictly connected with Leaselink API. It creates a requests objects and uses they to call to API.

Also, there are defined request and response classes for each API actions. They are stored in `src/includes/leaselink/request` and `src/includes/leaselink/response` subfolders. The classes are simple proxies for raw data used to communicate with api and responses from the api.

## Payment method

There are two places where the payment classes are stored.

- `src/includes/gateways/class-wc-gateway-pay-by-paynow-pl-leaselink.php` - This is the main class for payment in the checkout functionality. It defines the description, title and a few more payment configuration properties. Also, there is function to check if the payment should be available - we're checking there that can we get the offer for current cart. Next important function is `process_payment` - it creating offer and redirects client from merchant into leaselink wizard after placing an order. It is also responsible for adding metadata properties about leaselink offer (like status and number) to order.
- `src/Blocks/Payment/class-leaselink-payment.php` - this class is used as proxy to main class to show payment method in woocommerce blocks feature. It is also registering js assets for the payment. Read more: https://developer.woo.com/2022/07/07/exposing-payment-options-in-the-checkout-block/

## Notifications API

Notifications REST API are registered in `src/includes/leaselink/class-leaselink-notification-api.php`. It uses default wordpress mechanism to register endpoints, it is also adding the api url to settings page for integration purposes. The main method of the class is `process` function. It is taking care of whole process from receiving notification, through updates order data to change order status. It is written as simply as it can be. 

## Widget

Widget is rendered and displayed by `Leaselink_Widget` object stored in `src/includes/leaselink/class-leaselink-widget.php`. It is responsible for getting offer for client based on given products, prepare data, additional classes and render the widget in chosen location. It is also responsible for checking if widget can be rendered. Whole widget html are stored in `src/includes/templates/leaselink_widget.php`.

## Translations

Translations of the plugin are stored in `src/languages`. The plugin uses local translations because it is not published to wordpress repositories. There are to available languages: English (default, used as translation sources) and Polish.

To change Polish translations, i recommend to use other plugin like Loco Translate. We can edit translations using the other plugin GUI and then copy `.pot`, `.mo` and `.po` files into project.

To change English translations we need to find it in the code, change what is needed, and then sync translations source by other plugin, update Polish translations, and copy files to project. 
