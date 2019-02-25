<?php
/**
 * Created by PhpStorm.
 * User: 黄 超
 * Date: 2018\9\25 0025
 * Time: 13:33
 */
namespace app\common\Auth;

class Auth
{
    private static $instance;

    private $redis;//redis 实例

    private $saveType = 1;//权限保存模式 1/session     2/redis

    private function __construct(){

        if($this->saveType == 2){

            $this->redis = new \Redis();//创建redis 实例
            $this->redis->connect();
        }
    }

    //获取实例
    public static function getInstance()
    {

        if (!isset(self::$instance)) {

            self::$instance = new self();
        }

        return self::$instance;
    }

    //保存权限列表
    public function setRoleArr($roleArr=[]){

        switch ($this->saveType){

            case 1://session

                return session('roleArrJson',json_encode(array_filter($roleArr)));
                break;
            case 2://redis

                return $this->redis->hSet('roleArrJson',session('user_id'),json_encode(array_filter($roleArr)));
                break;
        }
    }

    //读取权限列表
    public function getRoleArr(){

        switch ($this->saveType){

            case 1://session

                return json_decode(session('roleArrJson'));
                break;
            case 2://redis

                return json_decode($this->redis->hGet('roleArrJson',session('user_id')));
                break;
        }
    }

    //鉴权
    public function check($authName = ''){

        $authArr = $this->getRoleArr();

        return in_array(trim($authName),$authArr)?true:false;
    }

    //禁止克隆
    private function __clone()
    {
        // TODO: Implement __clone() method.
    }
}