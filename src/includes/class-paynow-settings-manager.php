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
    private const SECTIONS_DEFINITION = [
        [
            'id' => 'paynow_production_config',
            'title' => 'Production environment config',
            'desc' => 'Klucze uwierzytelniające dostępne są w zakładce <i>Mój Biznes &gt; Paynow &gt; Ustawienia &gt; Sklepy i punkty płatności &gt; Dane uwierzytelniające</i> w bankowości internetowej mBanku.',
            'page' => self::SETTINGS_PAGE_NAME,
            'fields' => [
                [
                    'id' => 'production_api_key',
                    'title' => 'Production api key',
                    'type' => 'text',
                ], [
                    'id' => 'production_signature_key',
                    'title' => 'Production signature key',
                    'type' => 'text',
                ],
            ],
        ], [
            'id' => 'paynow_sandbox_config',
            'title' => 'Test environment config(sandbox)',
            'desc' => 'Klucze uwierzytelniające dla środowiska testowego znajdziesz w zakładce <i>Ustawienia &gt; Sklepy i punkty płatności &gt; Dane uwierzytelniające</i> w <a href="https://panel.sandbox.paynow.pl/auth/login" target="_blank">panelu środowiska testowego</a>.',
            'page' => self::SETTINGS_PAGE_NAME,
            'fields' => [
                [
                    'id' => 'is_sandbox',
                    'title' => 'Test mode (Sandbox)',
                    'type' => 'checkbox',
                ], [
                    'id' => 'sandbox_api_key',
                    'title' => 'Sandbox api key',
                    'type' => 'text',
                ], [
                    'id' => 'sandbox_signature_key',
                    'title' => 'Sandbox signature key',
                    'type' => 'text',
                ],
            ]
        ], [
            'id' => 'paynow_leaselink_config',
            'title' => 'Konfiguracja widgetu LeaseLink',
            'page' => self::SETTINGS_PAGE_NAME,
            'fields' => [
                [
                    'id' => 'll_is_sandbox',
                    'title' => 'Test mode (Sandbox)',
                    'type' => 'checkbox',
                ], [
                    'id' => 'll_sandbox_api_key',
                    'title' => 'Sandbox api key',
                    'type' => 'text',
                ], [
                    'id' => 'll_production_api_key',
                    'title' => 'Production api key',
                    'type' => 'text',
                ], [
                    'id' => 'll_widget_location',
                    'title' => 'Lokalizacja widgetu LeaseLink',
                    'type' => 'select',
                    'options' => [
                        self::SETTING_WIDGET_LOCALIZATION_NONE => 'Nie wyświetlaj',
                        self::SETTING_WIDGET_LOCALIZATION_ADD_TO_CART => 'Pod przyciskiem "Dodaj do koszyka"',
                        self::SETTING_WIDGET_LOCALIZATION_UNDER_TITLE => 'Pod tytułem produktu',
                        self::SETTING_WIDGET_LOCALIZATION_UNDER_CONTENT => 'Pod treścią produktu',
                    ],
                ], [
                    'id' => 'll_widget_show_rate',
                    'title' => 'Prezentacja wysokości raty',
                    'type' => 'checkbox',
                ], [
                    'id' => 'll_widget_color',
                    'title' => 'Kolorystyka widgetu LeaseLink',
                    'type' => 'select',
                    'options' => [
                        self::SETTING_WIDGET_COLOR_BLACK => 'Czarny',
                        self::SETTING_WIDGET_COLOR_WHITE => 'Biały',
                        self::SETTING_WIDGET_COLOR_BLACK_GHOST => 'Czarny przezroczysty',
                        self::SETTING_WIDGET_COLOR_WHITE_GHOST => 'Biały przezroczysty',
                    ]
                ], [
                    'id' => 'll_custom_css',
                    'title' => 'Dodatkowy CSS',
                    'type' => 'textarea',
                ]
            ]
        ], [
            'id' => 'paynow_help_section',
            'title' => 'Pomoc',
            'desc' => 'Jeśli masz jakiekolwiek pytania lub problemy, skontaktuj się z naszym wsparciem technicznym: <a href="mailto:support@paynow.pl">support@paynow.pl</a>.',
            'page' => self::SETTINGS_PAGE_NAME,
        ]
    ];

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

	public function __construct() {
        $this->options = get_option(self::OPTION_NAME, []);

        add_action('admin_menu', array($this, 'add_plugin_settings_page'));
        add_action('admin_init', array($this, 'register_fields'));
	}

    public function get_api_key()
    {
        return $this->is_sandbox() ? ($this->options['sandbox_api_key'] ?? '') : ($this->options['production_api_key'] ?? '');
    }

    public function get_signature_key()
    {
        return $this->is_sandbox() ? ($this->options['sandbox_signature_key'] ?? '') : ($this->options['production_signature_key'] ?? '');
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
        <div class="wrap">
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

        foreach (self::SECTIONS_DEFINITION as $section) {
            add_settings_section(
                $section['id'],
                $section['title'],
                array($this, 'section_callback'),
                self::SETTINGS_PAGE_NAME,
                $section
            );

            foreach ($section['fields'] ?? [] as $field) {
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

        foreach (self::SECTIONS_DEFINITION as $section) {
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

        if ($type === 'textarea') {
            printf(
                '<textarea class="large-text" name="%s[%s]" rows="20">%s</textarea>',
                self::OPTION_NAME,
                $id,
                $value
            );
        } elseif ($type === 'select') {
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
        } else {
            printf(
                '<input class="regular-text" type="%s" id="%s" name="%s[%s]" value="%s" %s />',
                $type,
                $id,
                self::OPTION_NAME,
                $id,
                $value,
                $type === 'checkbox' && $value ? 'checked' : ''
            );
        }
    }

    public function section_callback($args)
    {
        if (empty($args['desc'])) {
            return;
        }

        printf(
            '<p>%s</p>',
            $args['desc'] ?? ''
        );
    }
}
