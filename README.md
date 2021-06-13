## Подготовка к запуску
- Версия PHP: 7.3.17
- В php.ini должны быть расскомментированы строчки "extension  = fileinfo" и "extension  = pdo_mysql"
- База данных: 5.5.67-MariaDB
- Композер: 2.0.12
## Запуск веб сервиса
- Клонируем проект и переходим в папку
- Выполняем "composer i" (Устанавливаем пакеты)
- Копируем и переименовываем файл .env.example в .env
- В .env указываем доступы к бд (DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
- Прогоняем миграции, выполнив комманду "php artisan migrate"
- Выполняем "php artisan key:generate"
- Запускаем приложение коммандой "php artisan serve --host 0.0.0.0", приложение запустится на 8000 порту
## Использование веб сервиса
- Делаем POST запрос "/api/flights". В теле запроса отправляем форм дату с ключем flight_csv в котором должен находится csv
- Делаем GET запрос "/api/flights/1"