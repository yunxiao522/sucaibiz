<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
//获取Redis实例
function getRedis($select = 1)
{
    $redis = new \Redis();
    $redis->connect(config('REDIS_HOST'), 6379);
//    $redis->auth(config('REDIS_PASSWORD'));
    $redis->select($select);
    return $redis;
}

//获取Memcache实例
function getMemcache(){
    $Memcache = new Memcache();
    if($Memcache->connect('127.0.0.1')){
        return $Memcache;
    }
}

//获取上传文件新文件名方法
function getNewFileName()
{
    $new_file_name = md5(microtime() . rand(00001, 99999));
    return $new_file_name;
}

//将$_Files格式内容解析成直接可以使用的信息
function getUploadFileInfo($file)
{
    $arr = [
        'real_name' => $file['name'],
        'file_type' => $file['type'],
        'state' => $file['error'],
        'file_size' => $file['size'],
        'temp_file' => $file['tmp_name']
    ];
    return $arr;
}

//获取文件mime数组
function getFileMimeArray()
{
    $mime_types = array("323" => "text/h323",
        "acx" => "application/internet-property-stream",
        "ai" => "application/postscript",
        "aif" => "audio/x-aiff",
        "aifc" => "audio/x-aiff",
        "aiff" => "audio/x-aiff",
        "asf" => "video/x-ms-asf",
        "asr" => "video/x-ms-asf",
        "asx" => "video/x-ms-asf",
        "au" => "audio/basic",
        "avi" => "video/x-msvideo",
        "axs" => "application/olescript",
        "bas" => "text/plain",
        "bcpio" => "application/x-bcpio",
        "bin" => "application/octet-stream",
        "bmp" => "image/bmp",
        "c" => "text/plain",
        "cat" => "application/vnd.ms-pkiseccat",
        "cdf" => "application/x-cdf",
        "cer" => "application/x-x509-ca-cert",
        "class" => "application/octet-stream",
        "clp" => "application/x-msclip",
        "cmx" => "image/x-cmx",
        "cod" => "image/cis-cod",
        "cpio" => "application/x-cpio",
        "crd" => "application/x-mscardfile",
        "crl" => "application/pkix-crl",
        "crt" => "application/x-x509-ca-cert",
        "csh" => "application/x-csh",
        "css" => "text/css",
        "dcr" => "application/x-director",
        "der" => "application/x-x509-ca-cert",
        "dir" => "application/x-director",
        "dll" => "application/x-msdownload",
        "dms" => "application/octet-stream",
        "doc" => "application/msword",
        "dot" => "application/msword",
        "dvi" => "application/x-dvi",
        "dxr" => "application/x-director",
        "eps" => "application/postscript",
        "etx" => "text/x-setext",
        "evy" => "application/envoy",
        "exe" => "application/octet-stream",
        "fif" => "application/fractals",
        "flr" => "x-world/x-vrml",
        "gif" => "image/gif",
        "gtar" => "application/x-gtar",
        "gz" => "application/x-gzip",
        "h" => "text/plain",
        "hdf" => "application/x-hdf",
        "hlp" => "application/winhlp",
        "hqx" => "application/mac-binhex40",
        "hta" => "application/hta",
        "htc" => "text/x-component",
        "htm" => "text/html",
        "html" => "text/html",
        "htt" => "text/webviewhtml",
        "ico" => "image/x-icon",
        "ief" => "image/ief",
        "iii" => "application/x-iphone",
        "ins" => "application/x-internet-signup",
        "isp" => "application/x-internet-signup",
        "jfif" => "image/pipeg",
        "jpe" => "image/jpeg",
        "jpeg" => "image/jpeg",
        "jpg" => "image/jpeg",
        "js" => "application/x-javascript",
        "latex" => "application/x-latex",
        "lha" => "application/octet-stream",
        "lsf" => "video/x-la-asf",
        "lsx" => "video/x-la-asf",
        "lzh" => "application/octet-stream",
        "m13" => "application/x-msmediaview",
        "m14" => "application/x-msmediaview",
        "m3u" => "audio/x-mpegurl",
        "man" => "application/x-troff-man",
        "mdb" => "application/x-msaccess",
        "me" => "application/x-troff-me",
        "mht" => "message/rfc822",
        "mhtml" => "message/rfc822",
        "mid" => "audio/mid",
        "mny" => "application/x-msmoney",
        "mov" => "video/quicktime",
        "movie" => "video/x-sgi-movie",
        "mp2" => "video/mpeg",
        "mp3" => "audio/mpeg",
        "mpa" => "video/mpeg",
        "mpe" => "video/mpeg",
        "mpeg" => "video/mpeg",
        "mpg" => "video/mpeg",
        "mpp" => "application/vnd.ms-project",
        "mpv2" => "video/mpeg",
        "ms" => "application/x-troff-ms",
        "mvb" => "application/x-msmediaview",
        "nws" => "message/rfc822",
        "oda" => "application/oda",
        "p10" => "application/pkcs10",
        "p12" => "application/x-pkcs12",
        "p7b" => "application/x-pkcs7-certificates",
        "p7c" => "application/x-pkcs7-mime",
        "p7m" => "application/x-pkcs7-mime",
        "p7r" => "application/x-pkcs7-certreqresp",
        "p7s" => "application/x-pkcs7-signature",
        "pbm" => "image/x-portable-bitmap",
        "pdf" => "application/pdf",
        "pfx" => "application/x-pkcs12",
        "pgm" => "image/x-portable-graymap",
        "pko" => "application/ynd.ms-pkipko",
        "pma" => "application/x-perfmon",
        "pmc" => "application/x-perfmon",
        "pml" => "application/x-perfmon",
        "pmr" => "application/x-perfmon",
        "pmw" => "application/x-perfmon",
        "pnm" => "image/x-portable-anymap",
        "pot" => "application/vnd.ms-powerpoint",
        "png" => "image/png ",
        "ppm" => "image/x-portable-pixmap",
        "pps" => "application/vnd.ms-powerpoint",
        "ppt" => "application/vnd.ms-powerpoint",
        "prf" => "application/pics-rules",
        "ps" => "application/postscript",
        "pub" => "application/x-mspublisher",
        "qt" => "video/quicktime",
        "ra" => "audio/x-pn-realaudio",
        "ram" => "audio/x-pn-realaudio",
        "ras" => "image/x-cmu-raster",
        "rgb" => "image/x-rgb",
        "rmi" => "audio/mid",
        "roff" => "application/x-troff",
        "rtf" => "application/rtf",
        "rtx" => "text/richtext",
        "scd" => "application/x-msschedule",
        "sct" => "text/scriptlet",
        "setpay" => "application/set-payment-initiation",
        "setreg" => "application/set-registration-initiation",
        "sh" => "application/x-sh",
        "shar" => "application/x-shar",
        "sit" => "application/x-stuffit",
        "snd" => "audio/basic",
        "spc" => "application/x-pkcs7-certificates",
        "spl" => "application/futuresplash",
        "src" => "application/x-wais-source",
        "sst" => "application/vnd.ms-pkicertstore",
        "stl" => "application/vnd.ms-pkistl",
        "stm" => "text/html",
        "svg" => "image/svg+xml",
        "sv4cpio" => "application/x-sv4cpio",
        "sv4crc" => "application/x-sv4crc",
        "t" => "application/x-troff",
        "tar" => "application/x-tar",
        "tcl" => "application/x-tcl",
        "tex" => "application/x-tex",
        "texi" => "application/x-texinfo",
        "texinfo" => "application/x-texinfo",
        "tgz" => "application/x-compressed",
        "tif" => "image/tiff",
        "tiff" => "image/tiff",
        "tr" => "application/x-troff",
        "trm" => "application/x-msterminal",
        "tsv" => "text/tab-separated-values",
        "txt" => "text/plain",
        "uls" => "text/iuls",
        "ustar" => "application/x-ustar",
        "vcf" => "text/x-vcard",
        "vrml" => "x-world/x-vrml",
        "wav" => "audio/x-wav",
        "wcm" => "application/vnd.ms-works",
        "wdb" => "application/vnd.ms-works",
        "wks" => "application/vnd.ms-works",
        "wmf" => "application/x-msmetafile",
        "wps" => "application/vnd.ms-works",
        "wri" => "application/x-mswrite",
        "wrl" => "x-world/x-vrml",
        "wrz" => "x-world/x-vrml",
        "xaf" => "x-world/x-vrml",
        "xbm" => "image/x-xbitmap",
        "xla" => "application/vnd.ms-excel",
        "xlc" => "application/vnd.ms-excel",
        "xlm" => "application/vnd.ms-excel",
        "xls" => "application/vnd.ms-excel",
        "xlt" => "application/vnd.ms-excel",
        "xlw" => "application/vnd.ms-excel",
        "xof" => "x-world/x-vrml",
        "xpm" => "image/x-xpixmap",
        "xwd" => "image/x-xwindowdump",
        "z" => "application/x-compress",
        'rar' => "application/rar",
        "zip" => "application/zip");
    return $mime_types;
}

