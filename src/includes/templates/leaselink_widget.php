<div class="paynow-leaselink alignwide <?php echo $data['class']; ?>">
    <div class="paynow-leaselink__button" data-leaselink-widget-button>
        <?php if (wc_leaselink_plugin()->settings()->get_leaselink_show_rate()): ?>
            <p class="paynow-leaselink__title"><?php echo __('Take a lease from', 'leaselink-plugin-pl') . ' ' . wc_price($data['widget_net_value']) ?></p>
        <?php else: ?>
            <p class="paynow-leaselink__title"><?php echo __('Lease now', 'leaselink-plugin-pl') ?></p>
        <?php endif; ?>
        <div class="paynow-leaselink__image-container">
            <img
                class="paynow-leaselink__image"
                src="<?php echo WC_LEASELINK_PLUGIN_ASSETS_PATH . 'images/' . $data['image']; ?>"
                alt="Arrow"
            >
        </div>
    </div>
</div>

<div class="paynow-leaselink__calculator__background paynow-leaselink__calculator__hidden" data-leaselink-calculator>
    <script>
        window.leaselink_offers_json = <?php echo json_encode($data['offers']); ?>;
    </script>
    <div class="paynow-leaselink__calculator">
        <div class="paynow-leaselink__calculator__header">
            <img class="paynow-leaselink__calculator__header__logo" src="<?php echo WC_LEASELINK_PLUGIN_ASSETS_PATH . 'images/ll-logo.png'; ?>" alt="ll logo">
            <p><?php echo __('Leasing and installments for companies 24/7', 'leaselink-plugin-pl') ?></p>
            <div class="paynow-leaselink__calculator__header__exit-wrapper" data-leaselink-calculator-close>
                <img class="paynow-leaselink__calculator__header__exit" src="<?php echo WC_LEASELINK_PLUGIN_ASSETS_PATH . 'images/exit.png'; ?>" alt="exit icon">
            </div>
        </div>

        <div class="paynow-leaselink__calculator__content">
            <div class="paynow-leaselink__calculator__products">
                <p class="paynow-leaselink__calculator__products__title"><?php echo __('Selected product', 'leaselink-plugin-pl') ?></p>
                <div class="paynow-leaselink__calculator__products__product-wrapper">
                    <?php foreach ($data['widget_products'] ?? [] as $product): ?>
                        <div class="paynow-leaselink__calculator__products__product">
                            <p class="paynow-leaselink__calculator__products__product__qty">1 x</p>
                            <p class="paynow-leaselink__calculator__products__product__text">
                                <span class="paynow-leaselink__calculator__products__product__title"><?php echo $product->get_title(); ?></span>
                                <br class="paynow-leaselink__calculator__products__product__title-break"/>
                                <span class="paynow-leaselink__calculator__products__product__price"><?php echo wc_price(wc_get_price_excluding_tax($product)); ?> (<?php echo __('net', 'leaselink-plugin-pl') ?>)</span>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="paynow-leaselink__calculator__products-summary-lg">
                    <p class="paynow-leaselink__calculator__products-summary-lg__label"><?php echo __('Total (net):', 'leaselink-plugin-pl') ?></p>
                    <p class="paynow-leaselink__calculator__products-summary-lg__price"><?php echo wc_price($data['widget_products_sum']); ?></p>
                </div>
            </div>

            <div class="paynow-leaselink__calculator__products-summary">
                <p class="paynow-leaselink__calculator__products-summary__label"><?php echo __('Total (net):', 'leaselink-plugin-pl') ?></p>
                <p class="paynow-leaselink__calculator__products-summary__price"><?php echo wc_price($data['widget_products_sum']); ?></p>
            </div>

            <div class="paynow-leaselink__calculator__copy-lg">
                <p><?php echo __('The presented calculations do not constitute an offer within the meaning of the Civil Code.', 'leaselink-plugin-pl') ?></p>
            </div>

            <div class="paynow-leaselink__calculator__config">
                <div class="paynow-leaselink__calculator__config__form">
                    <p class="paynow-leaselink__calculator__config__title"><?php echo __('How many installments do you choose?', 'leaselink-plugin-pl') ?></p>
                    <div class="paynow-leaselink__calculator__config__buttons-wrapper">
                        <?php foreach ($data['rates'] ?? [] as $rate): ?>
                            <div>
                                <input class="paynow-leaselink__calculator__config__input" type="radio" id="rates-<?php echo $rate; ?>" name="rates" value="<?php echo $rate; ?>" <?php echo ($rate === $data['checked_rate'] ? 'checked' : ''); ?>>
                                <label class="paynow-leaselink__calculator__config__label" for="rates-<?php echo $rate; ?>"><?php echo $rate; ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <p class="paynow-leaselink__calculator__config__title"><?php echo __('How much money do you want to deposit initially?', 'leaselink-plugin-pl') ?></p>
                    <div class="paynow-leaselink__calculator__config__buttons-wrapper">
                        <?php foreach ($data['entry_payment_options'] ?? [] as $entry_payment): ?>
                            <div>
                                <input class="paynow-leaselink__calculator__config__input" type="radio" id="entry-payment-<?php echo $entry_payment; ?>" name="entry-payment" value="<?php echo $entry_payment; ?>" <?php echo ($entry_payment === $data['entry_net_payment_percent'] ? 'checked' : ''); ?>>
                                <label class="paynow-leaselink__calculator__config__label" for="entry-payment-<?php echo $entry_payment; ?>"><?php echo $entry_payment; ?>%</label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <p class="paynow-leaselink__calculator__config__title"><?php echo __('What buyout amount do you choose?', 'leaselink-plugin-pl') ?></p>
                    <div class="paynow-leaselink__calculator__config__buttons-wrapper">
                        <?php foreach ($data['closing_payment_options'] ?? [] as $closing_payment): ?>
                            <div>
                                <input class="paynow-leaselink__calculator__config__input" type="radio" id="closing-payment-<?php echo $closing_payment; ?>" name="closing-payment" value="<?php echo $closing_payment; ?>" <?php echo ($closing_payment === $data['closing_net_payment_percent'] ? 'checked' : ''); ?>>
                                <label class="paynow-leaselink__calculator__config__label" for="closing-payment-<?php echo $closing_payment; ?>"><?php echo $closing_payment; ?>%</label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <hr class="paynow-leaselink__calculator__config__hr" />

                <div class="paynow-leaselink__calculator__config__summary">
                    <div class="paynow-leaselink__calculator__config__summary__box --entry-payment">
                        <p class="paynow-leaselink__calculator__config__summary__title"><?php echo __('Initial charge', 'leaselink-plugin-pl') ?>:</p>
                        <p class="paynow-leaselink__calculator__config__summary__value" data-entry-netto-payment><?php echo wc_price($data['entry_net_payment']); ?> (<?php echo ($data['is_netto'] ? __('net', 'leaselink-plugin-pl') : __('gross', 'leaselink-plugin-pl')); ?>)</p>
                    </div>

                    <div class="paynow-leaselink__calculator__config__summary__box --closing-payment">
                        <p class="paynow-leaselink__calculator__config__summary__title"><?php echo __('Buyout', 'leaselink-plugin-pl') ?>:</p>
                        <p class="paynow-leaselink__calculator__config__summary__value" data-closing-netto-payment><?php echo wc_price($data['closing_net_payment']); ?> (<?php echo ($data['is_netto'] ? __('net', 'leaselink-plugin-pl') : __('gross', 'leaselink-plugin-pl')); ?>)</p>
                    </div>

                    <div class="--financial-product">
                        <p class="paynow-leaselink__calculator__config__summary__title"><?php echo __('Financial product:', 'leaselink-plugin-pl') ?></p>
                        <p class="paynow-leaselink__calculator__config__summary__value with-help">
                            <span data-financial-product-name><?php echo $data['financial_product_name']; ?></span>
                            <img class="paynow-leaselink__calculator__config__summary__tooltip" src="<?php echo WC_LEASELINK_PLUGIN_ASSETS_PATH . 'images/tooltip.png'; ?>" alt="tooltip icon" data-leaselink-calculator-help-open>
                        </p>
                    </div>
                </div>
            </div>

            <div class="paynow-leaselink__calculator__help-background paynow-leaselink__calculator__help__hidden" data-leaselink-calculator-help>
                <div class="paynow-leaselink__calculator__help <?php echo $data['help_tooltip_class']; ?>" data-leaselink-calculator-help-tooltip>
                    <div class="paynow-leaselink__calculator__help__exit-wrapper" data-leaselink-calculator-help-close>
                        <img class="paynow-leaselink__calculator__help__exit" src="<?php echo WC_LEASELINK_PLUGIN_ASSETS_PATH . 'images/exit.png'; ?>" alt="exit icon">
                    </div>
                    <div class="paynow-leaselink__calculator__help-header">
                        <p class="paynow-leaselink__calculator__help-leasing"><?php echo __('Leasing for companies', 'leaselink-plugin-pl') ?></p>
                        <hr class="paynow-leaselink__calculator__help-hr">
                        <p class="paynow-leaselink__calculator__help-loan"><?php echo __('Installments for companies', 'leaselink-plugin-pl') ?></p>
                    </div>
                    <div class="paynow-leaselink__calculator__help-row --row-gray">
                        <img class="paynow-leaselink__calculator__help__tick" src="<?php echo WC_LEASELINK_PLUGIN_ASSETS_PATH . 'images/tick.png'; ?>" alt="tick icon">
                        <p class="paynow-leaselink__calculator__help-leasing"><?php echo __('Each installment is entirely tax deductible', 'leaselink-plugin-pl') ?></p>
                        <hr class="paynow-leaselink__calculator__help-hr">
                        <p class="paynow-leaselink__calculator__help-loan"><?php echo __('You will enter the company\'s costs as with cash purchases', 'leaselink-plugin-pl') ?></p>
                    </div>
                    <div class="paynow-leaselink__calculator__help-row">
                        <img class="paynow-leaselink__calculator__help__tick" src="<?php echo WC_LEASELINK_PLUGIN_ASSETS_PATH . 'images/tick.png'; ?>" alt="tick icon">
                        <p class="paynow-leaselink__calculator__help-leasing"><?php echo __('For fixed assets and accessories', 'leaselink-plugin-pl') ?></p>
                        <hr class="paynow-leaselink__calculator__help-hr">
                        <p class="paynow-leaselink__calculator__help-loan"><?php echo __('For the entire range', 'leaselink-plugin-pl') ?></p>
                    </div>
                    <div class="paynow-leaselink__calculator__help-row --row-gray">
                        <img class="paynow-leaselink__calculator__help__tick" src="<?php echo WC_LEASELINK_PLUGIN_ASSETS_PATH . 'images/tick.png'; ?>" alt="tick icon">
                        <p class="paynow-leaselink__calculator__help-leasing"><?php echo __('The period depends on the selected item category', 'leaselink-plugin-pl') ?></p>
                        <hr class="paynow-leaselink__calculator__help-hr">
                        <p class="paynow-leaselink__calculator__help-loan"><?php echo __('Shorter terms, not available on leasing', 'leaselink-plugin-pl') ?></p>
                    </div>
                    <div class="paynow-leaselink__calculator__help-row">
                        <img class="paynow-leaselink__calculator__help__tick" src="<?php echo WC_LEASELINK_PLUGIN_ASSETS_PATH . 'images/tick.png'; ?>" alt="tick icon">
                        <p class="paynow-leaselink__calculator__help-leasing"><?php echo __('You become the owner at the end of the contract (buyout)', 'leaselink-plugin-pl') ?></p>
                        <hr class="paynow-leaselink__calculator__help-hr">
                        <p class="paynow-leaselink__calculator__help-loan"><?php echo __('You own it right away', 'leaselink-plugin-pl') ?></p>
                    </div>
                    <div class="paynow-leaselink__calculator__help-row --row-gray">
                        <img class="paynow-leaselink__calculator__help__tick" src="<?php echo WC_LEASELINK_PLUGIN_ASSETS_PATH . 'images/tick.png'; ?>" alt="tick icon">
                        <p class="paynow-leaselink__calculator__help-leasing"><?php echo __('The buyout amount depends on the selected item category and the financing period', 'leaselink-plugin-pl') ?></p>
                        <hr class="paynow-leaselink__calculator__help-hr">
                        <p class="paynow-leaselink__calculator__help-loan"><?php echo __('No buyout', 'leaselink-plugin-pl') ?></p>
                    </div>
                </div>
            </div>

            <div class="paynow-leaselink__calculator__copy">
                <p><?php echo __('The presented calculations do not constitute<br/>an offer within the meaning of the Civil Code.', 'leaselink-plugin-pl') ?></p>
            </div>

            <div class="paynow-leaselink__calculator__instruction">
                <h4 class="paynow-leaselink__calculator__instruction__title"><?php echo __('How to use?', 'leaselink-plugin-pl') ?></h4>
                <ol class="paynow-leaselink__calculator__instruction__list">
                    <li class="paynow-leaselink__calculator__instruction__list__item"><?php echo __('Add products to cart.', 'leaselink-plugin-pl') ?></li>
                    <li class="paynow-leaselink__calculator__instruction__list__item"><?php echo __('Select LeaseLink as your payment method.', 'leaselink-plugin-pl') ?></li>
                    <li class="paynow-leaselink__calculator__instruction__list__item"><?php echo __('Place your order and you will go to leaselink.pl.', 'leaselink-plugin-pl') ?></li>
                    <li class="paynow-leaselink__calculator__instruction__list__item"><?php echo __('Provide your basic details.', 'leaselink-plugin-pl') ?></li>
                    <li class="paynow-leaselink__calculator__instruction__list__item"><?php echo __('You will get the decision after 2 minutes and sign a simple contract remotely.', 'leaselink-plugin-pl') ?></li>
                </ol>
            </div>

            <div class="paynow-leaselink__calculator__rating">
                <img class="paynow-leaselink__calculator__rating__image" src="<?php echo WC_LEASELINK_PLUGIN_ASSETS_PATH . 'images/widget-ratings.png'; ?>" alt="widget ratings">
                <img class="paynow-leaselink__calculator__rating__image-lg" src="<?php echo WC_LEASELINK_PLUGIN_ASSETS_PATH . 'images/widget-ratings-lg.png'; ?>" alt="widget ratings">
            </div>
        </div>

        <div class="paynow-leaselink__calculator__summary">
            <div>
                <div class="paynow-leaselink__calculator__summary__button-wrapper" data-leaselink-calculator-close>
                    <div class="paynow-leaselink__calculator__summary__arrow-wrapper">
                        <img
                            class="paynow-leaselink__calculator__summary__arrow"
                            src="<?php echo WC_LEASELINK_PLUGIN_ASSETS_PATH . 'images/arrow-purple.png'; ?>"
                            alt="Arrow"
                        >
                    </div>
                    <button type="button" class="paynow-leaselink__calculator__summary__button"><?php echo __('Back to the store', 'leaselink-plugin-pl') ?></button>
                </div>
            </div>
            <div>
                <p class="paynow-leaselink__calculator__summary__label"><?php echo __('Monthly', 'leaselink-plugin-pl') ?> (<span data-monthly-payment-is-netto><?php echo ($data['is_netto'] ? __('net', 'leaselink-plugin-pl') : __('gross', 'leaselink-plugin-pl')); ?></span>):</p>
                <p class="paynow-leaselink__calculator__summary__price" data-monthly-netto-payment><?php echo wc_price($data['widget_net_value']) ?></p>
            </div>
        </div>
    </div>
</div>