import {expect, Page} from "@playwright/test";

const PAYMENT_METHOD_PATH = {
    'BLIK': '.payment_method_pay_by_paynow_pl_blik label[for="payment_method_pay_by_paynow_pl_blik"]',
    'PBL': '.payment_method_pay_by_paynow_pl_pbl label[for="payment_method_pay_by_paynow_pl_pbl"]',
    'CARD': '.payment_method_pay_by_paynow_pl_card label[for="payment_method_pay_by_paynow_pl_card"]',
    'GOOGLE_PAY': '.payment_method_pay_by_paynow_pl_google_pay label[for="payment_method_pay_by_paynow_pl_google_pay"]'
}

const PAYMENT_METHOD_TITLE = {
    'BLIK': "Płatność BLIKIEM",
    'PBL': "Płatność szybkim przelewem",
    'CARD': "Płatność kartą"
}

const LOCATOR_PATH = {
    'PLACE_ORDER_BUTTON': 'button[id="place_order"]',
    'AUTHORIZE_PAYMENT': '.button.accept',
    'REJECT_PAYMENT_BUTTON': '.button.reject',
    'BACK_TO_SHOP': '.statusbar__container button',
    'THANK_YOU_TEXT': 'text=Dziękujemy. Otrzymaliśmy Twoje zamówienie.',
    'THANK_YOU_TEXT_FAILED': 'text=Niestety Twoje zamówienie nie może być zrealizowane, ponieważ bank/operator płatności odrzucił Twoją transakcję.',
    'PAYMENT_METHOD_MTRANSFER': 'img[alt="Zapłać\\ przez\\ mTransfer"]'
}

async function goToCheckout(shopBaseUrl: String, page: Page) {
    await page.goto(shopBaseUrl + '/sklep/')

    // Click text=Bluza z kapturem
    await page.locator('text=Bluza z kapturem').click();
    await expect(page).toHaveURL(shopBaseUrl + '/produkt/hoodie-with-logo/');

    await page.locator('button:has-text("Dodaj do koszyka")').click();

    await page.goto(shopBaseUrl + '/zamowienie/')
}

async function assertBlikIsAvailable(page: Page) {
    await assertPaymentMethod(page, PAYMENT_METHOD_PATH.BLIK, PAYMENT_METHOD_TITLE.BLIK)
    await expect(page.locator('.payment_box.payment_method_pay_by_paynow_pl_blik')).toBeVisible();
}

async function assertCardIsAvailable(page: Page) {
    await assertPaymentMethod(page, PAYMENT_METHOD_PATH.CARD, PAYMENT_METHOD_TITLE.CARD)
    await expect(page.locator('.payment_box.payment_method_pay_by_paynow_pl_blik')).toBeVisible();
}

async function assertPblIsAvailable(page: Page) {
    await assertPaymentMethod(page, PAYMENT_METHOD_PATH.PBL, PAYMENT_METHOD_TITLE.PBL)
    await assertPaymentMethod(page, PAYMENT_METHOD_PATH.PBL, PAYMENT_METHOD_TITLE.PBL)
    await page.locator(PAYMENT_METHOD_PATH.PBL).click();
    await expect(page.locator('.paynow-payment-option-pbls .paynow-payment-option-pbl')).toHaveCount(20)
}

async function assertPaymentMethod(page: Page, path: string, title: string) {
    await expect(page.locator(path)).toHaveText(title);
}

async function fillOrderData(page: Page) {
    // Fill input[name="billing_first_name"]
    await page.locator('input[name="billing_first_name"]').fill('Jan');

    // Fill input[name="billing_last_name"]
    await page.locator('input[name="billing_last_name"]').fill('Testowy');

    // Fill input[name="billing_address_1"]
    await page.locator('input[name="billing_address_1"]').fill('Towarowa 1');

    // Fill input[name="billing_postcode"]
    await page.locator('input[name="billing_postcode"]').fill('60-100');

    // Fill input[name="billing_city"]
    await page.locator('input[name="billing_city"]').fill('Poznań');

    // Fill input[name="billing_phone"]
    await page.locator('input[name="billing_phone"]').fill('500100200');

    // Fill input[name="billing_email"]
    await page.locator('input[name="billing_email"]').fill('jan@testowy.pl');
}

async function authorizePayment(page: Page) {
    await page.locator(LOCATOR_PATH.AUTHORIZE_PAYMENT).click();
    await expect(page).toHaveURL(/.*authorize*./);
}

async function rejectPayment(page: Page) {
    await page.locator(LOCATOR_PATH.REJECT_PAYMENT_BUTTON).click();
    await expect(page).toHaveURL(/.*cancel*./);
}

async function goBackToShop(page: Page) {
    await page.locator('text=Powrót do sklepu').click();
    await expect(page).toHaveURL(/.*callback*./);

    await page.locator('#backToShop').click();
    await expect(page).toHaveURL(/.*order-received*./);
}

export {
    PAYMENT_METHOD_PATH,
    PAYMENT_METHOD_TITLE,
    LOCATOR_PATH,
    assertBlikIsAvailable,
    assertCardIsAvailable,
    assertPaymentMethod,
    assertPblIsAvailable,
    authorizePayment,
    goToCheckout,
    goBackToShop,
    fillOrderData,
    rejectPayment
}