//生成静态页面方法
function makeHtml($html_data, $html_file_dir, $html_file_name)
{
    //判断目录是否存在，不存在则创建目录
    if(!file_exists($html_file_dir)){
        mkdir($html_file_dir , 0777 ,true);
    }
    $html_file = $html_file_dir .'/' .$html_file_name;
    $content = $html_data; //得到缓存中的内容
    $fp = fopen($html_file, "w"); //创建一个文件，并打开，准备写入
    fwrite($fp, $content); //把php页面的内容全部写入1.html

}

//封装一个请求方法
function request1($url, $https = true, $method = 'get', $data = null)
{
    //1.初始化
    $ch = curl_init($url);
    //2.设置curl
    //返回数据不输出
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //满足https
    if ($https == true) {
        //绕过ssl验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    //满足post
    if ($method === 'post') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

//    curl_setopt($ch, CURLOPT_NOSIGNAL, true);    //注意，毫秒超时一定要设置这个
//    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100); //超时时间200毫秒
    //3.发送请求
    $content = curl_exec($ch);
    //4.关闭资源
    curl_close($ch);
    return $content;
}

//发送邮件函数
function sendEmail($address , $addressname , $title , $content){
    set_time_limit(0);    //设置不超时，程序一直运行。
    //实例化PHPMailer核心类
    $mail = new \mailer\PHPMailer();
    //使用smtp鉴权方式发送邮件
    $mail->isSMTP();
    //smtp需要鉴权
    $mail->SMTPAuth=true;
    //smtp服务器地址
    $mail->Host = \SucaiZ\config::get('cfg_smtp_host');
    //使用ssl加密方式登录鉴权
    $mail->STMPSecure = 'ssl';
    //ssl连接服务器使用的服务器端口
    $mail->Port = \SucaiZ\config::get('cfg_smtp_port');
    //设置smtp的hello消息头，
//    $mail->Helo = 'Hello smtp.qq.com server';
    //设置发送邮件的编码
    $mail->CharSet = 'UTF-8';
    //设置发件人昵称
    $mail->FromName = \SucaiZ\config::get('cfg_smtp_formname');
    //smtp登录的账号
    $mail->Username = \SucaiZ\config::get('cfg_smtp_user');
    //smtp登录的密码
    $mail->Password = \SucaiZ\config::get('cfg_smtp_password');
    //设置发件人邮箱地址
    $mail->From = \SucaiZ\config::get('cfg_smtp_from');
    //邮件正文是否为html编码
    $mail->isHTML(true);
    //设置收件人邮箱地址
    $mail->addAddress($address , $addressname);
    //设置该邮件的主题
    $mail->Subject = $title;
    //添加邮件正文
    $mail->Body = $content;
    //发送结果
    $status = $mail->send();
    if($status){
        return true;
    }else{
        return false;
    }
}

function sendEmail1($data){
    set_time_limit(0);
    return sendEmail($data['address'] ,$data['addressname'] ,$data['title'] ,$data['content']);
}
//获取浏览器信息方法
function getBrowserInfo()
{
    global $_SERVER;
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $browser = '';
    $browser_ver = '';

    if (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs)) {
        $browser = 'OmniWeb';
        $browser_ver = $regs[2];
    }

    if (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Netscape';
        $browser_ver = $regs[2];
    }

    if (preg_match('/safari\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Safari';
        $browser_ver = $regs[1];
    }

    if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs)) {
        $browser = 'Internet Explorer';
        $browser_ver = $regs[1];
    }

    if (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs)) {
        $browser = 'Opera';
        $browser_ver = $regs[1];
    }

    if (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs)) {
        $browser = '(Internet Explorer ' . $browser_ver . ') NetCaptor';
        $browser_ver = $regs[1];
    }

    if (preg_match('/Maxthon/i', $agent, $regs)) {
        $browser = '(Internet Explorer ' . $browser_ver . ') Maxthon';
        $browser_ver = '';
    }
    if (preg_match('/360SE/i', $agent, $regs)) {
        $browser = '(Internet Explorer ' . $browser_ver . ') 360SE';
        $browser_ver = '';
    }
    if (preg_match('/SE 2.x/i', $agent, $regs)) {
        $browser = '(Internet Explorer ' . $browser_ver . ') 搜狗';
        $browser_ver = '';
    }

    if (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'FireFox';
        $browser_ver = $regs[1];
    }

    if (preg_match('/Lynx\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Lynx';
        $browser_ver = $regs[1];
    }

    if (preg_match('/Chrome\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Chrome';
        $browser_ver = $regs[1];

    }

    if ($browser != '') {
        return $browser . ' ' . $browser_ver;
    } else {
        return 'unknow browser';
    }

}
//用户密码加密方法
function getUserPwd($password){
    return md5($password);
}

//生成会员短信验证码
function getUserSmsCode(){
    $code = rand(100000,999999);
    return $code;
}

//发送短信方法
function sendSms($phone ,$singname ,$templatescode ,$templateparams) {
    set_time_limit(0);
    $params = array ();

    // *** 需用户填写部分 ***

    // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
    $accessKeyId = config('ALIYUN_API');
    $accessKeySecret = config('ALIYUN_KEY');

    // fixme 必填: 短信接收号码
    $params["PhoneNumbers"] = $phone;

    // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
    $params["SignName"] = $singname;

    // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
    $params["TemplateCode"] = $templatescode;

    // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
    $params['TemplateParam'] = $templateparams;

    // fixme 可选: 设置发送短信流水号
//    $params['OutId'] = "12345";

    // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
//    $params['SmsUpExtendCode'] = "1234567";


    // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
    if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
        $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
    }

    // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
    $helper = new \aliyunSms\SignatureHelper();

    // 此处可能会抛出异常，注意catch
    $content = $helper->request(
        $accessKeyId,
        $accessKeySecret,
        "dysmsapi.aliyuncs.com",
        array_merge($params, array(
            "RegionId" => "cn-hangzhou",
            "Action" => "SendSms",
            "Version" => "2017-05-25",
        ))
    // fixme 选填: 启用https
    // ,true
    );
    if($content->Code == 'OK'){
        return true;
    }else{
        return false;
    }
}

