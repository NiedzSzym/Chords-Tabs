<?php

#[Attribute(Attribute::TARGET_METHOD)]
class AllowedMethods {
    public $methods = [];

    public function __construct(array $methods) {
        $this->methods = $methods;
    }

    
}