<?php
if(isset($_GET['u'])) {
  header('Location: '.$_COOKIE['news_'.$_GET['u']]);
}

$url='http://top.baidu.com/buzz?b=1&c=513&fr=topbuzz_b1';
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$newsData = curl_exec($curl);
curl_close($curl);

$html = iconv('gbk','utf-8',$newsData);

preg_match_all('/<a class="list-title" target="\_blank" href="(http:\/\/www\.baidu\.com\/baidu\?.*)" href\_top="(?:[^>"]*)">(.*)<\/a>/', $html, $arr);

$im=imagecreatetruecolor(400,200);
$white=imagecolorallocate($im,255,255,255);
imagefill($im,0,0,$white);

$top = 30;
$left = 0;

$f_color1=imagecolorallocate($im,60,60,60);

for($i=0; $i<10; $i++) {
  imagettftext($im, 16, 0, $left, $top, $f_color1, "./dx.ttf", $arr[2][$i]);
  setcookie('news_'.$i, $arr[1][$i], time()+999999);

  $top += 30;
  if($i == 4) {
    $top = 30;
    $left = 200;
  }
}

// imagecolortransparent($im,$white);  //设置具体某种颜色为透明色

header("Content-type:image/png");
imagepng($im);

/*
http://t.cn/EfbaDEH
http://t.cn/EfbScMk
http://t.cn/EfbohKh
http://t.cn/EfbSenX
http://t.cn/Efbo2Er

http://t.cn/EfboGPp
http://t.cn/EfboSnH
http://t.cn/EfboNbk
http://t.cn/EfbolvU
http://t.cn/EfboHbW
*/