function sendSms1($data){
    set_time_limit(0);
    return sendSms($data['phone'],$data['singname'] ,$data['templatescode'] ,$data['templateparams']);
}

//截取字符串
function cut_str($str , $length){
    if(mb_strlen($str,'UTF-8') > $length){
        $item=mb_substr($str,0,$length,'UTF-8').'...';
        return $item;
    }else{
        return $str;
    }
}

//检查字符串是否是时间格式
function checkDateTime($string ,$format ='Y-m-d H:i:s'){
    if(date('Y-m-d H:i:s' ,strtotime($string))){
        return true;
    }else{
        return false;
    }
}


//上传文件方法
function UploadOneFile($file = '' ,$file_dir = '' , $ext = '' ,$new_filename = ''){
    if(empty($file) || empty($file_dir) || empty($ext)){
        return false;
    }
    $file_dir = ltrim($file_dir ,'./');
    //检查创建文件夹
    if(!is_dir($file_dir)){
        mkdir ($file_dir,0777,true);
    }
    //判断新文件名是否存在，不存在则使用系统生成的文件名
    if(empty($new_filename)){
        $new_filename = getNewFileName();
    }
    //组合新文件
    $new_file ='./' .$file_dir .'/' .$new_filename .'.' .$ext;
    //移动文件到新文件位置
    if(copy($file ,$new_file)){
        unlink($file);
        //返回新文件绝对位置
        return ltrim($new_file ,'.');
    }else{
        return false;
    }
}

