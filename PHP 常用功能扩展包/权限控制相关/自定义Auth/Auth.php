<?php
//==============================================================================
// 名    称：康邻无人零售运营管理平台
// 概    要：未登录用户拦截 跳转登录页
// 版    权：Copyright © 成都康邻科技有限公司
// 作    者: 黄 超
// 创建日期：2018\6\26 0026 23:05
//==============================================================================


namespace app\admin\controller;

use think\cache\driver\Redis;
use think\Controller;
use think\Db;
use think\Session;
use app\common\Auth\Auth as inAuth;
class Auth extends Controller
{
    protected $auth;

    function __construct()
    {

        //引用父类 构造函数
        parent::__construct();

        //判断是否登录
        if(!Session::get('user_role_id')) {

            //生成登录页面url
             $url = 'http://'.request()->host().url('admin/login/index');

            //未登录状态 跳转到登录页面
            header("Location:$url");
        }

        $this->auth = inAuth::getInstance();
//
//        $this->auth->setRoleArr([1,2]);
//
//         print_r(request()->pathinfo());
//        die( var_dump($this->auth->check(2)));
    }



}


