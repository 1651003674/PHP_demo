<?php
namespace app\common\pay;
/**
 * 支付（支付宝、微信）的公共接口
 * User: Yyu
 * Date: 2018/04/24
 * Time: 14:17
 */

//微信支付
require_once 'WxPayApi.class.php';
require_once 'WxPayJsApiPay.class.php';
require_once 'WxPayData.class.php';
require_once 'WxPayConfig.class.php';
require_once 'CreatUuid.php';
//require_once 'alipay.php';

//支付宝
require_once ROOT_PATH.'application'.DS.'common'.DS.'pay'.DIRECTORY_SEPARATOR.'pay'.DIRECTORY_SEPARATOR.'zfb'.DIRECTORY_SEPARATOR.'aop'.DIRECTORY_SEPARATOR.'AopClient.php';
require_once ROOT_PATH.'application'.DS.'common'.DS.'pay'.DIRECTORY_SEPARATOR.'pay'.DIRECTORY_SEPARATOR.'zfb'.DIRECTORY_SEPARATOR.'aop'.DIRECTORY_SEPARATOR.'SignData.php';
require_once ROOT_PATH.'application'.DS.'common'.DS.'pay'.DIRECTORY_SEPARATOR.'pay'.DIRECTORY_SEPARATOR.'zfb'.DIRECTORY_SEPARATOR.'aop'.DIRECTORY_SEPARATOR.'request'.DIRECTORY_SEPARATOR.'AlipayTradeQueryRequest.php';
require_once ROOT_PATH.'application'.DS.'common'.DS.'pay'.DIRECTORY_SEPARATOR.'pay'.DIRECTORY_SEPARATOR.'zfb'.DIRECTORY_SEPARATOR.'aop'.DIRECTORY_SEPARATOR.'request'.DIRECTORY_SEPARATOR.'AlipayTradeRefundRequest.php';



class commonpay{
    /**
     * @name 订单退款（微信）
     * @param $out_trade_no
     * @param $total_fee
     * @param $refund_fee
     * @param $mchid
     * @param $appid
     * @param $key
     * @return \成功时返回，其他抛异常
     * @throws \WxPayException
     */
    public function com_WeChatRefund($out_trade_no,$total_fee,$refund_fee,$mchid,$appid,$key){
        define('APPID_w',$appid);   //微信分配的公众账号ID(企业号corpid即为此appId)
        define('MCHID_w',$mchid);   //微信支付分配的商户号
        define('KEY_w',$key);  //商户支付密钥
        define('apiclient_cert',ROOT_PATH.'application'.DS.'common'.DS.'pay'.DS.'PEM'.DS.$appid.DS.'apiclient_cert.pem');  //证书路径
        define('apiclient_key',ROOT_PATH.'application'.DS.'common'.DS.'pay'.DS.'PEM'.DS.$appid.DS.'apiclient_key.pem');  //证书路径
        $input  = new \WxPayRefund();  //申请退款

        $input->SetOut_trade_no($out_trade_no);  //设置订单编号
        $input->SetTotal_fee($total_fee);  //设置支付总金额
        $input->SetRefund_fee($refund_fee);  //设置退款金额
        $input->SetOp_user_id($mchid);  //设置商户号
        $com_crid = new \Common_CreatUuid();

        $input->SetOut_refund_no($com_crid->com_getMillisecond(1)); //设置商户退款单号

        $result = \WxPayApi::refund($input);  //退款结果
        return $result;
    }


    /**
     * 订单退款（支付宝）
     * @param $out_trade_no 主订单号
     * @param $refund_fee   退款总额 分
     * @param $appid    支付宝appid
     * @param $PrivateKey   支付宝私钥
     * @param $PublicKey    支付宝公钥
     * @return mixed
     * @throws Exception
     */
    public function com_AlipayRefund($out_trade_no,$refund_fee,$appid,$PrivateKey,$PublicKey){

        $aop = new \AopClient();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $appid;
        $aop->rsaPrivateKey = $PrivateKey;
        $aop->alipayrsaPublicKey = $PublicKey;
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='utf-8';
        $aop->format='json';
        $request = new \AlipayTradeRefundRequest();
        $com_crid = new \Common_CreatUuid();
        $out_request_no = $com_crid->com_getMillisecond(1);  //退款批次号
        $param = json_encode([
            'out_trade_no' => $out_trade_no,
            'refund_amount' => $refund_fee/100,
            'out_request_no' => $out_request_no,
        ]);
        $request->setBizContent($param);
        $result = $aop->execute($request);
        //echo 88;die;
        $result = (array)$result;
        $info['result'] = $result;
        $info['tkpch'] = $out_request_no;
        return $info;
    }


    /**
     * @name 获取会员退款批次号
     * @return string
     */
    public function com_getMillisecond(){
        $com_crid = new \Common_CreatUuid();

        return 'HYTK'.$com_crid->com_getMillisecond(1);
    }


    /**
     * @name 返回uuid 对象
     * @return \Common_CreatUuid
     */
    public function getUuid(){
        return new \Common_CreatUuid();
    }


    /*public function getAlipay(){
        return alipay::init();
    }*/

}
/*$com =new commonpay();
var_dump($com->getAlipay());*/

