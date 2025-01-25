# Task:

REST API Utwórz REST API przy użyciu frameworka Laravel / Symfony. Celem aplikacji jest umożliwienie przesłania przez
użytkownika informacji odnośnie firmy(nazwa, NIP, adres, miasto, kod pocztowy) oraz jej pracowników(imię, nazwisko,
email, numer telefonu(opcjonalne)) - wszystkie pola są obowiązkowe poza tym które jest oznaczone jako opcjonalne.
Uzupełnij endpointy do pełnego CRUDa dla powyższych dwóch. Zapisz dane w bazie danych. PS. Stosuj znane Ci dobre
praktyki wytwarzania oprogramowania oraz korzystaj z repozytorium kodu.

## Requirements:

- docker compose
- PHP >= 8.3
- composer >= 2.7

## Installation guides:

- Download the project:
 ```bash
 git clone https://github.com/jotone/nfd-task.git
 ```

- Install composer dependencies and generate the project key:
 ```bash
 composer install && php artisan key:genearate
 ```

- Run Sail installation:

 ```bash
 php artisan sail:install
 ```

- During the installation select the database as MySQL or PostgreSQL
- Start containers:

 ```bash
 ./vendor/bin/sail up
 ```

- Run migrations:

 ```bash
 ./vendor/bin/sail artisan migrate
 ```

- Run tests

 ```bash
 ./vendor/bin/sail artisan test
 ```

## Summary

Created two resource controllers: a CompanyController and an EmployeeController. Both are extended from a Controller class that shares such fields and methods:
- *getIndexRequestParams* - a method that extracts and processes pagination and ordering parameters from the request such as **take**, **order_by** and **order_dir**.
- *errorResponse* - a method to generate a standardized error response.
- *DB_ATTEMPTS* - a constant that indicates a number of the database transactions attempts
- *take* - a static integer variable that contains a default paginator items per page (can be rewritten from an inheritor class)
- *order_by* - a static array variable that contains default order query parameters (can be rewritten from an inheritor class as well)

Created the service class NipService that allows to generate and validate NIP number.

Created the RouteServiceProvider that provides an ability to separate api routes in the **routes/api.php** file.

Created models Company and Employee with migrations and resources files.

Created tests: Feature - for Company and Employee models and controllers; Unit - for NipService class testing;

### CompanyController

Available methods:

- index - Listing of companies with optional pagination and sorting.
- show - Retrieve and display a single company by ID or slug.
- store - Create a new company.
- update - Update an existing company.
- attachEmployees - Attach employees to a company.
- detachEmployees - Detach employees from a company.
- destroy - Delete a company.

| Method | URL | System name | Request Type | Request data | Response |
|:-----:|:----:|:-----------:|:------------:|:--------------|:---------|
| index | /api/companies | api.companies.index | GET or HEAD | *Options*: <br> `take` (**number**) - Number of items per page <br> `order_by` (**string**) - Field to sort the results by. <br> `order_dir` (**string**) - Direction of sorting <br> Example: /api/companies?take=50&order_by=name&order_dir=desc | *Response code*: **200 OK** <br> *Response body*: <br> `data` - a list of the companies <br> `links` - a list of the pagination uri <br> `meta` - a list of collection data, like: current page, last page, total items, items per page etc. |
| show | /api/companies/{id} | api.companies.show | GET or HEAD | *Options*: <br> &lowast;`id` - The ID of the existing company | *Response code*: **200 OK** <br> *Response body*: <br> `id` (**integer**) - ID of the company <br> `name` (**string**) - the company name <br> `slug` (**string**) - a URL-friendly identifier generated from the name <br> `tax_id` (**string**) - The tax identification number (NIP) <br> `address` (**string**) - The full address of the company <br> `city` (**string**) - The city where the company is located <br> `zip` (**string**) - The postal or ZIP code of the company's address <br> `created_at` (**string**) - The date and time when the resource was created, formatted as day/Month/Year Hour:Minute <br> `updated_at` (**string**) - The date and time when the resource was last updated, formatted as day/Month/Year Hour:Minute |
| store | /api/companies | api.companies.store | POST | *Request body*: <br> &lowast;`name` (**string**) <br> &lowast;`tax_id` (**string**) <br> &lowast;`address` (**string**) <br> &lowast;`city` (**string**) <br> &lowast;`zip` (**string**) | *Response code*: **201 CREATED** <br> *Response body*: Same as for **show** method |
| update | /api/companies/{id} | api.companies.update | PUT or PATCH | *Options*: <br> &lowast;`id` - The ID of the existing company <br> *Request body*: <br> `name` (**string**) <br> `tax_id` (**string**) <br> `address` (**string**) <br> `city` (**string**) <br> `zip` (**string**) | *Response code*: **200 OK** <br> *Response body*: <br> Same as for **show** method |
| attachEmployees | /api/companies/{id}/attach-employees | api.companies.attach-employees | PATCH | Options: <br> &lowast;`id` - The ID of the existing company <br> *Request body*: <br> &lowast;`list` - (**array**) - The array of employee IDs | *Response code*: **200 OK** <br> *Response body*: <br> `data` - a list of the employees <br> `links` - a list of the pagination uri <br> `meta` - a list of collection data, like: current page, last page, total items, items per page etc. |
| detachEmployees | /api/companies/{id}/detach-employees | api.companies.detach-employees | DELETE | Options: <br> &lowast;`id` - The ID of the existing company <br> *Request body*: <br> &lowast;`list` - (**array**) - The array of employee IDs | *Response code*: **204 NO CONTENT** |
| destroy | /api/companies/{id} | api.companies.destroy | DELETE | Options: <br> &lowast;`id` - The ID of the existing company | *Response code*: **204 NO CONTENT** |

