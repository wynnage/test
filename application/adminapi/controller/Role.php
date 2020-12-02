<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;

class Role extends BaseApi
{
    /**
     * 显示角色列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //查询数据
        $data=model("Role")->select();
        //返回结果
        $this->ok($data);
    }


    /**
     * 保存新增的角色
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //接收数据
        $data=input();
        //验证数据
        $validate=$this->validate($data,[
            "role_name|角色名"=>"require",
            "auth_ids|拥有的权限"=>"require"
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        $data['role_auth_ids']=$data['auth_ids'];
        //添加入库
        $res=model("Role")->allowField(true)->save($data);

        //返回结果
        if ($res){
            $this->ok($data);
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
            $this->fail("操作异常！！");
        }
        //查询一条
        $data=model("Role")->find($id);
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
        $data=input();
        //验证数据
        $validate=$this->validate($data,[
            "role_name|角色名"=>"require",
            "auth_ids|拥有的权限"=>"require",
            "id|角色id"=>"require|number"
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        $data['role_auth_ids']=$data['auth_ids'];
        //修改数据
        $res=model("Role")->allowField(true)->save($data,['id'=>$id]);

        //返回结果
        if ($res){
            $this->ok($data);
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
        if(!is_numeric($id)){
            $this->fail("操作异常");
        }
        //有用户在使用的角色不能被删除
        $data=model("Admin")->where("role_id",$id)->find();
        if ($data){
            $this->fail("该角色正在被使用，请查证后在删除");
        }
        $res=model("Role")->where("id",$id)->delete();
        if ($res){
            $this->ok();
        }else{
            $this->fail();
        }
    }
}
