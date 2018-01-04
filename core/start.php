<?php
	
namespace core;

use config\Config;
use core\Common;

	
	class Start{

	    private $config = [];

		public function __construct(){

		    if(DEBUG){
		        error_reporting(E_ALL);
                echo 'start memory: '.memory_get_usage();
                echo PHP_EOL;
            }else{
                error_reporting(0);
            }

            spl_autoload_register('self::loadClass');


		}

		public static function loadClass($class){
		    $class = str_replace('\\', '/', $class);
		    $file = ROOT_PATH.$class.'.php';

            if(is_file($file)){
                include_once $file;
            }
        }

		public static function run(){
            $spiders = Common::getConfig('config/spiderConfig.php');
            foreach($spiders as $v){
                Common::setCurrentSpider($v);
                $class = '\\'.$v['class'].'()';
                $spider = '';
                eval("\$spider = new $class;");
                $spider->run();
                sleep(1);
            }
            exit('all spiders finish');
        }
		
	}
	
	
	
	
	