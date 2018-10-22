<?php

namespace Illuminate\Routing;

use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response as PsrResponse;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Illuminate\Contracts\View\Factory as ViewFactoryContract;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;
use Illuminate\Routing\Contracts\ControllerDispatcher as ControllerDispatcherContract;

class RoutingServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //该register方法会自动运行，在框架启动的时候
        $this->registerRouter();

        $this->registerUrlGenerator();

        $this->registerRedirector();

        $this->registerPsrRequest();

        $this->registerPsrResponse();

        $this->registerResponseFactory();

        //注册控制器调度器
        $this->registerControllerDispatcher();
    }

    /**
     * Register the router instance.
     *
     * @return void
     */
    protected function registerRouter()
    {
        //router=匿名类（运行时返回路由实例）
        $this->app->singleton('router', function ($app) {
            //实例化路由实例，此参数$app是Applicaton的实例，并将events对应的匿名函数传递
            //events= $this->app->singleton('events', function ($app) {
            //队列管理器
            //return (new Dispatcher($app))->setQueueResolver(function () use ($app) {
            //    return $app->make(QueueFactoryContract::class);
            //});
            //});
            return new Router($app['events'], $app);
        });
    }

    /**
     * Register the URL generator service.
     *
     * @return void注册url生成器
     */
    protected function registerUrlGenerator()
    {
        $this->app->singleton('url', function ($app) {
            //获取路由实例
            //$app['router']能这样访问是因为它实现了数据访问接口类
            //在这里得到router实例对象，得到路由集合实例
            $routes = $app['router']->getRoutes();

            // The URL generator needs the route collection that exists on the router.
            // Keep in mind this is an object, so we're passing by references here
            // and all the registered routes will be available to the generator.
            //将routes=路由集合实例在容器实例里的instances[]里
            $app->instance('routes', $routes);

            //路由集合实例，请求实例
            $url = new UrlGenerator(
                $routes, $app->rebinding(
                    'request', $this->requestRebinder()
                )
            );

            $url->setSessionResolver(function () {
                return $this->app['session'];
            });

            // If the route collection is "rebound", for example, when the routes stay
            // cached for the application, we will need to rebind the routes on the
            // URL generator instance so it has the latest version of the routes.
            $app->rebinding('routes', function ($app, $routes) {
                $app['url']->setRoutes($routes);
            });

            return $url;
        });
    }

    /**
     * Get the URL generator request rebinder.
     *
     * @return \Closure
     */
    protected function requestRebinder()
    {
        return function ($app, $request) {
            $app['url']->setRequest($request);
        };
    }

    /**
     * Register the Redirector service.
     *
     * @return void
     */
    protected function registerRedirector()
    {
        //注册重定向
        $this->app->singleton('redirect', function ($app) {
            $redirector = new Redirector($app['url']);

            // If the session is set on the application instance, we'll inject it into
            // the redirector instance. This allows the redirect responses to allow
            // for the quite convenient "with" methods that flash to the session.
            if (isset($app['session.store'])) {
                $redirector->setSession($app['session.store']);
            }

            return $redirector;
        });
    }

    /**
     * Register a binding for the PSR-7 request implementation.
     *
     * @return void
     */
    protected function registerPsrRequest()
    {
        $this->app->bind(ServerRequestInterface::class, function ($app) {
            return (new DiactorosFactory)->createRequest($app->make('request'));
        });
    }

    /**
     * Register a binding for the PSR-7 response implementation.
     *
     * @return void
     */
    protected function registerPsrResponse()
    {
        $this->app->bind(ResponseInterface::class, function ($app) {
            return new PsrResponse;
        });
    }

    /**
     * Register the response factory implementation.
     *
     * @return void
     */
    protected function registerResponseFactory()
    {
        $this->app->singleton(ResponseFactoryContract::class, function ($app) {
            return new ResponseFactory($app[ViewFactoryContract::class], $app['redirect']);
        });
    }

    /**
     * Register the controller dispatcher.
     *注册控制器调度器
     * @return void
     */
    protected function registerControllerDispatcher()
    {
        //$this->bindings[ControllerDispatcherContract::class] = function ($app) {
        //    return new ControllerDispatcher($app);
        //}
        $this->app->singleton(ControllerDispatcherContract::class, function ($app) {
            return new ControllerDispatcher($app);
        });
    }
}
