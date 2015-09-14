<?php

require_once 'addNewRowToSS.php';
require_once 'getBuymaAccess.php';


function getAccess() {

    $getBuymaAccess = new getBuymaAccess;

    $getBuymaAccess->setLoginInfo(
        "yourBuymaId",
        "yourBuymaPassword"
    );

    $postData = $getBuymaAccess->getPostData();


    for ($i=0; $i<count($postData); $i++){

        $getBuymaAccess->doLogin($postData[$i]["url"]);

        $orderData = $getBuymaAccess->getOrderData();


        echo PHP_EOL;
        echo PHP_EOL;
        echo PHP_EOL;
        echo "==========orderDate_inwriteOrderToSS.php===========".PHP_EOL;
        foreach($orderData as $key => $value) {

            echo $key . '=>' . $value . PHP_EOL;
        }

        writeSS($orderData);

    };
}



function writeSS($orderData) {
    $addNewRowToSS = new addNewRowToSS;

    $addNewRowToSS->setAuthData(
        'yourGoogleID',
        'yourGooglePassword'
    );

    $addNewRowToSS->setTergetSS(
        'yourGoogleSheetsID',
        5 // <- sheet number you want to get
    );

    $addNewRowToSS->getGdataAuth();

//    $addNewRowToSS->getListFeed();
    $addNewRowToSS->addNewRow($orderData); 

//    $addNewRowToSS->getListFeed();
    
}


getAccess();

