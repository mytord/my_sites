Панель управления проектами MySites
========================

(тестовое задание)

Веб-сервис позволяет управлять вашими веб-проектами. 
Управление можно осуществлять через веб-интерфейс, либо через REST API.

Особенности
--------------
* Бакенд - Symfony2
* База данных - MySQL
* Фронтенд - jTable
* REST API - FOS\RestBundle

Установка
--------------

1. `composer update --verbose`
2. `php app/console doctrine:database:create`
3. `php app/console doctrine:schema:create`
4. Устанавливаем права на запись в директории, в которых будут храниться проекты и виртуальные хосты (по умолчанию: `web/projects` и `web/uploads/vhosts`)
5. Запускаем тесты: `bin/phpunit -c app`
6. Запускаем сервер: `php app/console server:run`
7. Пробуем создать проект: `curl -X POST -d '{"project": {"name": "Test name", "alias": "test_project", "type_id": 1}}' http://localhost:8000/api/projects --header "Content-Type:application/json" -v`

Документация по REST API доступна по ссылке `/api/doc`

Этапы создания нового проекта
--------------
1. Добавление проекта в базу данных.
2. Создание файловой структуры проекта
3. Создание виртуального хоста
4. Обновление конфигурации веб-сервера, создание dns-записей, обновление файла /etc/hosts

`ApiBundle\Service\EventListener` слушает события `postPersist`, `postRemove` и в нужный 
момент подключает `ProjectService` для дальнейшей организации инфраструктуры создаваемого проекта. 

За создание файловой структуры отвечает интерфейс `ApiBundle\Service\Structure\StructureInterface`. В процессе создания нового проекта пользователь может выбрать тип требуемой структуры.
Настройки по умолчанию: все проекты создаются в директории `web/projects`, файловая структура Simple HTML (файл index.html)

MySites предполагает использование nginx в качестве веб-сервера для ваших проектов.
Однако никто не мешает вам реализовать поддержку других серверов. За взаимодействие с сервером отвечает интерфейс `ApiBundle\Service\Server\ServerInterface`.
Настройки по умолчанию: все виртуальные хосты создаются в директории `web/uploads/vhosts`, сервер - `nginx`.

MySites не предполагает наличие прав для работы с веб-сервером, а так же файловой системой операционной системы. Вместо этого предполагается использование CRON-сценария.

Все сервисы конфигурируются через Service Container.