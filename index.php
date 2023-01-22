<?php
include_once("connection.php");
global $link;

// ------------------- Lagerauslastung ---------------
function lagerauslastung(): void
{
    global $link;
    $sql_lager_frei = "SELECT lg.`Lagerplaetze-ID` , SUM(w.Menge) AS Gesamtmenge
                            FROM lagerplaetze lg
                                LEFT JOIN ware w ON w.`Lagerplaetze_Lagerplaetze-ID` = lg.`Lagerplaetze-ID`
                            WHERE lg.ISDELETE = 0
                            GROUP BY lg.`Lagerplaetze-ID`HAVING Gesamtmenge = 0 OR Gesamtmenge is null;";

    $result_sql_lager_frei = mysqli_query($link, $sql_lager_frei);
    if (!$result_sql_lager_frei) {
        echo "Verbindung fehlgeschlagen: ", mysqli_connect_error();
    }

    $rowcount = mysqli_num_rows( $result_sql_lager_frei );


    $sql_lagerplaetze_anzahl = "SELECT COUNT(`Lagerplaetze-ID`) AS Lagerplaetze_anzahl
                            FROM lagerplaetze;";

    $result_sql_lagerplaetze_anzahl = mysqli_query($link, $sql_lagerplaetze_anzahl);
    if (!$result_sql_lagerplaetze_anzahl) {
        echo "Verbindung fehlgeschlagen: ", mysqli_connect_error();
    }
    $lagerplaetze_anzahl = mysqli_fetch_assoc($result_sql_lagerplaetze_anzahl);
    $lagerplaetze_belegt = $lagerplaetze_anzahl['Lagerplaetze_anzahl']-$rowcount;

    if($lagerplaetze_anzahl['Lagerplaetze_anzahl'] > 0){
        $lagerauslastung = ($lagerplaetze_belegt/$lagerplaetze_anzahl['Lagerplaetze_anzahl']) * 100;
    }
    else{
        $lagerauslastung = 0;
    }

    echo '<span><b>Lagerauslastung:</b> Von ' .$lagerplaetze_anzahl['Lagerplaetze_anzahl']. ' Plätzen werden '.$lagerplaetze_belegt.' verwendet. ('.round($lagerauslastung,2).'%) </span>';
}


// ------------------- heute ankommende Bestellungen: Lieferungen-Tabelle ---------------
function heute_ankommende_tabelle(): void
{
    global $link;
    $sql_heute_angekommen_tabelle = "SELECT e.`Einkauf-ID`, l.Name, e.`Datum-Ankunft`, e.`Datum-RealAnkunft`
FROM Einkauf e
         JOIN lieferanten l ON e.`Lieferanten_Lieferanten-ID` = l.`Lieferanten-ID`
         JOIN ware_has_einkauf wha ON e.`Einkauf-ID` = wha.`Einkauf_Einkauf-ID`
WHERE (e.`Datum-RealAnkunft` is null AND e.`Datum-Ankunft` <= CURRENT_DATE()) -- alle ausstehende Bestellungen die schon da sein sollten und die heute kommen sollen 
GROUP BY e.`Einkauf-ID`
ORDER BY e.`Einkauf-ID`;";
    $result_heute_angekommen_tabelle = mysqli_query($link, $sql_heute_angekommen_tabelle);
    if (!$result_heute_angekommen_tabelle) {
        echo "Verbindung fehlgeschlagen: ", mysqli_connect_error();
        exit();
    }

    $which_row = 0;
    foreach ($result_heute_angekommen_tabelle as $eintrag) {
        if ($eintrag['Datum-Ankunft'] < date("Y-m-d")) {             // zum testem statt date(...): '2022-06-20'
            echo '<tr class="red">
                    <td class="expander">▼</td> 
                    <td>' . $eintrag['Einkauf-ID'] . '</td> 
                    <td>' . $eintrag['Name'] . '</td> 
                    <td>' . $eintrag['Datum-Ankunft'] . '</td> 
                 </tr>';
        } else
            echo '<tr>
                    <td class="expander">▼</td> 
                    <td>' . $eintrag['Einkauf-ID'] . '</td> 
                    <td>' . $eintrag['Name'] . '</td> 
                    <td>heute</td> 
                 </tr>';

        echo ' <tr style="display: none"> 
                            <td></td> 
                            <td colspan="100"> 
                                <table> 
                                    <thead> 
                                        <tr> 
                                            <th>PID</th> 
                                            <th>Produktname</th> 
                                            <th>Menge</th> 
                                        </tr> 
                                    </thead> 
                                    <tbody>';
// Ausgabe des Einkaufs vom Lieferanten
        global $link;
        heute_ankommende_produkte($link, $eintrag['Einkauf-ID']);
        echo ' </tbody> 
                                   </table> 
                                </td> 
                            </tr>';
        $which_row++;
    }
}

