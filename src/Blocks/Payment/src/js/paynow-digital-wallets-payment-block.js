const { decodeEntities } = wp.htmlEntities;
const { getSetting } = wc.wcSettings;
const { registerPaymentMethod } = wc.wcBlocksRegistry;

// Data
const settings = getSetting('pay_by_paynow_pl_digital_wallets_data', {});
const title = decodeEntities(settings.title || 'Digital wallets');
const description = decodeEntities(settings.description || '');
const available = decodeEntities(settings.available || false);

const canMakePayment = () => {
    return available;
};

const Content = props => {
    return <div>{description}</div>;
};

const Label = props => {
    const { PaymentMethodLabel } = props.components;
    return <PaymentMethodLabel className='paynow-block-label' text={title} />;
};

/**
 * Paynow Digital wallets method config.
 */
const PaynowDigitalWalletsOptions = {
    name: 'pay_by_paynow_pl_digital_wallets',
    label: <Label />,
    content: <Content />,
    edit: <Content />,
    canMakePayment: canMakePayment,
    ariaLabel: title
};

registerPaymentMethod(PaynowDigitalWalletsOptions);
