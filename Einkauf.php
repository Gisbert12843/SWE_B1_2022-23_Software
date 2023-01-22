<?php
//session_start();
include_once("connection.php");
global $link;
if (!$link) {
    echo "Verbindung fehlgeschlagen: ", mysqli_connect_error();
    exit();
}

// ------------------- Einkauf Angekommen-Fkt. ---------------
function einkauf_angekommen()
{
    $submitted = (int)($_POST['submitted'] ?? NULL);

    // guckt ob der "Speichern" Button gedrückt wurde
    if ($submitted) {
        // wählt alle zu abhakbaren Einkäufe aus
        $sql_ausstehende_einkaufe = "SELECT `Einkauf-ID`
                                    FROM einkauf
                                    WHERE `Datum-RealAnkunft` is null;";
        global $link;
        $result_ausstehende_einkaufe = mysqli_query($link, $sql_ausstehende_einkaufe);

        if (!$result_ausstehende_einkaufe) {
            echo "Verbindung fehlgeschlagen: ", mysqli_connect_error();
            exit();
        }

        // geht jeden abhakbaren Einkauf durch
        foreach ($result_ausstehende_einkaufe as $eintrag) {
            $einkaufID = (int)($_POST[$eintrag['Einkauf-ID']] ?? NULL);
            // falls Einkauf abgehakt wurde, dann wird dessen "Real-Ankunft" auf heute gesetzt
            if ($einkaufID) {
                $sql_einkauf_da = "UPDATE einkauf
                                  SET `Datum-RealAnkunft` = CURRENT_DATE()
                                  WHERE `Einkauf-ID` = '$einkaufID'";
                mysqli_query($link, $sql_einkauf_da);

                $sql_get_ordered_products = "SELECT Ware_EID,Menge FROM ware_has_einkauf WHERE `Einkauf_Einkauf-ID` = '$einkaufID'";
                $result = $link->query($sql_get_ordered_products);
                while($row = $result->fetch_assoc()){
                    $produkt_id = $row['Ware_EID'];
                    $gekaufteMenge = $row['Menge'];
                    $sql_update_ware = "UPDATE ware
                                    SET `Menge` = `Menge`+ '$gekaufteMenge'
                                    WHERE `EID` = '$produkt_id'";
                    $link->query($sql_update_ware);
                }

                $sql_get_lieferanten_id = "SELECT `Lieferanten_Lieferanten-ID` FROM einkauf WHERE `Einkauf-ID` = '$einkaufID' LIMIT 1";
                $result1 = $link->query($sql_get_lieferanten_id)->fetch_assoc();
                $lid = $result1['Lieferanten_Lieferanten-ID'];      // Lieferanten-ID vom Einkauf

                $sql_get_anzahl_einkäufe_beim_lieferant = "SELECT COUNT('*') AS anzahlLieferungen FROM einkauf WHERE `Lieferanten_Lieferanten-ID` = '$lid' AND `Datum-RealAnkunft` IS NOT NULL";
                $anzahl_lieferungen = $link->query($sql_get_anzahl_einkäufe_beim_lieferant)->fetch_object()->anzahlLieferungen;

                $sql_get_pünktliche_einkäufe_beim_lieferant = "SELECT COUNT('*') AS anzahlPünktlicheLieferungen
                                                                FROM einkauf
                                                                WHERE `Lieferanten_Lieferanten-ID` = '$lid' AND `Datum-RealAnkunft` IS NOT NULL AND DATEDIFF(`Datum-Ankunft`,`Datum-RealAnkunft`) >= 0";
                $anzahl_pünktliche_lieferungen = $link->query($sql_get_pünktliche_einkäufe_beim_lieferant)->fetch_object()->anzahlPünktlicheLieferungen;


                //$sql_select_daten = "SELECT DATEDIFF(day,`Datum-Ankunft`,`Datum-RealAnkunft`) FROM einkauf WHERE `Einkauf-ID` = '$einkaufID'";
                //$datum_differenz = $link->query($sql_select_daten)->fetch_assoc();

                // Zuverlässigkeit des Lieferanten aktualisieren
                $sql_update_lieferanten_zuverlässigkeit = "UPDATE lieferanten SET `Zuverlaessigkeit` = '$anzahl_pünktliche_lieferungen'/'$anzahl_lieferungen' WHERE `Lieferanten-ID` = '$lid'";
                $link->query($sql_update_lieferanten_zuverlässigkeit);
                // Wenn Einkauf verspätet ist
                //if ($datum_differenz > 0){

                //else{
                    //$sql_update_lieferanten_zuverlässigkeit = "UPDATE lieferanten SET `Zuverlaessigkeit` = ((`Zuverlaessigkeit`+1)*100)/101";

            }



        }
    }
}

