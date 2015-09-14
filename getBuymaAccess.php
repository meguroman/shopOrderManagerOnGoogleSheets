<?php


// read goutte
//require_once 'goutte.phar';
require_once 'goutte-v1.0.7.phar';


// Goutteオブジェクトの生成
use Goutte\Client;


class getBuymaAccess {

    private $client;
    private $id;
    private $pass;
    private $url;
    private $crawler;
    public $accessData = array();
    public $orderData = array();


    public function __construct() {

        // Goutteオブジェクトの生成
        $this->client = new Client();
    }


    public function getPostData() {
        return $postData = $_POST['data'];
    }


    public function setLoginInfo($id, $pass) {
        $this->id = $id;
        $this->pass = $pass;
    }

    public function getLoginInfo() {
        echo $this->id;
        echo $this->pass;
    }


    public function doLogin($url) {
        // Buymaのmyページに移動
        $this->crawler = $this->client->request('GET', $url);

        file_put_contents('request_log.html', $this->crawler->html());

        error_log("Data[this->crawler->filter('title')->text()]====>>". $this->crawler->filter('title')->text() . PHP_EOL, 3, "php_error.log");

        if (preg_match('/ログイン/u',$this->crawler->filter('title')->text())) {

            // MyPageにログイン
            $form = $this->crawler->selectButton('ログイン')->form();
            $form->setValues(array(
                'txtLoginId'=> $this->id,
                'txtLoginPass'=> $this->pass
            ));
            //$value = $form->getValues();
            //print_r($value);
        
            $this->crawler = $this->client->submit($form);
            
        }
    }



    private function removeUnit($data) {
        return str_replace("件", "", $data);
    }


    public function getOrderData() {

        global $orderData;

        $this->crawler->filter('#content .n_details_box > tbody > tr')->each(function($node,$i) {
            //set Node number of need data on order page.
            //$nodeNumOfNeedData = array(0,1,2,3,4,5,7,8,9,10,11,12,15,16,17);


            global $address;
            global $orderData;


            $keyOfNode = $node->filter('th');

            //echo PHP_EOL."==========正規表現生成データ===========".PHP_EOL;

            switch($i){
            case 0:
                //受注状況の文面を切り出し
                preg_match('/【(.*)】/', $node->filter('td')->text(), $matches);
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . $matches[1];
                $orderData['status'] = $matches[1];
                break;
            case 1:
                //標準的な、前後空白を取り除くのみ
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . trim($node->filter('td')->text());
                $orderData['orderId'] = trim($node->filter('td')->text());
                break;
            case 2:
                //商品名と商品IDを分離
                preg_match('/(.*)（商品ID：(\d*)）/', trim($node->filter('td')->text()), $matches);
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . $matches[1] . PHP_EOL;
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . $matches[2];
                $orderData['itemName'] = $matches[1];
                $orderData['itemId'] = $matches[2];
                break;
            case 3:
                //ニックネームと会員IDを分離
                preg_match('/(.*)（会員ID：(\d*)）/', trim($node->filter('td')->text()), $matches);
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . $matches[1] . PHP_EOL;
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . $matches[2];
                $orderData['userNicName'] = $matches[1];
                $orderData['userId'] = $matches[2];
                break;
            case 4:
                //標準的な、前後空白を取り除くのみ
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . trim($node->filter('td')->text());
                $orderData['userName'] = trim($node->filter('td')->text());
                break;
            case 5:
                //名前の後ろのふりがなを削除
                //node5,7,8,9を結合
                preg_match('/(.*)/', trim($node->filter('td')->text()), $matches);
                $address = $matches[1];
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . $address;
                break;
            case 7:
                //標準的な、前後空白を取り除くのみ
                //node5,7,8,9を結合
                $address = $address . ' ' . trim($node->filter('td')->text());
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . $address;
                break;
            case 8:
                //住所部分のみを切り出し
                //node5,7,8,9を結合
                preg_match('/漢字\n\t*\n\t*(.*)\n\t*(.*)\n\t*(.*)/', trim($node->filter('td')->text()), $matches);
                $address = $address . ' ' . $matches[1] . $matches[2] . $matches[3];
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . $address;
                break;
            case 9:
                //標準的な、前後空白を取り除くのみ
                //node5,7,8,9を結合
                $address = $address . ' ' . trim($node->filter('td')->text());
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . $address;
                $orderData['userAddress'] = $address;
                break;
            case 10:
                //価格と注文個数を切り出し
                preg_match('/[AB]\n\t*(\d*,?\d*).*x.*(\d)個/', trim($node->filter('td')->text()), $matches);
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . $matches[1];
                $orderData['itemPrice'] = $matches[1];
                $orderData['orderQty'] = $matches[2];
                break;
            case 11:
                //支払い方法切り出し
                preg_match('/(.*)[（決]/u', trim($node->filter('td')->text()), $matches);
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . $matches[1];
                $orderData['paymentMethod'] = $matches[1];
                break;
            case 12:
                //日付のみを切り出し
                preg_match('/\d{4}\/\d{2}\/\d{2}/', trim($node->filter('td')->text()), $matches);
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . $matches[0];
                $orderData['orderDate'] = $matches[0];
                break;
            case 15:
                //日付のみを切り出し
                preg_match('/\d{4}\/\d{2}\/\d{2}/', trim($node->filter('td')->text()), $matches);
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . $matches[0];
                $orderData['dueDateForPayment'] = $matches[0];
                break;
            case 16:
                //標準的な、前後空白を取り除くのみ
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . preg_replace('[\n\r\t]', '', trim($node->filter('td')->text()));
                $orderData['userContact'] = preg_replace('/[\n\r\t]/', '', trim($node->filter('td')->text()));
                break;
            case 17:
                //日付のみを切り出し
                preg_match('/\d{4}\/\d{2}\/\d{2}/', trim($node->filter('td')->text()), $matches);
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . $matches[0];
                $orderData['dueDateForShipping'] = $matches[0];
                break;
            //case 25:
                //注文個数のみを切り出し
                //後ろの余計な文章を削除
             //   preg_match('/\d*/', trim($node->filter('td')->text()), $matches);
              //  echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . $matches[0];
                //$orderData['orderQty'] = $matches[0];
                //break;
            }

            //if(array_search($i, $nodeNumOfNeedData) || $i == 0) {
                //echo PHP_EOL."==========生データ===========".PHP_EOL;
                //echo 'node' . $i . '[' . $keyOfNode->text() . '] ==> ' . trim($node->filter('td')->text());
                //echo PHP_EOL."=====================".PHP_EOL;
            //}
        });
        return $orderData;
    }

}
