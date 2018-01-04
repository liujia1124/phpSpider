<?php

namespace spider;

use core\Spider;
use core\Common;
use core\Db;
class Jokeji extends Spider
{

    public function getTotalPage($num=1){
        $html = file_get_html('http://www.jokeji.cn/hot.htm');
        if(empty($html)){
            exit('totalPage notFound');
        }
        foreach($html->find('a') as $e){
            if(strpos('~+~'.$e->href,'hot.asp?me_page=')){
                $num = (int)str_replace('hot.asp?me_page=','',$e->href);
            }
        }
        sleep(rand(1,5));
        return $num;
    }
    //分页获取list
    public function getList($nowPage, $step, $totalPage){
        $list = [];
        $currentPage = $nowPage*$step + 1;

        $i = 0;
        for ($j=0; $j<$step; $j++)
        {
            if($currentPage>$totalPage){
                break;
            }
            $listUrl = 'http://www.jokeji.cn/hot.asp?me_page='.$currentPage;
            $html = $this->getListObject($listUrl);
            if(empty($html)){
                continue;
            }
            foreach($html->find('a') as $e){
                if(preg_match('/\/jokehtml\/.+\.htm/',$e->href)){
                    $href = 'http://www.jokeji.cn'.$e->href;
                    $list[$i]['url'] = $href;
                    echo $href;
                    echo PHP_EOL;

                    $list[$i]['pic'] = '';
                    $i++;
                }
            }
            $currentPage++;
            sleep(rand(180,500));
        }
        return $list;
    }

    public function getArticle($map){
        $data = [];
        $article  = $this->getArticleObject($map['url']);
        if(empty($article)){
            return;
        }
        $keywords    = $article->find('meta[name="keywords"]')[0]->content;
        $description = $article->find('meta[name="description"]')[0]->content;
        $title    = $article->find('title')[0]->innertext;
        $content  = $article->find('span[id="text110"]')[0];
        $url   = $map['url'];
        $spiderTextId = $this->getspiderTextId($url);
        $title = explode('_', $title);
        $title = $title[0];

        $data['keywords']      =  $keywords;
        $data['excerpt']       =  $description;
        $data['category']      =  22;
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
        $step = 1;
        $totalPage = $this->getTotalPage();
        $num  =  $totalPage/$step;
        if($totalPage%$step>0){
            $num++;
        }
        for($i=0; $i<$num; $i++){
            $list = $this->getList($i, $step, $totalPage);
            foreach ($list as $v) {
                $data = $this->getArticle($v);
                $this->sendToApi($data);
                sleep(rand(180,400));
            }
        }
    }

    public function getspiderTextId($str){
        $id  = substr($str, -18, 14);
        return $id;
    }


}
	
