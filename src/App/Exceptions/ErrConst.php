<?php

namespace LaravelDev\App\Exceptions;

class ErrConst
{
    const UserNotLoggedIn = ['message' => '用户未登录', 'description' => '请重新登录', 'showType' => 3, 'code' => 10000];
    const AccountPasswordError = ['message' => '账号密码错误', 'description' => '请重新输入账号和密码', 'showType' => 3, 'code' => 10001];
    const AccountPasswordNotEqual = ['message' => '两次输入的密码不一致', 'description' => '请重新输入密码', 'showType' => 3, 'code' => 10002];
    const PerPageIsNotAllow = ['message' => '每页记录数不在允许的范围内', 'code' => 10011];
}