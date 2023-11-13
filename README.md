[**English version**][ext0]

# Wtyczka Paynow & Leaselink dla WooCommerce

Wtyczka Paynow & Leaselink dodaje szybkie płatności, płatności BLIK, płatność za pomocą Leaselink oraz obsługę widgetu Leaselink do sklepu WooCommerce.

## Spis treści

- [Wymagania](#wymagania)
- [Instalacja](#instalacja)
- [Konfiguracja](#konfiguracja)
- [Konfiguracja Leaselink](#konfiguracja-leaselink)
- [Wspierane funkcjonalności](#funkcje)
- [FAQ](#faq)
- [Sandbox](#sandbox)
- [Wsparcie](#wsparcie)
- [Licencja](#licencja)

## Wymagania

- PHP od wersji 7.1
- WooCommerce w wersji 2.2 lub wyższej

## Instalacja

Zobacz również [filmik instruktażowy][ext12].

1. Pobierz plik [pay-by-paynow-pl.zip][ext1] i zapisz na dysku swojego komputera
2. Przejdź do panelu administracyjnego Wordpress
3. Przejdź do zakładki `Wtyczki`

![Instalacja krok 3][ext3]

4. Wybierz opcję `Dodaj nową`

![Instalacja krok 4][ext4]

5. Wybierz opcję `Wyślij wtyczkę na serwer` i wskaż archiwum zawierające wtyczkę (pobrane w kroku 1.)

![Instalacja krok 5][ext5]

6. Następnie aktywuj ją za pomocą opcji `Włącz wtyczkę`

![Instalacja krok 6][ext6]

## Konfiguracja

1. Przejdź do zakładki `WooCommerce > Ustawienia Paynow` w panelu administracyjnym
2. Produkcyjne klucze dostępu znajdziesz w zakładce `Mój biznes > Paynow > Ustawienia > Sklepy i punkty płatności > Dane uwierzytelniające` w bankowości internetowej mBanku.

Klucze dla środowiska testowego znajdziesz w zakładce `Ustawienia > Sklepy i punkty płatności > Dane uwierzytelniające` w [panelu środowiska testowego][ext11].

![Konfiguracja krok 2a][ext8]

![Konfiguracja krok 2b][ext13]

3. W zależności od środowiska, z którym chesz się połaczyć w sekcji `Konfiguracja środowiska produkcyjnego` lub `Konfiguracja środowiska testowego(Sandbox)` podaj `Klucz dostępu do API` i `Klucz obliczania podpisu`.

![Konfiguracja krok 3][ext9]

4. Przejdź do `Woocommerce > Ustawienia > Płatności`
5. Na liście dostępnych metod płatności znajdź `Paynow` i kliknij `Zarządzaj`

![Konfiguracja krok 3][ext7]

6. Na widoku listy płatności bądź pojedynczej płatności możesz włączyć lub wyłączyć konkretną metodę

## Konfiguracja Leaselink

1. Przejdź do zakładki `WooCommerce > Ustawienia Paynow` w panelu administracyjnym
2. W zależności od środowiska, z którym chesz się połaczyć w sekcji `Konfiguracja widgetu LeaseLink` podaj `Klucz dostępu do API`. Jeśli nie posiadasz klucza napisz na [integracje@leaselink.pl](mailto:integracje@leaselink.pl), a odezwiemy się w ciągu 24h

![Konfiguracja Leaselink krok 2][ext14]

3. Aby płatności Leaselink działały poprawnie, wyślij adres powiadomień, widoczny w ustawieniach Paynow, swojemu opiekunowi Leaselink

![Konfiguracja Leaselink krok 3][ext15]

4. Pozostałe ustawienia uzupełnij wedle woli
5. Aby osadzić widget w dowolnym miejscu strony należy użyć funkcji `wc_pay_by_paynow_leaselink_render_widget` w taki sposób:

```php
// wtyczka spróbuje pobrać id produktu z globalnego obiektu post
wc_pay_by_paynow_leaselink_render_widget();

// lub
// wtyczka rozbije ciąg znaków za pomocą przecinka i wyświetli widget dla produktów o podanych numerach id
wc_pay_by_paynow_leaselink_render_widget('12,15,18');

// lub
// wtyczka wyświetli widget dla produktów o podanych numerach id
wc_pay_by_paynow_leaselink_render_widget([12, 15, 18]);
```

## Funkcje
1. Dodaje metody płatności
- Płatność BLIKIEM (w modelu White Label)
- Płatność szybkim przelewem
- Płatność kartą płątniczą
- Płatność Google Pay
- Płatność Leaselink
2. Umożliwia zwroty częściowe lub całościowe
3. Umożliwia ponowienie płatności
4. Dodaje widget kalkulacyjny Leaselink

## FAQ

**Jak skonfigurować adres powrotu?**

Adres powrotu ustawi się automatycznie dla każdego zamówienia. Nie ma potrzeby ręcznej konfiguracji tego adresu.

**Jak skonfigurować adres powiadomień?**

W panelu sprzedawcy Paynow przejdź do zakładki `Ustawienia > Sklepy i punkty płatności`, w polu `Adres powiadomień` ustaw adres:
`https://twoja-domena.pl/?wc-api=WC_Gateway_Pay_By_Paynow_PL`.

![Konfiguracja adresu powiadomień][ext10]

## Sandbox

W celu przetestowania działania bramki Paynow zapraszamy do skorzystania z naszego środowiska testowego. W tym celu zarejestruj się na stronie: [panel.sandbox.paynow.pl][ext2].

## Wsparcie

Jeśli masz jakiekolwiek pytania lub problemy, skontaktuj się z naszym wsparciem technicznym: support@paynow.pl.

Jeśli chciałbyś dowiedzieć się więcej o bramce płatności Paynow odwiedź naszą stronę: https://www.paynow.pl/.

## Licencja

Licencja GPL. Szczegółowe informacje znajdziesz w pliku LICENSE.

[ext0]: README.EN.md
[ext1]: https://github.com/pay-now/paynow-woocommerce/releases/latest/download/pay-by-paynow-pl.zip
[ext2]: https://panel.sandbox.paynow.pl/auth/register
[ext3]: instruction/step1.png
[ext4]: instruction/step2.png
[ext5]: instruction/step3.png
[ext6]: instruction/step4.png
[ext7]: instruction/step5.png
[ext8]: instruction/step6a.png
[ext9]: instruction/step7.png
[ext10]: instruction/step8.png
[ext11]: https://panel.sandbox.paynow.pl/merchant/payments
[ext12]: https://paynow.wistia.com/medias/g62mlym13x
[ext13]: instruction/step6b.png
[ext14]: instruction/step_ll_1.png
[ext15]: instruction/step_ll_3.png
