const { decodeEntities } = wp.htmlEntities;
const { getSetting } = wc.wcSettings;
const { registerPaymentMethod } = wc.wcBlocksRegistry;
const { useEffect } = wp.element;

// Data
const settings = getSetting('pay_by_paynow_pl_blik_data', {});
const title = decodeEntities(settings.title || 'BLIK');
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
            const blikInput = document.querySelector('#paynow_blik_code');
            const blikCode = blikInput ? blikInput.value : false;

            if ( blikCode ) {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            'authorizationCode': blikCode,
                        },
                    },
                };
            }

            return {
                type: emitResponse.responseTypes.ERROR,
                message: 'BLIK Code does not exists',
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
 * Paynow BLIK method config.
 */
const PaynowBlikOptions = {
    name: 'pay_by_paynow_pl_blik',
    label: <Label />,
    content: <Content />,
    edit: <Content />,
    canMakePayment: canMakePayment,
    ariaLabel: title
};

registerPaymentMethod(PaynowBlikOptions);
