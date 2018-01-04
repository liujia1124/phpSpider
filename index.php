<?php

    define('DEBUG', true);

	define('ROOT_PATH',__DIR__.DIRECTORY_SEPARATOR);

    include ROOT_PATH.'core/Start.php';

    $start = new core\Start();
    $start->run();
	
