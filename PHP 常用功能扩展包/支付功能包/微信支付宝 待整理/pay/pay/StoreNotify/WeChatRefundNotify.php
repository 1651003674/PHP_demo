<?php
/**
 * 康邻微信小程序退款异步验证
 * User: Yyu
 * Date: 20180809
 * Time: 21:01
 */
@header("Content-Type: text/html; charset=utf-8");
require_once '../../../Public/init.php';
require_once '../wx/WxPayApi.class.php';
require_once '../wx/WxPayJsApiPay.class.php';
require_once '../wx/WxPayData.class.php';
require_once API_ROOT.'/sm/Model/Store.php';
require_once API_ROOT.'/sm/Domain/Store.php';
require_once API_ROOT.'/sm/Common/PublicFunction.php';
require_once API_ROOT.'/sm/Common/CreatUuid.php';
$key = DI()->config->get('app.PayConfig.KEY_WX');
//接收传送的数据
$fileContent = file_get_contents("php://input");
$arr = xmlToArray($fileContent); //返回的订单数组信息
DI()->logger->log("WXXCXTKYB", "微信小程序退款异步返回数据", $arr);

function xmlToArray($xml){
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    $val = json_decode(json_encode($xmlstring),true);
    return $val;
}