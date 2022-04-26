import {test, expect, Page} from '@playwright/test';
import {assertBlikIsAvailable, assertCardIsAvailable, assertPblIsAvailable, goToCheckout} from "./common/helpers";

test.beforeEach(async ({page}) => {
    await page.goto(process.env.SHOP_URL);
});

test.describe('Available payments methods', () => {
    test('Should present available payment methods', async ({page}) => {
        await goToCheckout(process.env.SHOP_URL, page)

        // Expect 4 payment methods from paynow
        await expect(page.locator('.wc_payment_method')).toHaveCount(4)

        // Assert BLIK visible
        await assertBlikIsAvailable(page)

        // Assert PBLs visible
        await assertPblIsAvailable(page)

        // Assert PBLs visible
        await assertCardIsAvailable(page)
    });
});