<?php
/**
 * 康邻商城微信公众号支付异步验证
 * User: Yyu
 * Date: 20180806
 * Time: 20:01
 */
@header("Content-Type: text/html; charset=utf-8");
require_once '../../../Public/init.php';
require_once '../wx/WxPayConfig.class.php';
require_once '../wx/WxPayApi.class.php';
require_once '../wx/WxPayJsApiPay.class.php';
require_once '../wx/WxPayData.class.php';
require_once API_ROOT.'/sm/Model/Store.php';
//require_once API_ROOT.'/sm/Domain/Store.php';
require_once API_ROOT.'/sm/Common/PublicFunction.php';
require_once API_ROOT.'/sm/Common/CreatUuid.php';
require_once API_ROOT.'/Library/phpResque/project/queue.php';
/*define('APPSECRET_w',DI()->config->get('app.PayConfig.APPSECRET_WX'));//公众帐号secert（仅JSAPI支付的时候需要配置)
define('SSLCERT_PATH_w','./cert_zs_app/apiclient_cert.pem');
define('SSLKEY_PATH_w','./cert_zs_app/apiclient_key.pem');*/
//接收传送的数据
$fileContent = file_get_contents("php://input");
$arr = xmlToArray($fileContent); //返回的订单数组信息
/*$b = '{"appid":"wx342e908b86b8c3a8","attach":"pay","bank_type":"CFT","cash_fee":"11","fee_type":"CNY","is_subscribe":"Y","mch_id":"1488963282","nonce_str":"GVXA5rnoIKmTHc5j","openid":"oddcDw7N5XJYtSzUFOymFDih4Lmc","out_trade_no":"TE201809071015144494D63C05C40A","result_code":"SUCCESS","return_code":"SUCCESS","sign":"C8BAFE62BB53CB6467788756AA78EBD0","time_end":"20180907101519","total_fee":"11","trade_type":"JSAPI","transaction_id":"4200000175201809076286185687"}';
$arr = json_decode($b,true);*/
//print_r($arr);die;
DI()->logger->log("WXSCZFYB", "微信商城公众号支付异步返回数据", $arr);
$o = new Model_Store();
$ddid = $arr['out_trade_no'];  //订单id
$zddxx = $o->mod_getOrder($ddid);  //获取主订单信息
$serialize = $zddxx['serialize'];
$total_price = $zddxx['total_price'];
$zfpzxx = $o->mod_getZfpzxx($serialize,1);  //获取支付配置信息
//print_r($serialize);die;
$key = $zfpzxx[0]['wx_key'];
define('APPID_w',$zfpzxx[0]['appid']);   //微信分配的公众账号ID(企业号corpid即为此appId)
define('MCHID_w',$zfpzxx[0]['shh']);   //微信支付分配的商户号
define('KEY_w',$key);  //商户支付密钥
echo updateOrder($arr,$key,$serialize,$total_price,$ddid);  //返回结果
function updateOrder($arr,$key,$serialize,$total_price,$ddid){
    if($arr){
        ksort($arr);// 对数据进行排序
        $str = ToUrlParams($arr,$key);//对数据拼接成字符串
        $user_sign = strtoupper(md5($str));  //签名
        if($user_sign==$arr['sign']){  //签名正确
            $yzdd = wxzfztcs($ddid); //验证订单
            if($yzdd['code']==200){  //验证通过
                if($yzdd['total_fee']==$total_price){  //金额验证通过
                    $o = new Model_Store();
                    $ddxx = $o->mod_operationOrder($ddid,$arr['transaction_id'],$arr['openid']);  //获取订单信息，并将订单移到历史表
                    //print_r($ddxx);die;
                    /*$ddxx = '[{"id":"201808071615562714949478737598","form_id":"201808071615562714949478737598","pay_price":"320","pay_num":"1","tast_id":"","cheap_money":"0","user_product_id":"U20180529161911","user_product_name":"\u5eb7\u5e08\u5085\u8702\u871c\u7eff\u8336","user_product_img":"http:\/\/j.commlink.cn\/upfile\/9920171027005859\/img\/2018\/5\/0403437b1a8f214b501b53618b1cc5d2233e.jpg","serial_port":"[{\"A01\":2}]","serial_port_code":"","callback_serial_port_ok":"","callback_serial_port_fail":"","status":"0","create_time":"2018-08-07 16:15:56","p_month":"","p_day":"","serial_port_id":"","msg":"","resent_cmd":"","serial_board_code":"","source_price":"320","unique_id":"63839","serial_com":"COM1","is_mail":"0","mail_address":"","mail_company":"","mail_number":"","do_man_id":"","do_time":""}]';*/
                    //$ddxx = json_decode($ddxx,true);
                    //print_r($zddxx);die;
                    if($ddxx){  //操作订单成功
                        /*$dm = new Domain_Store();
                        $dm->dm_StartShipments($serialize,$ddxx); */ //发起出货协议
                        $o = new Queue();
                        $o->inQueue('store','My_Job',array('serialize'=>$serialize,'hdxx'=>$ddxx));
                        return '<xml><return_code>SUCCESS</return_code><return_msg>OK</return_msg></xml>';
                        //return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                    }
                    else{  //操作订单失败
                        DI()->logger->debug('商城微信异步回调操作订单失败'.$ddid);
                        return '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[操作订单失败]]></return_msg></xml>';
                        //return '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[操作订单失败]]></return_msg></xml>';
                    }
                }
                else{  //金额验证失败
                    DI()->logger->debug('商城微信异步回调金额验证失败'.$ddid);
                    return '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[金额不一致]]></return_msg></xml>';
                }
            }
            else{  //订单验证失败
                DI()->logger->debug('商城微信异步回调订单验证失败'.$ddid);
                return '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[订单不存在]]></return_msg></xml>';
            }
        }
        else{   //签名错误
            DI()->logger->debug('商城微信异步回调订单签名错误'.$ddid);
            return '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
        }
    }
}





//echo wxzfztcs('5af40aa12cfbf');
//查询微信订单支付状态
function wxzfztcs($ddbh){
    $pay = new \WxPayApi();
    $input = new \WxPayRefundQuery();
    $input->SetOut_trade_no($ddbh);
    //$input->SetTransaction_id($ddbh);
    $cx_order = $pay->orderQuery($input);  //查询订单状态
    //先查询微信订单
    if($cx_order['return_code'] == 'SUCCESS' && $cx_order['trade_state'] == 'SUCCESS' && $cx_order['return_msg'] == 'OK' && $cx_order['result_code'] == 'SUCCESS'){
        $retrun['code']=200;
        $retrun['total_fee']=$cx_order['total_fee'];
        $retrun['openid']=$cx_order['openid'];
        $retrun['transaction_id']=$cx_order['transaction_id'];
        return $retrun;
    }else{
        return $retrun['code']=400;
    }
}
function xmlToArray($xml){
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    $val = json_decode(json_encode($xmlstring),true);
    return $val;
}

/**
 * 格式化参数格式化成url参数
 */
function ToUrlParams($arr,$key)
{
    $weipay_key = $key;//微信的key,这个是微信支付给你的key，不要瞎填。
    $buff = "";
    foreach ($arr as $k => $v)
    {
        if($k != "sign" && $v != "" && !is_array($v)){
            $buff .= $k . "=" . $v . "&";
        }
    }

    $buff = trim($buff, "&");
    return $buff.'&key='.$weipay_key;
}