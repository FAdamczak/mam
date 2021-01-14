<?php 

    $toto = shell_exec("adb devices"); // 2&>1
    echo "<pre>";
    echo "<hr>";
    var_dump($toto);
    echo '<hr>';
    echo "</pre>";

    

?>
