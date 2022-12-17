<?php

$file = "xml/DOM.xml";
$fileopen=(fopen($file,'a'));
ftruncate($fileopen, 0);
$doc = new DomDocument();
$doc->validateOnParse = true;
$doc->preserveWhiteSpace = false;
$doc->load("tp.xml");

$res = new DOMDocument('1.0', 'utf-8'); //Création d'un document DOM de sortie
$implementation = new DOMImplementation();
$res->appendChild($implementation->createDocumentType('liste-présidents SYSTEM \'res.dtd\''));//Ajout de la DTD
$res->formatOutput = true;

$racine = $doc->documentElement;

$elements = $racine->firstChild;



//Permet de se placer dans le bon noeud directement
while(isset($elements->nodeName)) {
    if($elements->nodeName == 'liste-personnes') {
        $personnes = $elements->firstChild;
    }elseif($elements->nodeName == 'liste-pays') {
        $pays = $elements->firstChild;
    }elseif($elements->nodeName == 'liste-visites') {
        $visites = $elements->firstChild;
    }
    $elements = $elements->nextSibling;
}

$listeVisites = [];
$listePays = [];
$listePersonnes = [];

while (isset ($personnes->nodeName)) {
    if ($personnes->tagName == 'personne') {
        $personne = $personnes->firstChild;
        if ($personne->getAttribute('type') == 'Président de la République') {
            array_push($listePersonnes, ['Nom' => $personnes->getAttribute('nom'), 'Id' => $personne->getAttribute('xml:id')]);
            //Pour chaque noeud personne, si le type est président de la république on stock les valeurs dans un tableau associatif
        }
    }
    $personnes = $personnes->nextSibling;
    //Puis on passe au prochain frère
}
while (isset($pays->nodeName)) {
    $afrique =  false;
    $francais = false;

    $francophone = "";
    $continent = $pays->firstChild;

    while (isset($continent->nodeName)) {
        if ($continent->nodeName == 'encompassed' && $continent->getAttribute('continent') == 'africa') {
            $afrique = true; //Variable booléen a true (pays africain)
        } elseif ($continent->nodeName == 'language') {
            if ($continent->hasAttribute('percentage') && $continent->textContent == 'French') {
                $francais = true; //Variable booléen à true (pays utilisant le français

                if ($continent->getAttribute('percentage') >= 30) {
                    $francophone = "En-partie"; //Si le français est parlé à plus de 30%, alors il est parlé
                    // En Partie
                }

            } elseif (!$continent->hasAttribute('percentage') && $continent->textContent == 'French') {
            //Si il n'a pas l'attribut pourcentage mais possède la langue française, alors c'est la langue officielle
                $francophone = "Officiel";
                $francais = true;
            }
        }
        $continent = $continent->nextSibling;
    }
    if ($afrique) {//Si la variable booléenne est à true, alors c'est un pays d'afrique, on stock donc dans un tableau associatif
        array_push($listePays, ['Nom' => $pays->getAttribute('nom'), 'Id' => $pays->getAttribute('xml:id'), 'Francophone' => $francophone]);
    }
    $pays = $pays->nextSibling;
}


while (isset($visites->nodeName)) {
    if (in_array($visites->getAttribute('personne'), array_column($listePersonnes, 'Id')) && in_array($visites->getAttribute('pays'), array_column($listePays, 'Id'))) {
        //Si l'attribut Personne de la visite  est égal à xml:id de notre tableau associatif et que l'attribut Pays de la visite est également xml:id
        // alors on stock la visite
        array_push($listeVisites, ['Debut' => $visites->getAttribute('debut'), 'Fin' => $visites->getAttribute('fin'), 'Personne' => $visites->getAttribute('personne'), 'Pays' => $visites->getAttribute('pays')]);
    }

    $visites = $visites->nextSibling;
    //Et on passe à la prochaine visite
}

$racine = $res->createElement('liste-présidents'); //Création de l'élément liste-présidents pour le dom final.
$res->appendChild($racine);

foreach ($listePersonnes as $p) {
    //Pour chaque président, création d'un élément président
    $president_res = $res->createElement('président');
    $racine->appendChild($president_res);
    //Ajout de l'élément nom pour le président
    $president_res->setAttribute('nom', $p["Nom"]);
    foreach ($listePays as $pa) {
        $duree = 0;
        foreach ($listeVisites as $visite) {
            if (($visite['Pays'] == $pa['Id']) && ($visite['Personne'] == $p['Id'])) {
    //Pour chaque visite, si le pays est égal au pays actuel du foreach et si la personne est égal au président du for each
                //alors on effectue le calcul de durée
                $debut = strtotime($visite['Debut']);
                $fin = strtotime($visite['Fin']); //Convertie la date string en format timeStamp UNIX
                $difference = $fin - $debut;
                $duree += 1 + round($difference / 86400); // On divise par 86400 pour passer de seconde à jour
            }
        }
        if ($duree == 0) {
            $temps = "0";
        } else {
            $temps ="P".$duree."D";
        }
        if ($pa['Francophone'] != null) {//Si dans notre tableau associatif, la valeur est renseignée, on la stock dans une variable
            $fr = $pa['Francophone'];
        } else {
            $fr = ""; //Sinon la valeur est nulle
        }

        $pays_res = $res->createElement('pays');
    //Création d'un element pays et ajout dans notre DOM
        $president_res->appendChild($pays_res);
        $pays_res->setAttribute('nom', $pa["Nom"]);
        // Ajout du nom du pays
        if(!($fr == "")){
            $pays_res->setAttribute('franchophone',$fr);
        //Si la valeur n'est pas nulle, alors on créer un attribut francophone
        }
        $pays_res->setAttribute('durée',$temps);
        //Création d'un attribut durée contenant le temps de la visite


    }
}
fwrite($fileopen,$res->saveXML());
//ecriture du document final dans le fichier.
fclose($fileopen);