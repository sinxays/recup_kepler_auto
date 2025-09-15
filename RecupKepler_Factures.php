<!DOCTYPE html>
<html>

<head>
    <title>Recup API KEPLER FACTURE</title>
    <meta charset="utf-8" />
</head>

<body>
    <h2>Récupération du token</h2>


    <?php



    header('Content-type: text/html; charset=UTF-8');

    /******************************************  CODE MAIN ******************************************/

    include("xlsxwriter.class.php");

    include 'fonctions.php';

    // recup valeur token seulement
    $url = "https://www.kepler-soft.net/api/v3.0/auth-token/";
    $valeur_token = goCurlToken($url);

    sautdeligne();

    ?>



    <span style="color:red">
        <?php echo $valeur_token; ?>
    </span>

    <?php

    echo "<h2>Récupération des données factures</h2>";


    // recup données
    $request_facture = "v3.1/invoice/";
    $request_vehicule = "v3.7/vehicles/";

    $url = "https://www.kepler-soft.net/api/";

    $req_url = $url . "" . $request_facture;
    $req_url_vehicule = $url . "" . $request_vehicule;


    $datas_find = true;
    $array_datas = array();
    $nb_factures = 0;
    $nb_lignes = 0;
    $i = 0;
    $j = 1;

    while ($datas_find == true) {

        //recupere la requete !!!!
        $obj = GoCurl_Facture($valeur_token, $req_url, $j);
        $obj_final = $obj->datas;

        if (!empty($obj_final)) {
            $datas_find = true;



            $array_datas[$i] = ["Date de la facture", "Immatriculation", "Kilométrage", "N° de la facture", "Prix de vente HT", "Acheteur", "Adresse du client", "Code postal du client", "Ville du client", "Pays du client", "Email du client", "Telephone du client", "Telephone du client2", "Telephone portable du client", "Vendeur", "Parc", "Destination", "Source du client"];

            $i++;

            /*****************    BOUCLE du tableau de données récupérés *****************/
            foreach ($obj_final as $keydatas => $keyvalue) {
                //on boucle par rapport au nombre de bon de commande dans le tableau datas[]
    
                //get date facture
                $date_facture_tmp = substr($keyvalue->invoiceDate, 0, 10);
                $date_facture_tmp2 = str_replace("-", "/", $date_facture_tmp);
                $date_facture = date('d/m/Y', strtotime($date_facture_tmp2));


                //get numéro de facture
                $num_facture = $keyvalue->number;

                // get prix de vente HT
                // edit : on ne prends pas le prix de la facture totale mais le prix de la vente du VH seulement sans les différentes prestations.
                // $prixHT = $keyvalue->sellPriceWithoutTax;
    
                //get nom acheteur
                if (isset($keyvalue->owner->firstname)) {
                    $nom_acheteur = $keyvalue->owner->firstname . " " . $keyvalue->owner->lastname;
                } else {
                    if (isset($keyvalue->owner->corporateNameContact) && !empty($keyvalue->owner->corporateNameContact)) {
                        $nom_acheteur = $keyvalue->owner->corporateNameContact;
                    } else {
                        $nom_acheteur = "";
                    }
                }

                //adresse du client
                if (isset($keyvalue->customer->addressAddress) && !empty($keyvalue->customer->addressAddress)) {
                    $adresse_client = $keyvalue->customer->addressAddress;
                } else {
                    $adresse_client = "";
                }

                // Code postal du client
                if (isset($keyvalue->customer->addressPostalCode) && !empty($keyvalue->customer->addressPostalCode)) {
                    $cp_client = $keyvalue->customer->addressPostalCode;
                } else {
                    $cp_client = '';
                }

                // ville du client
                if (isset($keyvalue->customer->addressCity) && !empty($keyvalue->customer->addressCity)) {
                    $ville_client = $keyvalue->customer->addressCity;
                } else {
                    $ville_client = "";
                }

                // pays du client
                if (isset($keyvalue->customer->addressCity) && !empty($keyvalue->customer->addressCity)) {
                    $pays_client = $keyvalue->customer->addressCountry;
                } else {
                    $pays_client = "";
                }

                // email du client
                if (isset($keyvalue->customer->email) && !empty($keyvalue->customer->email)) {
                    $email_client = $keyvalue->customer->email;
                } else {
                    $email_client = '';
                }

                //tel fixe du client
                if (isset($keyvalue->customer->phoneNumber) && !empty($keyvalue->customer->phoneNumber)) {
                    $telfixe_client = $keyvalue->customer->phoneNumber;
                } else {
                    $telfixe_client = '';
                }

                //tel mobile du client
                if (isset($keyvalue->customer->cellPhoneNumber) && !empty($keyvalue->customer->cellPhoneNumber)) {
                    $telmobile_client = $keyvalue->customer->cellPhoneNumber;
                } else {
                    $telmobile_client = '';
                }

                //get nom vendeur
                $nom_vendeur = $keyvalue->seller;
                $nom_vendeur = explode("<", $nom_vendeur);
                $nom_vendeur = $nom_vendeur[0];
                $nom_vendeur = trim($nom_vendeur);




                //get destination sortie
                if (isset($keyvalue->destination) && !empty($keyvalue->destination)) {
                    $destination_sortie = $keyvalue->destination;
                } else {
                    $destination_sortie = '';
                }


                // source du client
                if (isset($keyvalue->customer->knownFrom) && !empty($keyvalue->customer->knownFrom)) {
                    $source_client = $keyvalue->customer->knownFrom;
                } else {
                    $source_client = '';
                }



                if (isset($keyvalue->items)) {
                    foreach ($keyvalue->items as $key_item => $value_item) {
                        if ($value_item->type == 'vehicle_selling') {

                            // enregistrer la facture dans la base > table : portail_massoutre > facturesventes
                            save_facture_to_portail_massoutre($keyvalue);

                            $reference_item = $value_item->reference;

                            $prixHT = $value_item->sellPriceWithoutTaxWithoutDiscount;

                            //  recup infos du véhicule
                            $obj_result = getvehiculeInfo($reference_item, $valeur_token, $req_url_vehicule, false);
                            if (empty($obj_result)) {
                                $obj_result = getvehiculeInfo($reference_item, $valeur_token, $req_url_vehicule, true);
                            }
                            $obj_vehicule = $obj_result;

                            //get IMMATRICULATION
                            if (empty($obj_vehicule->licenseNumber)) {
                                $immatriculation = 'N/C';
                            } else {
                                $immatriculation_tmp = $obj_vehicule->licenseNumber;
                                $immatriculation = str_replace("-", "", $immatriculation_tmp);
                            }

                            // get kilométrage
                            $kilometrage = $obj_vehicule->distanceTraveled;

                            //get de quel parc
                            // $parc = $obj_vehicule->fleet;
    
                            //si Guillaume et Humberto alors CVO SIEGE
                            // if ($nom_vendeur == "GUILLAUME HONNERT" || $nom_vendeur == "HUMBERTO ALVES") {
                            //     $parc = "CVO SIEGE";
                            // }
    
                            /** EXCEPTIONS **/
                            switch ($nom_vendeur) {
                                case "GUILLAUME HONNERT":
                                case "HUMBERTO ALVES":
                                    $parc = "CVO SIEGE";
                                    break;

                                case "Jean-Philippe Thomas":
                                    $parc = "CVO THOMAS";
                                    break;

                                case "PIERRE MEHDI AQQADE":
                                    $parc = "CVO AQQADE";
                                    break;

                                // sinon on prend le cvo du vendeur
                                default:
                                    $parc = strtoupper(get_CVO_by_vendeur($nom_vendeur));
                                    break;
                            }

                            $array_datas[$i]['date_facture'] = $date_facture;
                            $array_datas[$i]['immatriculation'] = $immatriculation;
                            $array_datas[$i]['kilometrage'] = $kilometrage;
                            $array_datas[$i]['numerofacture'] = $num_facture;
                            $array_datas[$i]['prixHT'] = $prixHT;
                            $array_datas[$i]['acheteur'] = $nom_acheteur;
                            $array_datas[$i]['adresseclient'] = $adresse_client;
                            $array_datas[$i]['CPclient'] = $cp_client;
                            $array_datas[$i]['villeclient'] = $ville_client;
                            $array_datas[$i]['paysclient'] = $pays_client;
                            $array_datas[$i]['emailclient'] = $email_client;
                            $array_datas[$i]['telfixeclient'] = $telfixe_client;
                            $array_datas[$i]['telfixeclient2'] = "";
                            $array_datas[$i]['telportableclient'] = $telmobile_client;
                            $array_datas[$i]['vendeur'] = $nom_vendeur;
                            $array_datas[$i]['parc'] = $parc;
                            $array_datas[$i]['destination'] = $destination_sortie;
                            $array_datas[$i]['source'] = $source_client;

                            $nb_lignes++;
                            $i++;
                        }
                    }
                }

                $nb_factures++;
            }
        } else {
            $datas_find = false;
        }
        $j++;
    }


    sautdeligne();

    echo 'nombre de Factures : ' . $nb_factures;

    sautdeligne();

    echo 'nombre de lignes : ' . $nb_lignes;






    $writer = new XLSXWriter();
    $writer->writeSheet($array_datas);
    $writer->writeToFile('VENTES_DU_JOUR.xlsx');



    ?>



</body>

</html>