const { decodeEntities } = wp.htmlEntities;
const { getSetting } = wc.wcSettings;
const { registerPaymentMethod } = wc.wcBlocksRegistry;
const { useEffect } = wp.element;

// Data
const settings = getSetting('leaselink_pay_by_paynow_pl_pbl_data', {});
const title = decodeEntities(settings.title || 'Pbl');
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
            const paymentMethodIdInput = document.querySelector('input[name="paymentMethodId"]:checked');
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
    return <div dangerouslySetInnerHTML={{__html: fields}}></div>;
};

const Label = props => {
    const { PaymentMethodLabel } = props.components;
    const icon = <img src={iconUrl} alt={title} name={title} />
    return <PaymentMethodLabel className='paynow-block-label' text={title} icon={icon} />;
};

/**
 * Paynow Pbl method config.
 */
const PaynowPblOptions = {
    name: 'leaselink_pay_by_paynow_pl_pbl',
    label: <Label />,
    content: <Content />,
    edit: <Content />,
    canMakePayment: canMakePayment,
    ariaLabel: title
};

registerPaymentMethod(PaynowPblOptions);
