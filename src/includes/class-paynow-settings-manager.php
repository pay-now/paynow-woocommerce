<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Paynow_Settings_Manager
 */
class Paynow_Settings_Manager {

    public const SETTING_WIDGET_COLOR_WHITE = 'white';
    public const SETTING_WIDGET_COLOR_BLACK = 'black';
    public const SETTING_WIDGET_COLOR_WHITE_GHOST = 'white_ghost';
    public const SETTING_WIDGET_COLOR_BLACK_GHOST = 'black_ghost';

    public const SETTING_WIDGET_LOCALIZATION_NONE = 'none';
    public const SETTING_WIDGET_LOCALIZATION_ADD_TO_CART = 'add_to_cart';
    public const SETTING_WIDGET_LOCALIZATION_UNDER_TITLE = 'under_title';
    public const SETTING_WIDGET_LOCALIZATION_UNDER_CONTENT = 'under_content';

    private const SETTINGS_NAME = 'paynow_settings_option_group';
    private const SETTINGS_PAGE_NAME = 'paynow-pl-settings';
    private const OPTION_NAME = 'paynow_global_settings_option';

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    private $notification_url = '';

	public function __construct() {
        $this->options = get_option(self::OPTION_NAME, []);

        add_filter('woocommerce_screen_ids', array($this, 'add_current_page_as_woocommerce_page'));
        add_action('admin_menu', array($this, 'add_plugin_settings_page'));
        add_action('admin_init', array($this, 'register_fields'));
	}

