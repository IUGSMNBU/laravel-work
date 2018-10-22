<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\ControllerDispatcher;

class TestController extends Controller
{
    protected $response;
    public function __construct()
    {
        //$this->response = $this->run();
    }

    //
    public function index()
    {
        $a = "hello";

       // echo app("ControllerDispatcher")->controller;
       //dd($GLOBALS["kernel"]->getRoute()->getRoutess());
        $b = 123;
        //var_dump($this->app['router']->getRoutesDispatched());
        //return  app()->publicPath();
        //return action("index");
        //return app()->controller();
        //return app()->route->getRuleList();

        //return $this->run();
        //return $this->response->show();
        //return $this->run();
        return 'index';
    }

    public function show(Request $request)
    {
        return 'hi';
    }
}
