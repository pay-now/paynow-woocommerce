[**English version**][ext0]
# Wtyczka Paynow dla WooCommerce

Wtyczka Paynow dodaje szybkie płatności i płatności BLIK do sklepu WooCommerce.

Wtyczka wspiera WooCommerce w wersji 2.2 lub wyższej.

## Instalacja
1. Pobierz  wtyczkę z [rezpozytorium Github][ext1] i zapisz na dysku swojego komputera jako plik zip
2. Rozpakuj pobrane archiwum
3. Utwórz archiwum zip z folderu /woocommerce-gateway-paynow
4. Przejdź do panelu administracyjnego Wordpress
5. Przejdź do zakładki `Wtyczki`
6. Wybierz opcję `Dodaj nową` i wskaż archiwum zawierające wtyczkę (utworzone w kroku 3)
7. Następnie aktywuj ją za pomocą opcji `Włącz wtyczkę`

## Konfiguracja
1. Przejdź do zakładki `WooCommerce` w panelu administracyjnym
2. Przejdź do `Ustawienia > Płatności`
3. Na liście dostępnych metod płatności znajdź `Paynow` i kliknij `Konfiguruj`
4. Klucze dostępu znajdziesz w zakładce `Ustawienia > Sklepy i punkty płatności > Dane uwierzytelniające` w panelu sprzedawcy Paynow
5. Wpisz `Klucz API` i `Klucz podpisu`

## FAQ
**Jak skonfigurować adres powrotu?**

Adres powrotu ustawi się automatycznie dla każdego zamówienia. Nie ma potrzeby ręcznej konfiguracji tego adresu.
**Jak skonfigurować adres powiadomień?**

W panelu sprzedawcy Paynow  przejdź do zakładki `Ustawienia > Sklepy i punkty płatności`, w polu `Adres powiadomień` ustaw adres:
`https://twoja-domena.pl/?wc-api=WC_Gateway_Paynow`.

## Sandbox
W celu przetestowania działania bramki Paynow zapraszamy do skorzystania z naszego środowiska testowego. W tym celu zarejestruj się na stronie: [panel.sandbox.paynow.pl][ext2]. 

## Wsparcie
Jeśli masz jakiekolwiek pytania lub problemy, skontaktuj się z naszym wsparciem technicznym: support@paynow.pl.

## Więcej informacji
Jeśli chciałbyś dowiedzieć się więcej o bramce płatności Paynow odwiedź naszą stronę: https://www.paynow.pl/

## Licencja
Licencja MIT. Szczegółowe informacje znajdziesz w pliku LICENSE.

[ext0]: README.EN.md
[ext1]: https://github.com/pay-now/paynow-woocommerce/releases/latest
[ext2]: https://panel.sandbox.paynow.pl/