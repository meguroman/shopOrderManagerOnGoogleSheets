<?php

/**
 * made by meguroman
 *
 * This is a code for adding data to Google Sheets.
 * I'm glad if you would be happy using this program.
 * Please contact @meguroman on Twitter if you have any suggestions.
 *
 *
 */


//Zend Framework Gdataの読み込み
require_once 'Zend/Version.php';
require_once 'Zend/Gdata/ClientLogin.php';
require_once 'Zend/Gdata/Spreadsheets.php';



class addNewRowToSS
{

    //Googleアカウント情報設定
    private $user;
    private $pass;

    //接続先Google SheetのKeyとワークシートID設定
    private $spreadsheetKey;
    private $worksheetId;

    //Google Sheetsへのアクセスオブジェクト
    private $spreadsheetService;

    //Google Sheetsから取得したデータオブジェクト
    private $listFeed;


    public function setAuthData($user, $pass) {
        $this->user = filter_var($user, FILTER_VALIDATE_EMAIL);
        $this->pass = $pass;
    }

    public function getGdataAuth() {
        //Gooogle Appsへの認証処理
        $service = Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
        $client = Zend_Gdata_ClientLogin::getHttpClient($this->user, $this->pass, $service);
        $this->spreadsheetService = new Zend_Gdata_Spreadsheets($client);
    }

    public function setTergetSS($key, $ws) {
        $this->spreadsheetKey = $key;
        $this->worksheetId = $ws;
    }

    public function getListFeed() {
        //対象Google Sheetsのデータ取得 
        $query = new Zend_Gdata_Spreadsheets_ListQuery();
        $query->setSpreadsheetKey($this->spreadsheetKey);
        $query->setWorksheetId($this->worksheetId);
        //$query->setSpreadsheetQuery("日付=3-17-2015"");
        $this->listFeed = $this->spreadsheetService->getListFeed($query);

        echo PHP_EOL."==========ListFeedOfSS===========".PHP_EOL;
        foreach($this->listFeed->entries as $entry){
        echo PHP_EOL."====print row====".PHP_EOL;
            $rowData = $entry->getCustom();
            foreach($rowData as $customEntry) {
                echo $customEntry->getColumnName() . " = " . $customEntry->getText() . PHP_EOL;
            }
        }
        echo PHP_EOL."=================================".PHP_EOL;
    }

    public function addNewRow($orderData) {

        $addRowData = array(
            "_cn6ca" => $orderData['status'],
            "_cokwr" => "入金待ち",
            "_cpzh4" => $orderData['itemName'],
            "_ckd7g" => $orderData['userContact'],
            "発送情報" => $orderData['orderDate'],
            "_d2mkx" => "00" + $orderData['itemId'],
            "_cssly" => "1",
            "取引連絡" => $orderData['userName'],
            "_cvlqs" => $orderData['userId'],
            "_cx0b9" => $orderData['userAddress'],
            "_d9ney" => $orderData['userNicName'],
            "_db1zf" => "クロネコDM便",
            "_dcgjs" => "000" . $orderData['orderId'],
            "_ddv49" => "https://www.buyma.com/my/buyerorderdetail/?tid=" . $orderData['orderId'],
            "_d5fpr" => $orderData['dueDateForPayment'],
            "売上" => $orderData['itemPrice']
        );

//        $orderData['status'] = "受注";///テスト用、すぐ消してね

        echo PHP_EOL."===============================".PHP_EOL;
        echo "------paymentMethod------::" . $orderData['paymentMethod'] . PHP_EOL;
        echo "orderData['status']=>" . $orderData['status'] . PHP_EOL;
        echo "受注情報＝＞" . $addRowData["受注情報"] . PHP_EOL;
        if ($orderData['status'] == "仮受注") {
            echo "------in karizytyu------".PHP_EOL;
            unset($addRowData['_cx0b9']);
            unset($addRowData['売上']);
            $addRowData['_d415a'] = "未連絡";
        } else if ($orderData['status'] == "受注" && $orderData['paymentMethod'] == "クレジット") {
            echo "------in jyutyu:credit------".PHP_EOL;
            unset($addRowData['_cokwr']);
            $addRowData['_d415a'] = "-";
            $addRowData['_d5fpr'] = "-";
            $addRowData['_d6ua4'] = "未連絡";
            $addRowData['_cyevm'] = "西本さん";
        } else if ($orderData['status'] == "受注" && $orderData['paymentMethod'] == "振込その他") {
            echo "------in jyutyu:hurikomi------".PHP_EOL;
            //read row data of this order number
            $query = new Zend_Gdata_Spreadsheets_ListQuery();
            $query->setSpreadsheetKey($this->spreadsheetKey);
            $query->setWorksheetId($this->worksheetId);
            $query->setSpreadsheetQuery('_dcgjs='.$orderData['orderId']);
            $this->listFeed = $this->spreadsheetService->getListFeed($query);

            $updateRowData = array();
            foreach($this->listFeed->entries as $entry){
                $rowData = $entry->getCustom();
                foreach($rowData as $customEntry) {
                    //echo "=====customEntlyColumn]====>>" . $customEntry->getColumnName() . " -> " . $customEntry->getText() . PHP_EOL;
                    //get current data
                    $updateRowData[$customEntry->getColumnName()] = $customEntry->getText();
                    //echo "updateRowData[]====>>".$customEntry->getColumnName()."->".$updateRowData[$customEntry->getColumnName()] . PHP_EOL;
                }
                //update Data
                $updateRowData["_cn6ca"] = $orderData['status'];
                $updateRowData["_cokwr"] = "";
                $updateRowData["_cx0b9"] = $orderData['userAddress'];
                $updateRowData["_d6ua4"] = "未連絡";
                $updateRowData["売上"] = $orderData['itemPrice'];
                $updateRowData['_cyevm'] = "西本さん";
                //run update method
                $updateListEntry = $this->spreadsheetService->updateRow($entry, $updateRowData);
                return;
            }
        } else {
            echo "=========unknown payment method:skip insertRow()========".PHP_EOL;
            throw new Exception("unknown payment method:skip insertRow()");
        }


        //対象Google Sheetsの行末に新規データ列の追加
        for ($i=0;$i<$orderData['orderQty'];$i++){
            $insertListEntry = $this->spreadsheetService->insertRow($addRowData, $this->spreadsheetKey, $this->worksheetId);
        }
    }



}