&lowast; - the required fields

### EmployeeController

Available methods:

- index - Listing of employees with optional pagination, sorting and filtering.
- show - Retrieve and display a single employee by ID.
- store - Create a new employee.
- update - Update an existing employee.
- destroy - Delete an employee.

| Method | URL | System name | Request Type | Request data | Response |
|:------:|:---:|:-----------:|:------------:|:-------------|:---------|
| index | /api/employees | api.employees.index | GET or HEAD | *Options*: <br> `take` (**number**) - Number of items per page <br> `order_by` (**string**) - Field to sort the results by. <br> `order_dir` (**string**) - Direction of sorting <br> `companies` (**array**) - The list of companies ID to filter employees <br> Example: /api/employees?take=50&order_by=name&order_dir=desc&companies[]=3&companies[]=4 | *Response code*: **200 OK** <br> *Response body*: <br> `data` - a list of the employees <br> `links` - a list of the pagination uri <br> `meta` - a list of collection data, like: current page, last page, total items, items per page etc. |
| show | /api/employees/{id} | api.employees.show | GET or HEAD | *Options*: <br> &lowast;`id` - The ID of the existing employee | *Response code*: **200 OK** <br> *Response body*: <br> `id` (**integer**) - ID of the employee <br> `first_name` (**string**) - the employee first name <br> `last_name`(**string**) - the employee last name <br> `email`(**string**) - the employee email <br> `phone` (**string**) - the employee email <br> `created_at` (**string**) - The date and time when the resource was created, formatted as day/Month/Year Hour:Minute <br> `updated_at` (**string**) - The date and time when the resource was last updated, formatted as day/Month/Year Hour:Minute |
| store | /api/employees | api.employees.store | POST | *Request body*: <br> &lowast;`first_name` (**string**) <br> &lowast;`last_name` (**string**) <br> &lowast;`email` (**string**) <br> &nbsp;`phone` (**string**) | *Response code*: **201 CREATED** <br> *Response body*: Same as for **show** method |
| update | /api/employees/{id} | api.employees.update | PUT or PATCH | *Options*: <br> &lowast;`id` - The ID of the existing employee <br> *Request body*: <br> &lowast;`first_name` (**string**) <br> &lowast;`last_name` (**string**) <br> &lowast;`email` (**string**) <br> &nbsp;`phone` (**string**) | *Response code*: **200 OK** <br> *Response body*: Same as for **show** method |
| destroy | /api/employees/{id} | api.employees.destroy | DELETE | Options: <br> &lowast;`id` - The ID of the existing employee | *Response code*: **204 NO CONTENT** |

&lowast; - the required fields
