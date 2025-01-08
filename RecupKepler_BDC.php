<html>

<head>
    <title>Recup API KEPLER BDC FINAL</title>
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



        $num_BDC = '';
        $date_bdc = '';

        // date d'hier
        // $date_bdc = date('Y-m-d', strtotime("-1 day"));
        // $date_bdc = date('2023-10-16');
        //date spécifique 
        //$date_bdc = "2024-11-02";
    

        // on reboucle pas si on met une valeur spécifique de bdc afin de récupérer qu'une seule page de l'API
        if ($num_BDC !== '') {
            $datas_find = false;
        }
        //recupere la requete !!!!
        $valeur_token = goCurlToken($url_token);
        $obj = GoCurl_Recup_BDC($valeur_token, $req_url_BC, $j, $num_BDC, $date_bdc);

        //a ce niveau obj est un object
    
        //on prends le tableau datas dans obj et ce qui nous fait un array sur obj_final
        if (!empty($obj)) {
            $obj_final = $obj->datas;
        } else {
            $obj_final = '';
        }

        // var_dump($obj_final);
        // die();
    
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


                //$test = utf8_decode($state);
    
                //echo $test.'<br/><br/>';
    
                // si validé , Facturé ou Facturé édité
                if ($state_bdc == "Validé" or $state_bdc == "Facturé" or $state_bdc == "Facturé édité") {

                    //get ID et uuid
                    $bdc = $keyvalue->uniqueId;
                    $uuid = $keyvalue->uuid;

                    echo "bon de commande numéro :" . $bdc;
                    sautdeligne();

                    //get nom acheteur
                    if (isset($keyvalue->owner->firstname)) {
                        $nom_acheteur = $keyvalue->owner->firstname . " " . $keyvalue->owner->lastname;
                    } else if(isset($keyvalue->customer->firstname)){
                        $nom_acheteur = $keyvalue->customer->firstname . " " . $keyvalue->customer->lastname;
                    }else{
                        $nom_acheteur = $keyvalue->customer->corporateName;
                    }

                    //get nom vendeur
                    $nom_vendeur = $keyvalue->seller;
                    $nom_vendeur = explode("<", $nom_vendeur);
                    $nom_vendeur = $nom_vendeur[0];

                    //get CVO Vente
                    $nom_cvo = get_CVO_by_vendeur($nom_vendeur);

                    //GET DATE BC
                    $date_BC_tmp = substr($keyvalue->date, 0, 10);
                    $date_BC_tmp2 = str_replace("-", "/", $date_BC_tmp);
                    $date_BC = date('d/m/Y', strtotime($date_BC_tmp2));


                    //get destination sortie
                    if (empty($keyvalue->destination)) {
                        $destination_sortie = '';
                    } else {
                        $destination_sortie = $keyvalue->destination;
                    }

                    //déterminer si c'est une vente particvulier ou vente marchand ( particulier ou pro )
    

                    echo "destination ==> $destination_sortie <br/><br/>";


                    //si c'est particulier 
                    if ($destination_sortie == "VENTE PARTICULIER") {

                        $erreur_vehicule_sorti = false;

                        // echo "<span style='color:red'>" . $erreur_vehicule_sorti . "</span>";
    
                        //Si il y a des items
                        if (isset($keyvalue->items)) {

                            $presta_Price_HT = array();
                            $presta_price_TTC = array();

                            //ON BOUCLE DANS LES ITEMS
                            foreach ($keyvalue->items as $key_item => $value_item) {
                                //si c'est un vehicule_selling
                                echo "_ _ _ _ _ _ _ _ _ _ _ _";
                                sautdeligne();
                                echo "item numéro : $key_item ==> $value_item->type";
                                sautdeligne();


                                if ($value_item->type == 'vehicle_selling') {

                                    $reference_item = $value_item->reference;

                                    $obj_result = '';

                                    //  recup infos du véhicule
                                    $valeur_token = goCurlToken($url_token);
                                    $state_vh = '';
                                    $obj_result = getvehiculeInfo($reference_item, $valeur_token, $req_url_vehicule, $state_vh,FALSE);
                                     //si on a des resultats c'est que le vh est non dispo à la vente
                                     if (empty ($obj_result)) {
                                        $result = getvehiculeInfo($reference_item, $valeur_token, $req_url_vehicule, $state_vh, TRUE);
                                        $obj_result = $result;
                                    }

                                    echo "LE TYPE DE OBJ RESULT EST : " . gettype($obj_result) . "<br/><br/>";


                                    $obj_vehicule = array();

                                    $type = gettype($obj_result);

                                    sautdeligne();

                                    // var_dump($type);
                                    // die();
    
                                    // si c'est bien un objet comme prévu
                                    if ($type == 'object') {

                                        // si le résultat n'est pas vide
                                        if (!empty($obj_result)) {

                                            $obj_vehicule = $obj_result;

                                            if ($state_bdc == "Facturé édité") {
                                                //On ne prend que les véhicules à l'état Vendu ou vendu AR
                                                if ($obj_vehicule->state == "vehicle.state.sold_ar" or $obj_vehicule->state == "vehicle.state.sold") {

                                                    // get VIN
                                                    if (isset($obj_vehicule->vin) || !empty($obj_vehicule->vin)) {
                                                        $vin = $obj_vehicule->vin;
                                                    } else {
                                                        $vin = "";
                                                    }

                                                    //get IMMATRICULATION
                                                    if (empty($obj_vehicule->licenseNumber)) {
                                                        $immatriculation = 'N/C';
                                                    } else {
                                                        $immatriculation = $obj_vehicule->licenseNumber;
                                                    }

                                                    $immatriculation = str_replace("-", "", $immatriculation);

                                                    $vehicule_seul_HT = $value_item->sellPriceWithoutTaxWithoutDiscount;
                                                    $vehicule_seul_TTC = $value_item->sellPriceWithTax;

                                                    $nb_total_vehicules_selling++;
                                                }
                                                // si facturé édité et que le véhicule n'est ni vendu ni vendu AR
                                                else {
                                                    $erreur_vehicule_sorti = true;
                                                }
                                            }
                                            //si pas facture éditée
                                            else {

                                                // get VIN
                                                $vin = $obj_vehicule->vin;

                                                //get IMMATRICULATION
                                                if (empty($obj_vehicule->licenseNumber)) {
                                                    $immatriculation = 'N/C';
                                                } else {
                                                    $immatriculation = $obj_vehicule->licenseNumber;
                                                }
                                                //immatriculation bon format
                                                $immatriculation = str_replace("-", "", $immatriculation);
                                                //prix du véhicule seul HT
                                                $vehicule_seul_HT = $value_item->sellPriceWithoutTaxWithoutDiscount;
                                                //prix du véhicule seul TTC
                                                $vehicule_seul_TTC = $value_item->sellPriceWithTax;

                                                $nb_total_vehicules_selling++;
                                            }
                                        }
                                        //si le résultat est vide
                                        else {
                                            echo "véhicule sorti";
                                            sautdeligne();
                                            $erreur_vehicule_sorti = true;
                                        }
                                    }
                                    //sinon si on ne trouve rien sur le vh
                                    else {
                                        $erreur_vehicule_sorti = true;
                                    }
                                }
                                //si c'est pas vehicule selling et donc service selling 
                                else {
                                    echo " - presta service : " . $value_item->name . "<br/>";
                                    $presta_Price_HT[$key_item] = $value_item->sellPriceWithoutTax;
                                    $presta_price_TTC[$key_item] = $value_item->sellPriceWithTax;
                                }
                            }



                            // si il y a ou pas des services selling
                            if (empty($presta_Price_HT)) {
                                $total_presta_HT = 0;
                            } else {
                                $total_presta_HT = array_sum($presta_Price_HT);
                            }
                            if (empty($presta_price_TTC)) {
                                $total_presta_TTC = 0;
                            } else {
                                $total_presta_TTC = array_sum($presta_price_TTC);
                            }

                            // si $erreur_vehicule_sorti == false
                            if (!$erreur_vehicule_sorti) {

                                $i++;

                                echo "+1 ligne </br>";

                                //total vehicule + prestations 
                                // $total_HT = $vehicule_seul_HT + $total_presta_HT;
                                // $total_TTC = $vehicule_seul_TTC + $total_presta_TTC;
                                $total_HT = $vehicule_seul_HT;
                                $total_TTC = $vehicule_seul_TTC;

                                // On place les valeurs dans les cellules
                                $array_datas[$i]['uniqueId'] = $bdc;
                                $array_datas[$i]['immatriculation'] = $immatriculation;
                                $array_datas[$i]['name'] = $nom_acheteur;
                                $array_datas[$i]['prixTotalHT'] = $total_HT;
                                $array_datas[$i]['prixTTC'] = $total_TTC;
                                $array_datas[$i]['nomVendeur'] = $nom_vendeur;
                                $array_datas[$i]['num_last_facture'] = '';
                                $array_datas[$i]['dateBC'] = $date_BC;
                                $array_datas[$i]['Destination_sortie'] = $destination_sortie;
                                $array_datas[$i]['VIN'] = $vin;
				$array_datas[$i]['uuid'] = $uuid;

                                // var_dump($array_datas[$i]);
    
                                $nb_lignes++;
                            }
                        }
                        $nb_BC++;
                    }


                    /****************************************** si c'est une VENTE A MARCHAND BUYS BACK OU VENTE EXPORT CE  ***********************************/
                    // else {
                    elseif ($destination_sortie == "VENTE MARCHAND" || $destination_sortie == "BUY BACK" || $destination_sortie == "VENTE EXPORT CE" || $destination_sortie == "VENTE EXPORT HORS CE" || $destination_sortie == "EPAVE") {

                        $erreur_vehicule_sorti = false;

                        //Si il y a des items
                        if (isset($keyvalue->items)) {
                            //on boucle dans les items
    
                            foreach ($keyvalue->items as $key_item => $value_item) {
                                $comptabilisation_ligne = false;
                                //on crée une variable qui contiendra le numéro de bdc initial
                                echo "_ _ _ _ _ _ _ _ _ _ _ _ <br/>";

                                $obj_vehicule = array();

                                //si c'est un vehicule_selling
                                if ($value_item->type == 'vehicle_selling') {

                                    $reference_item = $value_item->reference;

                                    //  recup infos du véhicule
                                    $valeur_token = goCurlToken($url_token);
                                    //pas disponible à la vente
                                    $state_vh = '';
                                    $obj_result = getvehiculeInfo($reference_item, $valeur_token, $req_url_vehicule, $state_vh,FALSE);
                                     //si on a des resultats c'est que le vh est non dispo à la vente
                                     if (empty ($obj_result)) {
                                        $result = getvehiculeInfo($reference_item, $valeur_token, $req_url_vehicule, $state_vh, TRUE);
                                        $obj_result = $result;
                                    }


                                    echo "LE TYPE DE OBJ RESULT EST : " . gettype($obj_result) . "<br/><br/>";

                                    //array = OK ;  si object alors pas OK
                                    $type = gettype($obj_result);
                                    sautdeligne();

                                    //si on obtient un object.
                                    if ($type == 'object') {

                                        // si le résultat n'est pas vide
                                        if (!empty($obj_result)) {

                                            $obj_vehicule = $obj_result;

                                            if ($state_bdc == "Facturé édité") {

                                                //On ne prend que les véhicules à l'état Vendu ou vendu AR
                                                if ($obj_vehicule->state == "vehicle.state.sold_ar" or $obj_vehicule->state == "vehicle.state.sold") {

                                                    // get VIN
                                                    if (isset($obj_vehicule->vin) || !empty($obj_vehicule->vin)) {
                                                        $vin = $obj_vehicule->vin;
                                                    } else {
                                                        $vin = "";
                                                    }

                                                    //get IMMATRICULATION
                                                    if (empty($obj_vehicule->licenseNumber)) {
                                                        $immatriculation = 'N/C';
                                                    } else {
                                                        $immatriculation = $obj_vehicule->licenseNumber;
                                                    }

                                                    $immatriculation = str_replace("-", "", $immatriculation);

                                                    $vehicule_seul_HT = $value_item->sellPriceWithoutTaxWithoutDiscount;
                                                    $vehicule_seul_TTC = $value_item->sellPriceWithTax;

                                                    $nb_total_vehicules_selling++;
                                                    $comptabilisation_ligne = true;
                                                }
                                                // si facturé édité et que le véhicule n'est ni vendu ni vendu AR
                                                else {
                                                    $comptabilisation_ligne = false;
                                                }
                                            }
                                            //si pas facture éditée
                                            else {
                                                if ($obj_vehicule->state == "vehicle.state.sold_ar" or $obj_vehicule->state == "vehicle.state.sold") {
                                                    // get VIN
                                                    if (isset($obj_vehicule->vin) || !empty($obj_vehicule->vin)) {
                                                        $vin = $obj_vehicule->vin;
                                                    } else {
                                                        $vin = "";
                                                    }

                                                    //get IMMATRICULATION
                                                    if (empty($obj_vehicule->licenseNumber)) {
                                                        $immatriculation = 'N/C';
                                                    } else {
                                                        $immatriculation = $obj_vehicule->licenseNumber;
                                                    }

                                                    $immatriculation = str_replace("-", "", $immatriculation);

                                                    $vehicule_seul_HT = $value_item->sellPriceWithoutTaxWithoutDiscount;
                                                    $vehicule_seul_TTC = $value_item->sellPriceWithTax;

                                                    $nb_total_vehicules_selling++;
                                                    $comptabilisation_ligne = true;
                                                } else {
                                                    $comptabilisation_ligne = false;
                                                }
                                            }

                                            if ($comptabilisation_ligne == true) {

                                                //total vehicule 
                                                $total_HT = $vehicule_seul_HT;
                                                $total_TTC = $vehicule_seul_TTC;

                                                $i++;

                                                // On place les valeurs dans les cellules
                                                $array_datas[$i]['uniqueId'] = $bdc;
                                                $array_datas[$i]['immatriculation'] = $immatriculation;
                                                $array_datas[$i]['name'] = $nom_acheteur;
                                                $array_datas[$i]['prixTotalHT'] = $total_HT;
                                                $array_datas[$i]['prixTTC'] = $total_TTC;
                                                $array_datas[$i]['nomVendeur'] = $nom_vendeur;
                                                $array_datas[$i]['num_last_facture'] = '';
                                                $array_datas[$i]['dateBC'] = $date_BC;
                                                //demande mickael : si c'est une epave on transforme en vente marchand
                                                if ($destination_sortie == 'EPAVE') {
                                                    $destination_sortie = 'VENTE MARCHAND';
                                                }
                                                $array_datas[$i]['Destination_sortie'] = $destination_sortie;
                                                $array_datas[$i]['VIN'] = $vin;
						$array_datas[$i]['uuid'] = $uuid;

                                                echo "+1 ligne </br>";
                                                $nb_lignes++;
                                            }
                                        } else {
                                            echo "véhicule sorti";
                                            sautdeligne();
                                            $erreur_vehicule_sorti = true;
                                        }
                                    } else {
                                        $erreur_vehicule_sorti = false;
                                    }
                                }
                            }
                        }
                        $nb_BC++;
                    }
                    // si pas vente marchand NI vente PARTICULIER
                     else {
                         echo "bdc numero $bdc erreur : destination ni vente particulier ni vente marchand<br/>";
                     }
    
                }
                //si c'est ni validé ni facturé ni facturé édité 
                else {
                    $state_array[] = $state_bdc;
                }
 		//on insere dans la base de donnée temporaire pour identifier les bdc avec leur uuid 
                if (!is_null($bdc) && !is_null($uuid)) {
                    $date_bdc = date("Y-m-d");
                    upload_bdc_ventes_uuid($bdc, $uuid, $date_bdc);
                }
            }

        }
        //si il n'y a pas de données
        else {
            $datas_find = false;
        }
        $j++;
    }


    sautdeligne();

    //on ne compte pas les bdc annulé ou autre 
    $nbre_bdc_nonCompris = count($state_array);
    $nb_BC = $nb_BC - $nbre_bdc_nonCompris;

    echo 'nombre de BC : ' . $nb_BC;

    sautdeligne();

    echo 'nombre de lignes : ' . $nb_lignes;

    sautdeligne();

    echo 'nombre de véhicules vendus : ' . $nb_total_vehicules_selling;

    sautdeligne();

    print_r($state_array);


    $time_post = time();

    sautdeligne();
    sautdeligne();






    // print_r($array_datas);
    

    // array2csv($array_datas);
    
    $writer = new XLSXWriter();
    $writer->writeSheet($array_datas);
    $writer->writeToFile('bdc_crees_modifies.xlsx');


    $exec_time = $time_post - $time_pre;
    echo "temps d'exécution ==> " . $exec_time . " secondes";




    /*************************************  FIN CODE MAIN ************************************************************/


    ?>


</body>

</html>