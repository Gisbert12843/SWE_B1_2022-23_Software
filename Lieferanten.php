<?php
include_once("connection.php");
global $link;



//------------- Senden von  Bearbeiteten Lieferantendaten -------------
if (!empty($_POST['lieferantenid'])) {
    $lieferantenid = $_POST['lieferantenid'];
    $name = $_POST['name'];
    $strasse = $_POST['strasse'];
    $hausnr = $_POST['hausnummer'];
    $plz = $_POST['plz'];
    $ort = $_POST['ort'];
    if (!empty($_POST['status'])) $new_status = "1";
    else $new_status = "0";
    $sql_lieferant_bearb = "UPDATE Lieferanten 
            SET Name = '$name', Strasse = '$strasse', Hausnummer = '$hausnr', Ort = '$ort', Plz = '$plz', Aktiv = '$new_status'
            WHERE `Lieferanten-ID` = '$lieferantenid'";
    $result_lieferant_bearb = mysqli_query($link, $sql_lieferant_bearb);
    $new_product = explode(",", $_POST['lieferant_products']);
    for ($i = 0;$i < count($new_product);$i++) {
        $sql_update_products = "UPDATE Ware SET `Lieferanten_Lieferanten-ID` = '$lieferantenid' WHERE EID = '$new_product[$i]'";
        $result_update_products = mysqli_query($link, $sql_update_products);
        if (!$result_update_products) {
            echo "Verbindung fehlgeschlagen:", mysqli_connect_error();
            exit();
        }
    }
    if (!$result_lieferant_bearb) {
        echo "Verbindung fehlgeschlagen: ", mysqli_connect_error();
        exit();
    }
}

// ---------- Senden und Verarbeiten von neuen Lieferanten -----------------
if ((!empty($_POST['nameh'])) || (!empty($_POST['strasseh'])) || (!empty($_POST['hausnummerh'])) || (!empty($_POST['plzh'])) || (!empty($_POST['orth']))) {
    $name = $_POST['nameh'];
    $strasse = $_POST['strasseh'];
    $hausnr = $_POST['hausnummerh'];
    $plz = $_POST['plzh'];
    $ort = $_POST['orth'];
    $sql_lieferant_hinz = "INSERT INTO Lieferanten (Name, Strasse, Hausnummer, Plz, Ort) 
            VALUES ('$name', '$strasse', '$hausnr', '$ort', '$plz')";
    $result_lieferant_hinz = mysqli_query($link, $sql_lieferant_hinz);
    if (!$result_lieferant_hinz) {
        echo "Verbindung fehlgeschlagen: ", mysqli_connect_error();
        exit();
    }
}