    public function get_sections_definition()
    {
        return [
            'paynow_information' => [
                'id' => 'paynow_information',
                'title' => __('Paynow information', 'pay-by-paynow-pl'),
                'desc' => __( 'If you do not have an account in the Paynow system yet, <a href="https://paynow.pl/boarding" target="_blank">register in the Production</a> or <a href="https://panel.sandbox.paynow.pl/auth/register" target="_blank">Sandbox environment</a>.<br /> If you have any problem with configuration, please find the manual <a href="https://github.com/pay-now/paynow-woocommerce/blob/master/README.EN.md" target="_blank">here</a>.', 'pay-by-paynow-pl' ),
                'page' => self::SETTINGS_PAGE_NAME,
            ],
            'paynow_production_config' => [
                'id' => 'paynow_production_config',
                'title' => __('Production configuration', 'pay-by-paynow-pl'),
                'desc' => __('Production authentication keys are available in <i>My Business > Paynow > Settings > Shops and payment points > Authentication data</i> in mBank\'s online banking.', 'pay-by-paynow-pl'),
                'page' => self::SETTINGS_PAGE_NAME,
                'fields' => [
                    [
                        'id' => 'production_api_key',
                        'title' => __('Api Key', 'pay-by-paynow-pl'),
                        'type' => 'text',
                    ], [
                        'id' => 'production_signature_key',
                        'title' => __('Signature Key', 'pay-by-paynow-pl'),
                        'type' => 'text',
                    ],
                ],
            ],
            'paynow_sandbox_config' => [
                'id' => 'paynow_sandbox_config',
                'title' => __('Sandbox configuration', 'pay-by-paynow-pl'),
                'desc' => __('Sandbox authentication keys can be found in <i>Settings > Shops and poses > Authentication data</i> in <a href="https://panel.sandbox.paynow.pl/auth/login" target="_blank">the Paynow Sandbox panel</a>.', 'pay-by-paynow-pl'),
                'page' => self::SETTINGS_PAGE_NAME,
                'fields' => [
                    [
                        'id' => 'is_sandbox',
                        'title' => __('Test mode (Sandbox)', 'pay-by-paynow-pl'),
                        'type' => 'checkbox',
                    ], [
                        'id' => 'sandbox_api_key',
                        'title' => __('Api Key', 'pay-by-paynow-pl'),
                        'type' => 'text',
                    ], [
                        'id' => 'sandbox_signature_key',
                        'title' => __('Signature Key', 'pay-by-paynow-pl'),
                        'type' => 'text',
                    ],
                ]
            ],
            'paynow_additional_options' => [
                'id' => 'paynow_additional_options',
                'title' => __('Additional options', 'pay-by-paynow-pl'),
                'page' => self::SETTINGS_PAGE_NAME,
                'fields' => [
                    [
                        'id' => 'debug_logs',
                        'title' => __( 'Debug', 'pay-by-paynow-pl' ),
                        'label' => __( 'Enable logs', 'pay-by-paynow-pl' ),
                        'tip' => __( 'Save debug messages to the WooCommerce System Status log.', 'pay-by-paynow-pl' ),
                        'type' => 'checkbox',
                    ], [
                        'id' => 'send_order_items',
                        'title' => __( 'Send order items', 'pay-by-paynow-pl' ),
                        'label' => __( 'Enable sending ordered products information: name, categories, quantity and unit price', 'pay-by-paynow-pl' ),
                        'type' => 'checkbox',
                    ], [
                        'id' => 'use_payment_validity_time_flag',
                        'title' => __( 'Use payment validity time', 'pay-by-paynow-pl' ),
                        'label' => __( 'Enable to limit the validity of the payment.', 'pay-by-paynow-pl' ),
                        'type' => 'checkbox',
                    ], [
                        'id' => 'payment_validity_time',
                        'title' => __( 'Payment validity time', 'pay-by-paynow-pl' ),
                        'tip' => __( 'Determines how long it will be possible to pay for the order from the moment the payment link is generated. Value expressed in seconds. The value must be between 60 and 86400 seconds.', 'pay-by-paynow-pl' ),
                        'type' => 'number',
                    ],
                ],
            ],
            'paynow_help_section' => [
                'id' => 'paynow_help_section',
                'title' => __('Support', 'pay-by-paynow-pl'),
                'desc' => __('If you have any questions or issues, please contact our support at <a href="mailto:support@paynow.pl">support@paynow.pl</a>', 'pay-by-paynow-pl'),
                'page' => self::SETTINGS_PAGE_NAME,
            ],
            'paynow_leaselink_config' => [
                'id' => 'paynow_leaselink_config',
                'title' => __('LeaseLink widget configuration', 'pay-by-paynow-pl'),
                'desc' => __('Don\'t have a contract with LeaseLink yet? Write to us at <a href="mailto:integracje@leaselink.pl">integracje@leaselink.pl</a> and we will contact you within 24 hours', 'pay-by-paynow-pl'),
                'page' => self::SETTINGS_PAGE_NAME,
                'fields' => [
                    [
                        'id' => 'll_is_sandbox',
                        'title' => __('Test mode (Sandbox)', 'pay-by-paynow-pl'),
                        'type' => 'checkbox',
                    ], [
                        'id' => 'll_sandbox_api_key',
                        'title' => __('Sandbox api key', 'pay-by-paynow-pl'),
                        'type' => 'text',
                    ], [
                        'id' => 'll_production_api_key',
                        'title' => __('Production api key', 'pay-by-paynow-pl'),
                        'type' => 'text',
                    ], [
                        'id' => 'll_notification_url',
                        'title' => __('Notification url', 'pay-by-paynow-pl'),
                        'type' => 'constant',
                        'value' => $this->notification_url,
                    ], [
                        'id' => 'll_widget_location',
                        'title' => __('LeaseLink widget location', 'pay-by-paynow-pl'),
                        'type' => 'select',
                        'options' => [
                            self::SETTING_WIDGET_LOCALIZATION_NONE => __('Do not display', 'pay-by-paynow-pl'),
                            self::SETTING_WIDGET_LOCALIZATION_ADD_TO_CART => __('Under the "Add to cart" button', 'pay-by-paynow-pl'),
                            self::SETTING_WIDGET_LOCALIZATION_UNDER_TITLE => __('Under the product title', 'pay-by-paynow-pl'),
                            self::SETTING_WIDGET_LOCALIZATION_UNDER_CONTENT => __('Below the product content', 'pay-by-paynow-pl'),
                        ],
                    ], [
                        'id' => 'll_widget_show_rate',
                        'title' => __('Presentation of the installment amount', 'pay-by-paynow-pl'),
                        'type' => 'checkbox',
                    ], [
                        'id' => 'll_widget_color',
                        'title' => __('LeaseLink widget colors', 'pay-by-paynow-pl'),
                        'type' => 'select',
                        'options' => [
                            self::SETTING_WIDGET_COLOR_BLACK => __('Black', 'pay-by-paynow-pl'),
                            self::SETTING_WIDGET_COLOR_WHITE => __('White', 'pay-by-paynow-pl'),
                            self::SETTING_WIDGET_COLOR_BLACK_GHOST => __('Black transparent', 'pay-by-paynow-pl'),
                            self::SETTING_WIDGET_COLOR_WHITE_GHOST => __('White transparent', 'pay-by-paynow-pl'),
                        ]
                    ], [
                        'id' => 'll_custom_css',
                        'title' => __('Additional CSS', 'pay-by-paynow-pl'),
                        'type' => 'textarea',
                    ]
                ]
            ],
        ];
    }

    public function get_api_key()
    {
        return $this->is_sandbox() ? ($this->options['sandbox_api_key'] ?? '') : ($this->options['production_api_key'] ?? '');
    }

    public function get_signature_key()
    {
        return $this->is_sandbox() ? ($this->options['sandbox_signature_key'] ?? '') : ($this->options['production_signature_key'] ?? '');
    }

    public function get_send_order_items()
    {
        return $this->options['send_order_items'] ?? false;
    }

    public function get_use_payment_validity_time_flag()
    {
        return $this->options['use_payment_validity_time_flag'] ?? false;
    }

    public function get_payment_validity_time()
    {
        return $this->options['payment_validity_time'] ?? 86400;
    }

    public function is_sandbox()
    {
        return $this->options['is_sandbox'] ?? false;
    }

    public function leaselink_is_sandbox()
    {
        return $this->options['ll_is_sandbox'] ?? false;
    }

