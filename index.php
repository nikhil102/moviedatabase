<?php

if ( ! file_exists(__DIR__.'/.env') || ! (preg_match('/INSTALLED=(true|1)/', file_get_contents(__DIR__.'/.env')))) {
    header("Location: public");
    die();
} else {
    die('Could not find .htaccess file');
}