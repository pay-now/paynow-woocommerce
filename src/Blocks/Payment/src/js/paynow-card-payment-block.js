const { decodeEntities } = wp.htmlEntities;
const { getSetting } = wc.wcSettings;
const { registerPaymentMethod } = wc.wcBlocksRegistry;
const { useEffect } = wp.element;

// Data
const settings = getSetting('pay_by_paynow_pl_card_data', {});
const title = decodeEntities(settings.title || 'Card');
const iconUrl = settings.iconurl;
const available = decodeEntities(settings.available || false);
const fields = decodeEntities(settings.fields || '');
let paymentMethodFingerprint = null;

try {
    const fpPromise = import('https://static.paynow.pl/scripts/PyG5QjFDUI.min.js')
        .then(FingerprintJS => FingerprintJS.load())

    fpPromise
        .then(fp => fp.get())
        .then(result => {
            paymentMethodFingerprint = result.visitorId;
        })
} catch (e) {
    console.error('Cannot get fingerprint');
}

const canMakePayment = () => {
    return available;
};

const Content = props => {
    const { eventRegistration, emitResponse } = props;
    const { onPaymentProcessing } = eventRegistration;
    useEffect( () => {
        const unsubscribe = onPaymentProcessing( async () => {
            const paymentMethodTokenInput = document.querySelector('input[name="paymentMethodToken"]:checked');
            const paymentMethodTokenInputValue = paymentMethodTokenInput ? paymentMethodTokenInput.value : null;
            const data = {};

            if (paymentMethodTokenInputValue) {
                data.paymentMethodToken = paymentMethodTokenInputValue;
            }

            data.paymentMethodFingerprint = paymentMethodFingerprint

            return {
                type: emitResponse.responseTypes.SUCCESS,
                meta: {
                    paymentMethodData: data,
                },
            };
        } );
        return () => {
            unsubscribe();
        };
    }, [
        emitResponse.responseTypes.ERROR,
        emitResponse.responseTypes.SUCCESS,
        onPaymentProcessing,
    ] );
    return <div dangerouslySetInnerHTML={{__html: fields}}></div>;
};

const Label = props => {
    const { PaymentMethodLabel } = props.components;
    const icon = <img src={iconUrl} alt={title} name={title} />
    return <PaymentMethodLabel className='paynow-block-label' text={title} icon={icon} />;
};

/**
 * Paynow Card method config.
 */
const PaynowCardOptions = {
    name: 'pay_by_paynow_pl_card',
    label: <Label />,
    content: <Content />,
    edit: <Content />,
    canMakePayment: canMakePayment,
    ariaLabel: title
};

registerPaymentMethod(PaynowCardOptions);
