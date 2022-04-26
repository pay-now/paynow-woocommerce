import {test, expect, Page} from '@playwright/test';
import {
    authorizePayment,
    fillOrderData,
    goBackToShop,
    goToCheckout,
    LOCATOR_PATH,
    PAYMENT_METHOD_PATH,
    PAYMENT_METHOD_TITLE
} from "./common/helpers";

test.beforeEach(async ({page}) => {
    await page.goto(process.env.SHOP_URL);
});

test.describe('Success payments', () => {

    test('Should process payment successfully with BLIK', async ({page}) => {
        await goToCheckout(process.env.SHOP_URL, page)

        await fillOrderData(page)

        // Click BLIK payment method
        await page.locator(PAYMENT_METHOD_PATH.BLIK).click()

        // Fill BLIK with correct value
        await page.locator('input[name="authorizationCode"]').fill("111111")

        // Click Place order
        await page.locator(LOCATOR_PATH.PLACE_ORDER_BUTTON).click()

        // Assert that is redirected
        await expect(page).toHaveURL(/.*order-received.*/)

        // Assert BLIK confirmation text
        await expect(page.locator('.paynow-confirm-blik h2')).toHaveText("Potwierdź płatność BLIKIEM w aplikacji bankowej")
    });

    test('Should process payment with PBL', async ({page}) => {
        await goToCheckout(process.env.SHOP_URL, page)

        await fillOrderData(page)

        // Click PBL payment method
        await page.locator(PAYMENT_METHOD_PATH.PBL).click()

        // Click mTransfer
        await page.locator(LOCATOR_PATH.PAYMENT_METHOD_MTRANSFER).click();

        // Click text=Kupuję i płacę
        await Promise.all([
            page.waitForNavigation(),
            page.locator(LOCATOR_PATH.PLACE_ORDER_BUTTON).click()
        ]);

        await Promise.all([
            page.waitForNavigation(/*{ url: 'transfer' }*/),
            page.locator(LOCATOR_PATH.BACK_TO_SHOP).click()
        ]);

        await authorizePayment(page)

        await goBackToShop(page)

        await page.locator(LOCATOR_PATH.THANK_YOU_TEXT).waitFor();
    });

    test('Should process payment with CARD', async ({page}) => {
        await goToCheckout(process.env.SHOP_URL, page)
        await fillOrderData(page)

        // Click PBL payment method
        await page.locator(PAYMENT_METHOD_PATH.CARD).click()

        // Click text=Kupuję i płacę
        await Promise.all([
            page.waitForNavigation(/*{ url: 'paywall-page' }*/),
            page.locator(LOCATOR_PATH.PLACE_ORDER_BUTTON).click()
        ]);

        await page.locator('.card-component__container').waitFor();
        const frame = page.frameLocator('.card-component__bm-widget')

        // Fill [placeholder="Imię"]
        await frame.locator('input[name="first_name"]').fill('Jan');

        // Fill [placeholder="Nazwisko"]
        await frame.locator('input[name="last_name"]').fill('Nowak');

        // Fill input[name="card_number"]
        const card_number = frame.locator('input[name="card_number"]');
        await card_number.focus()
        await card_number.fill("4444 3333 2222 1111");

        // Fill input[name="expiration_date"]
        const expiration_date = frame.locator('input[name="expiration_date"]');
        await expiration_date.focus()
        await expiration_date.fill("12/25")

        // Fill input[name="code"]
        const authorization_code = frame.locator('input[name="code"]');
        await authorization_code.focus()
        await authorization_code.fill("111");

        // Click Pay button
        await Promise.all([
            page.waitForNavigation(),
            page.locator(LOCATOR_PATH.BACK_TO_SHOP).click()
        ]);

        await page.locator('.button.accept').click();
        await page.locator('#backToShop').click();
        await expect(page).toHaveURL(/.*order-received*./);

        await page.locator(LOCATOR_PATH.THANK_YOU_TEXT).waitFor();
    });
});

