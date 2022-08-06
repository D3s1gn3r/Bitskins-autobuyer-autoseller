<?php

	// Additional settings
	$bitskins_secret_key =  R::getRow( 'SELECT value FROM settings WHERE name = "bitskinsSecretKey"')['value'];
    $bitskins_api_key = R::getRow( 'SELECT value FROM settings WHERE name = "bitskinsApiKey"')['value'];
    $app_id = R::getRow( 'SELECT value FROM settings WHERE name = "appId"')['value'];
    $minItemPrice = R::getRow( 'SELECT value FROM settings WHERE name = "minItemPrice"')['value'];
    $maxItemPrice = R::getRow( 'SELECT value FROM settings WHERE name = "maxItemPrice"')['value'];
    $profitPercent = R::getRow( 'SELECT value FROM settings WHERE name = "profitPercent"')['value'];
    $personalFilters = R::getRow( 'SELECT value FROM settings WHERE name = "personalFilters"')['value'];
    $salesPerDay = R::getRow( 'SELECT value FROM settings WHERE name = "salesPerDayy"')['value'];
    $balanсeToStop = R::getRow( 'SELECT value FROM settings WHERE name = "balanсeToStop"')['value'];
    $maxCountItems = R::getRow( 'SELECT value FROM settings WHERE name = "maxCountItems"')['value'];
    $minProcentToSell = R::getRow( 'SELECT value FROM settings WHERE name = "minProcentToSell"')['value'];
    $amounOfDaysAfterBan = R::getRow( 'SELECT value FROM settings WHERE name = "amounOfDaysAfterBan"')['value'];

?>