//图像添加水印方法
function AppenWiter($file = ''){
    if(empty($file)){
        return false;
    }
    $file = './' .ltrim(ltrim($file ,'./') ,'/' );
    $ext = isImage($file);
    if($ext){
        //获取水印配置参数
        $water_info = \SucaiZ\config::water();
        //水印启用状态才会执行
        if($water_info['status'] == 1){
            $img = imagecreatefromstring(file_get_contents($file));
            //获取要添加水印的图片大小
            $img_size = getimagesize($file);
            //水印类型，1-图像，2-文字
            if($water_info['type'] == 1){
                $water_img = '.' .$water_info['water_img'];
                $water = imagecreatefrompng($water_img);
                //验证是否设置了水印大小，没有设置则获取原有水印图像大小
                $size = getimagesize($water_img);
                if($water_info['width'] == 0 || $water_info['height'] == 0){
                    $water_width = $size[0];
                    $water_height = $size[1];
                }else{
                    $water_width = $water_info['width'];
                    $water_height = $water_info['height'];
                }
                //判断水印位置是否为随机
                if($water_info['place'] == 5){
                    $place = rand(1,4);
                }else{
                    $place = $water_info['place'];
                }
                //根据水印位置和距离边框距离来生成图像
                if($place == 1){
                    imagecopymerge($img ,$water ,$water_info['x'] ,$water_info['y'] ,0,0,$water_width,$water_height,100);
                }else if($place == 2){
                    imagecopymerge($img ,$water ,$img_size[0] -($water_info['x'] + $water_width ),$water_info['y'] ,0,0,$water_width,$water_height,100);
                }else if($place == 3){
                    imagecopymerge($img ,$water ,$water_info['x'],$img_size[1] - ($water_info['y'] + $water_height),0,0,$water_width,$water_height,100);
                }else if($place == 4){
                    imagecopymerge($img ,$water ,$img_size[0] -($water_info['x'] + $water_width ) ,$img_size[1] - ($water_info['y'] + $water_height) ,0,0,$water_width,$water_height,100);
                }
                //保存生成后的图像
                if($ext == 'png'){
                    imagepng($img ,$file);
                }else if ($ext == 'jpeg'){
                    dump(imagejpeg($img ,$file));
                }else if ($ext == 'gif'){
                    imagegif($img ,$file);
                }
                imagedestroy($img);
                imagedestroy($water);
            }else if($water_info['type'] == 2){
                $font_family = '.' .$water_info['font_family'];
                $color = $water_info['color'];
                $color_arr = hex2rgb($color);
                $color = imagecolorallocate($img, $color_arr['r'] ,$color_arr['g'] ,$color_arr['b']);
                //判断水印位置是否为随机
                if($water_info['place'] == 5){
                    $place = rand(1,4);
                }else{
                    $place = $water_info['place'];
                }
                $water_info['x'] = (int)$water_info['x'];
                $water_info['y'] = (int)$water_info['y'];
                dump($water_info);
                //根据水印位置和距离边框距离来生成图像
                if($place == 1){
                    imagefttext($img , $water_info['font_size'] ,0 ,$water_info['x'] ,$water_info['y'] ,$color ,$font_family ,$water_info['font_value']);
                }else if($place == 2){
                    imagefttext($img , $water_info['font_size'] ,0 ,$img_size[0] - $water_info['x'] ,$water_info['y'] ,$color ,$font_family ,$water_info['font_value']);
                }else if($place == 3){
                    imagefttext($img , $water_info['font_size'] ,0 ,$water_info['x'] ,$img_size[1] - $water_info['y'] ,$color ,$font_family ,$water_info['font_value']);
                }else if($place == 4){
                    imagefttext($img , $water_info['font_size'] ,0 ,($img_size[0] - $water_info['x']) ,($img_size[1] -$water_info['y']) ,$color ,$font_family ,$water_info['font_value']);
                }
                //保存生成后的图像
                if($ext == 'png'){
                    imagepng($img ,$file);
                }else if ($ext == 'jpeg'){
                    dump(imagejpeg($img ,$file));
                }else if ($ext == 'gif'){
                    imagegif($img ,$file);
                }
                imagedestroy($img);
            }
        }
    }
//    return false;
}

