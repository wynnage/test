<?php
if(!function_exists("get_cate_list")){
    //返回无限级分类菜单
    function get_cate_list($list,$pid=0,$f=0){
        //声明一个静态数组存储处理后的数据
        static $arr = [];
        foreach($list as $val){
            if($val['pid']==$pid){
                $val['level'] = $f;
                $arr[]=$val;
                get_cate_list($list,$val['id'],$f+1);
            }
        }
        return $arr;
    }
}

if(!function_exists('get_tree_list')){
    //引用方式实现 父子级树状结构
    function get_tree_list($list){
        //将每条数据中的id值作为其下标
        $temp = [];
        foreach($list as $v){
            $v['son'] = [];
            $temp[$v['id']] = $v;
        }
        //获取分类树
        foreach($temp as $k=>$v){
            $temp[$v['pid']]['son'][] = &$temp[$v['id']];
        }
        return isset($temp[0]['son']) ? $temp[0]['son'] : [];
    }
}