    public function get_leaselink_api_key()
    {
        return $this->leaselink_is_sandbox() ? ($this->options['ll_sandbox_api_key'] ?? '') : ($this->options['ll_production_api_key'] ?? '');
    }

    public function get_leaselink_widget_color()
    {
        return $this->options['ll_widget_color'] ?? self::SETTING_WIDGET_COLOR_BLACK;
    }

    public function get_leaselink_widget_localization()
    {
        return $this->options['ll_widget_location'] ?? self::SETTING_WIDGET_LOCALIZATION_NONE;
    }

    public function get_leaselink_show_rate()
    {
        return $this->options['ll_widget_show_rate'] ?? false;
    }

    public function get_leaselink_custom_css()
    {
        return $this->options['ll_custom_css'] ?? null;
    }

    public function set_notification_url(string $url)
    {
        $this->notification_url = $url;
    }

    public function add_current_page_as_woocommerce_page($screen_ids)
    {
        $screen_ids[] = 'woocommerce_page_' . self::SETTINGS_PAGE_NAME;

        return $screen_ids;
    }

    public function add_plugin_settings_page() {

        add_submenu_page(
            'woocommerce',
            __('Paynow settings', 'pay-by-paynow-pl'),
            __('Paynow settings', 'pay-by-paynow-pl'),
            'manage_options',
            self::SETTINGS_PAGE_NAME,
            [$this, 'create_paynow_admin_settings_page'],
            500
        );
    }

    /**
     * @return void
     */
    public function create_paynow_admin_settings_page()
    {
        ?>
        <div class="wrap woocommerce">
            <h2><?php esc_html_e('Paynow settings', 'pay-by-paynow-pl') ?></h2>
            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php
                settings_fields(self::SETTINGS_NAME);
                do_settings_sections(self::SETTINGS_PAGE_NAME);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_fields() {
        register_setting(
            self::SETTINGS_NAME,
            self::OPTION_NAME,
            array( $this, 'sanitize' )
        );

        foreach ($this->get_sections_definition() as $section) {
            add_settings_section(
                $section['id'],
                $section['title'],
                array($this, 'section_callback'),
                self::SETTINGS_PAGE_NAME,
                $section
            );

            foreach ($section['fields'] ?? [] as $field) {
                if (!empty($field['tip'] ?? null)) {
                    $field['title'] .= sprintf('<span class="woocommerce-help-tip" tabindex="0" aria-label="%s" data-tip="%s"></span>', $field['tip'], $field['tip']);
                    $field['label_for'] = $field['id'];
                }

                add_settings_field(
                    $field['id'],
                    $field['title'],
                    [$this, 'field_callback'],
                    self::SETTINGS_PAGE_NAME,
                    $section['id'],
                    $field
                );
            }
        }
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();

        foreach ($this->get_sections_definition() as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                $id = $field['id'];

                if ($field['type'] === 'checkbox') {
                    $new_input[$id] = isset($input[$id]);
                    continue;
                }

                if (!isset($input[$id])) {
                    continue;
                }

                $new_input[$id] = sanitize_text_field($input[$id]);
            }
        }

        return $new_input;
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function field_callback($args)
    {
        $id = $args['id'];
        $value = isset($this->options[$id]) ? esc_attr($this->options[$id]) : '';
        $type = $args['type'] ?? 'text';

        switch ($type) {
            case 'constant':
                printf('<i>%s</i>', $args['value'] ?? '');
                break;
            case 'textarea':
                printf(
                    '<textarea class="large-text" name="%s[%s]" rows="20">%s</textarea>',
                    self::OPTION_NAME,
                    $id,
                    $value
                );
                break;
            case 'select':
                $options = '';
                foreach ($args['options'] ?? [] as $optionValue => $label) {
                    $options .= sprintf('<option value="%s" %s>%s</option>', $optionValue, $optionValue === $value ? 'selected' : '', $label);
                }

                printf(
                    '<select name="%s[%s]">%s</select>',
                    self::OPTION_NAME,
                    $id,
                    $options
                );
                break;
            case 'checkbox':
                $label_start = '';
                $label_end = '';

                if (!empty($args['label'] ?? null)) {
                    $label_start = sprintf('<label for="%s">', $id);
                    $label_end = sprintf(' %s</label>', $args['label']);
                }

                printf(
                    '%s<input class="regular-text" type="checkbox" id="%s" name="%s[%s]" value="%s" %s /> %s',
                    $label_start,
                    $id,
                    self::OPTION_NAME,
                    $id,
                    $value,
                    $value ? 'checked' : '',
                    $label_end
                );
                break;
            default:
                printf(
                    '<input class="regular-text" type="%s" id="%s" name="%s[%s]" value="%s" />',
                    $type,
                    $id,
                    self::OPTION_NAME,
                    $id,
                    $value
                );
        }
    }

    public function section_callback($args)
    {
        $desc = $args['desc'] ?? ($this->get_sections_definition()[$args['id'] ?? '']['desc'] ?? null);
        if (empty($desc)) {
            return;
        }

        printf(
            '<p>%s</p>',
            $desc
        );
    }
}
