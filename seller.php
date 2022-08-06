<?php

    require ('db_config.php');
    require ('rb.php');
    R::setup( 'mysql:host=' . $db_host . ';dbname=' . $db_name, $db_login, $db_pass );
    require ('settings.php');
    require_once('otphp-master/otphp-master/lib/otphp.php');

    $totp = new \OTPHP\TOTP($bitskins_secret_key);
    $code = $totp->now();

    $items_to_sale = R::getAll( 'SELECT * FROM `bougthitems`' );

    foreach ($items_to_sale as $value) {
        $priceToRecell = 0;
    	if($value['status'] == 'bought'){
            $priceToSale = round($value['averageprice'] * (100 + $profitPercent) / 100, 2);
            $data = file_get_contents('https://bitskins.com/api/v1/list_item_for_sale/?api_key=' . $bitskins_api_key . '&code=' . $code . '&item_ids=' . $value['itemid'] . '&prices=' . $priceToSale . '&app_id=' . $app_id);
            $data = json_decode($data);

            if($data->status == 'success'){
                R::exec( 'UPDATE bougthitems SET status="onsaleBaned" WHERE itemid = "' . $value['itemid'] . '"' );
            }
    	}
    	elseif ($value['status'] == 'onsaleBaned') {
            $data = file_get_contents('https://bitskins.com/api/v1/get_specific_items_on_sale/?api_key=' . $bitskins_api_key . '&code=' . $code . '&item_ids=' . $value['itemid'] . '&app_id=' . $app_id);
            $data = json_decode($data);

            if($data->status == 'success'){
                if(empty($data->data->items_on_sale)){
                    R::exec( 'UPDATE bougthitems SET status="sold" WHERE itemid = "' . $value['itemid'] . '"' );
                }
                else{
                    $timestamp = $data->data->items_on_sale[0]->withdrawable_at;
                    if(($timestamp + ($amounOfDaysAfterBan * 24 * 60 * 60)) >= date("U")){
                        $data = file_get_contents('https://bitskins.com/api/v1/get_price_data_for_items_on_sale/?api_key=' . $bitskins_api_key . '&app_id=' . $app_id . '&code=' . $code);
                        $data = json_decode($data);

                        if($data->status['success']){
                            $items = $data->data->items;
                            foreach ($items as $val) {
                                if($val->market_hash_name == $value['name']){
                                    $priceToRecell = $val->lowest_price;
                                }
                            }
                            if($priceToRecell == 0){
                                continue;
                            }
                            elseif($priceToRecell < $value['minsellprice'] ){
                                $priceToRecell = $value['minsellprice'] - 0.01;
                            }

                            $data = file_get_contents('https://bitskins.com/api/v1/relist_item/?api_key=' . $bitskins_api_key . '&code=' . $code . '&item_ids=' . $value['itemid'] . '&prices=' . $priceToSale . '&app_id=' . $app_id);
                            $data = json_decode($data);

                            if($data->status == 'success'){
                                R::exec( 'UPDATE bougthitems SET status="onsale" WHERE itemid = "' . $value['itemid'] . '"' );
                            }
                        }
                    }
                }
            }
    	}
        elseif($value['status'] == 'onsale'){
            $data = file_get_contents('https://bitskins.com/api/v1/get_specific_items_on_sale/?api_key=' . $bitskins_api_key . '&code=' . $code . '&item_ids=' . $value['itemid'] . '&app_id=' . $app_id);
            $data = json_decode($data);
            if($data->status['success']){
                if(empty($data->data->items_on_sale)){
                    R::exec( 'UPDATE bougthitems SET status="sold" WHERE itemid = "' . $value['itemid'] . '"' );
                }
                else{
                    $currentPrice = $data->data->items_on_sale[0]->price;
                    $data = file_get_contents('https://bitskins.com/api/v1/get_price_data_for_items_on_sale/?api_key=' . $bitskins_api_key . '&app_id=' . $app_id . '&code=' . $code);
                    $data = json_decode($data);

                    if($data->status['success']){
                        $items = $data->data->items;
                        foreach ($items as $val) {
                            if($val->market_hash_name == $value['name']){
                                $priceToRecell = $val->lowest_price;
                            }
                        }
                        if($currentPrice > $priceToRecell && ($priceToRecell-0.01) > round(($value['boughtprice'] * 1.05) , 2)){
                            $priceToSale = $priceToRecell - 0.01;
                            $data = file_get_contents('https://bitskins.com/api/v1/relist_item/?api_key=' . $bitskins_api_key . '&code=' . $code . '&item_ids=' . $value['itemid'] . '&prices=' . $priceToSale . '&app_id=' . $app_id);
                                $data = json_decode($data);
                        }
                    }
                }
            }
        }
    }

?>