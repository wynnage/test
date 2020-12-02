<?php

namespace app\adminapi\controller;

use app\common\model\Admin;
use \tools\jwt\Token;
use think\Controller;

class Login extends BaseApi
{
    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 登录接口
     */

    public function login()
    {
        //获取输入变量
        $param = input();
        //根据验证码标识，从缓存取出session_id 并重新设置session_id
        session_id(cache('session_id_'.$param['uniqid']));
        //进行验证码校验 使用手动验证方法
        if (!captcha_check($param['code'], $param['uniqid'])) {
            //验证码错误
            $this->fail('验证码错误');
        }
        $validate = $this->validate($param, [
            'username' => 'require',
            'password' => 'require',
            'code' => 'require',
            'uniqid' => 'require'
        ]);
        if($validate !== true){
            $this->fail($validate);
        }

        //根据用户名和密码（加密后的密码），查询管理员用户表
        $info = Admin::where('username',$param['username'])->find();
        if ($info){
            if ($info['password']==md5(md5($param['password']))){
                //返回结果
                $data['token'] = Token::getToken($info['id']);
                $data['user_id'] = $info['id'];
                $data['username'] = $info['username'];
                $data['nickname'] = $info['nickname'];
                $data['email'] = $info['email'];
                //登录成功
                $this->ok($data);
            }
        } else {
            //用户名或者密码错误
            $this->fail('密码错误');
        }


    }
    //

    /**
     * 验证码接口
     */
    public function captcha(){
        //验证码标识
        $uniqid=uniqid(mt_rand(100000,999999));
        //返回数据  验证码图片路径 验证码标识
        $data=[
            "src"=>captcha_src($uniqid),
            "uniqid"=>$uniqid,
        ];
        $this->ok($data);
    }

    /**
     * @throws \Exception
     * 注销接口
     */
    public function logout(){
        //清空token 将需要清空的token存入缓存 再次使用时 会读取缓存进行判断
        $token=Token::getRequestToken();
        $delete_token=cache('delete_token')?:[];
        $delete_token[]=$token;
        cache('delete_token',$delete_token,86400);
        $this->ok();
    }

}

