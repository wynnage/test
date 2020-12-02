<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;
use tools\jwt\Token;

class Admin extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //接收参数
        $keyword=input("keyword");
        //拼接条件
        $where=[];
        if (!empty($keyword)){
            $where['username']=["like","%{$keyword}%"];
        }
        //查询数据集
        $data=model("Admin")->join("role","role.id=pyg_admin.role_id")->where($where)->paginate(2);
        //返回结果
        return json(['code'=>200,"msg"=>"success","data"=>$data]);

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
        $data=$request->param();
        //验证数据
        $validate=$this->validate($data,[
           "username|用户名"=>"require",
            "email|邮箱"=>"require|email",
            "role_id|所属角色id"=>"require|number",
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        //处理数据
        //处理密码 如果没有 默认为123456
        $data['password']=md5(md5(input("password","123456")));
        //处理昵称 如果没有就和用户名保持一致
        $data['nickname']=input("nickname",$data['username']);
        //添加入库
        $res=model("Admin")->allowField(true)->save($data);
        $data=model("Admin")->where("username",$data['username'])->find();
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
            $this->fail("操作异常");
        }
        //查询一条
        $data=model("Admin")->find($id);
        if ($data){
            $this->ok($data);
        }else{
            $this->fail();
        }

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
        $data=$request->param();
        //处理数据
        //处理密码 如果没有 默认为123456
        $data['password']=md5(md5(input("password","123456")));
        //处理昵称 如果没有就和用户名保持一致
        $data['nickname']=input("nickname",$data['username']);
        //判断是否要重置密码
        if (isset($data['type']) && $data['type']=="reset_pwd"){
            //重置密码
            model("Admin")::update(['password'=>md5(md5($data['password']))],['id',$id]);
        }else{
            //验证数据
            $validate=$this->validate($data,[
                "username|用户名"=>"require",
                "email|邮箱"=>"require|email",
                "role_id|所属角色id"=>"require|number",
                "id"=>"require|number"
            ]);
            if ($validate !== true){
                $this->fail($validate);
            }
            //如果传递有密码 删除该字段
            if (isset($data['password'])){
                unset($data['password']);
            }
            //修改数据
            model("Admin")->allowField(true)->save($data,['id'=>$id]);
        }
        $data=model("Admin")->find($id);
        $this->ok($data);

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
        //管理员不能自己删除自己  从token中取值
        $user_id=Token::getUserId("id");
        if ($id == $user_id){
            $this->fail("不能删除自己");
        }
        //超级管理员不能删除
        $res=model("Admin")->where("id",$id)->find();
        if ($res['role_id']==1){
            $this->fail("不能删除管理员");
        }

        $res=model("Admin")->where("id",$id)->delete();
        if ($res){
            $this->ok();
        }else{
            $this->fail();
        }
    }
}
