
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
