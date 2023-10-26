<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Paynow_Leaselink
 */
class Paynow_Leaselink {

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

    private $widget;

	public function __construct(Paynow_Settings_Manager $settings_manager) {
        $this->settings_manager = $settings_manager;
        $this->client = new Leaselink_Client($settings_manager);
        $this->widget = new Leaselink_Widget($this->client, $settings_manager);
        new Leaselink_Notification_Api($this->settings_manager);

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
        $this->widget->render($products);
    }
}
