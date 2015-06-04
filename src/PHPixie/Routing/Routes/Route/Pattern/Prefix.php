<?php

namespace PHPixie\Routing\Routes\Route\Pattern;

class Prefix extends \PHPixie\Routing\Routes\Route\Pattern
{
    protected $routeBuilder;
    protected $route;
    
    public function __construct($builder, $routeBuilder, $configData)
    {
        $this->routeBuilder = $routeBuilder;
        parent::__construct($builder, $configData);
    }
    
    public function match($fragment)
    {
        if(!$this->isMethodValid($fragment)) {
            return null;
        }
        
        list($hostAttributes, $host) = $this->matchPattern(
            $this->hostPattern(),
            $fragment->host()
        );
        
        if($hostAttributes === null) {
            return null;
        }
        
        list($pathAttributes, $path) = $this->matchPattern(
            $this->pathPattern(),
            $fragment->path()
        );
        
        if($pathAttributes === null) {
            return null;
        }
        
        $attributes = array_merge(
            $this->defaults(),
            $hostAttributes,
            $pathAttributes
        );
        
        $fragment = $fragment->copy($path, $host);
        
        $match = $this->group()->match($fragment);
        if($match !== null) {
            $match->prependAttributes($attributes);
        }
        
        return $match;
    }
    
    protected function matchPattern($pattern, $string)
    {
        if($pattern === null) {
            return array(array(), $string);
        }
        
        return $this->builder->matcher()->matchPrefix($pattern, $string);
    }

    public function generate($match, $withHost = false)
    {
        $fragment   = $this->group()->generate($match, $withHost);
        $attributes = $this->mergeAttributes($match);
        
        $path = $this->generatePatternString($this->pathPattern(), $attributes);
        $path.= $fragment->path();
        $fragment->setPath($path);
        
        if($withHost) {
            $host = $this->generatePatternString($this->hostPattern(), $attributes);
            $host.= $fragment->host();
            $fragment->setHost($host);
        }
        
        return $fragment;
    }
    
    public function route()
    {
        if($this->route === null) {
            $routeConfig = $this->configData->slice('route');
            $this->route = $this->routeBuilder->buildFromConfig($routeConfig);
        }
        
        return $this->route;
    }
}