// ------------------- SQL Einkauf Produkte Tabelle ---------------
function einkauf_produkte($link, $eeid):
void
{
    $sql_einkauf_produkte = "SELECT w.EID, w.Name, wha.Einkaufspreis_State, wha.Menge, ROUND((wha.Einkaufspreis_State * wha.Menge),2) AS Zwischensumme
                            FROM Ware w
                                JOIN ware_has_einkauf wha ON w.EID = wha.`Ware_EID`
                            WHERE wha.`Einkauf_Einkauf-ID` ='$eeid'";

    $result_einkauf_produkte = mysqli_query($link, $sql_einkauf_produkte);

    if (!$result_einkauf_produkte) {
        echo "Verbindung fehlgeschlagen: ", mysqli_connect_error();
        exit();
    }
    foreach ($result_einkauf_produkte as $product) {
        $eid = $product['EID'];
        $name = $product['Name'];
        $einkaufspreis = $product['Einkaufspreis_State'];
        $menge = $product['Menge'];
        $zwischensumme = $product['Zwischensumme'];
        echo '<tr><td>' . $eid . '</td><td>' . $name . '</td><td style="text-align: right;">' . $einkaufspreis . '&euro;</td><td style="text-align: right;">' . $menge . '</td><td style="text-align: right;">' . $zwischensumme . '&euro;</td> </tr>';
    }
}

// ------------------- Einkauf Produkte Tabelle anzeigen ---------------
function tabelle_anzeigen($angekommen)
{
    $which_row = 0;
    global $link;
    $datum_sort = "e.`Datum-RealAnkunft`";

    if (!$angekommen) {
        $datum_sort = "e.`Datum-Ankunft`";
    }

    // ------------------- SQL Einkauf Tabelle ----------------------
    $sql_einkauf_tabelle = "SELECT e.`Einkauf-ID`, l.Name, e.`Datum-Ankunft`, e.`Datum-RealAnkunft`, ROUND(SUM(wha.Einkaufspreis_State * wha.Menge), 2) AS Summe
                            FROM Einkauf e
                                JOIN lieferanten l ON e.`Lieferanten_Lieferanten-ID` = l.`Lieferanten-ID`
                                JOIN ware_has_einkauf wha ON e.`Einkauf-ID` = wha.`Einkauf_Einkauf-ID`
                                JOIN ware w ON wha.`Ware_EID` = w.EID
                            GROUP BY e.`Einkauf-ID`
                            ORDER BY " . $datum_sort . " desc ;";
    $result_einkauf_tabelle = mysqli_query($link, $sql_einkauf_tabelle);
    if (!$result_einkauf_tabelle) {
        echo "Verbindung fehlgeschlagen: ", mysqli_connect_error();
        exit();
    }


    foreach ($result_einkauf_tabelle as $eintrag) {

        if (!($angekommen || isset($eintrag['Datum-RealAnkunft']))) { // Nur anzeigen, wenn noch Einkauf noch nicht angekommen
            if ($eintrag['Datum-Ankunft'] < date("Y-m-d")) {
                echo '<tr class="verspaetet">';
            } else
                echo '<tr>';

            echo ' <!--<tr class="new_tr"> -"new_tr unzuverlässig" wenn unzuverlässig ---> 
                      <td class="expander">▼</td> 
                      <td>' . $eintrag['Einkauf-ID'] . '</td> 
                      <td>' . $eintrag['Name'] . '</td> 
                      <td>' . $eintrag['Datum-Ankunft'] . '</td> 
                      <td style="text-align: right;">' . $eintrag['Summe'] . '&euro;</td>                      
                      <td class="checkbox_angekommen"><input type="checkbox" form="bt_save_form" id="' . $eintrag['Einkauf-ID'] . '" value="' . $eintrag['Einkauf-ID'] . '" name="' . $eintrag['Einkauf-ID'] . '"></td>
                 </tr>';
        } else if ($angekommen && isset($eintrag['Datum-RealAnkunft'])) {
            if ($eintrag['Datum-Ankunft'] < $eintrag['Datum-RealAnkunft']) {
                echo '<tr class="verspaetet">';
            } else
                echo '<tr>';

            echo ' <!-- <tr class="new_tr"> -"new_tr unzuverlässig" wenn unzuverlässig ---> 
                      <td class="expander">▼</td> 
                      <td>' . $eintrag['Einkauf-ID'] . '</td> 
                      <td>' . $eintrag['Name'] . '</td> 
                      <td>' . $eintrag['Datum-Ankunft'] . '</td>
                      <td>' . $eintrag['Datum-RealAnkunft'] . '</td> 
                      <td style="text-align: right;">' . $eintrag['Summe'] . '&euro;</td>
                   </tr>';
        }
        echo ' <tr style="display: none"> 
                            <td></td> 
                            <td colspan="100"> 
                                <table> 
                                    <thead> 
                                        <tr> 
                                            <th>PID</th> 
                                            <th>Produktname</th> 
                                            <th>Einkaufspreis</th> 
                                            <th>Menge</th>
                                            <th>Zwischensumme</th>
                                        </tr> 
                                    </thead> 
                                    <tbody>';
// Ausgabe des Einkaufs vom Lieferanten
        global $link;
        einkauf_produkte($link, $eintrag['Einkauf-ID']);
        echo ' </tbody> 
                                   </table> 
                                </td> 
                            </tr>';
        $which_row++;

    }
}

