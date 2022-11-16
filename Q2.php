<?php header('Content-type: text/xml; Encoding: utf-8');
$doc = new DOMDocument();
$doc->preserveWhiteSpace = false;
$doc->validateOnParse  = true;
$doc->load("tp.xml");

$p = $doc->documentElement->lastChild->firstChild;

//Génération du résultat
echo "<?xml version='1.0' encoding='UTF-8' ?>\n";
echo "<!DOCTYPE deplacement SYSTEM 'tp.dtd'>\n";
echo "<liste-présidents>\n";



while (($p instanceOf DOMELEMENT) && ($p->tagName == 'personne')) {
    $personne = $p;
    $f = $personne->lastChild;
    $fonction = $f->getAttribute("type");
    if ($fonction == "Président de la République") {
        $nom = $personne->getAttribute("nom");
        echo "<Président nom=\"" .$nom . "\">"."\n";
        }
    $p=$p->nextSibling;
}

echo "</liste-présidents>\n";?>
