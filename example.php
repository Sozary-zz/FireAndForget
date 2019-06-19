<?php
use Sozary\FireAndForget\FireAndForget;

require_once "./vendor/autoload.php";

$fire_and_forget = new FireAndForget();

$fire_and_forget->send("post", "http://localhost:1234/receive.php", ["data" => 69], "Bearer 123");
