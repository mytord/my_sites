Task
---------

Create web application that:

1) Provides features to manage your projects and sites (CRUD operations)

2) Site should be displayed by unique path like `http://mysites.prj/~test_project` or `http://test_project.mysites.prj`

Keywords: Symfony2, MySQL, jTable

Solution
----------

![Screenshot](https://cloud.githubusercontent.com/assets/7060998/10024262/0bb5284c-615f-11e5-93a0-de44ff5aa4f1.png "Screenshot")

Time required: `16 hours`

Used libraries:

1) `FOSRestBundle` for projects REST API features.

2) `NelmioApiDocBundle` for REST API documentation. See at: `/api/doc`

3) `vfsStream` for Mocking filesystem in Unit tests.

How it works
--------------

1) Add project to database

2) Building project file structure by `StructureInterface` (Simple HTML by default)

3) Building virtual host file by `ServerInterface` (By default application creates virtual hosts for Nginx)

4) Reload server configuration, update DNS-records or `/etc/hosts`. (Application assumes that PHP user have no permissions for this operations. It means that this operations delegates to CRON)

Installation
--------------

Install composer dependencies:

`composer install`

Database configuration:

`app/console doctrine:database:create`

`app/console doctrine:schema:update --force`

Setup writing permissions to project and virtual hosts folders (`web/projects` and `web/uploads/vhosts` by default).

Run unit tests

`bin/phpunit -c app`

Run server

`app/console server:run`

Try to create new project:

`curl -X POST -d '{"project": {"name": "Test name", "alias": "test_project", "type_id": 1}}' http://localhost:8000/api/projects --header "Content-Type:application/json" -v`

If you want to project be displayed like `http://mysites.prj/~test_project` - put it in your `nginx.conf` file (server section):

```
# projects router
location ~ ^/\~(.*)$ {
    alias /<path>/<to>/<root>/web/projects$
    autoindex off;
}
```