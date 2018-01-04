<?php

namespace core;
use core\Common;
use core\Db;
	class Spider{
		public $header =[];
        public $spider ;
		public function __construct()
        {
            include_once ROOT_PATH.'core/simplehtmldom/simple_html_dom.php';
        }
        //simpledom 无法抓取，则采用此方法
        public function send($arr)
        {
            $defaultHeader = [
                'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language:zh-CN,zh;q=0.8',
                'Connection:keep-alive',
                'Upgrade-Insecure-Requests:1',
                'User-Agent:Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.79 Safari/537.36'
            ];
            $url = empty($arr['url']) ? 0 : $arr['url'];
            $header = empty($arr['header']) ? $defaultHeader : $this->merge($defaultHeader, $arr['header']);
            $data = empty($arr['data']) ? '' : $arr['data'];
            $post = empty($arr['post']) ? 0 : 1;
            $debug = empty($arr['debug']) ? 0 : 1;
            $showHeader = empty($arr['showHeader']) ? 0 : 1;
            $cookie_jar_index = ROOT_PATH . 'cookies/sina.txt';
            $ch = curl_init();
            if (!$url) {
                echo "请输入url参数";
                return false;
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            //	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar_index);
            //	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar_index);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //是否post
            if ($post) {
                curl_setopt($ch, CURLOPT_POST, 1);
            }
            //发送头消息
            if (!empty($header)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            }
            //post内容
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            //是否输出到页面
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //是否显示头消息
            if ($showHeader) {
                curl_setopt($ch, CURLOPT_HEADER, true);
            }
            // 是否开启调试
            if ($debug) {
                curl_setopt($ch, CURLOPT_VERBOSE, true);
            }
            $rs = curl_exec($ch);
            //是否开启调试
             if($debug){
             var_dump(curl_getinfo($ch));
             }
            curl_close($ch);
            return $rs;
        }
        //存储本地数据库
        public function save($data){
            if (empty($data)) {
                echo 'data empty';
                echo PHP_EOL;
                return false;
            }

            $data = $this->format($data);

            $title       = $data['title'];
            $content     = $data['content'];
            $keywords    = $data['keywords'];
            $description = $data['description'];
            $url         = $data['url'];

            $time = date('Y-m-d H:i:s',time());
            $spiderId = Common::getCurrentSpider()['id'];


            $db = Db::connect();
            $sql = "insert into content(`title`,`content`, `keywords`,`description`, `create_time`, `url`, `spider_id`) values('$title', '$content', '$keywords', '$description', '$time', '$url', '$spiderId') ";
            $rs = $db->exec($sql);

            if ($rs) {
                echo 'insert success';
                echo PHP_EOL;;
            } else {
                echo 'insert database failed';
                echo PHP_EOL;
            }
        }
        //上传到接口
        public function sendToApi($data,$api='/api/article/addArticle'){
            if (empty($data)) {
                echo 'data empty';
                echo PHP_EOL;
                return false;
            }
            $header['time'] = time();
            $header['sign'] = Common::getCurrentSpider()['sendToDomain'];
            $header['token'] = $this->getToken($header['time'], $header['sign']);
            $all = ['header'=>$header, 'data'=>$data];
            $all = ['api'=>urlencode(json_encode($all))];
            $url = $header['sign'].$api;
            $rs  = $this->send(['data'=>$all, 'url'=>$url,'post'=>1,'debug'=>0]);
            return $rs;
        }
        //格式化数据
        public function formatArticle($data){
            $spiderId               = Common::getCurrentSpider()['id'];
            $data['time']          = time();
            $data['spiderId']      = $spiderId;
            $data['userId']        = 999;

            $data = Common::toUtf8($data);
            $data = Common::htmlChar($data);
            return $data;
        }
        //入口文件
        public function run()
        {

        }

        public function getToken($time, $sign){
            return md5('string'.$sign.$time.$sign);
        }

        public function toTradition($data){
            $data['title'] = Common::simpleToTradition($data['title']);
            $data['keywords'] = Common::simpleToTradition($data['title']);
            $data['dscription'] = Common::simpleToTradition($data['title']);

            $data['content'] =  Common::simpleToTradition($data['content']);

            return $data;
        }

        public function logEmptyDate($str, $type, $path=''){
            $path = ROOT_PATH.'public'.DIRECTORY_SEPARATOR.Common::$currentSpider['value'];
            if(!is_dir($path)){
                mkdir($path);
            }
            Common::log( $path.DIRECTORY_SEPARATOR.$type.'.log', $str);
            return;
        }

        /**
         * 合并头消息, arr2 覆盖arr1重复部分
         * @param $arr1
         * @param $arr2
         */
        public function merge($arr1, $arr2, $arr=[]){
            $arrNew1 = [];
            $arrNew2 = [];
            foreach($arr1 as $k=>$v){
                $v = explode(':', $v);
                $arrNew1[$v[0]] = $v[1];
            }
            foreach($arr2 as $k=>$v){
                $v = explode(':', $v);
                $arrNew2[$v[0]] = $v[1];
            }

            $arrNew = array_merge($arrNew1, $arrNew2);
            foreach($arrNew as $k=>$v){
                $arr[] = $k.':'.$v;
            }

            return $arr;
        }

        /**
         * 检查文章是否已存在
         * @param $textId
         * @return bool
         */
        public function notExist($textId){
            $api  = '/api/Article/checkExist';
            $data = ['spiderId'=>Common::getCurrentSpider()['id'], 'textId'=>$textId];
            $rs = $this->sendToApi($data, $api );
            if($rs=='no'){
                return true;
            }
            return false;
        }

    }
	
	
	
	
