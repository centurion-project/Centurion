<?php

header('Location: ' . substr($_SERVER['PHP_SELF'], 0, -strlen('index.php')) . 'status/');
die(); 
