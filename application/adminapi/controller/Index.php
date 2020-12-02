<?php
namespace app\adminapi\controller;

class Index extends BaseApi
{
    public function index()
    {
        //生成Token
        $token=\tools\jwt\Token::getToken(100);
        dump($token);
        //从token获取用户id
        $user_id=\tools\jwt\Token::getUserId($token);
        dump($user_id);die();
    }
}
