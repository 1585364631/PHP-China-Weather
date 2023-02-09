<?php
    $html = file_get_contents("id.json");
    $mysqli = new mysqli("ip","账号","密码","数据库");
    $json = json_decode($html);
    foreach($json as $i => $ii){
        foreach($ii as $j => $jj){
            foreach($jj as $k => $kk){
                $id = $kk->AREAID;
                $sql = "INSERT INTO `china_weather` (`province`, `city`, `district`, `id`) VALUES ('$i', '$j', '$k', '$id')";
                echo("$sql<br>");
                $mysqli->query($sql);
            }
        }
    }
?>