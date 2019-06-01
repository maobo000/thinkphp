<?php

namespace app\http\middleware;

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
