<?php
include_once("connection.php");
global $link;
//----------------- Verarbeitung Hinzufügen Produkte ------------------------
if (!empty($_POST["PID"])) {
    $hinzufuegen_produkt_PID = $_POST["PID"];
    $hinzufuegen_produkt_Pname = $_POST["Produktnameh"];
    $hinzufuegen_produkt_Epreis = $_POST["Einkaufspreish"];
    $hinzufuegen_produkt_Vpreis = $_POST["Verkaufspreish"];
    $hinzufuegen_produkt_Menge = $_POST["Mengeh"];
    $hinzufuegen_produkt_Lplatz = $_POST["Lagerplatzh"];
    $sql_lagerplaetze = "SELECT * FROM Lagerplaetze";
    $result_lagerplaetze = mysqli_query($link, $sql_lagerplaetze);



    $aktueller_lagerplatz = "";
    $count_lplatz = 0;
    foreach($result_lagerplaetze as $lagerplatz) {
        $aktueller_lagerplatz = $lagerplatz['Name'];
        if ($lagerplatz['Name'] == $hinzufuegen_produkt_Lplatz) {
            $count_lplatz = $lagerplatz['Lagerplaetze-ID'];
            break;
        }
    }

    // Falls Lagerplatz noch nicht angelegt
    if($aktueller_lagerplatz != $hinzufuegen_produkt_Lplatz){
        $sql_lagerplatz_hinzufügen = "INSERT INTO `MehrMarktDatabase`.`Lagerplaetze` (Name) VALUE ('$hinzufuegen_produkt_Lplatz')";
        $result_lagerplatz_hinzufuegen = mysqli_query($link, $sql_lagerplatz_hinzufügen);

        $sql_lagerplatz_count = "SELECT count(*) AS count FROM Lagerplaetze";
        $result_lagerplatz_count = mysqli_query($link, $sql_lagerplatz_count);
        $tmp = mysqli_fetch_assoc($result_lagerplatz_count);
        $count_lplatz = $tmp['count'];

        if ( !$result_lagerplatz_hinzufuegen || !$result_lagerplatz_count) {
            echo "Fehler während des sendens an die Datenbank bei lagerplatz.: ", mysqli_error($link);
            exit();
        }
    }


    $sql_produkt_hinzufuegen = "INSERT INTO `MehrMarktDatabase`.`Ware`  
                    (EID, Menge, Ware.Name, Einkaufspreis, `Lieferanten_Lieferanten-ID`, `Lagerplaetze_Lagerplaetze-ID`, Verkaufspreis) 
                    VALUES ('$hinzufuegen_produkt_PID','$hinzufuegen_produkt_Menge','$hinzufuegen_produkt_Pname','$hinzufuegen_produkt_Epreis',
                    null, '$count_lplatz','$hinzufuegen_produkt_Vpreis')";
    $result_lager_hinzufuegen = mysqli_query($link, $sql_produkt_hinzufuegen);
    if (!$result_lagerplaetze || !$result_lager_hinzufuegen) {
        echo "Fehler während des sendens an die Datenbank. bei Ware: ", mysqli_error($link);
        exit();
    }
    mysqli_free_result($result_lagerplaetze);
}
//----------------- Verarbeitung Bearbeitete Produkte ------------------------
if (!empty($_POST["lager_pid"])) {
    $bearbeitetes_produkt_PID = $_POST["lager_pid"];
    $bearbeitetes_produkt_Pname = $_POST["Produktname"];
    $bearbeitetes_produkt_Epreis = $_POST["Einkaufspreis"];
    $bearbeitetes_produkt_Vpreis = $_POST["Verkaufspreis"];
    $bearbeitetes_produkt_Menge = $_POST["Menge"];
    $bearbeitetes_produkt_Lplatz = $_POST["Lagerplatz"];
    $sql_lagerplaetze = "SELECT * FROM Lagerplaetze";
    $result_lagerplaetze = mysqli_query($link, $sql_lagerplaetze);

    $aktueller_lagerplatz = "";
    $count_lplatz = 0;
    foreach($result_lagerplaetze as $lagerplatz) {
        $aktueller_lagerplatz = $lagerplatz['Name'];
        if ($lagerplatz['Name'] == $bearbeitetes_produkt_Lplatz) {
            $count_lplatz = $lagerplatz['Lagerplaetze-ID'];
            break;
        }
    }

    // Falls Lagerplatz noch nicht angelegt
    if($aktueller_lagerplatz != $bearbeitetes_produkt_Lplatz){
        $sql_lagerplatz_bearbeitet = "INSERT INTO `MehrMarktDatabase`.`Lagerplaetze` (Name) VALUE ('$bearbeitetes_produkt_Lplatz')";
        $result_lagerplatz_bearbeiten = mysqli_query($link, $sql_lagerplatz_bearbeitet);

        $sql_lagerplatz_count = "SELECT count(*) AS count FROM Lagerplaetze";
        $result_lagerplatz_count = mysqli_query($link, $sql_lagerplatz_count);
        $tmp = mysqli_fetch_assoc($result_lagerplatz_count);
        $count_lplatz = $tmp['count'];

        if ( !$result_lagerplatz_bearbeiten || !$result_lagerplatz_count) {
            echo "Fehler während des sendens an die Datenbank bei lagerplatz.: ", mysqli_error($link);
            exit();
        }
    }




    $sql_produkt_bearbeiten = "UPDATE Ware 
                        SET Menge = '$bearbeitetes_produkt_Menge', 
                            Name = '$bearbeitetes_produkt_Pname', 
                            Verkaufspreis = '$bearbeitetes_produkt_Vpreis', 
                            Einkaufspreis = '$bearbeitetes_produkt_Epreis', 
                            `Lagerplaetze_Lagerplaetze-ID` = '$count_lplatz'
                        WHERE EID = '$bearbeitetes_produkt_PID'";
    $result_lager_bearbeiten = mysqli_query($link, $sql_produkt_bearbeiten);
    if (!$result_lager_bearbeiten || !$result_lagerplaetze) {
        echo "Fehler während des sendens an die Datenbank.:  ", mysqli_error($link);
        exit();
    }
    mysqli_free_result($result_lagerplaetze);
}
?>




    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewpLagerplatz" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="Lager.css">
        <title>Lager</title>
    </head>

    <body class="my_body">
    <div id="global_wrapper">
        <div id="nav_wrapper">
            <nav>
                <a href="index.php"><button id="bt_Auswertung"><img src="./img/auswertung.png" alt="Bild von Auswertung.">  Auswertung</button></a>
                <a href="Lager.php"><button id="bt_Lager"><img src="./img/lager.png" alt="Bild von Lager.">Lager</button></a>
                <a href="Lieferanten.php"><button id="bt_Lieferanten"><img src="./img/lieferant.png" alt="Bild von Lieferant.">Lieferanten</button></a>
                <a href="Einkauf.php"><button id="bt_Einkauf"><img src="./img/price-tag.png" alt="Bild von Einkauf.">Einkauf</button></a>
                <a href="Verkauf.php"><button id="bt_Verkauf"><img src="./img/shopping-cart.png" alt="Bild von Verkauf.">Verkauf</button></a>
            </nav>
        </div>

        <div id="content_wrapper">
            <div id="inner_content_wrapper">

                <div id="popup_div">
                    <div id="inner_popup">
                        <div class="firstchild">
                            <div class="img_wrapper" id="lagerpng">
                                <img src="./img/lager.png" alt="">
                            </div>
                            <span><b>Produkt - Bearbeiten:</b></span><br>
                        </div>
                        <div class="secondchild">
                            <form method="post" id="bearbeiten_lager" action="Lager.php">
                                <div>
                                    <label for="lager_pid">PID:</label>
                                    <input type="text"  readonly id="lager_pid" name="lager_pid" value="platzhalter" maxlength="15" style="font-weight: bold">
                                    <label for="Produktname">Produktname:</label>
                                    <input type="text" id="Produktname" name="Produktname" value="platzhalter" maxlength="60" required>
                                </div>
                                <!-- <br> -->
                                <div>
                                    <label for="Einkaufspreis">Einkaufspreis:</label>
                                    <input type="text" id="Einkaufspreis" name="Einkaufspreis" size="13" maxlength="11"
                                           value="platzhalter" oninput="this.value = this.value.replace(/[^0-9\.]+/g, '').replace(/(\..*)\./g, '$1');" required>
                                    <label for="Verkaufspreis">Verkaufspreis:</label>
                                    <input type="text" id="Verkaufspreis" name="Verkaufspreis" size="13" maxlength="11"
                                           value="platzhalter" oninput="this.value = this.value.replace(/[^0-9\.]+/g, '').replace(/(\..*)\./g, '$1');" required>
                                </div>
                                <div>
                                    <label for="Menge">Menge:</label>
                                    <input type="text" id="Menge" name="Menge" value="platzhalter" oninput="this.value = this.value.replace(/[^0-9]+/g, '').replace(/(\..*)\./g, '$1');" maxlength="10" required> 
                                    <label for="Lagerplatz">Lagerplatz:</label>
                                    <input type="text" id="Lagerplatz" name="Lagerplatz" maxlength="4" size="4"
                                           value="platzhalter" required>
                                </div>
                                <div>
                                    <button type="reset" class="bt_abbrechen2" id="bt_abbrechen2" onclick="func_abbrbutton2()">
                                        <div id="div_abbrechen">
                                            <img src="./img/cancel-square-button.png" alt="">
                                            <!-- <input type="button" id="add_button" name="add_button"> -->
                                            <div><span>Abbrechen</span></div>
                                        </div>
                                    </button>
                                    <button type="submit" class="bt_speichern2" id="bt_speichern2" onclick="func_speicherbutton2()">
                                        <div id="div_speichern">
                                            <img src="./img/save.png" alt="">
                                            <!-- <input type="button" id="add_button" name="add_button"> -->
                                            <div><span>Speichern</span></div>
                                        </div>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>






                <div id="Lager_wrapper">
                    <div id="Title_Lager_wrapper">
                        <div class="img_wrapper" id="lagerpng">
                            <img src="./img/lager.png" alt="">
                        </div>
                        <span><b>Lager:</b></span><br>
                        <button class="bt_addnew" id="bt_addnew">
                            <div id="div_addnew">
                                <img src="./img/plus-positive-add-mathematical-symbol.png" alt="">
                                <!-- <input type="button" id="add_button" name="add_button"> -->
                                <div><span>Hinzufügen</span></div>
                            </div>
                        </button>
                    </div>
                    <div id="adding_wrapper">
                        <div class="firstchild">
                            <div class="img_wrapper" id="lagerpng">
                                <img src="./img/lager.png" alt="">
                            </div>
                            <span><b>Produkt - Hinzufügen:</b></span><br>
                        </div>
                        <div class="secondchild">
                            <form action="Lager.php" method="post" id="hinzufuegen_lager">
                                <div>
                                    <label for="PID">PID:</label>
                                    <input type="text" id="PID" name="PID" maxlength="15" oninput="this.value = this.value.replace(/[^0-9]+/g, '').replace(/(\..*)\./g, '$1');" required>
                                    <label for="Produktnameh">Produktname:</label>
                                    <input type="text" id="Produktnameh" name="Produktnameh" maxlength="60" required>
                                </div>
                                <p id="Exception_PID_schon_vergeben"> PID schon vergeben!</p>
                                <div>
                                    <label for="Einkaufspreish">Einkaufspreis:</label>
                                    <input type="text" id="Einkaufspreish" name="Einkaufspreish" maxlength="11" size="10" oninput="this.value = this.value.replace(/[^0-9\.]+/g, '').replace(/(\..*)\./g, '$1');" required>
                                    <label for="Verkaufspreish">Verkaufspreis:</label>
                                    <input type="text" id="Verkaufspreish" name="Verkaufspreish" maxlength="11" size="10" oninput="this.value = this.value.replace(/[^0-9\.]+/g, '').replace(/(\..*)\./g, '$1');" required>
                                    <label for="Mengeh">Menge:</label>
                                    <input type="text" id="Mengeh" name="Mengeh" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]+/g, '').replace(/(\..*)\./g, '$1');" required>
                                    <label for="Lagerplatzh">Lagerplatz:</label>
                                    <input type="text" id="Lagerplatzh" name="Lagerplatzh" maxlength="4" size="4" required>
                                </div>
                                <div>
                                    <button  type="reset" class="bt_abbrechen" id="bt_abbrechen" onclick="func_abbrbutton()">
                                        <div id="div_abbrechen">
                                            <img src="./img/cancel-square-button.png" alt="">
                                            <!-- <input type="button" id="add_button" name="add_button"> -->
                                            <div><span>Abbrechen</span></div>
                                        </div>
                                    </button>

                                    <button type="submit" class="bt_speichern" id="bt_speichern" onclick="func_speicherbutton()">
                                        <div id="div_speichern">
                                            <img src="./img/save.png" alt="">
                                            <!-- <input type="button" id="add_button" name="add_button"> -->
                                            <div><span>Speichern</span></div>
                                        </div>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>


                <?php
                //---------------- Verarbeitung und Ausgabe Lagerbestand Tabelle ------------------
                
                $sql_lagertabelle = "SELECT w.EID PID, w.Name Produktname, w.Menge , lp.Name Lagerplatz, w. Einkaufspreis ,
                        w.Verkaufspreis FROM WARE w LEFT JOIN Lagerplaetze lp on w.`Lagerplaetze_Lagerplaetze-ID` = lp.`Lagerplaetze-ID` ORDER BY w.Name";
                $result_Lagertabelle = mysqli_query($link, $sql_lagertabelle);
                if (!$result_Lagertabelle) {
                    echo "Fehler während der Abfrage für Lagertabelle.:  ", mysqli_error($link);
                    exit();
                }
                // ---------------  STATUS LAGER ----------------

                function lager_status($link, $id, $menge) {
                    $status = "nicht vorrätig";
                    if ($menge != 0) {
                        $status = "vorrätig";
                    }
                    $sql_in_einkauf = "SELECT CASE WHEN EXISTS (SELECT e.`Datum-RealAnkunft`
                                                                FROM Einkauf e
                                                                    JOIN ware_has_einkauf wha ON e.`Einkauf-ID` = wha.`Einkauf_Einkauf-ID`
                                                                    JOIN ware w ON wha.`Ware_EID` = w.EID
                                                                WHERE Ware_EID = '$id' AND e.`Datum-RealAnkunft` is null) 
                                                THEN CAST(1 AS binary ) 
                                                ELSE CAST(0 AS binary ) END as bool";
                    $result_in_einkauf = mysqli_query($link, $sql_in_einkauf);
                    if (!$result_in_einkauf) {
                        echo "Fehler während der Abfrage für Status.:  ", mysqli_error($link);
                        exit();
                    }
                    $bool_in_einkauf = mysqli_fetch_assoc($result_in_einkauf);
                    if ($bool_in_einkauf['bool'] == '1') {
                        $status = $status. ", In Bestellung";
                    }
                    return $status;
                }
                echo '<span><b>Lagerbestand</b></span> 
                         
                        <table> 
                            <tr> 
                                <th>PID</th> 
                                <th>Produktname</th> 
                                <th>Menge</th> 
                                <th>Lagerplatz</th> 
                                <th>Status</th> 
                                <th>Einkaufspreis</th> 
                                <th>Verkaufspreis</th> 
                                <th>Bearbeiten</th>                          
                            </tr>';?>
                <script>
                    let row = [];
                </script>
                <?php
                $which_row = 0;
                foreach($result_Lagertabelle as $eintrag) {
                    echo ' <tr class = "new_tr"> 
                                           <td>' . $eintrag['PID'] . '</td>';
                                           
                                           if(mb_strwidth($eintrag['Produktname']) >=30)
                                           {
                    echo                   '<td> <div id="produktname_div_container"><div id="produktname_div">' . $eintrag['Produktname'] . '</div></div></td>';
                                           }else
                                           {
                    echo                   '<td> <div><div>' . $eintrag['Produktname'] . '</div></div></td>';
                                           };
                    echo                   '<td style="text-align: right;">' . $eintrag['Menge'] . '</td> 
                                           <td>' . $eintrag['Lagerplatz'] . '</td> 
                                           <td>' . lager_status($link, $eintrag['PID'], $eintrag['Menge']) . '</td>
                                           <td style="text-align: right;">' . $eintrag['Einkaufspreis'] . '€ </td>
                                           <td style="text-align: right;">' . $eintrag['Verkaufspreis'] . '€ </td>
                                           <script>  
                                           //---- sowie Speicherung der Informationen die beim Bearbeiten displayed werden müssen---------- 
                                                    row.push(new Array("' . $eintrag['PID'] . '","' . $eintrag['Produktname'] . '","' . $eintrag['Menge'] . '","' . $eintrag['Lagerplatz'] . '",
                                                                            "' . $eintrag['Einkaufspreis'] . '","' . $eintrag['Verkaufspreis'] . '"));
                                           </script> 
                                           <td> 
                                           <div> 
                                           <button class="bt_bearbeiten" onclick="func_bearbeitenbutton(row[' . $which_row . '])">
                                           <img src="./img/pen.png" alt="Stift"> 
                                           </button> 
                                           </div>                                           
                                           </td> 
                            </tr>';
                    $which_row++;
                }
                ?>

                </table>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        const table = document.querySelector("table");
        table.addEventListener("click", function (e)
        {
            const td = e.target;
            if (td.classList.contains("expander"))
            {
                const style = td.parentNode.nextElementSibling.style;
                const wasOpen = !style.display;
                console.log(wasOpen);
                style.display = wasOpen ? "none" : "";
                td.textContent = wasOpen ? "▼" : "▲";
            }
        });


        document.getElementById("bt_addnew").onclick = function () { func_addbutton() };
        function func_addbutton()
        {
            document.getElementById("Title_Lager_wrapper").style.display = "none";
            document.getElementById("adding_wrapper").style.display = "flex";
        }
        document.getElementById("bt_abbrechen").onclick = function () { func_abbrbutton() };
        function func_abbrbutton()
        {
            document.getElementById("Title_Lager_wrapper").style.display = "flex";
            document.getElementById("adding_wrapper").style.display = "none";
        }
        document.getElementById("bt_speichern").onclick = function () { func_speicherbutton() };
        function func_speicherbutton(){
            let exception = false;

            const PID = document.getElementById('PID').value;

            // Prüfe ob PID bereits genutzt.

            for(let i = 0; i < row.length ; i++){
                if(PID == row[i][0]) {
                    document.getElementById("Exception_PID_schon_vergeben").style.display = "flex";
                    event.preventDefault();
                    exception = true;
                    break;
                }
            }
            // PID Exception soll nur angezeigt werden wenn PID schon vorhanden.
            if(!exception)
                document.getElementById("Exception_PID_schon_vergeben").style.display = "none";

            // Stelle sicher das alle Felder ausgefüllt sind.
            const Name = document.getElementById('Produktnameh')[0].value;
            const Vpreis = document.getElementById('Verkaufspreish')[0].value;
            const Epreis = document.getElementById('Einkaufspreish')[0].value;
            const Lplatz = document.getElementById('Lagerplatzh')[0].value;
            const Menge = document.getElementById('Mengeh')[0].value;

            // Wenn es keine Fehler gibt, gehts weiter.
            if(!exception) {
                document.getElementById("Title_Lager_wrapper").style.display = "flex";
                document.getElementById("adding_wrapper").style.display = "none";
            }
        }


        document.getElementById("bt_abbrechen2").onclick = function () { func_abbrbutton2() };
        function func_abbrbutton2()
        {
            document.getElementById("popup_div").style.display = "none";
            document.body.classList.remove('noscroll');

        }
        document.getElementById("bt_speichern2").onclick = function () { func_speicherbutton2() };
        function func_speicherbutton2()
        {
            //TODO optional.
            //man muss placeholder anstelle von value in bearbeiten setzen und dann prüfen das auch alle felder gesetzt sind

            document.getElementById("popup_div").style.display = "none";
            document.body.classList.remove('noscroll');
        }

        document.getElementsByClassName("bt_bearbeiten").onclick = function () { func_bearbeitenbutton() };

        function func_bearbeitenbutton(row)
        {
            document.getElementById("popup_div").style.display = "flex";
            document.body.classList.add('noscroll');

            document.getElementsByName("lager_pid")[0].value = row[0];
            document.getElementsByName('Menge')[0].value= row[2];
            document.getElementsByName('Einkaufspreis')[0].value= row[4];
            document.getElementsByName('Verkaufspreis')[0].value= row[5];
            document.getElementsByName('Lagerplatz')[0].value= row[3];
            document.getElementsByName('Produktname')[0].value= row[1];
        }





    </script>
    </body>

    </html>
<?php
//-------------- Link schließen DB ----------------------
mysqli_free_result($result_Lagertabelle);
mysqli_close($link);