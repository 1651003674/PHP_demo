<?php
/**
 * 康邻商城支付宝支付异步验证
 * User: Yyu
 * Date: 20180806
 * Time: 20:01
 */
@header("Content-Type: text/html; charset=utf-8");
require_once '../../../Public/init.php';
require_once '../zfb/aop/AopClient.php';
require_once '../zfb/aop/SignData.php';
require_once '../zfb/aop/request/AlipayTradeQueryRequest.php';
require_once '../zfb/aop/request/AlipayTradeRefundRequest.php';
require_once API_ROOT.'/sm/Model/Store.php';
require_once API_ROOT.'/sm/Common/CreatUuid.php';
require_once API_ROOT.'/sm/Common/PublicFunction.php';
//require_once API_ROOT.'/sm/Domain/Store.php';
require_once API_ROOT.'/Library/phpResque/project/queue.php';
//接收传送的数据
$fileContent = file_get_contents("php://input");
$list = array();
$list = explode('&',$fileContent);
foreach ($list as $k=>$v){
    $list[$k] = explode('=',$v);
}
foreach ($list as $k1=>$va){
    $name=$va[0];
    $value=$va[1];
    $data_list[$name]= $value;
}
DI()->logger->log("ZFBSCZFYB", "支付宝商城支付异步返回数据", $data_list);
$o = new Model_Store();
$ddbh = $data_list['out_trade_no'];
$zddxx = $o->mod_getOrder($ddbh);  //获取主订单信息
$serialize = $zddxx['serialize'];
$total_price = $zddxx['total_price'];
$zfpzxx = $o->mod_getZfpzxx($serialize,2);  //获取支付配置信息
$rsaPrivateKey = $zfpzxx[0]['rsa_private_key'];
$app_id = $zfpzxx[0]['appid'];
$rsaPublicKey = $zfpzxx[0]['rsa_public_key'];
/*$data_list = '{"gmt_create":"2018-08-08+09%3A42%3A28","charset":"utf-8","seller_email":"liangy%40commlink.cn","subject":"%E6%88%90%E9%83%BD%E5%BA%B7%E9%82%BB%E7%A7%91%E6%8A%80%E6%97%A0%E4%BA%BA%E9%9B%B6%E5%94%AE%E5%95%86%E5%93%81%E8%AE%A2%E5%8D%95","sign":"Mu9Kn%2Fqq4abHsxsbJuu%2BUPDFr5BWGkL%2B%2Femq0YjqY90H6AI6dC9R2%2FXjbYPQjjL9uyx9sr6G9FYCVSvP9TEVfGLnFR4u0p0Qsq8u0bLa%2FUvRP5fElkHXfF9vc7Z34drH0csXT8Yy0HKYnpCxlJe3jCXuEP5tE0A5xk%2BJ6FlsBMWBMNk%2Bjq4lTEtNQ7kA2SBKY8m9dBGM27BFXO4mntmeXxjODJCjujb5DK5ffNacAPZx6Fj1rGKc62nPTVND9AjyqIQ2IqVptRJqooG2FuhGdTCcbVHgoipB1M0VrEZOgUs4Grbl4TrSvCNNQlgk9ijAEdjKC%2FLDVn6VCAcA%2F9ApqQ%3D%3D","body":"%E5%95%86%E5%93%81%E8%B4%AD%E4%B9%B0","buyer_id":"2088702007669434","invoice_amount":"0.01","notify_id":"4334cecadb0a40b6a251dc5cc4cd3c6jbl","fund_bill_list":"%5B%7B%22amount%22%3A%220.01%22%2C%22fundChannel%22%3A%22PCREDIT%22%7D%5D","notify_type":"trade_status_sync","trade_status":"TRADE_SUCCESS","receipt_amount":"0.01","app_id":"2017110209679645","buyer_pay_amount":"0.01","sign_type":"RSA2","seller_id":"2088721776349114","gmt_payment":"2018-08-08+09%3A42%3A29","notify_time":"2018-08-08+09%3A42%3A30","version":"1.0","out_trade_no":"TE20180808094210891E4ED8C1078E","total_amount":"0.01","trade_no":"2018080821001004430555886715","auth_app_id":"2017110209679645","buyer_logon_id":"183****8669","point_amount":"0.00"}';
$data_list = json_decode($data_list,true);*/
$res = zfbddztcx($ddbh,$app_id,$rsaPrivateKey,$rsaPublicKey,$data_list,$serialize,$total_price); //验证订单,并返回结果
echo $res;


//支付宝订单查询支付状态并修改订单
function zfbddztcx($ddbh,$app_id,$rsaPrivateKey,$rsaPublicKey,$data_list,$serialize,$total_price){
    $aop = new AopClient ();
    $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
    $aop->appId = $app_id;
    $aop->rsaPrivateKey =$rsaPrivateKey;
    $aop->alipayrsaPublicKey=$rsaPublicKey;
    $aop->apiVersion = '1.0';
    $aop->signType = 'RSA2';
    $aop->postCharset='UTF-8';
    $aop->format='json';
    $request = new \AlipayTradeQueryRequest();
    $request->setBizContent("{" ."\"out_trade_no\":\"$ddbh\"" ."}");
    $result = $aop->execute ($request);
    $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
    $resultCode = $result->$responseNode->code;
    //DI()->logger->log("YZDD", "验证订单", $resultCode);
    if(!empty($resultCode) && $resultCode == 10000){  //订单验证通过
        if($data_list['total_amount']*100==$total_price){  //金额验证通过
            $o = new Model_Store();
            $ddxx = $o->mod_operationOrder($ddbh,$data_list['trade_no'],$data_list['buyer_logon_id']);  //获取订单信息，并将订单移到历史表
            if($ddxx){  //操作订单成功
                /*$dm = new Domain_Store();
                $dm->dm_StartShipments($serialize,$ddxx); */ //发起出货协议
                $o = new Queue();
                $o->inQueue('store','My_Job',array('serialize'=>$serialize,'hdxx'=>$ddxx));  //发起出货协议
                return 'success';
            }
            else{  //操作订单失败
                DI()->logger->debug('商城支付宝异步回调操作订单失败'.$ddbh);
                return 'failure';
            }
        }
        else{  //金额验证失败
            DI()->logger->debug('商城支付宝异步回调金额验证失败'.$ddbh);
            return 'failure';
        }
    } else {  //订单验证失败
        DI()->logger->debug('商城支付宝异步回调订单验证失败'.$ddbh);
        return 'failure';
    }
}

