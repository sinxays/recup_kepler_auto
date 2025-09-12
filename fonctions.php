<?php

use App\Connection;

include "pdo.php";


ini_set('xdebug.var_display_max_depth', 10);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

// RECUPERER LE TOKEN 
function goCurlToken($url)
{
    $ch2 = curl_init();

    curl_setopt($ch2, CURLOPT_URL, $url);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch2, CURLOPT_POST, true);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_POSTFIELDS, "apiKey=54c4fbe3ee0ed3683a17488371d5e762b9c5f4db6a7bc0507d3518c40bedfe300fbece014e3eac21acd380a142e874c460931659fe922bc1ef170d1f325e499c");

    $result = curl_exec($ch2);

    curl_close($ch2);

    $data = json_decode($result, true);

    return $data['value'];
}



//FONCTIONS POUR RECUPERER LES BDC DE LA VEILLE
function GoCurl_Recup_BDC($token, $url, $page, $num_bdc = '', $date_bdc = '')
{

    $ch = curl_init();

    // le token
    $header = array();
    $header[] = 'X-Auth-Token:' . $token;
    $header[] = 'Content-Type:text/html;charset=utf-8';

    // choper un BC spécifique
    if (isset($num_bdc) && $num_bdc != '') {
        $dataArray = array(
            /* 'state' => array(
            //     'administrative_selling.state.invoiced_edit',
            //     'administrative_selling.state.valid',
            //     'administrative_selling.state.invoiced',
            // ),*/
            "uniqueId" => $num_bdc,
            "page" => $page
        );
    }
    //sinon par date
    else {

        if (isset($date_bdc) && $date_bdc != '') {

            $dataArray = array(
                // "state" => array(
                //     'administrative_selling.state.invoiced_edit',
                //     'administrative_selling.state.valid',
                //     'administrative_selling.state.invoiced',
                // ),
                "orderFormDateFrom" => "$date_bdc",
                "orderFormDateTo" => "$date_bdc",
                "count" => 100,
                "page" => $page
            );
        }
        //si pas de date alors on prend ceux d'hier
        else {
            $date_hier = date('Y-m-d', strtotime('-1 day'));
            $dataArray = array(
                "state" => array(
                    'administrative_selling.state.invoiced_edit',
                    'administrative_selling.state.valid',
                    'administrative_selling.state.invoiced',
                ),
                // "state" => "administrative_selling.state.invoiced",
                "orderFormDateFrom" => "$date_hier",
                "orderFormDateTo" => "$date_hier",
                // "updateDateFrom" => "$date_hier",
                // "updateDateTo" => "$date_hier",
                "count" => 100,
                "page" => $page
            );
        }
    }


    $getURL = $url . '?' . http_build_query($dataArray);

    print_r($getURL);

    // die();

    sautdeligne();

    curl_setopt($ch, CURLOPT_URL, $getURL);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);


    $result = curl_exec($ch);

    if (curl_error($ch)) {
        $result = curl_error($ch);
        print_r($result);
        echo "<br/> erreur";
    }

    curl_close($ch);

    echo gettype($result);
    echo $result;

    $obj = json_decode($result);

    // sautdeligne();
    // echo gettype($obj);
    // die();



    // echo gettype($obj);
    // var_dump($obj);


    // echo '<pre>' . print_r($obj) . '</pre>';

    if (!isset($num_bdc) && $num_bdc == '') {
        echo '<pre> nombre total de BDC : ' . $obj->total . '</pre>';
        echo '<pre> page actuelle :' . $obj->currentPage . '</pre>';
        echo '<pre> BDC par page :' . $obj->perPage . '</pre>';
    }

    return $obj;
}