test('Should process payment with GOOGLE_PAY', async ({page}) => {
    await goToCheckout(process.env.SHOP_URL, page)
    await fillOrderData(page)

    // Click PBL payment method
    await page.locator(PAYMENT_METHOD_PATH.GOOGLE_PAY).click()

    // Click text=Kupuję i płacę
    await Promise.all([
        page.waitForNavigation(/*{ url: 'paywall-page' }*/),
        page.locator(LOCATOR_PATH.PLACE_ORDER_BUTTON).click()
    ]);

    await page.evaluate(() => {
        let GooglePayClient = (function() {
            const gpayPaymentMethodId = '2003';
            const gpayPaymentMethodProviderId = '127';

            function GooglePayClient() {
                console.log('Initializing GpayClient mock for e2e testing ...');
            }

            function getHost(environment, subdomain) {
                switch (environment) {
                    case 'sandbox':
                    case 'stage': {
                        return `https://${subdomain}.${environment}.paynow.pl`;
                    }
                    case 'devops':
                    case 'lab': {
                        return `https://${subdomain}.${environment}.integrator.emfale.com`;
                    }
                    default: {
                        return `https://${subdomain}-${environment}.devel.integrator.emfale.com`;
                    }
                }
            }

            function uuidv4() {
                return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c =>
                    (c ^ (crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (c / 4)))).toString(16)
                );
            }

            function getToken() {
                var parts = location.href.split('=');
                return parts[parts.length - 1];
            }

            function updatePayment(url, accessToken) {
                return new Promise((resolve, reject) => {
                    var xhr = new XMLHttpRequest();
                    xhr.open('PATCH', url);

                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.setRequestHeader('Authorization', accessToken);

                    xhr.onload = () => {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            resolve(xhr.response);
                        } else {
                            reject(xhr.statusText);
                        }
                    };
                    xhr.onerror = () => reject(xhr.statusText);
                    var data = `{
            "paymentMethodId": "${gpayPaymentMethodId}",
            "authorizationCode": "",
            "paymentMethodToken": "${uuidv4()}",
            "pmpt": "${gpayPaymentMethodProviderId}",
            "locale": "PL" }`;

                    xhr.send(data);
                });
            }

            GooglePayClient.prototype.isReadyToPay = function(request) {
                return Promise.resolve({
                    result: true,
                    paymentMethodPresent: true,
                });
            };

            GooglePayClient.prototype.loadPaymentData = function(request) {
                return Promise.resolve({
                    apiVersion: request.apiVersion,
                    apiVersionMinor: request.apiVersionMinor,
                    paymentMethodData: {
                        type: 'GPAY',
                        tokenizationData: {
                            type: 'PAYMENT_GATEWAY',
                            token: 'token',
                        },
                    },
                });
            };

            GooglePayClient.prototype.createButton = function(request) {
                var btn = document.createElement('button');
                btn.className = 'mocked-gpay-btn';
                btn.style = 'background-color: black; color:white;padding:10px';
                btn.innerHTML = 'Zapłać z GooglePay';
                return btn;
            };

            GooglePayClient.prototype.prefetchPaymentData = function(request) {
                var btn = document.getElementsByClassName('mocked-gpay-btn')[0];
                // @ts-ignore
                var apihost = getHost(window.environment, 'api');
                // @ts-ignore
                var simulatorhost = getHost(window.environment, 'simulator-gateway');
                // @ts-ignore
                btn.onclick = () => {
                    var paymentUpdateUrl = `${apihost}/paywalldata/payments/${request.transactionInfo.transactionId}`;
                    updatePayment(paymentUpdateUrl, getToken()).then(
                        success => {
                            var paymentAuthorizeUrl = `${simulatorhost}/api/simulator/bm/paynow/continue/${request.transactionInfo.transactionId}`;
                            console.log('Payment update succeed', success);
                            location.href = paymentAuthorizeUrl;
                        },
                        error => console.log('Payment patch failed', error)
                    );
                };
            };

            return GooglePayClient;
        })();
        // @ts-ignore
        window.environment = 'sandbox'
        // @ts-ignore
        window.google = {
            payments: {
                api: {
                    PaymentsClient: GooglePayClient,
                },
            },
        };
    });

    page.locator('.gpay-button button').first().click()

    // Click Authorize payment
    await Promise.all([
        page.waitForNavigation(/*{ url: 'authorize-page' }*/),
        page.locator(LOCATOR_PATH.AUTHORIZE_PAYMENT).click()
    ]);

    // Click Back to shop
    await expect(page).toHaveURL(/.*callback*./);

    // Click Back to shop on paywall
    await page.locator('#backToShop').click();
    await expect(page).toHaveURL(/.*order-received*./);

    await page.locator(LOCATOR_PATH.THANK_YOU_TEXT).waitFor();
});