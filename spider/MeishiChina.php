<?php

namespace spider;

use core\Spider;
use core\Common;
class MeishiChina extends Spider
{
    protected $totalPage = 0;
    protected $cateMap = [
        'recai'=>54
    ];


    /**
     * 查看文章列表
     * @param $nowPage
     * @param $step
     * @param $totalPage
     */
    public function getList($grapCat, $pageNum=1){

        $url  = "http://home.meishichina.com/recipe/$grapCat/page/$pageNum/";
        $html = file_get_html($url);
        if(empty($html)){
            return [];
        }
        $articleList = [];
        foreach($html->find('#J_list ul li div[class=pic] a') as $k => $e ){
            $articleList[$k]['url'] = $e->href;
        }
        foreach($html->find('#J_list ul li div[class=pic] a img ') as $k => $e ){
            $articleList[$k]['pic'] = $e->attr['data-src'];
        }
        return $articleList;
    }

    public function getArticle($map, $saveCat){
        $data = [];
        $article = file_get_html($map['url']);
        if(empty($article)){
            return [];
        }
        if(empty($article->find('title')[0])||empty($article->find('.recipeStep')[0])){
            return [];
        }
        $title    = $article->find('title')[0]->innertext;
        $content  = $article->find('.recipeStep')[0]->innertext ;
        $url   = $map['url'];
        $spiderTextId = $this->getspiderTextId($url);
        $title = explode('_', $title);
        $title = $title[0];


        $data['category']      =  $saveCat;
        $data['title']         =  $title;
        $data['content']       =  $content;
        $data['spiderTextId'] = $spiderTextId;
        $data['thumbnail']    =  $map['pic'];
        $data['url']    =  $map['url'];

        return $data;
    }

    //入口文件
    public function run()
    {
        $config = $this->cateMap;
        foreach($config as $k=>$v){
            $this->grap($k, $v);
            sleep(rand(5, 10));
        }
    }

    public function getspiderTextId($str){
        $id  = substr($str, -11, 6);
        return $id;
    }

    public function grap( $grapCat, $saveCat, $num=3){

        for($page=1; $page<=$num; $page++){
            $list = $this->getList($grapCat, $page);
            sleep(rand(5,10));
            if(empty($list)){
                echo "Cat: $grapCat Page: $page  Empty";
                echo PHP_EOL;
                continue;
            }
            foreach ($list as $v) {
                $textId = $this->getspiderTextId($v['url']);
                sleep(1);
                if(!$this->notExist($textId) ){
                    echo "Cat: $grapCat Id: $textId  Exist";
                    echo PHP_EOL;
                    continue;
                }
                $data = $this->getArticle($v, $saveCat);
                sleep(rand(30,100));
                if(empty($data)){
                    echo "Error: {$v['url']}";
                    echo PHP_EOL;
                    continue;
                }
                $data = $this->formatArticle($data);
                $rs = $this->sendToApi($data);
                if($rs == 'success'){
                    echo "Cat: $grapCat Id: $textId  Success";
                    echo PHP_EOL;
                }else{
                    echo "Cat: $grapCat Id: $textId  Fail";
                    echo PHP_EOL;
                }
            }
        }
    }


}
	
