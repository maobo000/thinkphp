<?php
/**
 * Created by PhpStorm.
 * User: qingyun
 * Date: 19/5/29
 * Time: 下午4:44
 */


namespace app\http\prevent;

class Login
{
    public function handle($request, \Closure $next)
    {
        if (!session('adminLoginInfo')){
            return redirect('admin/Login/in');
        }
        return $next($request);
    }
}
