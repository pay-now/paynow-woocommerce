const { decodeEntities } = wp.htmlEntities;
const { getSetting } = wc.wcSettings;
const { registerPaymentMethod } = wc.wcBlocksRegistry;
const { useEffect } = wp.element;

// Data
const settings = getSetting('pay_by_paynow_pl_digital_wallets_data', {});
const title = decodeEntities(settings.title || 'Digital wallets');
const description = decodeEntities(settings.description || '');
const iconUrl = settings.iconurl;
const available = decodeEntities(settings.available || false);
const fields = decodeEntities(settings.fields || '');

const canMakePayment = () => {
    return available;
};

const Content = props => {
    const { eventRegistration, emitResponse } = props;
    const { onPaymentProcessing } = eventRegistration;
    useEffect( () => {
        const unsubscribe = onPaymentProcessing( async () => {
            const paymentMethodIdInput = document.querySelector('#paynow_block_digital-wallets input[name="paymentMethodId"]:checked');
            const paymentMethodId = paymentMethodIdInput ? paymentMethodIdInput.value : false;

            if ( paymentMethodId ) {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            'paymentMethodId': paymentMethodId,
                        },
                    },
                };
            }

            return {
                type: emitResponse.responseTypes.ERROR,
                message: 'Payment method ID does not exists',
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
    return <div id="paynow_block_digital-wallets" dangerouslySetInnerHTML={{__html: fields}}></div>;
};

const Label = props => {
    const { PaymentMethodLabel } = props.components;
    const icon = <img src={iconUrl} alt={title} name={title} />
    return <PaymentMethodLabel className='paynow-block-label' text={title} icon={icon} />;
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
