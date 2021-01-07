<?php
use FlxPHP\Route;

Route::any('/','index@index');

Route::any('init','cfginit@init');

Route::any('{dirid}','index@index')->where([
    'dirid' => '[A-Za-z0-9]+'
]);

Route::get('download/{fileid}','index@getDownload')->where([
    'fileid' => '[A-Za-z0-9]+'
]);