<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;

class Brand extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //接收参数
        $keyword=input('keyword');
        $cate_id=input('cate_id');
        $where=[];
        //验证参数
        if (isset($keyword)){
            $where['name']=['like',"%{$keyword}%"];
        }
        if (isset($cate_id)){
            $where['cate_id']=$cate_id;
        }
        //查询数据
        $data=model("Brand")->where($where)->select();
        //返回数据
        $this->ok($data);

    }


    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //接收数据
        $param=$request->param();
        //验证数据
        $validate=$this->validate($param,[
           'name'=>'require',
            'logo'=>'require',
            'url'=>'require|url',
            'sort'=>'require|number'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        //添加入库
        $res=model("Brand")->allowField(true)->save($param);
        //返回数据
        if ($res){
            $this->ok();
        }else{
            $this->fail();
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //验证id
        if (!is_numeric($id)){
            $this->fail("操作异常");
        }
        //查询一条
        $data=model("Brand")->find($id);
        //返回数据
        $this->ok($data);
    }


    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //接收数据
        $param=$request->param();
        //验证数据
        $validate=$this->validate($param,[
            'name'=>'require',
            'logo'=>'require',
            'url'=>'require|url',
            'sort'=>'require|number'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        //修改参数
        $res=model("Brand")->allowField(true)->save($param,['id'=>$id]);
        //返回数据
        if ($res){
            $this->ok();
        }else{
            $this->fail();
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //验证id
        if (!is_numeric($id)){
            $this->fail("操作异常");
        }
        //判断条件
        $res=model("Goods")->where('brand_id',$id)->find();
        if ($res){
            $this->fail("该品牌下有商品不能删除");
        }
        //删除数据
        $res=model("Brand")::destroy($id);
        //返回数据
        if ($res){
            $this->ok();
        }else{
            $this->fail();
        }
    }
}
