const { decodeEntities } = wp.htmlEntities;
const { getSetting } = wc.wcSettings;
const { registerPaymentMethod } = wc.wcBlocksRegistry;
const { useEffect } = wp.element;

// Data
const settings = getSetting('pay_by_paynow_pl_paypo_data', {});
const title = decodeEntities(settings.title || 'Card');
const iconUrl = settings.iconurl;
const available = decodeEntities(settings.available || false);
const fields = decodeEntities(settings.fields || '');

const canMakePayment = () => {
    return available;
};

const Content = props => {
    return <div dangerouslySetInnerHTML={{__html: fields}}></div>;
};

const Label = props => {
    const { PaymentMethodLabel } = props.components;
    const icon = <img src={iconUrl} alt={title} name={title} />
    return <PaymentMethodLabel className='paynow-block-label' text={title} icon={icon} />;
};

/**
 * Paynow PayPo method config.
 */
const PaynowPayPoOptions = {
    name: 'pay_by_paynow_pl_paypo',
    label: <Label />,
    content: <Content />,
    edit: <Content />,
    canMakePayment: canMakePayment,
    ariaLabel: title
};

registerPaymentMethod(PaynowPayPoOptions);