//------------------- SQL lieferant tabelle----------------------
$sql_lieferanten_tabelle = "SELECT l.Name, l.Plz, l.Ort, l.Strasse, l.Hausnummer, l.`Lieferanten-ID`, l.Aktiv, l.Zuverlaessigkeit FROM Lieferanten l ORDER BY l.Name";
$result_lieferant_tabelle = mysqli_query($link, $sql_lieferanten_tabelle);
if (!$result_lieferant_tabelle) {
    echo "Verbindung fehlgeschlagen: ", mysqli_connect_error();
    exit();
}
//--------------- Status berechnen ---------------
function func_status($aktiv, $zuverleassig):
String {
    $status = "inaktiv";
    if ($aktiv == "1") $status = "aktiv";

    if($zuverleassig != "")
        $zuverleassig = ", ".$zuverleassig;

    return $status.$zuverleassig;
}
//------------------- SQL Lieferanten Produkte Tabelle ---------------
function lieferant_produkte($link, $lid):
void {
    $sql_lieferanten_produkte = "SELECT EID, Name, Einkaufspreis FROM Ware WHERE `Lieferanten_Lieferanten-ID` = '$lid'";
    $result_lieferanten_produkte = mysqli_query($link, $sql_lieferanten_produkte);
    if (!$result_lieferanten_produkte) {
        echo "Verbindung fehlgeschlagen: ", mysqli_connect_error();
        exit();
    }

    foreach($result_lieferanten_produkte as $product) {
        $eid = $product['EID'];
        $einkauf = $product['Einkaufspreis'];
        $name = $product['Name'];
        echo '<tr><td>' . $eid . '</td><td>' . $name . '</td><td style="text-align: right;">' . $einkauf . '€</td> </tr>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="Lieferanten.css">


    <title>Lieferanten </title>
</head>

<body>
<div id="global_wrapper">
    <div id="nav_wrapper">
        <nav>
            <a href="index.php"><button id="bt_Auswertung"><img src="./img/auswertung.png" alt ="auswertung">
                    Auswertung</button></a>
            <a href="Lager.php"><button id="bt_Lager"><img src="./img/lager.png" alt ="lager">Lager</button></a>
            <a href="Lieferanten.php"><button id="bt_Lieferanten"><img
                            src="./img/lieferant.png" alt ="lieferanten">Lieferanten</button></a>
            <a href="Einkauf.php"><button id="bt_Einkauf"><img src="./img/price-tag.png" alt ="einkauf">Einkauf</button></a>
            <a href="Verkauf.php"><button id="bt_Verkauf"><img src="./img/shopping-cart.png" alt ="verkauf">Verkauf</button></a>
        </nav>
    </div>

    <div id="content_wrapper">
        <div id="inner_content_wrapper">




            <div id="popup_div">
                <div id="inner_popup">
                    <div class="firstchild">
                        <div class="img_wrapper" id="lieferantenpng">
                            <img src="./img/lieferant.png" alt="">
                        </div>
                        <b><span id="lieferant_name">Bearbeiten</span></b><br>
                    </div>
                    <div class="secondchild">
                        <form id="bform" name="form_bearb_lieferant" method="post" action="Lieferanten.php">
                            <div>

                                <label for="name">Lieferant-Name:</label>
                                <input type="text" id="name" name="name" value="Placeholder" maxlength="60" >
                                <label for="status">Status:</label>
                                <input type="checkbox" id="status" name="status" checked >
                                <label for="lieferantenid" hidden></label>
                                <input hidden name="lieferantenid" id="lieferantenid" value="Placeholder" required>
                                <label for="lieferant_products" hidden></label>
                                <input hidden name="lieferant_products" id="lieferant_products" value="Placeholder">
                            </div>
                            <!-- <br> -->
                            <div>
                                <label for="strasse">Strasse:</label>
                                <input type="text" id="strasse" name="strasse" value="Placeholder" maxlength="20">
                                <label for="hausnummer">Hausnummer:</label>
                                <input type="text" id="hausnummer" name="hausnummer" value="Placeholder" maxlength="5">

                            </div>
                            <div>
                                <label for="plz">PLZ:</label>
                                <input type="text" id="plz" name="plz" value="Placeholder" maxlength="8">
                                <label for="ort">Ort:</label>
                                <input type="text" id="ort" name="ort" value="Placeholder" maxlength="15">
                            </div>
                            <div>
                                <div>
                                    <label for="PID">PID:</label>
                                    <input type="text" id="PID" name="PID">

                                    <script> let new_products = [];</script>
                                    <button type="button" class="bt_add2" id="bt_add2" onclick="func_add2(new_products)">
                                        <div id="div_add2">
                                            <img src="./img/plus-positive-add-mathematical-symbol.png" alt="">
                                            <!-- <input type="button" id="add_button" name="add_button"> -->
                                            <div><span>Hinzufügen</span></div>
                                        </div>
                                    </button>
                                    <!-- <button class="bt_rem2" id="bt_rem2" onclick="func_add2()">
                                        <div id="div_rem2">
                                            <img src="./img/minus-sign.png" alt="">
                                            // <input type="button" id="add_button" name="add_button">
                                            <div><span>Löschen</span></div>
                                        </div>
                                    </button> -->
                                </div>
                                <p id="pid_exception" > PID nicht verfügbar oder bereits vergeben!</p>
                                <br>
                                <span><b>Warenkatalog</b></span>
                                <div id="popup_table_div">

                                    <table>
                                        <thead>
                                        <tr>
                                            <th>PID</th>
                                            <th>Produktname</th>
                                            <th>Einkaufspreis</th>

                                        </tr>
                                        </thead>
                                        <tbody id="table_warenkatalog">
                                        <!--Wird befüllt durch func_add2()-->
                                        </tbody>
                                    </table>

                                </div>






                            </div>
                            <div id="popup_btdiv">
                                <button type="reset" class="bt_abbrechen2" id="bt_abbrechen2" onclick="func_abbrbutton2()">
                                    <div id="div_abbrechen">
                                        <img src="./img/cancel-square-button.png" alt="">
                                        <!-- <input type="button" id="add_button" name="add_button"> -->
                                        <div><span>Abbrechen</span></div>
                                    </div>
                                </button>
                                <button type="submit" class="bt_speichern2" id="bt_speichern2" onclick="func_speicherbutton2(new_products)">
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







            <div id="Lieferanten_wrapper">
                <div id="Title_Lieferanten_wrapper">
                    <div class="img_wrapper" id="lieferantenpng">
                        <img src="./img/lieferant.png" alt="">
                    </div>
                    <span><b>Lieferanten:</b></span><br>
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
                        <div class="img_wrapper" id="lieferantenpng">
                            <img src="./img/lieferant.png" alt="">
                        </div>
                        <span><b>Lieferant - Hinzufügen:</b></span><br>
                    </div>
                    <div class="secondchild">
                        <form id="hform" action="Lieferanten.php" method="post">
                            <div>
                                <label for="nameh">Name:</label>
                                <input type="text" id="nameh" name="nameh" maxlength="60">
                            </div>
                            <!-- <br> -->
                            <div>
                                <label for="strasseh">Strasse:</label>
                                <input type="text" id="strasseh" name="strasseh" maxlength="20">
                                <label for="hausnummerh">Hausnummer:</label>
                                <input type="text" id="hausnummerh" name="hausnummerh" maxlength="5">
                                <label for="plzh">PLZ:</label>
                                <input type="text" id="plzh" name="plzh" maxlength="8">
                                <label for="orth">Ort:</label>
                                <input type="text" id="orth" name="orth" maxlength="15">
                            </div>
                            <div>
                                <button type="reset" class="bt_abbrechen" id="bt_abbrechen" onclick="func_abbrbutton()">
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
                <span><b>Lieferanden-Liste</b></span>
                <table id="main_table">
                    <tr>
                        <th></th>
                        <th>Lieferant</th>
                        <th>Adresse</th>
                        <th>Status</th>
                        <th>Bearbeiten</th>
                    </tr>
                    <script>
                        let row = [];
                    </script>
                    <?php
                    $which_row = 0;
                    foreach($result_lieferant_tabelle as $eintrag) {
                        $zuverlaessigkeit = "";
                        if($eintrag['Zuverlaessigkeit'] < 0.7)
                            $zuverlaessigkeit = "unzuverlaessig";
                        echo ' <tr class="new_tr '.$zuverlaessigkeit.'"> <!---"new_tr unzuverlässig" wenn unzuverlässig ---> 
                                            <td class="expander">▼</td> 
                                           <td>' . $eintrag['Name'] . '</td> 
                                           <td>' . $eintrag['Strasse'] . " " . $eintrag['Hausnummer'] . " " . $eintrag['Plz'] . " " . $eintrag['Ort'] . '</td>
                                           <td>' . func_status($eintrag['Aktiv'],$zuverlaessigkeit) . '</td>
                                           <script>  
                                           //---- sowie Speicherung der Informationen die beim Bearbeiten displayed werden müssen---------- 
                                                    row.push(new Array("' . $eintrag['Name'] . '","' . $eintrag['Strasse'] . '","' . $eintrag['Hausnummer'] . '","' . $eintrag['Plz'] . '",
                                                                            "' . $eintrag['Ort'] . '","' . $eintrag['Aktiv'] .'","' . $eintrag['Lieferanten-ID'] . '")); 
                                           </script> 
                                           <td> 
                                           <div> 
                                           <button class="bt_bearbeiten" onclick="func_bearbeitenbutton(row[' . $which_row . '])">
                                           <img src="./img/pen.png" alt="Stift"> 
                                           </button> 
                                           </div>                                           
                                           </td> 
                                           </tr>';
                        echo ' <tr style="display: none"> 
                            <td></td> 
                            <td colspan="100"> 
                                <table> 
                                    <thead> 
                                        <tr> 
                                            <th>PID</th> 
                                            <th>Produktname</th> 
                                            <th>Einkaufspreis</th> 

                                        </tr> 
                                    </thead> 
                                    <tbody>';
// Ausgabe der LieferantenProdukte
                        lieferant_produkte($link, $eintrag['Lieferanten-ID']);
                        echo ' </tbody> 
                                   </table> 
                                </td> 
                            </tr>';
                        $which_row++;
                    }
                    ?>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // const table = document.querySelector("table");
    const tablebyid = document.getElementById("main_table");
    tablebyid.addEventListener("click", function (e)
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




    // Hinzufügen Buttons
    document.getElementById("bt_addnew").onclick = function () { func_addbutton() };
    function func_addbutton()
    {
        document.getElementById("Title_Lieferanten_wrapper").style.display = "none";
        document.getElementById("adding_wrapper").style.display = "flex";
    }
    document.getElementById("bt_abbrechen").onclick = function () { func_abbrbutton() };
    function func_abbrbutton()
    {
        document.getElementById("Title_Lieferanten_wrapper").style.display = "flex";
        document.getElementById("adding_wrapper").style.display = "none";

    }
    document.getElementById("bt_speichern").onclick = function () { func_speicherbutton() };
    function func_speicherbutton()
    {
        document.getElementById("Title_Lieferanten_wrapper").style.display = "flex";
        document.getElementById("adding_wrapper").style.display = "none";
    }


    // Bearbeiten Buttons
    document.getElementById("bt_add2").onclick = function () { func_add2(new_products) };
    function func_add2(new_products)
    {

        const PID = document.getElementsByName("PID")[0].value;

        <?php
        $sql_produkte = "SELECT EID,Name,Einkaufspreis FROM Ware WHERE `Lieferanten_Lieferanten-ID` is null ";
        $result_produkte = mysqli_query($link, $sql_produkte);
        if (!$result_produkte) {
            echo "Verbindung fehlgeschlagen: ", mysqli_connect_error();
            exit();
        }
        $row = 0;
        echo 'let jsarray = new Array();';
        while ($product = mysqli_fetch_assoc($result_produkte)) {
            $EID = $product['EID'];
            $Name = $product['Name'];
            $Epreis = $product['Einkaufspreis'];
            echo 'jsarray[' . $row . ']= new Array(' . json_encode($EID) . ',' . json_encode($Name) . ',' . json_encode($Epreis) . ');';
            $row++;
        }
        ?>
        let pid_exception = 1;
        let Name = "test";
        let Epreis = "test";
        // Hat PID noch keinen Lieferanten
        for(let j = 0; j < jsarray.length ;j++){
            if(jsarray[j][0] == PID){
                Name = jsarray[j][1];
                Epreis = jsarray[j][2]+"€";
                pid_exception = 0;
                break;
            }

        }
        // PID schon unter neuen Produkten
        for(let j = 0; j < new_products.length ;j++){
            if(new_products[j] == PID){
                pid_exception = 1;
                break;
            }

        }



        if(pid_exception == 0){
            document.getElementById("pid_exception").style.display = "none";
            const tabelle = document.getElementById('table_warenkatalog');
            // schreibe Tabellenzeile
            const reihe = tabelle.insertRow(0);
            let zelle1 = reihe.insertCell();
            zelle1.innerHTML = PID; // TODO
            let zelle2 = reihe.insertCell();
            zelle2.innerHTML = Name;
            let zelle3 = reihe.insertCell();
            zelle3.innerHTML = Epreis;
            zelle3.style.textAlign = "right";
            new_products.push(PID);



        }else document.getElementById("pid_exception").style.display = "flex";

    }

    document.getElementById("bt_abbrechen2").onclick = function () { func_abbrbutton2() };
    function func_abbrbutton2()
    {
        document.getElementById("popup_div").style.display = "none";
        document.body.classList.remove('noscroll');
        window.location = window.location.href;

    }
    document.getElementById("bt_speichern2").onclick = function () { func_speicherbutton2(new_products) };
    function func_speicherbutton2(new_products)
    {

        document.getElementById("lieferant_products").value = new_products;

        document.getElementById("popup_div").style.display = "none";
        document.body.classList.remove('noscroll');

    }

    document.getElementsByClassName("bt_bearbeiten").onclick = function () { func_bearbeitenbutton() };
    function func_bearbeitenbutton(row)
    {
        document.getElementById("popup_div").style.display = "flex";
        document.body.classList.add('noscroll');
        let l_status = false;
        if(row[5] == "1")
            l_status = true;

        document.getElementById("lieferant_name").textContent = row[0] + " - Bearbeiten";
        document.getElementById("status").checked = l_status;
        document.getElementById("name").value = row[0];
        document.getElementById("strasse").value = row[1];
        document.getElementById("hausnummer").value = row[2];
        document.getElementById("plz").value = row[3];
        document.getElementById("ort").value = row[4];
        document.getElementById("lieferantenid").value = row[6];
    }

</script>
</body>

</html>