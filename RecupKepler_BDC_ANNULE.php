<html>

<head>
    <title>BDC ANNULE</title>
    <meta charset="utf-8" />
</head>

<?php

//use App\Helpers\Text;
//use App\Model\Post;
//use App\Connection;
//use App\URL;

?>


<body>
    <h2>Récupération du token</h2>


    <?php

    header('Content-type: text/html; charset=UTF-8');

    require('xlsxwriter.class.php');

    include 'fonctions.php';

    /******************************************  CODE MAIN ******************************************/

    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 256);
    ini_set('xdebug.var_display_max_data', 1024);

    $time_pre = time();
    $time_token = time();

    set_time_limit(0);

    // recup valeur token seulement
    $url_token = "https://www.kepler-soft.net/api/v3.0/auth-token/";
    $valeur_token = goCurlToken($url_token);
    //$valeur_token_first = $valeur_token;
    
    sautdeligne();

    ?>

    <span style="color:red">
        <?php echo $valeur_token; ?>
    </span>

    <?php

    echo "<h2>Récupération des données BDC</h2>";

    sautdeligne();

    // recup données
    
    $request_bon_de_commande = "v3.1/order-form/";
    $request_facture = "v3.1/invoice/";
    $request_vehicule = "v3.7/vehicles/";

    $url = "https://www.kepler-soft.net/api/";

    $req_url_BC = $url . "" . $request_bon_de_commande;
    $req_url_vehicule = $url . "" . $request_vehicule;
    $req_url_factures = $url . "" . $request_facture;
    //$req_url = $url . "" . $request_vehicule;
    
    sautdeligne();

    $j = 1;

    $array_datas = array();

    $nb_BC = 0;
    $nb_lignes = 0;
    $i = 0;
    $nb_total_vehicules_selling = 0;

    $datas_find = true;
    $datas_find_vehicule = true;

    $state_array = array();

    $array_datas[$i] = ["N Bon Commande", "Immatriculation", "RS/Nom Acheteur", "Prix Vente HT", "Prix de vente TTC", "Vendeur Vente", "Dernier n° de facture", "date du dernier BC", "Destination de sortie", "VIN"];

    while ($datas_find == true) {


        //par numero bdc
        // $num_BDC = '83493';
    
        // date d'hier
        $date_bdc = date('Y-m-d', strtotime("-1 day"));
    
        //date spécifique 
        // $date_bdc = "2024-04-15";


        // on reboucle pas si on met une valeur spécifique de bdc afin de récupérer qu'une seule page de l'API
        if ($num_BDC !== '') {
            $datas_find = false;
        }
        //recupere la requete !!!!
        $valeur_token = goCurlToken($url_token);
        $obj = GoCurl_Recup_BDC_ANNULE($valeur_token, $req_url_BC, $j, $num_BDC, $date_bdc);

        // var_dump($obj);
        // die();
    

        //a ce niveau obj est un object
    
        //on prends le tableau datas dans obj et ce qui nous fait un array sur obj_final
        if (!empty($obj)) {
            $obj_final = $obj->datas;
        } else {
            $obj_final = '';
        }


        if (!empty($obj_final)) {

            sautdeligne();
            sautdeligne();

            /*****************    BOUCLE du tableau de données récupérés *****************/
            $i++;

            //on boucle par rapport au nombre de bon de commande dans le tableau datas[]
            foreach ($obj_final as $keydatas => $keyvalue) {

                echo ("____________________________________________________________________________________________________________________");
                sautdeligne();

                // on récupere le state du bon de commande 
                if (isset($keyvalue->state)) {
                    $state_bdc = html_entity_decode($keyvalue->state);
                    // $state = utf8_decode($keyvalue->state);
    
                    echo "ETAT ==>" . $state_bdc;
                    sautdeligne();
                }

                //on recupere l'uuid pour retrouver le bon numero de bdc original
                $uuid = $keyvalue->uuid;
                $bdc = get_bdc_from_uuid($uuid);

                echo "bon de commande numéro :" . $bdc;
                sautdeligne();


                /***** remplissage du tableau excel ****/

                if (!empty($bdc)) {
                    $chassis = get_chassis_from_bdc($bdc);
                    foreach ($chassis as $index_chassis => $vin) {
                         $array_datas[$i]['bdc'] = $bdc;
                        $array_datas[$i]['immat'] = "";
                        $array_datas[$i]['nom_acheteur'] = "";
                        $array_datas[$i]['prix_vente_ht'] = "";
                        $array_datas[$i]['prix_vente_ttc'] = "";
                        $array_datas[$i]['vendeur'] = "";
                        $array_datas[$i]['num_facture'] = "";
                        $array_datas[$i]['date_bdc'] = "";
                        $array_datas[$i]['destination_sortie'] = "";
                        $array_datas[$i]['VIN'] = $vin['numero_chassis'];
                        $i++;
                    }
                }

                $nb_BC++;

            }
        }
        //si il n'y a pas de données
        else {
            $datas_find = false;
        }
        $j++;
    }

    sautdeligne();

    echo 'nombre de BC annulé : ' . $nb_BC;

    sautdeligne();

    print_r($state_array);

    $time_post = time();

    // print_r($array_datas);
    
    // array2csv($array_datas);
    
    $writer = new XLSXWriter();
    $writer->writeSheet($array_datas);
    $writer->writeToFile('bdc_annules.xlsx');


    $exec_time = $time_post - $time_pre;
    echo "temps d'exécution ==> " . $exec_time . " secondes";



    /*************************************  FIN CODE MAIN ************************************************************/


    ?>


</body>

</html>