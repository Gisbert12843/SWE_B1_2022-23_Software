<!DOCTYPE html>
<html lang="en">
<?php
include_once("connection.php");
global $link;

//---------- Senden von neuem Verkauf, Lager Updaten----------------
if (!empty($_POST['verkauf_products'])) {
    $new_product = explode(",",$_POST['verkauf_products'] );


   if(!empty($new_product)) {
       $date = date("Y-m-d");
       $sql_verkauf_hinz = "INSERT INTO `Verkauf/Warenkoerbe`(Datum) VALUE('$date')";
       $result_verkauf_hinz = mysqli_query($link, $sql_verkauf_hinz);

       // Get last WarenkorbID
       $sql_getVid = "SELECT LAST_INSERT_ID() AS ID FROM `Verkauf/Warenkoerbe` LIMIT 1";
       $result_lastid = mysqli_query($link, $sql_getVid);
        if (!$result_lastid) {
           echo "Verbindung fehlgeschlagen: ", mysqli_connect_error();
           exit();
       }

        $id = mysqli_fetch_assoc($result_lastid);



       for ($i = 0;$i < count($new_product);$i+=3) {
           $eid = $new_product[$i];
           $menge = $new_product[$i+1];
           $preis = $new_product[$i+2];
           $wid = $id['ID'];
           $sql_update_products = "INSERT INTO `Ware_has_Verkauf/Warenkoerbe`(Ware_EID, `Verkauf/Warenkoerbe_Warenkorb-ID`, Menge, Verkaufspreis_State) VALUES('$eid','$wid','$menge','$preis')";
           $result_update_products = mysqli_query($link, $sql_update_products);
           if (!$result_update_products) {
               echo "Verbindung fehlgeschlagen:", mysqli_connect_error();
               exit();
           }

           //Update Lager
           $menge = doubleval($menge);
           $sql_update_lager = "UPDATE Ware SET Menge = MENGE - $menge WHERE EID = '$eid'";
           $result_update_lager = mysqli_query($link, $sql_update_lager);
           if (!$result_update_lager) {
               echo "Verbindung fehlgeschlagen:", mysqli_connect_error();
               exit();
           }
       }


       if (!$result_verkauf_hinz) {
           echo "Verbindung fehlgeschlagen: ", mysqli_connect_error();
           exit();
       }





   }
}





?>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Verkauf.css">
    <title>Verkauf</title>
</head>

