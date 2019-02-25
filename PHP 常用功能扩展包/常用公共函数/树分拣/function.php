<?php

/**
 *菜单列表无限分类 一次查询  分拣器
 * @param $array            要分拣的一维数组
 * @param int $parent_id    开始层级id 父级id
 * @param string $pidField  父id字段/键
 * @return array            返回结果 树结构数组
 * @author : 黄 超
 * @dateTime : 2018/6/26    18:00
 * -------------------------- 修改履历 2018/6/26--------------------------
 * @editor :
 * @editContent : 修改内容
 */
function menuListGroupOrder($array,$parent_id = 0,$pidField = 'parent_id'){

    //定义返回 存储数组
    $res  = [];

    //声明循环中$res 的递加 K 标识 默认0开始
    $key = 0;

    //开始循环分拣
    foreach ($array as $value){

        // 比对当前遍历数据是否属于当前父级
        if($parent_id ==  $value[$pidField]){

//            //将匹配的子级写入当前父级
//            $res[$key] = $value;
//
//            //继续向下递归分拣
//            $res[$key]['son'] = menuListGroupOrder($array,$value['id'],$pidField);
//
//            //为结果数组生成新的 K
//            $key++;

            //将匹配的子级写入当前父级
//            $res[$key] = $value;

            //继续向下递归分拣
            $value['son'] = menuListGroupOrder($array,$value['id'],$pidField);
            $res[] = $value;
            //为结果数组生成新的 K
//            $key++;
        }
    }

    // 返回结果
    return $res;
}

