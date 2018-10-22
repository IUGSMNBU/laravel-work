<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function run()
    {
        $action = request()->route()->getActionMethod();
        $action1 = request()->route()->getActionName();
        $action2 = request()->decodedPath();

        $controller = explode("@",$action1)[0];
        $action = explode("@",$action1)[1];

echo $action2;
       return (new $controller)->show();

    }
}
