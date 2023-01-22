<?php
include_once("connection.php");

global $link;

//$pid = $_COOKIE['pid'] ?? null;
$pid = $_REQUEST["p"];
$sql_get_produktinfos = "";

if (!is_null($pid)) {

    $sql_get_produktinfos = "SELECT Name, Einkaufspreis FROM ware
                                WHERE Eid = '$pid'";

    $result = $link->query($sql_get_produktinfos);      // Abfrage starten

    if (!$result) {
        echo "Fehler bei Abfrage der Datenbank: " . mysqli_error($link);
    }

    // Die abgefragte Zeile erhalten
    $neuer_einkauf = $result->fetch_assoc();
    $produktname = $neuer_einkauf['Name'];
    $einkaufspreis = $neuer_einkauf['Einkaufspreis'];

    //$warenkorb_arr[] = array($pid,$lieferdatum,$lieferantname,$menge);
    //var_dump($warenkorb_arr);
    //echo 'let tmp_array = new Array(' . json_encode($produktname) . ',' . json_encode($einkaufspreis) . ');';
    echo "$produktname;$einkaufspreis";
    //setcookie("pid",$pid,-3600);
}

/*
$neuer_eintrag = get_produkt_infos($_COOKIE['produkt_id']);
//echo '<script>alert("Hello");</script>';
//$pid = $_COOKIE['produkt_id'];
echo 'warenkorb_tmp[warenkorb_item][0]=pid;';
echo 'warenkorb_tmp[warenkorb_item][1]=' . json_encode($neuer_eintrag['Name']) . ';';
echo 'warenkorb_tmp[warenkorb_item][2]=' . json_encode($neuer_eintrag['Einkaufspreis']) . ';';
echo 'warenkorb_tmp[warenkorb_item][3]=menge;';
echo 'warenkorb_tmp[warenkorb_item][4]=menge * warenkorb_tmp[warenkorb_item][2];';    // Zwischensumme = Menge * Einkaufspreis
echo 'warenkorb_item++;';    // Platz für evtl. neues Produkt im Warenkorb schaffen
*/

?>