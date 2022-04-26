import {test, expect, Page} from '@playwright/test';
import {
    assertPblIsAvailable,
    authorizePayment,
    fillOrderData,
    goBackToShop,
    goToCheckout,
    LOCATOR_PATH,
    PAYMENT_METHOD_PATH, rejectPayment
} from "./common/helpers";

test.beforeEach(async ({page}) => {
    await page.goto(process.env.SHOP_URL);
});

test.describe('Failed payments', () => {
    test('Should not process payment successfully with PBL', async ({page}) => {
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

        await rejectPayment(page)

        await goBackToShop(page)

        await page.locator(LOCATOR_PATH.THANK_YOU_TEXT_FAILED).waitFor();
    });

    test('Should not process payment successfully with PBL and retry payment', async ({page}) => {
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

        await rejectPayment(page)

        await goBackToShop(page)

        await page.locator(LOCATOR_PATH.THANK_YOU_TEXT_FAILED).waitFor();

        await Promise.all([
            page.waitForNavigation(/*{ url: 'transfer' }*/),
            await page.locator('.woocommerce-thankyou-order-failed-actions a').click()
        ]);

        await assertPblIsAvailable(page)

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
});