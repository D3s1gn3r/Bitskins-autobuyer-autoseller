<?php

    require ('db_config.php');
    require ('rb.php');
    R::setup( 'mysql:host=' . $db_host . ';dbname=' . $db_name, $db_login, $db_pass );
	require ('settings.php');
    require_once('otphp-master/otphp-master/lib/otphp.php');

    R::wipe( 'prices' );

    $totp = new \OTPHP\TOTP($bitskins_secret_key);

    $code = $totp->now();

    $items_price = file_get_contents('https://bitskins.com/api/v1/get_price_data_for_items_on_sale/?api_key=' . $bitskins_api_key . '&code=' . $code . '&app_id=730');
    $items_price = json_decode($items_price);

    $filters = explode(",", $personalFilters);

    if($items_price->status == 'success'){
    	$items = $items_price->data->items;
    	foreach ($items as $value) {
            if($filters[0] == ''){
                $filter_check = true;
            }
            else{
                $filter_check = false;
            }
    		if($value->recent_sales_info->average_price >= $minItemPrice && $value->recent_sales_info->average_price <= $maxItemPrice){
                $name = $value->market_hash_name;
                $price = $value->recent_sales_info->average_price;
                if($name == '' || $price == ''){
                    continue;
                }
                if($filter_check == false){
                    foreach ($filters as $value) {
                        if(stristr($name, $value)){
                            $filter_check = true;
                        }
                    }
                }
                if($filter_check){
                    $salePrice = round(($price * (100 - $profitPercent))/100, 2);
                    $book = R::dispense( 'prices' );
                    $book->name = $name;
                    $book->price = $salePrice;
                    $book->averagerice = $price;
                    R::store( $book );
                }
            }
    	}
    }




