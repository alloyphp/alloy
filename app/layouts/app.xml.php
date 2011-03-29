<?php
    $response = \Kernel()->response();

    // No cache and hide potential errors
    ini_set('display_errors', 0);
    $response->header("Expires", "Mon, 26 Jul 1997 05:00:00 GMT"); 
    $response->header("Last-Modified", gmdate( "D, d M Y H:i:s" ) . "GMT"); 
    $response->header("Cache-Control", "no-cache, must-revalidate"); 
    $response->header("Pragma", "no-cache");

    $response->contentType('text/xml');
    $response->sendHeaders();

    echo $content;