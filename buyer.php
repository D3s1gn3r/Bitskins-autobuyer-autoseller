<?php

	require ('db_config.php');
    require ('rb.php');
    R::setup( 'mysql:host=' . $db_host . ';dbname=' . $db_name, $db_login, $db_pass );
	require ('settings.php');
    require_once('otphp-master/otphp-master/lib/otphp.php');

    $totp = new \OTPHP\TOTP($bitskins_secret_key);
    $code = $totp->now();

    $data = file_get_contents('https://bitskins.com/api/v1/get_account_balance/?api_key=' . $bitskins_api_key . '&code=' . $code);
	$data = json_decode($data);

	if($data->status == 'success'){
		$accountBalance = $data->data->available_balance;

		if($accountBalance > $balanсeToStop){

			$data = file_get_contents('https://bitskins.com/api/v1/get_my_inventory/?api_key=' . $bitskins_api_key . '&page=1&app_id=' . $app_id . '&code=' . $code);
			$data = json_decode($data);

			if($data->status == 'success'){
				$items = $data->data->steam_inventory->items;

				$inventory = [];
				foreach ($items as $value) {
					$inventory[$value->market_hash_name] = $value->number_of_items;
				}

	    		$all_prices = R::getAll('SELECT * FROM prices');
	    		$prices = [];
	    		foreach ($all_prices as $value) {
	    			$prices[$value['name']] = $value['price'];
	    		}
	    		for($k = 0; $k<45; $k++){
				   	$data = file_get_contents('https://bitskins.com/api/v1/get_inventory_on_sale/?api_key=' . $bitskins_api_key . '&page=1&app_id=' . $app_id . '&code=' . $code . '&min_price=' . $minItemPrice . '&max_price=' . $maxItemPrice . '&page=1');
					$data = json_decode($data);

					if($data->status == 'success'){
						$items = $data->data->items;

						$i = 0;
						foreach ($items as $value) {
							if($i == 3){
								break;
							}
							if ($inventory[$value->market_hash_name] == null ||  $inventory[$value->market_hash_name] < $maxCountItems) {
								if($prices[$value->market_hash_name] != null && $prices[$value->market_hash_name] >= $value->price){
									$url = 'https://bitskins.com/api/v1/buy_item/?api_key=' . $bitskins_api_key . '&code=' . $code . '&item_ids=' . $value->item_id . '&prices=' . $value->price . '&app_id=' . $app_id . '&allow_trade_delayed_purchases=true';
									$curl = curl_init();
									curl_setopt($curl, CURLOPT_URL, $url);
									curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
									$out = curl_exec($curl);
									$data = json_decode($out);
									curl_close($curl);

									if($data->status == 'success'){
										$minsellprice = round(($prices[$value->market_hash_name]/100) * (100 + $minProcentToSell), 2);
										$book = R::dispense( 'bougthitems' );
		                    			$book->itemid = $value->item_id;
		                    			$book->name = $value->market_hash_name;
		 			                    $book->boughtprice = $value->price;
		 			                    $book->minsellprice = $minsellprice;
		 			                    $book->averageprice = $prices[$value->market_hash_name];
		 			                    $book->status = 'bought';
				                    	::store( $book );
									}
								}
							}
							$i++;
						}
					}
				}
			}
		}
	}

?>