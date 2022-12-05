<?php header('Content-type: text/xml; Encoding: utf-8');

//Chemin vers le fichier texte
$file = "finQ2.xml";
//Ouverture en mode écriture
$fileopen=(fopen($file,'a'));
ftruncate($fileopen, 0);

$doc = new DOMDocument();

$doc->load("tp.xml");

$pa = $doc->documentElement->firstChild->firstChild;
$p = $doc->documentElement->lastChild->firstChild;

echo $pa;
//Génération du résultat
fwrite($fileopen, "<?xml version='1.0' encoding='UTF-8' ?>\n");
fwrite($fileopen, "<!DOCTYPE deplacement SYSTEM 'tp.dtd'>\n");
fwrite($fileopen, "<liste-présidents>\n");



while (($p instanceOf DOMELEMENT) && ($p->tagName == 'personne')) {
    $personne = $p;
    $f = $personne->firstChild;
    $fonction = $f->getAttribute("type");
    if ($fonction == "Président de la République") {
        $nom = $personne->getAttribute("nom");
        //Ecriture dans le fichier texte
        fwrite($fileopen, "<president nom=\"" .$nom . "\">"."\n");
        $nomP = "test";
        $continent = "";

        while (($pa instanceOf DOMELEMENT) && ($pa->tagName == 'pays')) {
            $nomP = "oui";
            $pays = $pa;
            $c = $pays->firstChild;
            $continent = $c->getAttribute("continent");
            if($continent == 'africa'){
                $nomP .=$pays->getAttribute('name')."\n";
            }

        }
        fwrite($fileopen, $nomP."\n");

    }
    $p=$p->nextSibling;
}
function affichepays($pa){
    $nom = "";
    $continent = "";
    while (($pa instanceOf DOMELEMENT) && ($pa->tagName == 'pays')) {
        $pays = $pa;
        $c = $pays->firstChild;
        $continent = $c->getAttribute("continent");
        if($continent == 'africa'){
            $nom .=$pays->getAttribute('name')."\n";
        }

    }
return $continent;

}



fwrite($fileopen, "</liste-présidents>\n");
//On ferme le fichier
fclose($fileopen);
?>
