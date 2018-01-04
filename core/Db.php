<?php

namespace core;
	
	class Db{
		private static $create;
		public static $pdo;

		private function __construct()
        {

        }
        private function __clone()
        {

        }

        public static function connect(){
            if(self::$create){
                return self::$pdo;
            }else{
                $config = Common::getConfig('config/dbConfig.php');
                self::$pdo = new \PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}",$config['db_user'],$config['db_password']);
                self::$create = true;
                return self::$pdo;
            }
			
		}
		public function update(){
			
		}
		public function select(){
			
		}
		public function find(){
			
		}
		
		
		
	}
	