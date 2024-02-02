const { decodeEntities } = wp.htmlEntities;
const { getSetting } = wc.wcSettings;
const { registerPaymentMethod } = wc.wcBlocksRegistry;

// Data
const settings = getSetting('pay_by_paynow_pl_google_pay_data', {});
const title = decodeEntities(settings.title || 'Google Pay');
const description = decodeEntities(settings.description || '');
const iconUrl = settings.iconurl;
const available = decodeEntities(settings.available || false);

const canMakePayment = () => {
    return available;
};

const Content = props => {
    return <div>{description}</div>;
};

const Label = props => {
    const { PaymentMethodLabel } = props.components;
    const icon = <img src={iconUrl} alt={title} name={title} />
    return <PaymentMethodLabel className='paynow-block-label' text={title} icon={icon} />;
};

/**
 * Paynow Google Pay method config.
 */
const PaynowGooglePayOptions = {
    name: 'pay_by_paynow_pl_google_pay',
    label: <Label />,
    content: <Content />,
    edit: <Content />,
    canMakePayment: canMakePayment,
    ariaLabel: title
};

registerPaymentMethod(PaynowGooglePayOptions);
