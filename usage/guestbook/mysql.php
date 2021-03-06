<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once '../../lib/config.php';
require_once 'DB/Adapter/Factory.php';

$dsn = parse_ini_file(dirname(__FILE__) . '/../../config/db-credentials.ini');
$DB = DB_Adapter_Factory::connect($dsn['mysql']);
$DB->setIdentPrefix('db_adapter_example_');
prepareMessageTable($DB);

$messages = $DB->fetchAll("SELECT * FROM ?_guestbook_message ORDER BY created DESC");
if ($_POST) {
    addMessage($DB, $_POST);    
    header('Location: ' . $_SERVER['REQUEST_URI']);
}

function prepareMessageTable($DB)
{
    // $DB->query("DROP TABLE ?_guestbook_message");
    $DB->query("
        CREATE TABLE IF NOT EXISTS ?_guestbook_message (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            author varchar(100) NOT NULL,
            text varchar(300) NOT NULL,
            created timestamp NOT NULL
        );
    ");
}

function addMessage($DB, $message)
{
    if (!empty($message['text'])) {
        $DB->query("INSERT INTO ?_guestbook_message (?#) VALUES (?a)",
            array_keys($message), array_values($message)
        );
    }
}

require_once '_template.php';