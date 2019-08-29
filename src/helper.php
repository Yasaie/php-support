<?php

if (! function_exists('dot')) {
    function dot($object, $dots, ...$params)
    {
        return \Yasaie\Support\Yalp::dot($object, $dots, ...$params);
    }
}