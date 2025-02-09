<?php
    $serveris = "localhost";
    $lietotajs = "grobina1_sinicina";
    $parole = "EG!YbUHn7";
    $datubaze = "grobina1_sinicina";

    $savienojums = mysqli_connect($serveris, $lietotajs, $parole, $datubaze);

    // if(!$savienojums){
    // echo "Viss slikti!";
    // }else{
    // echo "Viss ok!";
    // }
?>