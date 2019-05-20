<?php
/**
 * Created by PhpStorm.
 * User: qingyun
 * Date: 19/5/20
 * Time: 下午9:05
 */

namespace app\admin\controller;


use think\Controller;

class Index extends  Controller
{
    public function index(){
        return $this->fetch();
    }
}