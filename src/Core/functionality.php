<?php

function dd(...$vars) {

    ?><style>
        body {
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 10px 0;
        }
        pre {
            background-color: #f6f8fa;
            padding: 20px;
            width: 95vw;
            overflow-x: auto;
        }
        strong { color: #e91e63; }
    </style><?php

    $printVar = function($var) {
        ?><pre><?php
        var_dump($var);
        ?></pre><?php
    };

    foreach ($vars as $var) {
        if(is_callable($var)) {
            $var($printVar);
        } else {
            $printVar($var);
        }
    }
    die();
}

function dd_json($var, ...$vars) {
    header('Content-Type: application/json; charset=utf-8');
    if(count($vars) < 1) {
        echo json_encode($var, JSON_INVALID_UTF8_IGNORE);
        die();
    }

    echo json_encode([$var, ...$vars], JSON_INVALID_UTF8_IGNORE);
    die();
}

function env(string $key, $default = null) {
    if(isset($_SERVER[$key])) {
        return $_SERVER[$key];
    }
    return $default;
}

function publicUrl(string $url) {
    $basePublicUrl = env('PUBLIC_URL', 'localhost');
    $urlSections = explode('/', $url);
    return $basePublicUrl . '/' . implode('/', $urlSections);
}