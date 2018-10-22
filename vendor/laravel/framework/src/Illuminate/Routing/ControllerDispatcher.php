<?php

namespace Illuminate\Routing;

use Illuminate\Container\Container;
use Illuminate\Routing\Contracts\ControllerDispatcher as ControllerDispatcherContract;

class ControllerDispatcher implements ControllerDispatcherContract
{
    use RouteDependencyResolverTrait;

    /**
     * The container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * Create a new controller dispatcher instance.
     *
     * @param  \Illuminate\Container\Container  $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Dispatch a request to a given controller and method.
     *运行控制器->方法()并返回响应对象
     * @param  \Illuminate\Routing\Route  $route
     * @param  mixed  $controller
     * @param  string  $method
     * @return mixed
     */
    public function dispatch(Route $route, $controller, $method)
    {
        //先解决控制器的方法参数，如果控制器的方法参数是个类就会实例化实现注入
        $parameters = $this->resolveClassMethodDependencies(
            $route->parametersWithoutNulls(), $controller, $method
        );
        //控制器->方法(参数)
        if (method_exists($controller, 'callAction')) {
            return $controller->callAction($method, $parameters);
        }

       
        //控制器->方法(参数)
        return $controller->{$method}(...array_values($parameters));
    }

    /**
     * Get the middleware for the controller instance.
     *
     * @param  \Illuminate\Routing\Controller  $controller
     * @param  string  $method
     * @return array
     */
    public function getMiddleware($controller, $method)
    {
        if (! method_exists($controller, 'getMiddleware')) {
            return [];
        }

        return collect($controller->getMiddleware())->reject(function ($data) use ($method) {
            return static::methodExcludedByOptions($method, $data['options']);
        })->pluck('middleware')->all();
    }

    /**
     * Determine if the given options exclude a particular method.
     *
     * @param  string  $method
     * @param  array  $options
     * @return bool
     */
    protected static function methodExcludedByOptions($method, array $options)
    {
        return (isset($options['only']) && ! in_array($method, (array) $options['only'])) ||
            (! empty($options['except']) && in_array($method, (array) $options['except']));
    }
}
