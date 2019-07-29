<?php
if(isset($_GET['url']))
{
  if(isset($_COOKIE['url'])&&!empty($_COOKIE['url']))
  {
    header('Location:'.$_COOKIE['url']);
  }
  else
  {
    header('Location:https://live.bilibili.com/5316');
  }
  die();
}

/*
	SAE：http://t.cn/Ez0lEf1
    三夜：http://t.cn/EzFmR7X
*/

$text='';
$isGetWeather=false;//是否获取天气，地区发生变化时重新获取天气用

function get_client_ip($type = 0) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if(isset($_SERVER['HTTP_X_REAL_IP'])){//nginx 代理模式下，获取客户端真实IP
        $ip=$_SERVER['HTTP_X_REAL_IP'];
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {//客户端的ip
        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {//浏览当前页面的用户计算机的网关
        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos    =   array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip     =   trim($arr[0]);
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];//浏览当前页面的用户计算机的ip地址
    }else{
        $ip=$_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

function getUserInfo()
{
  $ipUrl='http://ip.taobao.com/service/getIpInfo.php?ip='.get_client_ip();
  // $ipUrl='http://ip.taobao.com/service/getIpInfo.php?ip=61.132.88.0';
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $ipUrl);
  curl_setopt($curl, CURLOPT_HEADER, 0);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  $ipData = curl_exec($curl);
  curl_close($curl);

  if($ipData)
  {
    $result = json_decode($ipData,true);
    setcookie('userInfo',$ipData,time()+1800);
  }
  else
  {
    $result=NULL;
  }

  return $result;
}

function getWeather($cityName)
{
  include_once('./c_code.php');
  if(!empty($c_code[$cityName]))
  {
    $weatherUrl='http://t.weather.sojson.com/api/weather/city/'.$c_code[$cityName];
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $weatherUrl);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.2.149.27 Safari/525.13');
    $weaData = curl_exec($curl);
    curl_close($curl);
    setcookie('weather',$weaData,time()+3600);
  }
  else
  {
    $weaData=NULL;
  }
  return json_decode($weaData,true);
}

if(isset($_COOKIE['userInfo'])&&!empty($_COOKIE['userInfo']))
{
  $userInfo = json_decode($_COOKIE['userInfo'],true);
  if($userInfo['code']!==0)
  {
    $userInfo=getUserInfo();
    $isGetWeather=true;
  }
}
else
{
  $userInfo=getUserInfo();
  $isGetWeather=true;
}

// $userInfo['data']['region']='日本';
// $userInfo['data']['city']='日本';

if($userInfo!=NULL&&$userInfo['code']===0)
{
  if(empty($userInfo['data']['city'])||$userInfo['data']['region']==$userInfo['data']['city']||$userInfo['data']['city']=='XX')
  {
    if(empty($userInfo['data']['region']))
    {
      $text.='小兄dei，没有获取到你具体的城市信息，显示不了天气情况啦。'."\n";
    }
    else
    {
      $city=$userInfo['data']['region'];
    }
  }
  else
  {
    $city=$userInfo['data']['region'].$userInfo['data']['city'];
  }

  $text.='Hello 这位'.$city.'的小兄dei，没错就是你，你好呀。'."\n";

  if($userInfo['data']['country']!='中国')
  {
    $text.='小兄dei，你的地区不在中国，显示不了天气情况啦。'."\n";
  }
  else
  {
    if(!$isGetWeather&&isset($_COOKIE['weather'])&&$_COOKIE['weather']!=NULL)
    {
      $weather=json_decode($_COOKIE['weather'],true);
      if(empty($weather['status'])||$weather['status']!='200')
      {
        $weather=getWeather($userInfo['data']['city']);
      }
    }
    else
    {
      $weather=getWeather($userInfo['data']['city']);
    }
    if($weather['status']=='200'&&$weather!==NULL)
    {
      $today=date("Ymd")-$weather['date'];
      $text.='今天天气：'.$weather['data']['forecast'][$today]['type'].'，'.substr($weather['data']['forecast'][$today]['low'],7).'～'.substr($weather['data']['forecast'][$today]['high'],7)."\n";
      if($today==0) $text.='空气质量：'.$weather['data']['quality'].'，湿度：'.$weather['data']['shidu']."\n";;
      $text.='今天也要有个好心情呀，下面是明后天天气'."\n\n";
      if(is_array($weather['data']['forecast'][$today+1])) $text.='明天天气：'.$weather['data']['forecast'][$today+1]['type'].'，'.substr($weather['data']['forecast'][$today+1]['low'],7).'～'.substr($weather['data']['forecast'][$today+1]['high'],7)."\n";
      if(is_array($weather['data']['forecast'][$today+2])) $text.='后天天气：'.$weather['data']['forecast'][$today+2]['type'].'，'.substr($weather['data']['forecast'][$today+2]['low'],7).'～'.substr($weather['data']['forecast'][$today+2]['high'],7)."\n";
      $text.='不知道说对了没';
    }
    else
    {
      $text.='小兄dei，你的地区显示不了天气情况';
    }
  }
}


// echo $text;

$im=imagecreatetruecolor(900,230);
$white=imagecolorallocate($im,255,255,255);
imagefill($im,0,0,$white);

// imagecolortransparent($im,$white);  //设置具体某种颜色为透明色

$f_color1=imagecolorallocate($im,120,120,120);

imagettftext($im,16,0,0,18,$f_color1,"./dx.ttf",$text);

if(isset($_GET['test'])) {
  echo nl2br($text);
  exit();
}
header("Content-type:image/png");
imagepng($im);
