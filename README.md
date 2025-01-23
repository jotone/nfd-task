# Task:
REST API Utwórz REST API przy użyciu frameworka Laravel / Symfony. Celem aplikacji jest umożliwienie przesłania przez użytkownika informacji odnośnie firmy(nazwa, NIP, adres, miasto, kod pocztowy) oraz jej pracowników(imię, nazwisko, email, numer telefonu(opcjonalne)) - wszystkie pola są obowiązkowe poza tym które jest oznaczone jako opcjonalne. Uzupełnij endpointy do pełnego CRUDa dla powyższych dwóch. Zapisz dane w bazie danych. PS. Stosuj znane Ci dobre praktyki wytwarzania oprogramowania oraz korzystaj z repozytorium kodu.

## Requirements:
 - docker compose
 - PHP >= 8.3
 - composer >= 2.7

## Installation guides:
 - Run Sail installation:
  ```bash
  php artisan sail:install
  ```
 - Start containers: 
  ```bash
  ./vendor/bin/sail up
  ```
 - Run migrations:
  ```bash
  ./vendor/bin/sail artisan migrate
  ```