//判断一个文件是否是图像方法
function isImage($filename)
{
    if(file_exists($filename))
    {

        $mime = mime_content_type($filename);
        $a = explode('/' ,$mime);
        if(isset($a[1]) && strtolower($a[0]) == 'image'){
            return strtolower($a[1]);
        }
    }
    return false;
}

/**
 * 十六进制 转 RGB
 */
function hex2rgb($hexColor) {
    $color = str_replace('#', '', $hexColor);
    if (strlen($color) > 3) {
        $rgb = array(
            'r' => hexdec(substr($color, 0, 2)),
            'g' => hexdec(substr($color, 2, 2)),
            'b' => hexdec(substr($color, 4, 2))
        );
    } else {
        $color = $hexColor;
        $r = substr($color, 0, 1) . substr($color, 0, 1);
        $g = substr($color, 1, 1) . substr($color, 1, 1);
        $b = substr($color, 2, 1) . substr($color, 2, 1);
        $rgb = array(
            'r' => hexdec($r),
            'g' => hexdec($g),
            'b' => hexdec($b)
        );
    }
    return $rgb;
}

//获取文件大小
function getsize($file, $format = 'kb') {
    $size = filesize($file);
    $p = 0;
    if ($format == 'kb') {
        $p = 1;
    } elseif ($format == 'mb') {
        $p = 2;
    } elseif ($format == 'gb') {
        $p = 3;
    }
    $size /= pow(1024, $p);
    return number_format($size, 3);
}

//文件大小转化
function tosize($bit)
{
    $type = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $bit >= 1024; $i++)//单位每增大1024，则单位数组向后移动一位表示相应的单位
    {
        $bit /= 1024;
    }
    return (floor($bit * 100) / 100) . $type[$i];//floor是取整函数，为了防止出现一串的小数，这里取了两位小数
}

//文件上传阿里云Oss调用方法
function uploadOss($data){

    //设置用户信息
    \SucaiZ\File::setUserInfo($data['user_type'] ,$data['id']);

    //设置文件完整名称
    \SucaiZ\File::$filename = $data['file'];

    //设置文件名称
    \SucaiZ\File::$info['name'] = $data['filename'];

    //设置要存储的阿里云Oss相关信息
    $bucket = isset($data['backet']) ? $data['bucket'] : '' ;
    $object = isset($data['object']) ? $data['object'] : '' ;
    $ext = isset($data['ext']) ? $data['ext'] : '' ;
    \SucaiZ\File::setOssInfo($bucket ,$object ,$ext);

    //设置文档内容信息
    \SucaiZ\File::setArticleInfo($data['article_id'] ,$data['article_title']);

    //执行数据上传
    return \SucaiZ\File::uplodOss();

}

