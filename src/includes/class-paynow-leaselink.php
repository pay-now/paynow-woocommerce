<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Paynow_Leaselink
 */
class Paynow_Leaselink {

    const WIDGET_COLOR_SETTING_MAP = [
        Paynow_Settings_Manager::SETTING_WIDGET_COLOR_BLACK => [
            'image' => 'arrow-purple.svg',
            'class' => 'paynow-leaselink__color-black',
        ],
        Paynow_Settings_Manager::SETTING_WIDGET_COLOR_WHITE => [
            'image' => 'arrow-purple.svg',
            'class' => 'paynow-leaselink__color-white',
        ],
        Paynow_Settings_Manager::SETTING_WIDGET_COLOR_BLACK_GHOST => [
            'image' => 'arrow-black.svg',
            'class' => 'paynow-leaselink__color-black-ghost',
        ],
        Paynow_Settings_Manager::SETTING_WIDGET_COLOR_WHITE_GHOST => [
            'image' => 'arrow-white.svg',
            'class' => 'paynow-leaselink__color-white-ghost',
        ]
    ];

    const WIDGET_LOCALIZATION_SETTINGS_MAP = [
        Paynow_Settings_Manager::SETTING_WIDGET_LOCALIZATION_ADD_TO_CART => [
            'hook' => 'woocommerce_after_add_to_cart_form',
            'priority' => 10,
        ],
        Paynow_Settings_Manager::SETTING_WIDGET_LOCALIZATION_UNDER_CONTENT => [
            'hook' => 'woocommerce_after_single_product_summary',
            'priority' => 12,
        ],
        Paynow_Settings_Manager::SETTING_WIDGET_LOCALIZATION_UNDER_TITLE => [
            'hook' => 'woocommerce_single_product_summary',
            'priority' => 7,
        ],
    ];

    private $client;

    private $settings_manager;

	public function __construct(Paynow_Settings_Manager $settings_manager) {
        $this->settings_manager = $settings_manager;
        $this->client = new Leaselink_Client($settings_manager);

        $this->add_filters_and_actions();
	}

    public function client() {
        return $this->client;
    }

