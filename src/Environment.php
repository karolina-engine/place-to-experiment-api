<?php

namespace Karolina;

class Environment
{
    private $loadedEnvVariables = array();
    public $dotenv;

    public function __construct($dotenv)
    {
        $this->dotenv = $dotenv;
    }

    public function loadEnvVariablesByHostname($hostname)
    {
        $hostname = strtolower($hostname);

        try {
            $dsn = "mysql:host=".getenv('database_hostname').";";
            $dsn .= "dbname=environments";

            $pdo = new \PDO($dsn, getenv('database_user'), getenv('database_pass'));
            $stmt = $pdo->prepare('SELECT * 
				FROM variables 
		        LEFT JOIN hosts 
		            ON (hosts.collection=variables.collection) 
				WHERE hostname = :hostname');
            $stmt->bindParam(':hostname', $hostname);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $this->loadEnvVariablesFromArray($result);
        } catch (\PDOException $e) {
            if (getenv('environment') == 'development') {
                echo 'Connection failed: ' . $e->getMessage();
            }
        } catch (\Exception $e) {
            if (getenv('environment') == 'development') {
                echo 'Connection failed: ' . $e->getMessage();
            }
        }
    }

    private function loadEnvVariablesFromArray(array $variables)
    {
        foreach ($variables as $key => $variable) {
            $this->loadedEnvVariables[$key] = $variable;
        }
    }


    public function setEnvVariablesFromArray(array $variables)
    {
        foreach ($variables as $name => $value) {
            $this->setEnvVariable($name, $value);
        }
    }

    private function setEnvVariable($varible, $value)
    {
        putenv("$varible=$value");
    }

    public function setLoadedEnvVariables()
    {

        // Set loaded
        foreach ($this->loadedEnvVariables as $variable) {
            $this->setEnvVariable($variable['name'], $variable['value']);
        }

        $this->dotenv->load();
    }
}
