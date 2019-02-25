<?php
/**
 * 小程序支付宝退款异步验证
 * User: Yyu
 * Date: 20180809
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
require_once API_ROOT.'/sm/Domain/Store.php';
$rsaPrivateKey = DI()->config->get('app.PayConfig.PrivateKey');
$app_id= DI()->config->get('app.PayConfig.ZFBAPPID');
$rsaPublicKey = DI()->config->get('app.PayConfig.PublicKey');
//接收传送的数据
$fileContent = file_get_contents("php://input");
DI()->logger->log("ZFBTKYB", "支付宝退款异步返回数据", $fileContent);
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
DI()->logger->log("ZFBXCXTKYB", "支付宝小程序退款异步返回数据", $data_list);