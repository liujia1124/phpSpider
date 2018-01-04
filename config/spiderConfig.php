<?php



    $spiders = [
       

        'meishichina' =>[
            'id'     => '4',
            'value'  => 'meishichina',
            'name'   => '美食天下',
            'class'  => 'spider\MeishiChina',
            'domain' => 'http://home.meishichina.com',
            'sendToDomain' => 'http://127.0.0.6',
        ],

        'zhonghuayangshengwang' =>[
            'id'     => '5',
            'value'  => 'zhonghuayangshengwang',
            'name'   => '中华养生网',
            'class'  => 'spider\Cnys',
            'domain' => 'http://www.cnys.com',
            'sendToDomain' => 'http://127.0.0.6',
        ],

    ];

	return $spiders;
	
	
	