//备份数据库方法
function backup($data){
    $user_type = isset($data['user_type']) ? $data['user_type'] : '' ;
    $id = isset($data['id']) ? $data['id'] : '' ;
    \SucaiZ\File::setUserInfo($user_type ,$id);
    return \SucaiZ\File::backUp();
}

//离线下载条用方法
function liXianDown($data){

    //获取离线下载的链接
    $url = $data['url'];
    if(!isset($url) || empty($url)){
        return false;
    }

    //设置用户信息
    \SucaiZ\File::setUserInfo($data['user_type'] ,$data['id']);

    //获取储存阿里云Oss相关信息
    $bucket = isset($data['bucket']) ? $data['bucket'] : '' ;
    $object = isset($data['object']) ? $data['object'] : '' ;
    $ext = isset($data['ext']) ? $data['ext'] : '' ;

    //设置存储阿里云Oss相关信息
    \SucaiZ\File::setOssInfo($bucket ,$object ,$ext);

    //获取保存文件完整名字
    $savename = isset($data['savename']) ? $data['savename'] : '';

    //设置保存文件的名字
    \SucaiZ\File::$saveName = $savename;

    //获取是否上传阿里云Oss状态
    $oss = \SucaiZ\config::get('cfg_upload_site') ==1 ? true : false;

    //设置文档内容信息
    $article_id = isset($data['article_id']) ? $data['article_id'] : '';
    $article_title = isset($data['article_title']) ? $data['article_title'] : '';
    \SucaiZ\File::setArticleInfo($article_id ,$article_title);

    //执行离线下载
    return \SucaiZ\File::liXianDown($url ,$oss);
}

//获取文档token方法
function getArticleToken(){
    return md5(time() . rand(00001, 99999));
}

function getContent($content ,$user_info ,$article_info){
    $pattern = "/<img.*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
    preg_match_all($pattern , $content , $match);

    //循环数组，取出图片
    foreach($match[1] as $key => $value){
        //获取文件后缀名
        $ext = substr(strrchr($value , '.') , 1);

        //组合数据添加离线下载任务
        $d = [
            'function' => 'liXianDown',
            'url' => $value,
            'oss' => true,
            'bucket' => 'image-sucaibiz',
            'object' => date('Y-m-d', time()) . '/' . getNewFileName() . '.' . $ext,
            'ext' => $ext,
            'user_type' => $user_info['user_type'],
            'user_id' => $user_info['user_id'],
            'article_id'=>$article_info['article_id'],
            'article_title'=>$article_info['article_title']
        ];

        $url = 'http://image.sucai.biz/' .$d['object'];

        task('liXianDown', $d);
        //替换原内容的数据
        $content = str_replace($value , $url , $content);
    }
    return $content;
}

//生成缩略图
function thumbImage($data){

    //验证图片详细信息
    if(!isset($data['file']) || empty($data['file'])){
        return false;
    }

    //设置图片
    \SucaiZ\Image::setImageInfo($data['file']);

    //设置oss
    $oss = isset($data['oss']) ? $data['oss'] : false;
    \SucaiZ\Image::setOss($oss);

    //设置文档信息
    $article_id = isset($data['article_id']) ? $data['article_id'] : '';
    $article_title = isset($data['article_title']) ? $data['article_title'] : '';
    \SucaiZ\Image::setArticleInfo($article_id ,$article_title);

    //设置用户信息
    $user_type = isset($data['user_type']) ? $data['user_type'] : '';
    $user_id = isset($data['user_id']) ? $data['user_id'] : '';
    \SucaiZ\Image::setUserInfo($user_type ,$user_id);

    //设置阿里云Oss信息
    $bucket = isset($data['bucket']) ? $data['bucket'] : '' ;
    $object = isset($data['object']) ? $data['object'] : '' ;
    $ext = isset($data['ext']) ? $data['ext'] : '' ;
    \SucaiZ\Image::setOssInfo($bucket ,$object ,$ext);

    //设置缩略后的长宽
    $width = isset($data['width']) ? $data['width'] : '';
    $height = isset($data['height']) ? $data['height'] : '';

    //调用生成缩略图方法
    return \SucaiZ\Image::thumb($width ,$height);
}

