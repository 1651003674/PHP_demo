<?php



class Createuuid{
    /**
     * 产生随机数字字符串
     * @param    int        $length  输出长度
     * @param    string     $chars   可选的 ，默认为 0123456789
     * @return   string     字符串
     */
    public function random($length, $chars = '0123456789') {
        $hash = '';
        $max = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }
    /**
     * 生成36位唯一id
     * @Author Yyu 2017-12-28
     * @return uuid
     */
    public function com_CreatUuid(){
        $guid = $this->guid();
        //$regex = "/-|{|}/";
        //$guid = strtolower(preg_replace($regex,"",$guid));
        $string = $this->generate_password(4);
        return $guid.$string;
    }
    /**
     * 生成guid（32位）
     * @Author Yyu 2017-12-28
     * @return guid
     */
    public function guid(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);
            $uuid = chr(123)
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);
            return strtolower(preg_replace("/-|{|}/","",$uuid));
        }
    }
    /**
     * 生成guid（16位）
     * @Author Yyu 20180514
     * @return guid
     */
    public function guid_16(){
        mt_srand((double)microtime()*10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);
        $uuid = chr(123)
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .chr(125);
        return strtolower(preg_replace("/-|{|}/","",$uuid));
    }
    /**
     * 生成随机字符串
     * @Author Yyu 2017-12-28
     * @param  $length    随机字符串长度
     * @return string
     */
    public function generate_password($length) {
        $chars = '0123456789abcdefghijklmnpqrstuvwxyz';
        $password = '';
        for ( $i = 0; $i < $length; $i++ )
        {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组 $chars 的任意元素
            // $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
            $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $password;
    }
    /**
     * 获取当前时间的毫秒
     * @author: Yyu  20171228
     * @param  $lx      类型：1：返回yyyymmddhhmmssmis类型；2：返回yyyy-mm-dd hh:mm:ss.mis类型
     *  3:返回yyyymmddhhmmss.三位随机数类型；4：返回yyyymmddhh.七位随机数；5：返回yyyymmddhhmmssmis.七位随机数类型；6：返回yyyymmddhhmmssmis.十三位随机数类型；7：返回TE.yyyymmddhhmmssmis.十一位随机数类型；
     * @return  返回毫秒值
     */
    public function com_getMillisecond($lx){
        list($t1, $t2) = explode(' ',microtime());
        $timestap = (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
        $Millisecond = substr($timestap,-3);
        $time = substr($timestap,0,10);
        if($lx==1){
            return date('YmdHis',$time).$Millisecond;
        }
        else if($lx==2){
            return date('Y-m-d H:i:s',$time).'.'.$Millisecond;
        }
        else if($lx==3){
            $sjs = mt_rand(100,999);  //三位随机数
            return date('YmdHis',$time).$sjs;
        }
        else if($lx==4){
            $sjs = mt_rand(1000000,9999999);  //七位随机数
            return date('YmdH',$time).$sjs;
        }
        else if($lx==5){
            $chars = '123456789';
            $string = '';
            for ($i = 1; $i <= 8; $i++) {
                $string .= $chars[mt_rand(0, strlen($chars) - 1)];
            }
            return date("YmdHis").$Millisecond.$string;
        }
        else if($lx==6){
            $sjs = $this->random(13);  //十三位随机数
            return date('YmdHis',$time).$Millisecond.$sjs;
        }
        else if($lx==7){
            $chars = '0123456789ABCDEF';
            $sjs = $this->random(11,$chars);  //十一位随机数
            return 'TE'.date('YmdHis',$time).$Millisecond.$sjs;
        }
    }
}

//$th = new Common_CreatUuid();
//echo $th->com_CreatUuid();