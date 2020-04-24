# ` my_movies_lists`

__Type__ : Symfony project

__Repository name__ :  symfony_project

## Installation

Clone the project

```git clone <repository>```

Get required dependencies

```symfony composer update```

Set up database if needed

In ```.env``` you have to set up your own database informations ```DATABASE_URL=...```

```php bin/console doctrine:database:create```

```php bin/console doctrine:migrations:migrate```

Start the server.

```symfony server:start```

That's all, enjoy !
