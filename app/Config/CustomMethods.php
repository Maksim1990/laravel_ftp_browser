<?php


//-- Custom function that overwrite default asset() method
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

function custom_asset($path, $secure = null){
    return asset('public/'.$path);
}

//-- Functionality for programmatically replace variables in env file
function setEnvironmentValue($envKey, $envValue)
{
    $envFile = app()->environmentFilePath();

    $str = file_get_contents($envFile);

    $oldValue = env($envKey);

    $str = str_replace("{$envKey}={$oldValue}", "{$envKey}={$envValue}", $str);

    $fp = fopen($envFile, 'w');
    fwrite($fp, $str);
    fclose($fp);
}

