<?php

if (! function_exists('dot')) {
    function dot($object, $dots, ...$params)
    {
        \Yasaie\Support\Yalp::dot($object, $dots, ...$params);
    }
}