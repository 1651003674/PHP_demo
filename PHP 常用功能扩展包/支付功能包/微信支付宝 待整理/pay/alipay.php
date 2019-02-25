<?php
namespace app\common\pay;
//支付宝支付
require_once ROOT_PATH.'application'.DS.'common'.DS.'pay'.DIRECTORY_SEPARATOR.'pay'.DIRECTORY_SEPARATOR.'zfb'.DIRECTORY_SEPARATOR.'aop'.DIRECTORY_SEPARATOR.'AopClient.php';
require_once ROOT_PATH.'application'.DS.'common'.DS.'pay'.DIRECTORY_SEPARATOR.'pay'.DIRECTORY_SEPARATOR.'zfb'.DIRECTORY_SEPARATOR.'aop'.DIRECTORY_SEPARATOR.'SignData.php';
require_once ROOT_PATH.'application'.DS.'common'.DS.'pay'.DIRECTORY_SEPARATOR.'pay'.DIRECTORY_SEPARATOR.'zfb'.DIRECTORY_SEPARATOR.'aop'.DIRECTORY_SEPARATOR.'request'.DIRECTORY_SEPARATOR.'AlipayTradeQueryRequest.php';
require_once ROOT_PATH.'application'.DS.'common'.DS.'pay'.DIRECTORY_SEPARATOR.'pay'.DIRECTORY_SEPARATOR.'zfb'.DIRECTORY_SEPARATOR.'aop'.DIRECTORY_SEPARATOR.'request'.DIRECTORY_SEPARATOR.'AlipayTradeRefundRequest.php';
require_once 'CreatUuid.php';

class alipay{
    private static $init = null;

    public static function init(){
        if (self::$init){
            return self::$init;
        }else{
            self::$init = new static([]);
            return self::$init;
        }

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
        $result = (array)$result;
        $info['result'] = $result;
        $info['tkpch'] = $out_request_no;
        return $info;
    }


    public function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }
    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
    public static function curlPost($url = '', $postData = '', $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
//    public static function arrayToXml($arr)
//    {
//        $xml = "<xml>";
//        foreach ($arr as $key => $val) {
//            if (is_numeric($val)) {
//                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
//            } else
//                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
//        }
//        $xml .= "</xml>";
//        return $xml;
//    }
}