    public function add_filters_and_actions() {
        add_filter('manage_edit-shop_order_columns', array($this, 'add_order_columns'));
        add_filter('woocommerce_shop_order_search_fields', array($this, 'add_order_list_search_field'));
        add_filter('woocommerce_admin_order_preview_get_order_details', array($this, 'add_leaselink_info_to_order_preview'), 10, 2);

        add_action('manage_shop_order_posts_custom_column', array($this, 'add_order_columns_content'));
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'add_leaselink_data_to_order_page'));
        add_action('woocommerce_order_details_after_order_table', array($this, 'add_leaselink_data_to_client_order_page'));
        add_action('wp_ajax_leaselink_get_offer_for_client', array($this, 'get_offer_for_client'));
        add_action('leaselink_process_order_status', array($this, 'process_order_status'), 10, 2);
        add_action( 'wp_head', array($this, 'print_custom_styles'));

        if ($this->settings_manager->get_leaselink_widget_localization() !== Paynow_Settings_Manager::SETTING_WIDGET_LOCALIZATION_NONE) {
            $localization = self::WIDGET_LOCALIZATION_SETTINGS_MAP[$this->settings_manager->get_leaselink_widget_localization()] ?? [];
            add_action($localization['hook'] ?? 'woocommerce_after_add_to_cart_form', 'wc_pay_by_paynow_leaselink_render_widget', $localization['priority'] ?? 10);
        }
    }

    public function add_order_columns($columns) {
        $new_columns = array();
        foreach ($columns as $column_name => $column_info) {
            $new_columns[$column_name] = $column_info;
            if ('order_status' === $column_name) {
                $new_columns['leaselink_status'] = 'Status leaselink';
                $new_columns['leaselink_number'] = 'Numer wniosku leaselink';
                $new_columns['leaselink_form'] = 'Forma wniosku leaselink';
            }
        }
        return $new_columns;
    }

    public function add_order_columns_content($column) {
        global $post;
        if ( in_array($column, ['leaselink_status', 'leaselink_number', 'leaselink_form']) ) {
            $order = wc_get_order( $post->ID );

            echo $order->get_meta('_' . $column) ?? '';
        }
    }

    public function add_order_list_search_field($search_fields) {
        $search_fields[] = '_leaselink_number';

        return $search_fields;
    }

    public function add_leaselink_info_to_order_preview($data, $order) {
        $number = $order->get_meta('_leaselink_number');

        if (!$number || empty($data['payment_via'])) {
            return $data;
        }

        $status = $order->get_meta('_leaselink_status');
        $form = $order->get_meta('_leaselink_form');

        $data['payment_via'] .= '<br />Status: ' . $status . '<br />Number: ' . $number . '<br />Forma: ' . $form;

        return $data;
    }

    public function add_leaselink_data_to_order_page($order) {
        $number = $order->get_meta('_leaselink_number');

        if (!$number) {
            return;
        }

        $status = $order->get_meta('_leaselink_status');
        $form = $order->get_meta('_leaselink_form');

        printf('<h3>Dane Leaselink</h3>
        <div>
            <p>
                Numer: %s <br/>
                Status: %s <br/>
                Forma: %s
            </p>
        </div>',
            $number,
            $status,
            $form
        );
    }

    public function add_leaselink_data_to_client_order_page($order) {
        $number = $order->get_meta('_leaselink_number');

        if (!$number) {
            return;
        }

        $status = $order->get_meta('_leaselink_status');
        $form = $order->get_meta('_leaselink_form');

        printf('<h2 class="woocommerce-column__title">Dane Leaselink</h2>
            <address>
                Numer: %s <br/>
                Status: %s <br/>
                Forma: %s
            </address>',
            $number,
            $status,
            $form
        );
    }

    public function print_custom_styles() {
        $css = $this->settings_manager->get_leaselink_custom_css();

        if (empty($css)) {
            return;
        }

        printf('<style>%s</style>', $css);
    }

    public function render_widget($products = null) {
        if (empty($products)) {
            $product = wc_get_product();

            if ($product) {
                $products = $product->get_id();
            }
        }

        if (empty($products)) {
            return;
        }

        $data = self::WIDGET_COLOR_SETTING_MAP[wc_pay_by_paynow()->settings()->get_leaselink_widget_color()];
        $data['product_ids'] = is_array($products) ? join(',', $products) : $products;
        $data['rates'] = [3, 6, 12, 18, 24, 36, 48, 60, 72];
        $data['entry_payment'] = [1, 10, 20, 30];
        $data['closing_payment'] = [1, 30];
        $data['widget_products'] = $this->get_products_from_string_of_ids($data['product_ids']);
        $data['widget_products_sum'] = $this->calculate_products_sum($data['widget_products']);

        /** @var \Leaselink_Offer_For_Client_Response $response */
        $response = $this->client->get_offer_for_client($data['widget_products'], [
            'rates' => $data['rates'][0],
            'entry_payment' => $data['entry_payment'][0],
            'closing_payment' => $data['closing_payment'][0],
        ]);

        if (!$response->is_success()) {
            return;
        }

        $data['widget_net_value'] = $response->get_monthly_rate_net_value();
        $data['checked_rate'] = $response->get_number_of_rates();
        $data['entry_net_payment'] = $response->get_entry_net_payment();
        $data['closing_net_payment'] = $response->get_closing_net_payment();
        $data['financial_product_name'] = $response->get_financial_product_name();
        $data['help_tooltip_class'] = $response->get_financial_operation_type_name() === 'OperationalLeasing' ? '--only-leasing' : '--only-loan';

        include WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . WC_PAY_BY_PAYNOW_PL_PLUGIN_TEMPLATES_PATH . 'leaselink_widget.phtml';
    }

    public function get_offer_for_client() {
        if (!function_exists('wp_send_json')) {
            return;
        }

        $products = $this->get_products_from_request();

        /** @var \Leaselink_Offer_For_Client_Response $response */
        $response = $this->client->get_offer_for_client($products, [
            'rates' => sanitize_text_field($_REQUEST['number_of_rates'] ?? '') ?? 60,
            'entry_payment' => sanitize_text_field($_REQUEST['entry_payment_percent'] ?? '') ?? 1,
            'closing_payment' => sanitize_text_field($_REQUEST['closing_payment_percent'] ?? '') ?? 1,
        ]);

        wp_send_json([
            'number_of_rates' => $response->get_number_of_rates(),
            'entry_payment' => wc_price($response->get_entry_net_payment()) . ' (netto)',
            'closing_payment' => wc_price($response->get_closing_net_payment()) . ' (netto)',
            'financial_product' => $response->get_financial_product_name(),
            'monthly_rate' => wc_price($response->get_monthly_rate_net_value()),
        ]);
    }

    public function process_order_status($order_id, $attempt) {
        Leaselink_Order_Status_Processor::process($order_id, $attempt);
    }

    private function get_products_from_request() {
        $products_ids = sanitize_text_field($_REQUEST['products'] ?? '');

        return $this->get_products_from_string_of_ids($products_ids);
    }

    private function get_products_from_string_of_ids(string $ids) {
        $products_ids = explode(',', $ids);
        $products = [];

        foreach ($products_ids as $id) {
            $product = wc_get_product($id);

            if ($product) {
                $products[] = $product;
            }
        }

        return $products;
    }

    private function calculate_products_sum($products) {
        $sum = 0;

        foreach ($products as $product) {
            $price = wc_get_price_excluding_tax($product);

            if ($price) {
                $sum += $price;
            }
        }

        return $sum;
    }
}
