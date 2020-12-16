<?php
return [
    '/' => 'index@index',
    '{5f[A-Za-z0-9]+}' => 'index@index',
    'download/{5f[A-Za-z0-9]+}' => 'index@getDownload'
];