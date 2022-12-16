<?php

$file = "finQ2.xml";
$fileopen=(fopen($file,'a'));
ftruncate($fileopen, 0);
$doc = new DomDocument();
$doc->validateOnParse = true;
$doc->preserveWhiteSpace = false;
$doc->load("tp.xml");

$xpath = new DOMXPath($doc);
$res = new DOMDocument('1.0', 'utf-8');
$implementation = new DOMImplementation();
$res->appendChild($implementation->createDocumentType('liste-présidents SYSTEM \'res.dtd\''));
$res->formatOutput = true;


foreach ($xpath->query('//comment()') as $comment) {
    $comment->parentNode->removeChild($comment);
}


$racine = $doc->documentElement;
//$element = $racine->firstChild->firstChild;
$pays = $doc->documentElement->firstChild->firstChild;
$personnes = $doc->documentElement->lastChild->firstChild;
$visites = $doc->documentElement->firstChild->nextSibling->firstChild;
$listeP = $doc->documentElement->firstChild;
$listeVisites = [];
$listePays = [];
$listePersonnes = [];

while (($personnes instanceOf DOMELEMENT) && ($personnes->tagName == 'personne')) {
    if ($personnes->tagName == 'personne') {
        $personne = $personnes->firstChild;
        if ($personne->getAttribute('type') == 'Président de la République') {
            array_push($listePersonnes, ['Nom' => $personnes->getAttribute('nom'), 'Id' => $personne->getAttribute('xml:id')]);
        }
    }
    $personnes = $personnes->nextSibling;
}

while (($pays instanceOf DOMELEMENT) && ($pays->tagName == 'pays')) {
    $afrique =  false;
    $francais = false;

    $francophone = "";
    $continent = $pays->firstChild;
    while (isset($continent->nodeName)) {
        if ($continent->getAttribute('continent') == 'africa') {
            $afrique = true;
        } elseif ($continent->tagName == 'language') {
            if ($continent->hasAttribute('percentage') && $continent->textContent == 'French') {
                $francais = true;

                if ($continent->getAttribute('percentage') >= 30) {
                    $francophone = "En-partie";
                }

            } elseif (!$continent->hasAttribute('percentage') && $continent->textContent == 'French') {
                $francophone = "Officiel";
                $francais = true;
            }
        }
        $continent = $continent->nextSibling;
    }
    if ($afrique) {
        array_push($listePays, ['Nom' => $pays->getAttribute('nom'), 'Id' => $pays->getAttribute('xml:id'), 'Francophone' => $francophone]);
    }
    $pays = $pays->nextSibling;
}



while (($visites instanceOf DOMELEMENT) && ($visites->tagName == 'visite')) {
    // if (in_array($visites->getAttribute('pays'), array_column($listePays, 'Id')) && in_array($visites->getAttribute('personne'), array_column($listePersonnes, 'Id'))) {
    if (in_array($visites->getAttribute('personne'), array_column($listePersonnes, 'Id')) && in_array($visites->getAttribute('pays'), array_column($listePays, 'Id'))) {

        array_push($listeVisites, ['Debut' => $visites->getAttribute('debut'), 'Fin' => $visites->getAttribute('fin'), 'Personne' => $visites->getAttribute('personne'), 'Pays' => $visites->getAttribute('pays')]);
    }

    $visites = $visites->nextSibling;
}

$racine = $res->createElement('liste-présidents');
$res->appendChild($racine);

foreach ($listePersonnes as $p) {
    $president_res = $res->createElement('président');
    $racine->appendChild($president_res);
    $president_res->setAttribute('nom', $p["Nom"]);
    foreach ($listePays as $pa) {
        $duree = 0;
        foreach ($listeVisites as $visite) {
            if (($visite['Pays'] == $pa['Id']) && ($visite['Personne'] == $p['Id'])) {
                $debut = strtotime($visite['Debut']);
                $fin = strtotime($visite['Fin']);
                $difference = $fin - $debut;
                $duree += 1 + round($difference / 86400);
            }
        }
        if ($duree == 0) {
            $temps = "0";
        } else {
            $temps ="P".$duree."D";
        }
        if ($pa['Francophone'] != null) {
            $fr = $pa['Francophone'];
        } else {
            $fr = "";
        }

        $pays_res = $res->createElement('pays');
        $president_res->appendChild($pays_res);
        $pays_res->setAttribute('nom', $pa["Nom"]);
        if(!($fr == "")){
            $pays_res->setAttribute('francophone',$fr);
        }
        $pays_res->setAttribute('durée',$temps);

    }
}
fwrite($fileopen,$res->saveXML());

fclose($fileopen);