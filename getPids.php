<?php
include_once("connection.php");
global $link;

$lieferantname = $_REQUEST['lieferant'];

$sql_get_pids = "SELECT EID 
                    FROM ware w
                    INNER JOIN lieferanten l ON w.`Lieferanten_Lieferanten-ID` = l.`Lieferanten-ID`
                    WHERE l.Name like '$lieferantname'
                    ";

$result = $link->query($sql_get_pids);

$pid_arr = [];  // Array für die PIDs des gewählten Lieferanten
while($row = mysqli_fetch_assoc($result)){
    $pid_arr[] = $row['EID'];       // PID ("EID") zum Array hinzufügen
}

$pid_arr_str = implode(';',$pid_arr);

echo $pid_arr_str;
//echo "test;test;test;test";

$link->close();
