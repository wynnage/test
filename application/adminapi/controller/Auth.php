<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;
use think\Validate;

class Auth extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //接收参数
        $param=input();
        //验证数据
        $where=[];
        if (!empty($data['keyword'])){
            $where['auth_name']=['like',"%{$param['keyword']}%"];
        }
        //查询数据 将查询的数据转化为二维数组
        $data=model("Auth")->where($where)->select()->toArray();

        //无限极分类列表
        if (!empty($param['type']) && $param['type']=='tree'){
            //父子级树状列表  参数必须为数组
            $data=get_tree_list($data);
        }else{
            //无限极分类列表   参数必须为数组
            $data=get_cate_list($data);
        }
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
        $param=input();
        //验证数据
        $validate=$this->validate($param,[
            "auth_name|权限名称"=>"require",
            "pid|父级id"=>"require|number",
            "radio|菜单权限"=>"require|in:1,0"
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        //pid =父级id的族谱 _拼上父级id
        $data=model("Auth")->where("id",$param['pid'])->find();
        //判断当前记录是否为为顶级
        if ($param['pid']!=0){
            //查询出当前记录的父级pid_path 和 level
            $parent=model("Auth")->field("pid_path,level")->find($param['pid'])->toArray();
            //组装pid_path
            $param['pid_path']=$parent['pid_path']."_".$param['pid'];
            //组装level
            $param['level']=$parent['level']+1;
        }
        $res=model("Auth")->create($param,true);
        if ($res){
            $this->ok($res);
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
        //
        //定义路由
        //验证数据
        if (!is_numeric($id)){
            $this->fail("操作异常");
        }
        $data=model("Auth")->where('id',$id)->find();
        if ($data){
            $this->ok($data);
        }else{
            $this->fail();
        }
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
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
        //
        //接收数据
        $param=$request->param();
        //验证数据
        $validate=$this->validate($param,[
            "auth_name|权限名称"=>"require",
            "pid|父级id"=>"require|number",
            "is_nav|菜单权限"=>"require|in:1,0",
            "id|权限id"=>"require|number"
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        //pid =父级id的族谱 _拼上父级id
        $data=model("Auth")->where("pid",$param['pid'])->find()->toArray();
        //判断当前记录是否为为顶级
        if ($param['pid']!=0){
            //查询出当前记录的父级pid_path 和 level
            $parent=model("Auth")->field("pid_path,level")->find($param['pid'])->toArray();
            //组装pid_path
            $param['pid_path']=$parent['pid_path']."_".$param['pid'];
            //组装level
            $param['level']=$parent['level']+1;
        }
//        $param['is_nav']=$param['radio'];
        unset($param["/auths/$id"]);
        unset($param["radio"]);
        $res=model("Auth")->update($param,['id'=>$id]);
        if ($res){
            $this->ok($res);
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
        //验证
        if(!is_numeric($id)){
            $this->fail("操作异常！");
        }
        //该权限下有子权限时不能删除
        $data=model("Auth")->where("pid",$id)->find();
        if ($data){
            $this->fail("该权限下有子权限 不能删除");
        }
        //权限中有角色在使用时不能删除
        $role=model("Role")->column("role_auth_ids");
        $data=explode(",",implode(",",$role));
        if (in_array($id,$data)){
            $this->fail("该权限正在使用，请查证后删除");
        }
        //删除
        $res=model("Auth")::destroy($id);
        if ($res){
            $this->ok("删除成功");
        }else{
            $this->fail("删除失败");
        }




    }
    public function nav(){
        //获取当前管理员id
        $user_id=1;
        //查询权限 先查询角色id
        $data=model("Admin")::find($user_id);
        $role_id=$data['role_id'];
        //如果是超级管理员  查询全部
        if ($role_id == 1){
            $power=model("Auth")->select();
        }else{
            $role_auth_ids=model("Role")->where("id",$role_id)->value("role_auth_ids");
            //找出对应权限
            $power=model("Auth")->where("id","in",$role_auth_ids)->select();
        }
        //生成树状菜单
        $power=get_tree_list($power->toArray());
        $this->ok($power);

    }
}
