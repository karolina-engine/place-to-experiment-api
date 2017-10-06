<?php
namespace Karolina\Database;

use Illuminate\Database\Capsule\Manager as Capsule;

class DatabaseConnector
{
    private $capsule;

    public function __construct()
    {
        $this->capsule = new Capsule;
    }

    public function addConnection($schema, $name = "default")
    {
        $this->capsule->addConnection([
                'driver'    => 'mysql',
                'host'      => getenv('database_hostname'),
                'database'  => $schema,
                'username'  => getenv('database_user'),
                'password'  => getenv('database_pass'),
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
            ], $name);
    }

    public function makeGlobal()
    {
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
    }
}