function GoCurl_Recup_BDC_ANNULE($token, $url, $page, $num_bdc = '', $date_bdc = '')
{

    $ch = curl_init();

    // le token
    $header = array();
    $header[] = 'X-Auth-Token:' . $token;
    $header[] = 'Content-Type:text/html;charset=utf-8';

    // choper un BC spécifique
    if (isset($num_bdc) && $num_bdc != '') {
        $dataArray = array(
            "state" => array(
                'administrative_selling.state.canceled'
            ),
            "uniqueId" => $num_bdc,
            "page" => $page
        );
    }
    //sinon par date
    else {

        if (isset($date_bdc) && $date_bdc != '') {

            $dataArray = array(
                "state" => 'administrative_selling.state.canceled',
                "updateDateFrom" => "$date_bdc",
                "updateDateTo" => "$date_bdc",
                "count" => 100,
                "page" => $page
            );
        }
        //si pas de date alors on prend de début avril à hier
        else {
            $date_from = "2024-04-18";
            $date_to = date('Y-m-d', strtotime('-1 day'));
            $dataArray = array(
                "state" => "administrative_selling.state.canceled",
                // "orderFormDateFrom" => "$date_from",
                // "orderFormDateTo" => "$date_to",
                "updateDateFrom" => "$date_from",
                "updateDateTo" => "$date_from",
                "count" => 100,
                "page" => $page
            );
        }
    }


    $getURL = $url . '?' . http_build_query($dataArray);

    print_r($getURL);

    // die();

    sautdeligne();

    curl_setopt($ch, CURLOPT_URL, $getURL);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);


    $result = curl_exec($ch);

    if (curl_error($ch)) {
        $result = curl_error($ch);
        print_r($result);
        echo "<br/> erreur";
    }

    curl_close($ch);

    echo gettype($result);
    echo $result;

    $obj = json_decode($result);

    // sautdeligne();
    // echo gettype($obj);
    // die();



    // echo gettype($obj);
    // var_dump($obj);


    // echo '<pre>' . print_r($obj) . '</pre>';

    if (!isset($num_bdc) && $num_bdc == '') {
        echo '<pre> nombre total de BDC : ' . $obj->total . '</pre>';
        echo '<pre> page actuelle :' . $obj->currentPage . '</pre>';
        echo '<pre> BDC par page :' . $obj->perPage . '</pre>';
    }

    return $obj;
}

// FONCTION INFOS DU VEHICULES
function getvehiculeInfo($reference, $token, $url_vehicule, $state, $is_not_available_for_sell = '')
{

    $ch = curl_init();

    // le token
    $header = array();
    $header[] = 'X-Auth-Token:' . $token;
    $header[] = 'Content-Type:text/html;charset=utf-8';

    switch ($is_not_available_for_sell) {
        case TRUE:

            if (isset($state) && $state == 'arrivage_or_parc') {
                $dataArray = array(
                    "reference" => $reference,
                    "state" => 'vehicle.state.on_arrival,vehicle.state.parc',
                    "isNotAvailableForSelling" => TRUE
                );
            }
            // !!!! si le vh est vendu, vendu AR, en cours, sorti, sorti AR ou annulé alors il faudra mettre l'état obligatoirement si tu veux un retour 
            else {
                $dataArray = array(
                    "reference" => $reference,
                    "state" => 'vehicle.state.sold,vehicle.state.sold_ar,vehicle.state.pending,vehicle.state.out,vehicle.state.out_ar,vehicle.state.canceled',
                    "isNotAvailableForSelling" => TRUE
                );
            }

            break;

        case FALSE:
            if (isset($state) && $state == 'arrivage_or_parc') {
                $dataArray = array(
                    "reference" => $reference,
                    "state" => 'vehicle.state.on_arrival,vehicle.state.parc',
                    "isNotAvailableForSelling" => FALSE
                );
            }
            // !!!! si le vh est vendu, vendu AR, en cours, sorti, sorti AR ou annulé alors il faudra mettre l'état obligatoirement si tu veux un retour 
            else {
                $dataArray = array(
                    "reference" => $reference,
                    "state" => 'vehicle.state.sold,vehicle.state.sold_ar,vehicle.state.pending,vehicle.state.out,vehicle.state.out_ar,vehicle.state.canceled',
                    "isNotAvailableForSelling" => FALSE
                );
            }
            break;

    }


    $data = http_build_query($dataArray);

    $getURL = $url_vehicule . '?' . $data;

    print_r($getURL);

    sautdeligne();

    curl_setopt($ch, CURLOPT_URL, $getURL);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    $result = curl_exec($ch);

    if (curl_error($ch)) {
        $result = curl_error($ch);
        print_r($result);
        echo "<br/> erreur";
    }

    var_dump(gettype($result));
    // var_dump($result);

    curl_close($ch);

    // créer un objet à partir du retour qui est un string
    $obj_vehicule = json_decode($result);

    // var_dump($obj_vehicule);

    // la on a un array
    //si on a l'erreur de token authentification alors on relance un token
    if (isset($obj_vehicule->code) && $obj_vehicule->code == 401) {
        $url = "https://www.kepler-soft.net/api/v3.0/auth-token/";
        $valeur_token = goCurlToken($url);
        $obj = getvehiculeInfo($reference, $valeur_token, $url_vehicule, $state);
        $return = $obj;
    }
    //sinon on continue normal
    else {
        //on prend l'object qui est dans l'array
        $return = $obj_vehicule[0];
        // on retourne un objet
    }
    // var_dump($return);
    return $return;
}


