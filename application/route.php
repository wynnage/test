<?php
use \think\Route;

//后台接口路由
Route::domain("admin",function (){
    //默认首页  adminapi.txyj.com  访问到 adminapi/index/index
    Route::get("/","adminapi/Index/index");
    //在这配置adminapi模块下的其他路由

    //登录模块资源
    Route::post("login","adminapi/Login/login");
    //获取验证码的接口
    //访问图片需要的路径 生成验证码
    Route::get("captcha/:id","\\think\\captcha\\CaptchaController@index");
    //设置验证码的接口路由
    Route::get("captcha","adminapi/Login/captcha");
    //登录接口
    Route::post("login","adminapi/Login/login");
    //注销接口
    Route::get("logout","adminapi/Login/logout");

    //权限接口路由
    Route::resource("auths","adminapi/auth");

    //菜单权限路由
    Route::get("nav","adminapi/auth/nav");

    //角色接口路由
    Route::resource("roles","adminapi/role");

    //管理员接口路由
    Route::resource("admins","adminapi/admin");

    //品牌管理接口路由
    Route::resource("brands","adminapi/brand");
});
