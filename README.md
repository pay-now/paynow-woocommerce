[**English version**][ext0]

# Wtyczka Paynow dla WooCommerce

Wtyczka Paynow dodaje szybkie płatności i płatności BLIK do sklepu WooCommerce.

## Spis treści

- [Wymagania](#wymagania)
- [Instalacja](#instalacja)
- [Konfiguracja](#konfiguracja)
- [FAQ](#FAQ)
- [Sandbox](#sandbox)
- [Wsparcie](#wsparcie)
- [Licencja](#licencja)

## Wymgania

- PHP od wersji 7.1
- WooCommerce w wersji 2.2 lub wyższej

## Instalacja

Zobacz również [filmik instruktażowy][ext12].

1. Pobierz plik paynow.zip z [rezpozytorium Github][ext1] i zapisz na dysku swojego komputera
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

1. Przejdź do zakładki `WooCommerce` w panelu administracyjnym
2. Przejdź do `Ustawienia > Płatności`
3. Na liście dostępnych metod płatności znajdź `Paynow` i kliknij `Zarządzaj`

![Konfiguracja krok 3][ext7]

4. Produkcyjne klucze dostępu znajdziesz w zakładce `Mój biznes > Paynow > Ustawienia > Sklepy i punkty płatności > Dane uwierzytelniające` w bankowości internetowej mBanku.

   Klucze dla środowiska testowego znajdziesz w zakładce `Ustawienia > Sklepy i punkty płatności > Dane uwierzytelniające` w [panelu środowiska testowego][ext11].

![Konfiguracja krok 4a][ext8]

![Konfiguracja krok 4b][ext13]

5. W zależności od środowiska, z którym chesz się połaczyć w sekcji `Konfiguracja środowiska produkcyjnego` lub `Konfiguracja środowiska testowego(Sandbox)` podaj `Klucz dostępu do API` i `Klucz obliczania podpisu`.

![Konfiguracja krok 5][ext9]

## FAQ

**Jak skonfigurować adres powrotu?**

Adres powrotu ustawi się automatycznie dla każdego zamówienia. Nie ma potrzeby ręcznej konfiguracji tego adresu.

**Jak skonfigurować adres powiadomień?**

W panelu sprzedawcy Paynow przejdź do zakładki `Ustawienia > Sklepy i punkty płatności`, w polu `Adres powiadomień` ustaw adres:
`https://twoja-domena.pl/?wc-api=WC_Gateway_Paynow`.

![Konfiguracja adresu powiadomień][ext10]

## Sandbox

W celu przetestowania działania bramki Paynow zapraszamy do skorzystania z naszego środowiska testowego. W tym celu zarejestruj się na stronie: [panel.sandbox.paynow.pl][ext2].

## Wsparcie

Jeśli masz jakiekolwiek pytania lub problemy, skontaktuj się z naszym wsparciem technicznym: support@paynow.pl.

Jeśli chciałbyś dowiedzieć się więcej o bramce płatności Paynow odwiedź naszą stronę: https://www.paynow.pl/.

## Licencja

Licencja MIT. Szczegółowe informacje znajdziesz w pliku LICENSE.

[ext0]: README.EN.md
[ext1]: https://github.com/pay-now/paynow-woocommerce/releases/latest
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