// ------------------- heute angekommene Bestellungen: Produkt-Tabelle (ausklappbar) ---------------
function heute_ankommende_produkte($link, $eeid): void
{
    $sql_auswertung_produkte = "SELECT w.EID, w.Name, wha.Menge
                            FROM Ware w
                                JOIN ware_has_einkauf wha ON w.EID = wha.`Ware_EID`
                            WHERE wha.`Einkauf_Einkauf-ID` ='$eeid'";

    $result_auswertung_produkte = mysqli_query($link, $sql_auswertung_produkte);

    if (!$result_auswertung_produkte) {
        echo "Verbindung fehlgeschlagen: ", mysqli_connect_error();
        exit();
    }
    foreach ($result_auswertung_produkte as $product) {
        $eid = $product['EID'];
        $name = $product['Name'];
        $menge = $product['Menge'];
        echo '<tr><td>' . $eid . '</td><td>' . $name . '</td><td style="text-align: right;">' . $menge . '</td> </tr>';
    }
}

?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="Auswertung.css">
    <title>Auswertung</title>
</head>

<body>
<div id="global_wrapper">
    <div id="nav_wrapper">
        <nav>
            <a href="index.php">
                <button id="bt_Auswertung"><img src="./img/auswertung.png"> Auswertung</button>
            </a>
            <a href="Lager.php">
                <button id="bt_Lager"><img src="./img/lager.png">Lager</button>
            </a>
            <a href="Lieferanten.php">
                <button id="bt_Lieferanten"><img src="./img/lieferant.png">Lieferanten</button>
            </a>
            <a href="Einkauf.php">
                <button id="bt_Einkauf"><img src="./img/price-tag.png">Einkauf</button>
            </a>
            <a href="Verkauf.php">
                <button id="bt_Verkauf"><img src="./img/shopping-cart.png">Verkauf</button>
            </a>
        </nav>
    </div>

    <div id="content_wrapper">
        <div id="inner_content_wrapper">
            <div id="Lagerzustand_wrapper">
                <div class="img_wrapper" id="lagerzustandpng">
                    <img src="./img/lager.png" alt="">
                </div>
                <?php lagerauslastung(); ?>
            </div>
            <div id="HeuteAnkommendeBestellungen_wrapper">
                <div id="Title_HeuteAnkommendeBestellungen_wrapper">
                    <div class="img_wrapper" id="lieferpng">
                        <img src="./img/delivery-time.png" alt="">
                    </div>
                    <span><b>Heute ankommende Bestellungen:</b></span><br>
                </div>

                <table>
                    <tr>
                        <th></th>
                        <th>Einkauf-ID</th>
                        <th>Lieferant</th>
                        <th>urspüngliches Lieferdatum</th>
                    </tr>
                    <?php heute_ankommende_tabelle() ?>
                </table>
            </div>


            <div id="HaeufigVerkaufteWare_wrapper">
                <div id="Title_HaeufigVerkaufteWare_wrapper">
                    <div class="img_wrapper" id="lieferpng">
                        <img src="./img/price-tag.png" alt="">
                    </div>
                    <span><b>Häufig verkaufte Ware:</b></span><br>
                </div>

                <table>
                    <tr>
                        <th>Platz</th>
                        <th>Produktname</th>
                        <th>PID</th>
                        <th>Menge</th>
                    </tr>
                    <?php

                    $sql = "Select EID,Name,sum(`Ware_has_Verkauf/Warenkoerbe`.`Menge`) as summenge from `Ware_has_Verkauf/Warenkoerbe` Join Ware on Ware_EID = EID group by Ware_EID ORDER by sum(`Ware_has_Verkauf/Warenkoerbe`.`Menge`) desc limit 10;";
                    $result = mysqli_query($link, $sql);
                    $count = 1;
                    while ($row = mysqli_fetch_assoc($result)) {

                        echo "<tr>" .
                            "<td>" . $count . "</td>" .
                            "<td>" . $row["Name"] . "</td>" .
                            "<td>" . $row["EID"] . "</td>" .
                            "<td style='text-align: right;'>" . $row["summenge"] . "</td>" .
                            "</tr>";
                        $count++;
                    }
                    mysqli_free_result($result);
                    ?>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    const table = document.querySelector("table");
    table.addEventListener("click", function (e) {
        const td = e.target;
        if (td.classList.contains("expander")) {
            const style = td.parentNode.nextElementSibling.style;
            const wasOpen = !style.display;
            console.log(wasOpen);
            style.display = wasOpen ? "none" : "";
            td.textContent = wasOpen ? "▼" : "▲";
        }
    });
</script>
</body>

</html>
