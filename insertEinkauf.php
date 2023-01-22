<?php
include_once("connection.php");


global $link;
$data = $_POST['data'];
if (empty($data)) {
    exit();
}
$data_arr = explode(',', $data);
$sql_select_lieferanten_id = "SELECT `Lieferanten-ID` FROM lieferanten 
                                WHERE Name = '". $data_arr[0] ."'";
$result = mysqli_query($link, $sql_select_lieferanten_id);
$lid = $result->fetch_assoc();

$sql_insert_einkauf = "INSERT INTO einkauf(`Datum-Ankunft`,`Lieferanten_Lieferanten-ID`)
                        VALUES('" . $data_arr[1] . "', '" . $lid["Lieferanten-ID"] . "')";
$result = mysqli_query($link, $sql_insert_einkauf);

$sql_select_einkauf_id = "SELECT `Einkauf-ID` FROM einkauf ORDER BY `Einkauf-ID` DESC LIMIT 1;";
$result = $link->query($sql_select_einkauf_id);
$einkauf_id = $result->fetch_assoc();

for ($i = 2; $i < count($data_arr); $i++) {
var_dump($data_arr[$i]);
    $sql_select_ware_preis = "SELECT Einkaufspreis FROM ware WHERE '". $data_arr[$i] ."' = EID;";
    $result = $link->query($sql_select_ware_preis);
    $ware_preis = $result->fetch_assoc();
    var_dump($ware_preis);

    $sql_insert_ware_has_einkauf = "INSERT INTO `ware_has_einkauf` (`Ware_EID`, `Menge`, `Einkauf_Einkauf-ID`, `Einkaufspreis_State`)
                                        VALUES('". $data_arr[$i] ."', '". $data_arr[++$i] ."', '". $einkauf_id["Einkauf-ID"] ."', '". $ware_preis["Einkaufspreis"] ."')";

    $result = $link->query($sql_insert_ware_has_einkauf);
    //$result->fetch_assoc();
}

header("location: Einkauf.php");