function getVehicules_VOreprise_RepTVA($token, $url_vehicule, $date_filtre, $page)
{
    $ch = curl_init();

    // le token
    $header = array();
    $header[] = 'X-Auth-Token:' . $token;
    $header[] = 'Content-Type:text/html;charset=utf-8';


    //le ou les parametres de l'url
    $dataArray = array(
        'dateUpdatedStart' => $date_filtre,
        'count' => 100,
        'page' => $page
    );


    $data = http_build_query($dataArray);

    $getURL = $url_vehicule . '?' . $data;


    print_r($getURL);

    sautdeligne();

    curl_setopt($ch, CURLOPT_URL, $getURL);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    $result = curl_exec($ch);

    if (curl_error($ch)) {
        $result = curl_error($ch);
        print_r($result);
        echo "<br/> erreur";
    }

    curl_close($ch);

    // print_r($result);
    // die();

    // créer un array à partir du retour qui est un string
    $obj_vehicule = json_decode($result);

    // $return = $obj_vehicule[0];

    return $obj_vehicule;
}


//FONCTION POUR RECUPERER LES DONNEES
function GoCurl_Facture($token, $url, $page)
{

    $ch = curl_init();

    // le token
    //$token = '7MLGvf689hlSPeWXYGwZUi\/t2mpcKrvVr\/fKORXMc+9BFxmYPqq4vOZtcRjVes9DBLM=';
    $header = array();
    $header[] = 'X-Auth-Token:' . $token;
    $header[] = 'Content-Type:text/html;charset=utf-8';


    // sur un véhicule spécifique
    // $dataArray = array(
    //     "vehicleReference" => 'rcvfq',
    //     "page" => $page
    // );

    $date_to = date('Y-m-d', strtotime('-1 day'));
    // $date_to = "2024-07-11";

    // choper une facture spécifique
    $dataArray = array(
        "number" => 'VO109079',
        "page" => $page
    );


    //sur une date
    $dataArray = array(
        "state" => 'invoice.state.edit',
        "invoiceDateFrom" => $date_to,
        "invoiceDateTo" => $date_to,
        // "updateDateFrom" => "2024-07-01",
        // "updateDateTo" => "2023-07-20",
        "count" => "100",
        "page" => $page


    );




    $data = http_build_query($dataArray);

    $getURL = $url . '?' . $data;

    print_r($getURL);

    sautdeligne();

    curl_setopt($ch, CURLOPT_URL, $getURL);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    $result = curl_exec($ch);

    if (curl_error($ch)) {
        $result = curl_error($ch);
        print_r($result);
        echo "<br/> erreur";
    }

    curl_close($ch);

    echo "<pre>";
    print_r($result);
    echo "</pre>";

    $obj = json_decode($result);

    return $obj;
}

function GoCurl_Facture_edite($token, $url, $page)
{

    $ch = curl_init();

    // le token
    //$token = '7MLGvf689hlSPeWXYGwZUi\/t2mpcKrvVr\/fKORXMc+9BFxmYPqq4vOZtcRjVes9DBLM=';
    $header = array();
    $header[] = 'X-Auth-Token:' . $token;
    $header[] = 'Content-Type:text/html;charset=utf-8';

    // choper une facture spécifique
    // $dataArray = array(
    //     "orderFormNumber" => '70670',
    //     "page" => $page
    // );


    // sur un véhicule spécifique
    // $dataArray = array(
    //     "vehicleReference" => 'rcvfq',
    //     "page" => $page
    // );

    //sur une date
    $dataArray = array(
        "state" => 'invoice.state.edit',
        "invoiceDateFrom" => "2023-12-08",
        // "invoiceDateTo" => "2023-11-06",
        // "updateDateFrom" => "2023-12-08",
        // "updateDateTo" => "2023-11-06",
        "count" => "100",
        "page" => $page


    );


    $data = http_build_query($dataArray);

    $getURL = $url . '?' . $data;

    print_r($getURL);

    sautdeligne();

    curl_setopt($ch, CURLOPT_URL, $getURL);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    $result = curl_exec($ch);

    if (curl_error($ch)) {
        $result = curl_error($ch);
        print_r($result);
        echo "<br/> erreur";
    }

    curl_close($ch);

    echo "<pre>";
    print_r($result);
    echo "</pre>";

    $obj = json_decode($result);

    return $obj;
}


// JSON TO CSV
function array2csv($data, $delimiter = ';', $enclosure = '"', $escape_char = "\\")
{
    $f = fopen('test_datas_bdc.csv', 'wr+');
    $entete[] = ["N° Bon Commande", "Immatriculation", "RS/Nom Acheteur", "Prix Vente HT", "Prix de vente TTC", "Vendeur Vente", "date du dernier BC", "Destination de sortie", "VIN"];

    //entete
    foreach ($entete as $item) {
        fputcsv($f, $item, $delimiter, $enclosure, $escape_char);
    }

    foreach ($data as $item) {
        fputcsv($f, $item, $delimiter, $enclosure, $escape_char);
    }
    rewind($f);
    return stream_get_contents($f);
}


