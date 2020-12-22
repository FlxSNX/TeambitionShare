<?php
return [
    '/' => 'index@index',
    'init' => 'cfginit@init',
    '{[A-Za-z0-9]+}' => 'index@index',
    'download/{[A-Za-z0-9]+}' => 'index@getDownload'
];