<?php
require_once "../extend/fast/Http.php";
require_once "../vendor/creatcode/liccore/src/CloudService.php";
require_once "../thinkphp/library/think/Config.php";

$method = $_SERVER['REQUEST_METHOD'];
$lockFile = "../application/common/license/register.lock";
$licenseFile = "../application/common/license/license.lic";
$pemFile = "../application/common/license/public.pem";
if (!is_dir("../application/common/license/")) {
    mkdir("../application/common/license/", 0755, true);
}
if (is_file($lockFile)) {
    exit('<title>站点注册</title><div style="text-align:center;margin-top:300px;font-size:20px;">此站点已注册</div>');
}
$config = include "../application/extra/cloud.php";
foreach ($config as $key => $value) {
    if (empty($value)) {
        $msgcontent = "请先在配置文件 application/extra/cloud.php 中填写完整的配置信息再进行操作";
        echo <<<EOF
    <style>
    .fullscreen-mask {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .mask-message {
        background: #fff;
        color: #000;
        padding: 20px 30px;
        border-radius: 8px;
        font-size: 18px;
        box-shadow: 0 0 10px rgba(0,0,0,0.3);
        font-family: "Microsoft Yahei", sans-serif;
    }
    </style>
    
    <div class="fullscreen-mask">
        <div class="mask-message">$msgcontent</div>
    </div>
    EOF;
        break;
    }
}
if ($method == 'POST') {
    try {
        define('DS', DIRECTORY_SEPARATOR);
        defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
        defined('ROOT_PATH') or define('ROOT_PATH', dirname(realpath(APP_PATH)) . DS);
        defined('RUNTIME_PATH') or define('RUNTIME_PATH', ROOT_PATH . 'runtime' . DS);

        $os    = PHP_OS_FAMILY;
        $filename     = md5("{$os}_machine_code") . ".dat";
        $cachePath    = RUNTIME_PATH . "{$filename}";
        @unlink($cachePath);

        $url = $config['url'] . '/api/index/site_reg';
        $params['name'] = $_POST['name'];
        $params['period'] = $_POST['period'] ?? '';
        $params['devnum'] = $_POST['devnum'] ?? '-1';
        $params['version'] = $config['version'];
        $params['project_id'] = $_POST['project_id'] ?? $config['type'];
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $fullDomain = $protocol . '://' . $host;
        $params['url'] = $fullDomain;
        $params['type'] = 2;
        if (preg_match('/^(10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|192\.168\.|127\.0\.0\.1)/', $_SERVER['SERVER_ADDR'] ?? '127.0.0.1')) {
            $params['type'] = 1;
        }

        $macAddress = \safeaccess\CloudService::init()->getcode();
        if (empty($macAddress)) {
            throw new Exception('获取设备编码失败');
        }

        $params['code'] = $macAddress;
        $reponse = \fast\Http::post($url, $params);
        $reponse = json_decode($reponse, true);
        if (!$reponse) {
            throw new Exception('网络错误，请求失败');
        }
        if ($reponse['code'] != 1) {
            throw new Exception('错误：' . $reponse['msg']);
        }

        //写入文件
        file_put_contents($lockFile, $reponse['data']['secret_key']);
        file_put_contents($licenseFile, $reponse['data']['license']);
        file_put_contents($pemFile, $reponse['data']['pem']);
        // $cloudConfig = include('../application/extra/cloud.php');
        // $cloudConfig = array_merge($cloudConfig, ['type' => $reponse['data']['project_type']]);
        // file_put_contents('../application/extra/cloud.php', '<?php' . "\n\nreturn " . var_export($cloudConfig, true) . ";\n");
        //删除当前安装脚本
        @unlink(__FILE__);
    } catch (\Throwable $e) {
        exit(json_encode(['code' => 0, 'msg' => $e->getMessage()]));
    }
    exit(json_encode(['code' => 1, 'msg' => '注册成功']));
}
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>站点注册</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1">
    <meta name="renderer" content="webkit">
    <link href="//unpkg.com/layui@2.10.3/dist/css/layui.css" rel="stylesheet">
    <script src=".\assets\libs\jquery\dist\jquery.min.js"></script>

    <script src="//unpkg.com/layui@2.10.3/dist/layui.js"></script>
    <style>
        body {
            background: #f1f6fd;
            margin: 0;
            padding: 0;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body,
        input,
        button {
            font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, 'Microsoft Yahei', Arial, sans-serif;
            font-size: 14px;
            color: #7E96B3;
        }

        .container {
            max-width: 480px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }

        a {
            color: #4e73df;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        h1 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 28px;
            font-weight: normal;
            color: #3C5675;
            margin-bottom: 0;
            margin-top: 0;
        }

        form {
            margin-top: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group .form-field:first-child input {
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }

        .form-group .form-field:last-child input {
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 4px;
        }

        .form-field input {
            background: #fff;
            margin: 0 0 2px;
            border: 2px solid transparent;
            transition: background 0.2s, border-color 0.2s, color 0.2s;
            width: 100%;
            padding: 15px 15px 15px 180px;
            box-sizing: border-box;
        }

        .form-field input:focus {
            border-color: #4e73df;
            background: #fff;
            color: #444;
            outline: none;
        }

        .form-field label {
            float: left;
            width: 160px;
            text-align: right;
            margin-right: -160px;
            position: relative;
            margin-top: 15px;
            font-size: 14px;
            pointer-events: none;
            opacity: 0.7;
        }

        button,
        .btn {
            background: #3C5675;
            color: #fff;
            border: 0;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            padding: 15px 30px;
            -webkit-appearance: none;
        }

        button[disabled] {
            opacity: 0.5;
        }

        .form-buttons {
            height: 52px;
            line-height: 52px;
        }

        .form-buttons .btn {
            margin-right: 5px;
        }

        #error,
        .error,
        #success,
        .success,
        #warmtips,
        .warmtips {
            background: #D83E3E;
            color: #fff;
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        #success {
            background: #3C5675;
        }

        #error a,
        .error a {
            color: white;
            text-decoration: underline;
        }

        #warmtips {
            background: #ffcdcd;
            font-size: 14px;
            color: #e74c3c;
        }

        #warmtips a {
            background: #ffffff7a;
            display: block;
            height: 30px;
            line-height: 30px;
            margin-top: 10px;
            color: #e21a1a;
            border-radius: 3px;
        }

        .custom-select {
            /* padding: 10px; */
            /* border: 2px solid #4CAF50; */
            border: none;
            border-radius: 8px;
            background-color: white;
            color: #333;
            font-size: 16px;
            appearance: none;
            /* Chrome */
            -webkit-appearance: none;
            /* Safari */
            -moz-appearance: none;
            /* Firefox */
            background-image: url('data:image/svg+xml;utf8,<svg ... />');
            /* 可加图标 */
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
            /* min-width: 150px; */
            width: 100%;
            padding: 15px 15px 15px 180px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>
            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="80px" height="96px" viewBox="0 0 64 64" enable-background="new 0 0 64 64" xml:space="preserve">
                <image id="image0" width="64" height="64" x="0" y="0"
                    xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABABAMAAABYR2ztAAAAIGNIUk0AAHomAACAhAAA+gAAAIDo
AAB1MAAA6mAAADqYAAAXcJy6UTwAAAAeUExURQAAANcgCNcdBdgdBdcgCNkeBtgeBtceBtgeBv//
/3haf5gAAAAIdFJOUwAgYL9An9+AZHoGogAAAAFiS0dECfHZpewAAAAHdElNRQfpBAgFJxAMEg4v
AAABg0lEQVRIx9WVPW/CMBCGndSUjhk7hlRCnRGqGEFVKCNUhGYtSzOWBXVOGsjPrmXfJf7IuVKV
pe+Uyz0++474hbGhFfnT4aJa+fJpI7Sj8w+N1IzYJswbULXszRdNq2pClkfN7W0OmME6HwYRLrB2
3O6k95vi8los422xnbP9q4qfMYZ+N86KF9ymlOEjlNea508ucDROHZxtYO/8LIUOVLE7OZ4kyQSB
0vsNDAWkWaYfNMxAMQJWnRFOb/ufAX0+FpC8rbzAWpQ9eYAb+b1GNPAlT/ZOAoE6+pUEsLuIAu4A
iCngHoBPCth08/tjhVsAlhQwVvkLPSh1K79/m+S2B1irkBc4SBsQriDDUYH3XAfkkvQowwCtJ9dv
t/SSwPigpub1v0bWfcttAzHdleeuBenuiraoAHTGywnzY3iDvsKBb+YqRtvsbK31TunQaJu1fu4p
vBRed8ZHsyt01zqHh37b69TzvzTWiZj1qG3GcGWDOPjzQrKDMmK0RDMz5lW6Z4PrB+cAkBWPxSLd
AAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDI1LTA0LTA4VDA1OjM5OjE2KzAwOjAwCszI4wAAACV0RVh0
ZGF0ZTptb2RpZnkAMjAyNS0wNC0wOFQwNTozOToxNiswMDowMHuRcF8AAAAodEVYdGRhdGU6dGlt
ZXN0YW1wADIwMjUtMDQtMDhUMDU6Mzk6MTYrMDA6MDAshFGAAAAAAElFTkSuQmCC" />
            </svg>
        </h1>
        <h2>站点注册</h2>
        <div>

            <form method="post">
                <div id="error" style="display:none"></div>
                <div id="success" style="display:none"></div>

                <!-- <div class="form-group">
                    <div class="form-field">
                        <label>项目类型</label>
                        <select name="project_id" class="custom-select" required id="make">
                            <option value="">----- 请选择项目类型 -----</option>
                        </select>
                    </div>
                </div> -->
                <div class="form-group">
                    <div class="form-field">
                        <label>站点名称</label>
                        <input type="text" name="name" value="" required="">
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-field">
                        <label>授权有效期</label>
                        <input type="text" name="period" value="" required="" id="ID-laydate-demo" readonly>
                    </div>
                </div>
                <?php if ($config['type'] == 'iotadmin'): ?>
                    <div class="form-group">
                        <div class="form-field">
                            <label>授权设备数</label>
                            <input type="text" name="devnum" value="-1" required="">
                            <span>tips:-1表示不限制</span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-buttons">
                    <button type="submit">提 交</button>
                </div>
            </form>

            <script>
                // 获取项目数据
                // function callback(data) {
                //     data.forEach(function(item) {
                //         $('#make').append('<option value="' + item.id + '">' + item.name + '</option>');
                //     });
                // }
                let baseurl = "<?php echo $config['url'] ?>";
                // $.ajax({
                //     url: baseurl + '/api/index/getproject',
                //     type: "GET",
                //     dataType: "jsonp",
                // })

                $(function() {
                    $('form :input:first').select();

                    $('form').on('submit', function(e) {
                        e.preventDefault();
                        var form = this;
                        var $error = $("#error");
                        var $success = $("#success");
                        var $button = $(this).find('button')
                            .text("提交中...")
                            .prop('disabled', true);

                        var $sub_buttons = $(".form-buttons", form);

                        $.ajax({
                            url: "",
                            type: "POST",
                            dataType: "json",
                            data: $(this).serialize(),
                            success: function(ret) {
                                if (ret.code == 1) {
                                    var data = ret.data;
                                    $error.hide();
                                    $(".form-group", form).remove();
                                    $button.remove();
                                    $("#success").text(ret.msg).show();
                                    $("<a class='btn' id='hehe' href='/' style='background:#4e73df'>返回主页</a>").appendTo($sub_buttons);
                                } else {
                                    $error.show().text(ret.msg);
                                    $button.prop('disabled', false).text("重新提交");
                                    $("html,body").animate({
                                        scrollTop: 0
                                    }, 500);
                                }
                            },
                            error: function(xhr) {
                                $error.show().text(xhr.responseText);
                                $button.prop('disabled', false).text("重新提交");
                                $("html,body").animate({
                                    scrollTop: 0
                                }, 500);
                            }
                        });
                        return false;
                    });

                    layui.use(function() {
                        var laydate = layui.laydate;
                        laydate.render({
                            elem: '#ID-laydate-demo',
                            type: 'datetime',
                            max: '2999-12-31 23:59:59',
                            shortcuts: [{
                                text: "永久",
                                value: function() {
                                    return "2999-12-31 23:59:59";
                                }
                            }]
                        });
                    });
                });
            </script>
        </div>
    </div>
</body>

</html>