// ------------------- Lieferanten-DropDown-Auswahl ---------------
function lieferanten_anzeigen()
{
    $sql_lieferanten_namen = "SELECT name
                            FROM lieferanten WHERE Aktiv = 1
                            ORDER BY name";

    global $link;
    $result_lieferanten_name = mysqli_query($link, $sql_lieferanten_namen);

    if (!$result_lieferanten_name) {
        echo "Verbindung fehlgeschlagen: ", mysqli_connect_error();
        exit();
    }

    foreach ($result_lieferanten_name as $lieferant) {
        echo '<option value="' . $lieferant['name'] . '">' . $lieferant['name'] . '</option>';

        //<option value="Müller GmbH">Müller GmbH</option>
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="Einkauf.css">
    <title>Einkauf</title>
</head>

<body>
<?php einkauf_angekommen(); ?>


<div id="global_wrapper">
    <div id="nav_wrapper">
        <nav>
            <a href="index.php">
                <button id="bt_Auswertung"><img src="./img/auswertung.png">
                    Auswertung
                </button>
            </a>
            <a href="Lager.php">
                <button id="bt_Lager"><img src="./img/lager.png">Lager</button>
            </a>
            <a href="Lieferanten.php">
                <button id="bt_Lieferanten"><img
                            src="./img/lieferant.png">Lieferanten
                </button>
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

            <div id="Einkauf_wrapper">
                <div id="Title_Einkauf_wrapper">
                    <div class="img_wrapper" id="einkaufpng">
                        <img src="./img/price-tag.png" alt="">
                    </div>
                    <span><b>Einkäufe:</b></span><br>
                    <button class="bt_addnew" id="bt_addnew" onclick="func_addbutton()">
                        <div id="div_addnew">
                            <img src="./img/plus-positive-add-mathematical-symbol.png" alt="">
                            <!-- <input type="button" id="add_button" name="add_button"> -->
                            <div><span>Hinzufügen</span></div>
                        </div>
                    </button>
                </div>
                <div id="adding_wrapper">
                    <div class="firstchild">
                        <div class="img_wrapper" id="einkaufpng">
                            <img src="./img/price-tag.png" alt="">
                        </div>
                        <span><b>Einkauf - Hinzufügen:</b></span><br>
                    </div>
                    <div class="secondchild">
                        <!--<form action="">-->

                        <div>
                            <label for="lieferant">Lieferant:</label>
                            <select id="lieferant_select" name="lieferant" required onchange="pid_anzeigen()">
                                <option hidden disabled selected value> -- Wählen Sie einen Lieferanten aus --</option>
                                <?php lieferanten_anzeigen(); ?>
                            </select>
                            <label for="lieferdatum">Lieferdatum:</label>
                            <input type="date" id="lieferdatum" name="lieferdatum" required>
                        </div>
                        <!-- <br> -->
                        <div id="secondchild_1">
                            <div id="addproduct_div">

                                <label for="PID">PID:</label>
                                <!--<input type="text" id="PID" name="PID" required>-->
                                <select id="PID" name="PID" disabled >
                                    <option hidden disabled selected value> -- Wähle eine PID aus --</option>
                                </select>
                                <label for="menge">Menge:</label>
                                <input type="text" id="menge" name="menge" maxlength="10" size="4" required>
                                <!--<input id="produktinfos" hidden>-->

                                <br>
                                <!--<span id="PID_warning">PID nicht beim Lieferanten hinterlegt!</span>-->
                            </div>
                            <button class="bt_add2" id="bt_add2" onclick="func_addbutton_inner()">
                                <div id="div_add2">
                                    <img src="./img/plus-positive-add-mathematical-symbol.png" alt="">
                                    <!-- <input type="button" id="add_button" name="add_button"> -->
                                    <div><span>Hinzufügen</span></div>
                                </div>
                            </button>
                        </div>
                        <p id="fehlermeldung" class="red"></p>
                        <!--</form>-->

                        <div>
                            <button class="bt_abbrechen" id="bt_abbrechen" onclick="func_abbrbutton()">
                                <div id="div_abbrechen">
                                    <img src="./img/cancel-square-button.png" alt="">
                                    <!-- <input type="button" id="add_button" name="add_button"> -->
                                    <div><span>Abbrechen</span></div>
                                </div>
                            </button>
                            <form action="insertEinkauf.php" method="post">
                                <input name="data" id="postvalue" hidden>
                                <button type="submit" class="bt_speichern" id="bt_speichern"
                                        onclick="func_speicherbutton()">
                                    <div id="div_speichern">
                                        <img src="./img/save.png" alt="">
                                        <!-- <input type="button" id="add_button" name="add_button"> -->
                                        <div><span>Bestellen</span></div>
                                    </div>
                                </button>
                            </form>

                        </div>
                        <span class="ueberschrift"><b>Warenkorb</b></span>
                        <div id="warenkorb_div">
                            <table class="table3" id="table4">
                                <tr>
                                    <th>PID</th>
                                    <th>Produktname</th>
                                    <th>Einkaufspreis</th>
                                    <th>Menge</th>
                                    <th>Zwischensumme</th>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <span class="ueberschrift"><b>Ausstehende Lieferungen</b></span>
                <table class="table1" id="table2">

                    <tr>
                        <th></th>
                        <th>EID</th>
                        <th>Lieferant</th>
                        <th>geplantes Lieferdatum</th>
                        <th>Summe</th>
                        <th>Angekommen</th>
                    </tr>
                    <!-- Hier werden die Daten aus der Datenbanken eingefügt -->

                    <?php
                    tabelle_anzeigen(false)
                    ?>
                </table>

                <form action="Einkauf.php" method="post" id="bt_save_form">
                    <input type="hidden" name="submitted" id ="1" value="1">
                    <button type="submit" class="bt_save" id="bt_save"
                            onclick="func_speicherbutton2()">
                        <div id="div_save">
                            <img src="./img/save.png" alt="">
                            <div><span>Speichern</span></div>
                        </div>
                    </button>
                </form>


                <span class="ueberschrift"><b>Erhaltene Lieferungen</b></span>
                <div id="summary_table">
                    <table id="table3">
                        <tr>
                            <th></th>
                            <th>EID</th>
                            <th>Lieferant</th>
                            <th>geplantes Lieferdatum</th>
                            <th>tatsächliches Lieferdatum</th>
                            <th>Summe</th>
                        </tr>

                        <?php
                        tabelle_anzeigen(true);
                        ?>


                    </table>

                </div>
            </div>
        </div>
    </div>

</div>
<script type="text/javascript">
    <?php
    $warenkorb_tmp = [];
    $warenkorb_item = 0;     // Speichert wie viele Einträge im Warenkorb sind
    ?>



    // const table = document.querySelector("table");
    const table2 = document.getElementById('table2');
    table2.addEventListener("click", function (e) {
        console.log("works");
        const td = e.target;
        if (td.classList.contains("expander")) {
            const style = td.parentNode.nextElementSibling.style;
            const wasOpen = !style.display;
            console.log(wasOpen);
            style.display = wasOpen ? "none" : "";
            td.textContent = wasOpen ? "▼" : "▲";
        }
    });

    const table3 = document.getElementById('table3');
    table3.addEventListener("click", function (e) {
        console.log("works");
        const td = e.target;
        if (td.classList.contains("expander")) {
            const style = td.parentNode.nextElementSibling.style;
            const wasOpen = !style.display;
            console.log(wasOpen);
            style.display = wasOpen ? "none" : "";
            td.textContent = wasOpen ? "▼" : "▲";
        }
    });

    //const btn_addnew = document.getElementById('bt_addnew');
    //btn_addnew.addEventListener('click',func_addbutton);

    document.getElementById("bt_addnew").onclick = function () {
        func_addbutton();
    };

    function func_addbutton() {
        document.getElementById("Title_Einkauf_wrapper").style.display = "none";
        document.getElementById("adding_wrapper").style.display = "flex";
        console.log("Hello");
        // document.getElementById("PID_warning").style.display = "flex";
    }

    //document.getElementById("bt_add2").onclick = function () { func_addbutton_inner(); };
    document.getElementById("bt_add2").onclick = function () {
        func_addbutton_inner()
    };

    let lieferantname;
    let lieferdatum;
    let einkauf_warenkorb = [];

    function func_addbutton_inner() {
        console.log("Test");
        document.getElementById("Title_Einkauf_wrapper").style.display = "none";
        document.getElementById("adding_wrapper").style.display = "flex";

        let lieferantname = document.getElementById("lieferant_select").value;
        let lieferdatum = document.getElementById("lieferdatum").value;
        let pid = document.getElementById("PID").value;
        let menge = document.getElementById("menge").value;

        // Überprüfen, ob alle Eingaben getätigt wurden
        let fehlermeldung = document.getElementById("fehlermeldung");
        if (lieferantname == "") {
            fehlermeldung.innerHTML = "Wählen Sie einen Lieferant aus";
            return;
        } else if (lieferdatum == "") {
            fehlermeldung.innerHTML = "Geben sie bitte das Lieferdatum an";
            return;
        } else if (pid == "") {
            fehlermeldung.innerHTML = "Geben Sie bitte eine PID an";
            return;
        } else if (menge == "") {
            fehlermeldung.innerHTML = "Geben Sie bitte eine Menge an";
            return;
        } else {
            fehlermeldung.innerHTML = "";
        }

        //warenkorb_tmp[warenkorb_item][0]=pid;
        const warenkorbtabelle = document.getElementById("table4");
        let neue_zeile = warenkorbtabelle.insertRow(1);  // Neue Zeile am Anfang der Warenkorbtabelle (Nach den Spaltenüberschriften)

        // Die fünf Spalten füllen
        let zelle1 = neue_zeile.insertCell();
        zelle1.innerHTML = pid;
        let zelle2 = neue_zeile.insertCell();
        //zelle2.innerHTML = tmp_array[0];    // Produktname einfügen
        let zelle3 = neue_zeile.insertCell();
        //zelle3.innerHTML = tmp_array[1];    // Einkaufspreis des Produkts einfügen
        let zelle4 = neue_zeile.insertCell();
        zelle4.innerHTML = menge;
        let zelle5 = neue_zeile.insertCell();


        einkauf_warenkorb.push(pid + "," + menge);
        let datum_arr = lieferdatum.split('.');
        console.log(lieferdatum);
        let isodate = datum_arr[0];
        console.log(isodate);
        document.getElementById("postvalue").value = lieferantname + "," + isodate.toString() + "," + einkauf_warenkorb.toString();

        let xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            //console.log(this.responseText);
            //document.getElementById("produktinfos").value = this.responseText;
            //console.log(document.getElementById("produktinfos").value);
            let tmp_array = this.responseText.split(';');
            zelle2.innerHTML = tmp_array[0];
            zelle3.innerHTML = tmp_array[1];
            zelle5.innerHTML = ((parseFloat(zelle3.innerHTML) * parseFloat(menge)).toFixed(2)).toString() + "€";
            zelle3.innerHTML += "€";
            fehlermeldung.value = "";

            zelle3.style.textAlign = "right";
            zelle4.style.textAlign = "right";
            zelle5.style.textAlign = "right";
        }

        xmlhttp.open("GET", "getProductInfos.php?p=" + pid, true);
        xmlhttp.send();

        // Nach einem erfolgreichen Hinzufügen -> Lieferant und Lieferdatum nicht auswählbar
        document.getElementById("lieferant_select").disabled = true;
    }

    document.getElementById("bt_abbrechen").onclick = function () {
        func_abbrbutton()
    };

    function func_abbrbutton() {
        console.log("works");
        document.getElementById("Title_Einkauf_wrapper").style.display = "flex";
        document.getElementById("adding_wrapper").style.display = "none";
        //document.getElementById("PID_warning").style.display = "";

        document.getElementById("lieferant_select").disabled = false;
        document.getElementById("lieferdatum").disabled = false;
        const warenkorbtabelle = document.getElementById("table4");

        // Inhalt der Warenkorbtabelle entfernen
        while (warenkorbtabelle.rows.length > 1) {
            warenkorbtabelle.deleteRow(warenkorbtabelle.rows.length - 1);
        }

        /*Eingabefelder reseten*/
        document.getElementById("lieferant_select").value = "";
        document.getElementById("lieferdatum").value = "";
        document.getElementById("PID").value = "";
        document.getElementById("menge").value = "";
        document.getElementById('PID').innerHTML = "";
        document.getElementById('PID').disabled = true;
        document.getElementById("fehlermeldung").innerHTML = "";

        // Zwischengespeicherte Daten leeren
        document.getElementById("postvalue").value = "";
        einkauf_warenkorb = [];
    }

    document.getElementById("bt_speichern").onclick = function () {
        func_speicherbutton()
    };

    function func_speicherbutton() {
        document.getElementById("Title_Einkauf_wrapper").style.display = "flex";
        document.getElementById("adding_wrapper").style.display = "none";
        document.getElementById("PID_warning").style.display = "";
    }

    function func_abbrbutton2() {
        document.getElementById("popup_div").style.display = "none";
        document.body.classList.remove('noscroll');

    }

    document.getElementById("bt_save").onclick = function () {
        func_speicherbutton2()
    };

    function func_speicherbutton2() {
        // document.getElementById("popup_div").style.display = "none";
        document.getElementById("PID_warning").style.display = "";
        document.body.classList.remove('noscroll');

    }

    document.getElementsByClassName("bt_bearbeiten").onclick = function () {
        func_bearbeitenbutton()
    };

    function func_bearbeitenbutton() {
        document.getElementById("popup_div").style.display = "flex";
        document.body.classList.add('noscroll');
    }

    function pid_anzeigen() {
        //let pidSelect = document.getElementById('PID');
        //pidSelect.innerHTML = "";
        let xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function (){
            let gueltige_pids = this.responseText.split(';');

            let pidSelect = document.getElementById('PID');

            pidSelect.innerHTML = "";
            for (let i = 0; i < gueltige_pids.length; i++){

                const opt = document.createElement('option');
                opt.value = gueltige_pids[i];
                opt.innerHTML = gueltige_pids[i];
                pidSelect.appendChild(opt);
            }

            pidSelect.disabled = false;
        }
        xmlhttp.open('GET', 'getPids.php?lieferant=' + document.getElementById('lieferant_select').value, true);
        xmlhttp.send();
    }

</script>
</body>
</html>