<?php
/**
 * FlxPHP
 * Author:FlxSNX<211154860@qq.com>
 * [系统错误提示页模板]
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>Sysetm Error</title>
    <style>
        *{
            margin: 0; 
            padding: 0;
        }

        html, body{
            width: 100%;
            height: 100%;
        }

        body{
            font-family:微软雅黑;
            background-color: #f8f8f8;
            color: #666;
        }

        .error{
            position:relative;
            width: 100%;
            height: 300px;
            top: 50%;
            margin-top: -150px; 
        }

        p.title{
            text-align: center;
            font-size: 20px;
            font-weight: 100;
            color: #333;
        }

        p.errorMsg{
            font-size: 16px;
        }

        p{
            text-align: center;
            font-size: 14px;
            font-weight: 100;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="error">
        <p class="title">FlxPHP - SysetmError</p>
        <p class="errorMsg">-><?=$error['error']?><-</p>
        <p class="info">[<?=$error['info']?>]</p>
        <p class="errorin">ErrorIN-><br><?=$error['file']?>#<?=$error['line']?></p>
        <p class="ver">FlxPHP V<?=FLXPHP_VER?> build<?=FLXPHP_BUILD?></p>
    </div>
</body>
</html>