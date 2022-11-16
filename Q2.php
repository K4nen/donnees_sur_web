<?php header('Content-type: text/xml; Encoding: utf-8');

//Chemin vers le fichier texte
$file = "finQ2.xml";
//Ouverture en mode écriture
$fileopen=(fopen($file,'a'));
ftruncate($fileopen, 0);

$doc = new DOMDocument();
$doc->preserveWhiteSpace = false;
$doc->validateOnParse  = true;
$doc->load("tp.xml");


$p = $doc->documentElement->lastChild->firstChild;
$pa = $doc->documentElement->firstChild->firstChild;

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
        fwrite($fileopen, "<Président nom=\"" .$nom . "\">"."\n");
        }
    $p=$p->nextSibling;
}




fwrite($fileopen, "</liste-présidents>\n");
//On ferme le fichier
fclose($fileopen);
?>
