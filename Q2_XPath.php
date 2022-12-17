<?php
$file = "xml/DOMXPATH.xml";
$fileopen=(fopen($file,'a'));
ftruncate($fileopen, 0);

$doc = new DOMDocument();
$doc->preserveWhiteSpace = false;
$doc->load("tp.xml");
$doc->formatOutput = true;
$xpath = new DOMXPath($doc);

//Creation du document DOM Final
$Docres = new DOMDocument('1.0', 'utf-8');
$implementation = new DOMImplementation();
$Docres->appendChild($implementation->createDocumentType('liste-présidents SYSTEM \'res.dtd\''));
$Docres->formatOutput = true;

//Creation des 3 tableaux associatifs finaux.
$listVisitesTraite = array();
$listPaysTraite = array();
$listPresidentTraite = array();

//Requête XPATH ou nous récupèrons toutes les personnes ayant pour type 'Président de la République'
$listePresident = $xpath->query("/déplacements/liste-personnes/personne/fonction[@type = 'Président de la République']");
//Requête XPATH ou nous récupèrons toutes les pays ayant pour continent l'afrique
$listePays = $xpath->query("/déplacements/liste-pays/pays[./encompassed/@continent='africa']");
$listeVisite = array();
$listPaysTraitement = array();



//recherche des présidents
foreach ($listePresident as $resultat) {
    $president = array();
    $president["Fonction"] = $resultat->getAttribute("type");

    $president["Id"] = $resultat->getAttribute("xml:id");
    $pres = $resultat->parentNode;
    $president["Nom"] = $pres->getAttribute("nom");

    $listPresidentTraite[] = $president;

}
//liste-pays affiche chaque pays
foreach ($listePays as $resultat) {

    $pourcent = 0;
    $pays = array("Id" => $resultat->getAttribute("xml:id"),"Nom" => $resultat->getAttribute("nom"),  "Francophone" => false);
    //Pour chaque pays nous stockons dans notre tableau associatif les valeurs des pays.

    //Pour tout les sous éléments de pays (Encompassed et Language)
    foreach ($resultat->childNodes as $res) {

        if (isset($res->nodeName)) {
                if ($res->nodeName == "language") {
                    if ($res->nodeValue == "French") {

                        //var_dump($pays);
                        $percent = $res->getAttribute("percentage");
                        //Si nous n'avons pas de pourcentage mais que la langue est français, alors c'est la langue
                        //Officielle
                        if ($percent == "") {
                            $pourcent = "Officiel";
                            $pays["Francophone"] = $pourcent;
                        } else {
                            //SI c'est supérieur à 30% alors la langue est parlée en partie
                            if ($percent > 30 ) {
                                $pourcent = "En-partie";
                                $pays["Francophone"] = $pourcent;
                            }
                        }
                    }
                }
                //Si le nom du pays est dans notre tableau associatif
            //Alors nous stockons le xml:id du pays dans un autre tableau associatif, pour effectuer des traitement
                if (!in_array($resultat->getAttribute("nom"), $listPaysTraitement)) {
                    $listPaysTraitement[] = $resultat->getAttribute("xml:id");
                }
            }
        }
    $listPaysTraite[] = $pays;
    //Puis on stocke le pays
}

foreach($listPresidentTraite as $pre){
    foreach($listPaysTraite as $pays){
        $nomPre = $pre["Id"];
        $nomPays = $pays["Id"];
        $duree = 0;
        $ResultatlisteVisite = $xpath->query("/déplacements/liste-visites/visite[./@personne = '$nomPre'][./@pays = '$nomPays']");
        foreach($ResultatlisteVisite as $v){
            $visite = array("President" => $nomPre, "Lieu" => $nomPays, "Duree" => 0);
            //Afin de pouvoir retrouver les valeurs pour une visite, donc pour une clé Pays/Président
            //On créé une clé contenant les deux XML:ID
            $key = $nomPre . "_" .$nomPays;
            //var_dump($cle);
            if ($v->getAttribute("debut") == $v->getAttribute("fin")) {

                $visite["Duree"] += 1;

            } else {
                $debut = strtotime($v->getAttribute("debut"));
                $fin = strtotime($v->getAttribute("fin")); //Convertir le string en timestamp Unix
                $diff = $fin - $debut;
                $visite["Duree"] += 1 + round($diff / 86400);//De seconde vers jour
            }
            if (in_array($v->getAttribute("pays"), $listPaysTraitement)) {

                if (array_key_exists($key, $listVisitesTraite)) {
        //Si la clé précédemment crééé est présente dans notre tableau associatif
                    //Alors on incrémente la valeur de durée
                    $listVisitesTraite[$key]["Duree"] += $visite["Duree"];

                } else {
                    //Sinon on stock la visite
                    $listVisitesTraite[$key] = $visite;

                }
            }

        }

    }
}
//var_dump($listVisites);
$racine = $Docres->createElement('liste-présidents');
//Création de l'élément Liste Présidents
$Docres->appendChild($racine);

foreach($listPresidentTraite as $p){
    //Pour chaque président, création de l'élément président et de son attribut nom
    $president_res = $Docres->createElement('président');
    $racine->appendChild($president_res);
    $president_res->setAttribute('nom', $p["Nom"]);

    foreach($listPaysTraite as $pa){
//Pour chaque pays, création de l'élément pays et de ses attributs
        $pays_res = $Docres->createElement('pays');
        $president_res->appendChild($pays_res);
        $pays_res->setAttribute('nom', $pa["Nom"]);
        $fr = $pa["Francophone"];
        if(!($fr == "")){
            $pays_res->setAttribute('franchophone',$fr);
        }
//Nous recréons la clé afin de réacceder a nos durées
        $k = $p["Id"]."_".$pa["Id"];
            //var_dump($c);
        if (isset($listVisitesTraite[$k]["Duree"])) {
            $pays_res->setAttribute('durée',"P".$listVisitesTraite[$k]["Duree"]."D");

        }else{
            $pays_res->setAttribute('durée',0);
        }

            //echo
        }

}

fwrite($fileopen,$Docres->saveXML());
//On écrit dans le fichier
fclose($fileopen);