//生成压缩文件
function createZip($data){

    //验证要压缩的文件信息
    if(!isset($data['zipfile']) || empty($data['zipfile'])){
        return false;
    }

    //验证保存压缩文件的目录
    $path = isset($data['path']) ? $data['path'] : '';

    //验证压缩文件的名字
    $filename = isset($data['filename']) ? $data['filename'] : '';

    $zip = new \SucaiZ\Zip();

    //设置oss
    $oss = isset($data['oss']) ? $data['oss'] : false;
    $zip->setOss($oss);

    //设置文档信息
    $article_id = isset($data['article_id']) ? $data['article_id'] : '';
    $article_title = isset($data['article_title']) ? $data['article_title'] : '';
    $zip->setArticleInfo($article_id ,$article_title);

    //设置用户信息
    $user_type = isset($data['user_type']) ? $data['user_type'] : '';
    $user_id = isset($data['user_id']) ? $data['user_id'] : '';
    $zip->setUserInfo($user_type ,$user_id);

    //设置阿里云Oss信息
    $bucket = isset($data['bucket']) ? $data['bucket'] : '' ;
    $object = isset($data['object']) ? $data['object'] : '' ;
    $ext = isset($data['ext']) ? $data['ext'] : '' ;
    $zip->setOssInfo($bucket ,$object ,$ext);

    //调用压缩文件方法
    return $zip->zipToFile($data['zipfile'] ,$path ,$filename);
}

//判断默认字符在字符串中是否存在
/**
 * @param $string
 * @param $needle
 * @return array|bool
 */
function checkstr($string ,$needle){
    $tmparray = explode($needle,$string);
    if(count($tmparray)>1){
        return $tmparray;
    } else{
        return false;
    }
}

//上传文件夹到阿里云Oss
function uploadDirOss($data){
    //验证数据
    if(!isset($data['file_path']) || !isset($data['bucket']) || !isset($data['object'])){
        return false;
    }

    //执行数据上传
    return \SucaiZ\File::uploadDirToOss($data['file_path'] ,$data['bucket'] ,$data['object']);
}

//解压资源下载文件（解压表示需要展示）
function unResourceZip(){

    //设置不超时，程序一直运行。
    set_time_limit(0);

    //即使Client断开(如关掉浏览器),PHP脚本也可以继续执行.
    ignore_user_abort(true);

    //到数据库中取出需要执行的数据
    $article = new \app\admin\model\Article();

    //构建查询条件
    $where = ['is_show'=>1 ,'show_status'=>1];

    //取出符合条件的数据
    $resource_list = $article->getArticleAffiliateList('article_resource' ,$where ,' id,real_name,resource_filename ');

    //更新文档资源附加表展示状态值方法
    function updateResourceStatus($id ,$show_status){
        \think\Db::name('article_resource')->where(['id'=>$id])->update(['show_status'=>$show_status]);
    }

    foreach($resource_list as $value){
        updateResourceStatus($value['id'] ,2);
        //判断储存状态，是在本地还是阿里云Oss
        if(!empty($value['real_name'])){

            //判断真实存储名字内是否包含:
            $is_oss = checkstr($value['real_name'] ,':');

            //获取临时文件夹
            $upload_tmp_dir = './' .\SucaiZ\config::get('cfg_upload_tmp_dir');

            if($is_oss){

                //存储在阿里云Oss

                //分离出阿里云相关信息
                $bucket = $is_oss[0];
                $object = $is_oss[1];

                //设置File内阿里云Oss信息
                \SucaiZ\File::setOssInfo($bucket ,$object);

                //获取文件后缀名
                $filename_arr = explode('.', $object);
                $length = count($filename_arr);
                $ext = $filename_arr[($length - 1)];

                //设置File保存文件的详细位置
                $file = $upload_tmp_dir .$value['id'] .'.' .$ext;
                \SucaiZ\File::$filename = $file;

                //调用File内方法下载文件到本地
                $down_status = \SucaiZ\File::getOssFile();

                //判断下载状态
                if(!$down_status) {
                    updateResourceStatus($value['id'], 4);
                    break;
                }

            }else{
                //储存在本地

                //获取文件真实存储位置
                $file = $value['real_name'];

                //获取文件后缀名
                $filename_arr = explode('.', $file);
                $length = count($filename_arr);
                $ext = $filename_arr[($length - 1)];

            }

            //构建解压后的存储目录
            $un_file_path = $upload_tmp_dir .$value['id'] .'/';

            //判断解压后的文件是否存在，不存在则创建
            if(!file_exists($un_file_path)){
                mkdir($un_file_path ,0777 ,true);
            }

            //判断文件后缀类型，调用不同的解压方法
            if(strtolower($ext) == 'rar'){

                //实力化rar类
                $rar = new \SucaiZ\Rar();

                //调用解压方法
                if(!@$rar->unRar($file ,$un_file_path)){

                    //更新数据库执行状态
                    updateResourceStatus($value['id'] ,4);
                    break;
                }

            }else if(strtolower($ext) == 'zip'){

                //实例化zip类
                $zip = new \SucaiZ\Zip();

                //调用解压方法
                if(!@$zip->unZip($file ,$un_file_path)){

                    //更新数据库执行状态
                    updateResourceStatus($value['id'] ,4);
                    break;
                }
            }

            //组合数据添加到上传阿里云Oss队列
            //构建存储阿里云的object
            $object = date('Y-m-d' ,time()) .'/' .$value['resource_filename'];
            $bucket = 'web-sucaibiz';
            //判断以资源名为目录的文件夹在解压的文件夹里是否存在
            //构建上传文件夹的具体位置
            if(file_exists($un_file_path .$value['resource_filename'] .'/')){
                $file_path = $un_file_path.$value['resource_filename'] .'/';
            }else{
                $file_path = $un_file_path;
            }
            $b = [
                'function'=>'uploadDirOss',
                'bucket'=>$bucket,
                'object'=>$object,
                'file_path'=>$file_path
            ];

            //添加上传队列
            task('Oss' ,$b);

            //构建访问地址
            $resource_show = 'http://web.sucai.biz/' .$object .'/' .'index.html';

            //更新文档资源附加表信息
            \think\Db::name('article_resource')->where(['id'=>$value['id']])->update(['resource_show'=>$resource_show]);

            //更新数据库执行状态
            updateResourceStatus($value['id'] ,3);
        }else{

            //更新数据库执行状态
            updateResourceStatus($value['id'] ,4);
        }
    }
}