function get_CVO_by_vendeur($nom_vendeur)
{

    //dans le cas ou ya un é
    $nom_vendeur = str_replace('é', 'e', $nom_vendeur);

    $name_vendeur = get_name_acheteur_vendeur($nom_vendeur);

    $pdo = Connection::getPDO();
    $request = $pdo->query("SELECT cvo.nom_cvo FROM `cvo` 
        LEFT JOIN collaborateurs_payplan as cp ON cp.id_site = cvo.ID 
        WHERE cp.nom = '$name_vendeur' ");
    $cvo = $request->fetch(PDO::FETCH_COLUMN);
    return strtoupper($cvo);

}


function get_name_acheteur_vendeur($nom_complet)
{

    $nb_word_nom_complet = str_word_count($nom_complet);

    switch ($nb_word_nom_complet) {
        //si c'est une agence
        case 1:
            return $nom_complet;
            break;

        //sinon si c'est un collaborateur hors agence
        case 2:
            if ($nom_complet !== null || $nom_complet !== '') {
                $nom_complet_acheteur = $nom_complet;
                $acheteur = explode(" ", strtolower($nom_complet_acheteur));
                // si jamais on a un point à la place de l'espace
                if (empty($acheteur[1])) {
                    $acheteur = explode(".", strtolower($nom_complet_acheteur));
                }
                // $prenom_acheteur = $acheteur[0];
                $nom_acheteur = $acheteur[1];
                return $nom_acheteur;
            } else {
                return '';
            }
            break;
    }
}

function upload_bdc_ventes_uuid($bdc, $uuid, $date_bdc)
{
    $pdo = Connection::getPDO();
    $data = [
        'bdc' => $bdc,
        'uuid' => $uuid,
        'date_bdc' => $date_bdc
    ];
    $sql = "INSERT INTO bdc_ventes (numero_bdc, uuid,date_bdc) VALUES (:bdc, :uuid,:date_bdc)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);

}
function get_bdc_from_uuid($uuid)
{
    $pdo = Connection::getPDO();
    $request = $pdo->query("SELECT numero_bdc FROM bdc_ventes WHERE uuid = '$uuid' ");
    $uuid = $request->fetch(PDO::FETCH_COLUMN);
    return $uuid;
}

function get_chassis_from_bdc($bdc)
{
    $pdo = Connection::getPDO_2();
    $request = $pdo->query("SELECT numero_chassis FROM vehicules 
    LEFT JOIN bdcventes ON bdcventes.vehicule_id = vehicules.id
    WHERE bdcventes.numero = '$bdc' ");
    $uuid = $request->fetchAll(PDO::FETCH_ASSOC);
    return $uuid;
}



// SAUT DE LIGNE 
function sautdeligne()
{
    echo "<br/>";
    echo "<br/>";
}


function save_facture_to_portail_massoutre($obj_datas)
{

    $pack_first = FALSE;
    $garantie = FALSE;
    $nbr_mois_garantie_neo = 0;

    $pdo = Connection::getPDO();
    foreach ($obj_datas->items as $item_key => $item) {
        if ($item->type == 'service_selling') {
            // voir si on comptabilise un pack first
            if ($item->reference == 'MISE A LA ROUTE') {
                $pack_first = TRUE;
            }
            //si il ya une garantie NEO 
            if (strpos($item->reference, 'Garantie NEO' !== false)) {
                $garantie = TRUE;
                //alors on va voir quelle durée ? 6 ,12 ,24 ?
                $array_garantie_neo = explode(' ', $item->name);
                $nbr_mois_garantie_neo = $array_garantie_neo[2];
            }
        }
    }

    $data = [
        'uuid' => $obj_datas->uuid,
        'num_facture' => $obj_datas->number,
        'date_facture' => $obj_datas->invoiceDate,
        'destination' => $obj_datas->destination,
        'vendeur' => $obj_datas->seller,
        'prix_ht' => $obj_datas->sellPriceWithoutTax,
        'prix_ttc' => $obj_datas->sellPriceWithTax,
        'pack_first' => $pack_first == TRUE ? 1 : 0,
        'garantie' => $garantie == TRUE ? 1 : 0,
        'type_garantie' => $nbr_mois_garantie_neo,
        'num_bdc' => $obj_datas->orderForm->number
    ];
    $sql = "INSERT INTO facturesventes (num_facture,uuid,date_facture,num_bdc,destination,vendeur,prix_ht,prix_ttc,pack_first,garantie,type_garantie) 
    VALUES (:num_facture, :uuid,:date_facture,:num_bdc,:destination,:vendeur,:prix_ht,:prix_ttc,:pack_first,:garantie,:type_garantie)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
}


