<?php
sleep(10);
if ($_SERVER["HTTP_AUTHORIZATION"] == "Bearer 123")
    file_put_contents("test", print_r($_POST, true));
