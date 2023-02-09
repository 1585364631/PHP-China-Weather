<?php
    function getMillisecond() {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    $time = getMillisecond();

    if(!empty($_GET['time'])){
        $time = $_GET['time'];
    }

    if(!empty($_GET['city'])){
        $city = $_GET['city'];
    }

    $mysqli = new mysqli("ip","账号","密码","数据库");
    $sql = "SELECT * FROM `china_weather` WHERE `district` = '$city'";
    $row = mysqli_fetch_assoc($mysqli->query($sql));
    if(empty($row)){
        die("空");
    }
    $province = $row['province'];
    $city = $row['city'];
    $district = $row['district'];
    $id = $row['id'];
    if($district==$city)$district="城区";
    if($city==$province)$province="";
    $diqu = "$province$city$district";
    $url = "http://d1.weather.com.cn/weather_index/$id.html";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    $headers = array();
    $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36';
    $headers[] = 'Referer: http://www.weather.com.cn/';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    $result = explode(";",$result);
    $json = array();
    foreach($result as $i => $ii){
        $result[$i] = explode("=",$result[$i])[1];
        $json[] = json_decode($result[$i]);
    }
    $json[0] = $json[0]->weatherinfo;
    $json[1] = $json[1]->w;
    $json[3] = $json[3]->zs;
    $text = "$diqu";
    $date_time = $json[0]->fctime;
    $date_time = "日期：" . mb_substr($date_time,0,4) . "年" . $json[2]->date . "\n发布时间：" . mb_substr($date_time,8,2) . ":" . mb_substr($date_time,10,2) . "\n更新时间：" . $json[2]->time; 
    $text = "$text\n$date_time\n白天气温：" . $json[0]->temp . "℃\n夜间气温：" . $json[0]->tempn . "℃\n实时气温：" . $json[2]->temp . "℃ " . $json[2]->tempf . "℉\n天气情况：" . $json[2]->weather;
    $text = "$text\n风型：" . $json[0]->wd . "\n风级：" . $json[2]->WS . "\n风向：" . $json[2]->WD . " " . $json[2]->wde . "\n风速：" . $json[2]->wse;
    $text = "$text\n相对湿度：" . $json[2]->SD . "\n能见度：" . $json[2]->njd . "\n降水量：" . $json[2]->rain . "mm\n空气质量：" . $json[2]->aqi;
    if(!empty($json[1])){
        $text = "$text\n气象预警：";
        foreach($json[1] as $i){
            $text = "$text\n" . ((strstr($i->w9,"（预警信息来源：国家预警信息发布中心）"))?explode("（预警信息来源：国家预警信息发布中心）",$i->w9)[0]:$i->w9);
        }
    }
    
    $text = "$text\n生活指数：";
    $sum=0;
    foreach($json[3] as $j => $k){
        if($sum==0){
            $sum++;
            continue;
        }
        if($sum==1){
            $text = "$text\n$k" . "："; 
        }
        if($sum==2){
            $text = "$text$k"; 
        }
        if($sum==3){
            $text = "$text\n$k"; 
            $sum=0;
        }
        $sum++;
    }
    echo($text);
?>