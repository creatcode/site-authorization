<?php

\think\Hook::add('app_init', function () {
    if (!is_file(APP_PATH . 'common/license/register.lock') && is_file(__DIR__ . '/register.php')) {
        copy(__DIR__ . '/register.php', ROOT_PATH . 'public/register.php');
        unlink(__DIR__ . '/register.php');
        header("location:/register.php");
        exit;
    }
    try {
        if (!class_exists(base64_decode('XGppZWdlbGljXENsb3VkU2VydmljZQ=='))) {
            exception(base64_decode('5o6I5p2D57G75byC5bi4'));
        }
        $res = base64_decode("XGppZWdlbGljXENsb3VkU2VydmljZTo6aW5pdA==")()->{base64_decode("Y2hlY2tBdXRo")}();
        \think\Request::instance()->bind(base64_decode('bGljZGF0YQ=='), $res);
    } catch (\Throwable $th) {
        $type = 'html';
        $template = \think\Config::get('template');
        $view = \think\Config::get('view_replace_str');
        \think\Lang::set('Warning', base64_decode('5o6I5p2D6K2m5ZGKfg=='));
        // APP_PATH . 'common' . DS . 'view' . DS . 'tpl' . DS . 'dispatch_jump.tpl'
        $result = \think\View::instance($template, $view)
            ->fetch(__DIR__ . '/dispatch_jump.tpl', [
                'code' => 0,
                'msg'  => $th->getMessage(),
                'data' => '',
                'url'  => '',
                'wait' => 3,
            ]);

        $basepath = explode('/', request()->path())[0] ?? '';
        if ($basepath == 'api' || $basepath == 'adminapi' || request()->isAjax()) {
            $result = [
                'code' => (int)base64_decode('NDA3'),
                'msg'  => $th->getMessage(),
                'time' => \think\Request::instance()->server('REQUEST_TIME'),
                'data' => '',
            ];
            $type = 'json';
        }

        $response = \think\Response::create($result, $type)->header([]);
        abort($response);
    }
});
