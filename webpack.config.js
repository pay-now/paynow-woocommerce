const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const WooCommerceDependencyExtractionWebpackPlugin = require('@woocommerce/dependency-extraction-webpack-plugin');
const path = require('path');

const wcDepMap = {
    '@woocommerce/blocks-registry': ['wc', 'wcBlocksRegistry'],
    '@woocommerce/settings'       : ['wc', 'wcSettings']
};

const wcHandleMap = {
    '@woocommerce/blocks-registry': 'wc-blocks-registry',
    '@woocommerce/settings'       : 'wc-settings'
};

const requestToExternal = (request) => {
    if (wcDepMap[request]) {
        return wcDepMap[request];
    }
};

const requestToHandle = (request) => {
    if (wcHandleMap[request]) {
        return wcHandleMap[request];
    }
};

// Export configuration.
module.exports = {
    ...defaultConfig,
    entry: {
        'paynow-apple-pay-block': '/src/Blocks/Payment/src/js/paynow-apple-pay-payment-block.js',
        'paynow-blik-block': '/src/Blocks/Payment/src/js/paynow-blik-payment-block.js',
        'paynow-card-block': '/src/Blocks/Payment/src/js/paynow-card-payment-block.js',
        'paynow-digital-wallets-block': '/src/Blocks/Payment/src/js/paynow-digital-wallets-payment-block.js',
        'paynow-google-pay-block': '/src/Blocks/Payment/src/js/paynow-google-pay-payment-block.js',
        'paynow-paywall-block': '/src/Blocks/Payment/src/js/paynow-paywall-payment-block.js',
        'paynow-pbl-block': '/src/Blocks/Payment/src/js/paynow-pbl-payment-block.js',
    },
    output: {
        path: path.resolve( __dirname, 'src/Blocks/Payment/build' ),
        filename: '[name].js',
    },
    plugins: [
        ...defaultConfig.plugins.filter(
            (plugin) =>
                plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
        ),
        new WooCommerceDependencyExtractionWebpackPlugin({
            requestToExternal,
            requestToHandle
        })
    ]
};