<body> <div id="global_wrapper">
    <div id="nav_wrapper">
        <nav>
            <a href="index.php"><button id="bt_Auswertung"><img src="./img/auswertung.png">
                Auswertung</button></a>
            <a href="Lager.php"><button id="bt_Lager"><img src="./img/lager.png">Lager</button></a>
            <a href="Lieferanten.php"><button id="bt_Lieferanten"><img
                    src="./img/lieferant.png">Lieferanten</button></a>
            <a href="Einkauf.php"><button id="bt_Einkauf"><img src="./img/price-tag.png">Einkauf</button></a>
            <a href="Verkauf.php"><button id="bt_Verkauf"><img src="./img/shopping-cart.png">Verkauf</button></a>
        </nav>
    </div>

    <div id="content_wrapper">
        <div id="inner_content_wrapper">




            <div id="Verkauf_wrapper">
                <div id="Title_Verkauf_wrapper">
                    <div class="img_wrapper" id="verkaufpng">
                        <img src="./img/shopping-cart.png" alt="">
                    </div>
                    <span><b>Verkauf:</b></span><br>
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
                        <div class="img_wrapper" id="verkaufpng">
                            <img src="./img/shopping-cart.png" alt="">
                        </div>
                        <span><b>Verkauf - Hinzufügen:</b></span><br>
                    </div>
                    <div class="secondchild">
                    <form id="formverkauf" method="post" action="Verkauf.php">
                        <div id="input_div">
                            <div>
                                <label for="PID">PID:</label>
                                <input type="text" id="PID" name="PID" maxlength="15" required>
                            </div>
                            <!-- <br> -->
                            <div>
                                <label for="menge">Menge:</label>
                                <input type="text" id="menge" name="menge" required>

                            </div>
                            <input name ="verkauf_products" id="verkauf_products" hidden value="placeholder">
                            <div id="btadd_div">
                                <button type="button" class="bt_add" id="bt_add" onclick="func_addbutton_inner(new_products)">
                                    <div id="btadd_div_inside">
                                        <img src="./img/plus-positive-add-mathematical-symbol.png" alt="">
                                        <!-- <input type="button" id="add_button" name="add_button"> -->
                                        <div><span>Hinzufügen</span></div>
                                    </div>
                                </button>
                            </div>
                            <div class="break" style="  flex-basis: 100%; height: 0;"></div> <!-- break to a new row -->

                        </div>
                        <span id="PID_warning">PID nicht im Lager eingetragen.!</span>


                        <div id="buttons_div">
                            <button type="reset" class="bt_abbrechen" id="bt_abbrechen" onclick="func_abbrbutton()">
                                <div id="div_abbrechen">
                                    <img src="./img/cancel-square-button.png" alt="">
                                    <!-- <input type="button" id="add_button" name="add_button"> -->
                                    <div><span>Abbrechen</span></div>
                                </div>
                            </button>
                            <span><b id="verkaufskorbsumme">Summe: 0.00€</b></span>
                            <button type="submit" class="bt_speichern" id="bt_speichern" onclick="func_speicherbutton(new_products)">
                                <div id="div_speichern">
                                    <img src="./img/save.png" alt="">
                                    <!-- <input type="button" id="add_button" name="add_button"> -->
                                    <div><span>Speichern</span></div>
                                </div>
                            </button>
                        </div>
                        <div>
                            <script>
                                let new_products = [];
                                let verkaufskorbsumme = 0;
                            </script>
                            <table id="table_verkaufskorb">
                                <tr>
                                    <th>PID</th>
                                    <th>Produktname</th>
                                    <th>Menge</th>
                                    <th>Verkaufspreis</th>
                                    <th>Zwischensumme</th>

                                </tr>

                            </table>
                        </div>
                    </form>
                    </div>
                </div>
                <span id="table_caption"><b>Alle Verkäufe</b></span>
                <table id="main_table">
                    <tr>
                        <th></th>
                        <th>VID</th>
                        <th>Datum</th>
                        <th>Gesamtpreis</th>
                    </tr>


                    <?php
                        $sql = "select `Warenkorb-id`,`Datum` from `verkauf/warenkoerbe` where ISDELETE = 0 ORDER BY Datum DESC";
                        $result = mysqli_query($link, $sql);

                        while ($row = mysqli_fetch_assoc($result)) {
                        $balance = 0;

                        $sqlinner = "select `Ware_has_Verkauf/Warenkoerbe`.`Ware_EID`,Ware.Name,`Ware_has_Verkauf/Warenkoerbe`.`Verkaufspreis_State`,`Ware_has_Verkauf/Warenkoerbe`.`Menge` from `Ware_has_Verkauf/Warenkoerbe` JOIN `Ware` ON `Ware_has_Verkauf/Warenkoerbe`.`Ware_EID` = `Ware`.`EID` AND `Ware`.`ISDELETE` = 0 Where `Ware_has_Verkauf/Warenkoerbe`.ISDELETE = 0 AND `Ware_has_Verkauf/Warenkoerbe`.`Verkauf/Warenkoerbe_Warenkorb-ID` = ".$row['Warenkorb-id'];
                        $resultinner = mysqli_query($link, $sqlinner);
                            while ($rowinner = mysqli_fetch_assoc($resultinner)){
                                $balance = $balance + ($rowinner['Verkaufspreis_State'] * $rowinner['Menge']);
                            }



                            echo
                                "<tr class=\"new_tr verspaetet\">".
                    "<td class=\"expander\">▼</td>".
                    "<td>".$row['Warenkorb-id']."</td>".
                    "<td>".$row['Datum']."</td>".
                    "<td style='text-align: right;'>".$balance."€</td>".
                    "</tr>";
                    echo
                    "<tr style=\"display: none\">".
                    "<td></td>".
                    "<td colspan=\"100\">".
                    "<table class=\"expand_table\">".
                        "<tr>".
                            "<th>PID</th>".
                            "<th>Produktname</th>".
                            "<th>Verkaufspreis</th>".
                            "<th>Menge</th>".
                            "<th>Summe</th>".
                            "</tr>";

                        $sqlinner = "select `Ware_has_Verkauf/Warenkoerbe`.`Ware_EID`,Ware.Name,`Ware_has_Verkauf/Warenkoerbe`.`Verkaufspreis_State`,Ware.Verkaufspreis,`Ware_has_Verkauf/Warenkoerbe`.`Menge` from `Ware_has_Verkauf/Warenkoerbe` JOIN `Ware` ON `Ware_has_Verkauf/Warenkoerbe`.`Ware_EID` = `Ware`.`EID` AND `Ware`.`ISDELETE` = 0 Where `Ware_has_Verkauf/Warenkoerbe`.ISDELETE = 0 AND `Ware_has_Verkauf/Warenkoerbe`.`Verkauf/Warenkoerbe_Warenkorb-ID` = ".$row['Warenkorb-id'];
                        $resultinner = mysqli_query($link, $sqlinner);


                        while ($rowinner = mysqli_fetch_assoc($resultinner)) {

                        echo "<tr>".
                            "<td>".$rowinner['Ware_EID']."</td>".
                            "<td>".$rowinner['Name']."</td>".
                            "<td style='text-align: right;'>".$rowinner['Verkaufspreis_State']."€</td>".
                            "<td style='text-align: right;'>".$rowinner['Menge']."</td>".
                            "<td style='text-align: right;'>".$rowinner['Verkaufspreis_State'] * $rowinner['Menge']."€</td>".
                            "</tr>";
                        $balance = $balance + ($rowinner['Verkaufspreis_State'] * $rowinner['Menge']);
                        }

                        mysqli_free_result($resultinner);
                        echo
                        "</table>".
                    "</td>".
                    "</tr>";

                    }
                    ?>
                </table>
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

    // document.getElementById("bt_addnew").onclick = function () { func_addbutton() };
    function func_addbutton()
    {
        document.getElementById("Title_Verkauf_wrapper").style.display = "none";
        document.getElementById("table_caption").style.display = "none";
        document.getElementById("main_table").style.display = "none";
        document.getElementById("adding_wrapper").style.display = "flex";

    }
    function func_addbutton_inner(new_products)
    {
        document.getElementById("Title_Verkauf_wrapper").style.display = "none";
        document.getElementById("table_caption").style.display = "none";
        document.getElementById("main_table").style.display = "none";
        document.getElementById("adding_wrapper").style.display = "flex";

        // Erwiterung tabelle und prüfen von Eingaben
        const PID = document.getElementsByName("PID")[0].value;
        const eingabemenge = document.getElementsByName("menge")[0].value;

        <?php
        $sql_produkte = "SELECT EID,Name,Verkaufspreis, Menge FROM Ware";
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
            $Vpreis = $product['Verkaufspreis'];
            $Menge = $product['Menge'];
            echo 'jsarray[' . $row . ']= new Array(' . json_encode($EID) . ',' . json_encode($Name) . ',' . json_encode($Vpreis). ',' . json_encode($Menge) . ');';
            $row++;
        }
        ?>
            let pid_exception = 1;
            let fehlermeldung  = "";
            let Name = "";
            let Vpreis = "";
            let LagerMenge = "";
        // PID vorhanden ?
        for(let j = 0; j < jsarray.length ;j++){
            if(jsarray[j][0] == PID){
                Name = jsarray[j][1];
                Vpreis = jsarray[j][2];
                LagerMenge = jsarray[j][3];
                pid_exception = 0;
                break;
            }
        }


        // PID nicht vorhanden
        if(pid_exception == 1)
            fehlermeldung = "PID nicht verwendbar!";


        // Zuwenig im Lager um soviel zu verkaufen
        if(parseInt(LagerMenge) < parseInt(eingabemenge)) {
            pid_exception = 1;
            fehlermeldung = fehlermeldung + " Zu wenig Produkt im Lager!";
        }
        // PID schon in Verkaufskorb enthalten
        for(let j = 0; j < new_products.length ;j++){
            if(new_products[j][0] == PID){
                pid_exception = 1;
                fehlermeldung = fehlermeldung + "PID wurde bereits eingefügt!"
                break;
            }

        }



        if (pid_exception == 0) {
            document.getElementById("PID_warning").style.display = "none";
            const tabelle = document.getElementById('table_verkaufskorb');
            // schreibe Tabellenzeile
            const reihe = tabelle.insertRow(1);
            let zelle1 = reihe.insertCell();
            zelle1.innerHTML = PID;
            let zelle2 = reihe.insertCell();
            zelle2.innerHTML = Name;
            let zelle3 = reihe.insertCell();
            zelle3.innerHTML = eingabemenge;
            let zelle4 = reihe.insertCell();
            zelle4.innerHTML = Vpreis+"€";
            let zelle5 = reihe.insertCell();
            zelle5.innerHTML = (parseFloat(Vpreis) * parseFloat(eingabemenge)).toFixed(2).toString() + "€";

            document.getElementById("verkaufskorbsumme").textContent
                = "Summe: " + (verkaufskorbsumme += (parseFloat(Vpreis) * parseFloat(eingabemenge))).toFixed(2).toString() + "€";

            new_products.push([PID, eingabemenge, Vpreis]);
            zelle3.style.textAlign = "right";
            zelle4.style.textAlign = "right";
            zelle5.style.textAlign = "right";

        } else{
            document.getElementById("PID_warning").textContent = fehlermeldung;
            document.getElementById("PID_warning").style.display = "block";
        }



    }
    // document.getElementById("bt_abbrechen").onclick = function () { func_abbrbutton() };
    function func_abbrbutton()
    {
        document.getElementById("Title_Verkauf_wrapper").style.display = "flex";
        document.getElementById("table_caption").style.display = "";
        document.getElementById("main_table").style.display = "";
        document.getElementById("adding_wrapper").style.display = "none";
        document.getElementById("PID_warning").style.display = "none";
        window.location = window.location.href;
    }
    // document.getElementById("bt_speichern").onclick = function () { func_speicherbutton() };
    function func_speicherbutton(new_products)
    {
        document.getElementById("verkauf_products").value = new_products;

        document.getElementById("Title_Verkauf_wrapper").style.display = "flex";
        document.getElementById("table_caption").style.display = "";
        document.getElementById("main_table").style.display = "";
        document.getElementById("adding_wrapper").style.display = "none";
        document.getElementById("PID_warning").style.display = "none";

    }

</script>
</body>

</html>