function dateWriteFile($data){
    $redis = getRedis();
    $html_data = $redis->get($data['key']);
    $redis->del($data['key']);
    //判断目录是否存在，不存在则创建目录
    if(!file_exists($data['html_file_dir'])){
        mkdir($data['html_file_dir'] , 0777 ,true);
    }
    $html_file = $data['html_file_dir'] .'/' .$data['html_file_name'];
    $content = $html_data; //得到缓存中的内容
    $fp = fopen($html_file, "w"); //创建一个文件，并打开，准备写入
    fwrite($fp, $content); //把php页面的内容全部写入1.html
    return true;
}

//获取广告
function ad($ad_id = ''){
    if(empty($ad_id)){
        return [];
    }
    $where = [
        'id'=>$ad_id,
        'status'=>1
    ];
    $advert = new \app\admin\model\Advert();
    $info = $advert->getAdvert($where ,' content ');
    if(empty($info)){
        return '';
    }else{
        return $info['content'];
    }
}

//生成用户token方法
function createUserToken(){
    $string = time() .rand(10000 , 99999);
    return md5($string);
}

//自定义函数手机号隐藏中间四位
function yc_phone($str){
    $str=$str;
    $resstr=substr_replace($str,'****',3,4);
    return $resstr;
}

//自定义函数邮箱地址隐藏部分
function yc_email($str){
    $email_array = explode("@", $str);
    $prevfix = (strlen($email_array[0]) < 4) ? "" : substr($str, 0, 3); //邮箱前缀
    $count = 0;
    $str = preg_replace('/([\d\w+_-]{0,100})@/', '***@', $str, -1, $count);
    $rs = $prevfix . $str;
    return $rs;
}
//获取用户头像方法
function getUserFace(){
    $face_number = rand(1, 24);
    $face_url = '/upload/face/' . $face_number . '.jpg';
    return $face_url;
}

function makeRss(){
    try{
        $column = new \app\common\model\Column();
        //查询桌面壁纸和素材资讯
        $column_list = $column->getAll([],'id,parent_id',10000);
        $bz_column_list = \app\common\controller\BaseController::getSonList(1,$column_list);
        $zx_column_list = \app\common\controller\BaseController::getSonList(24,$column_list);
        $sj_column_list = \app\common\controller\BaseController::getSonList(54,$column_list);
        $arr = array_merge($bz_column_list,$zx_column_list,$sj_column_list);
        array_push($arr,1,24,54);
        $where = [
            'column_id'=>[
                'in',
                $arr
            ],
            'is_delete'=>1,
            'is_audit'=>1
        ];
        $article_list = \app\common\controller\Article::getArticleList($where,'id,title,column_id,pubdate',100000,true);
        foreach($article_list as $key => $value){
            $article_list[$key]['time'] = date('r',$value['pubdate']);
        }
        $view = new \think\View();
        $view->share('article',$article_list);
        $html_data = $view->fetch('./templates/default/rss.xml');
        makeHtml($html_data ,'./' ,'rss.xml');
    }catch(\think\Exception $e){
        return false;
    }
    return true;
}
function test(){
    return true;
}


