<?php header('Content-type: text/xml; Encoding: utf-8');
ini_set("memory_limit", -1);
//Chemin vers le fichier texte
$file = "finQ2.xml";
//Ouverture en mode écriture
$fileopen=(fopen($file,'a'));
ftruncate($fileopen, 0);

$doc = new DOMDocument();
$doc->preserveWhiteSpace = false;
$doc->validateOnParse  = true;
$doc->load("tp.xml");
$xpath = new DOMXPath($doc);
foreach ($xpath->query('//comment()') as $comment) {
    $comment->parentNode->removeChild($comment);
}


$p = $doc->documentElement->lastChild->firstChild;
$pa = $doc->documentElement->firstChild->firstChild;

//Génération du résultat
fwrite($fileopen, "<?xml version='1.0' encoding='UTF-8' ?>\n");
fwrite($fileopen, "<!DOCTYPE deplacement SYSTEM 'tp.dtd'>\n");
fwrite($fileopen, "<liste-présidents>\n");

$president = array();
$listpays = array();

while (($p instanceOf DOMELEMENT) && ($p->tagName == 'personne')) {
    $personne = $p;
    $f = $personne->firstChild;
    $fonction = $f->getAttribute("type");
    if ($fonction == "Président de la République") {
        $nom = $personne->getAttribute("nom");
        //Ecriture dans le fichier texte
        /*


        $res = affichepays($pa);
        foreach($res as $c){
            fwrite($fileopen, $c."\n");
        }

        */
        array_push($president,$nom);
    }
    $p=$p->nextSibling;
}
while (($pa instanceOf DOMELEMENT) && ($pa->tagName == 'pays')) {
    $nomP="";
    $pays = $pa;
    $c = $pays->firstChild;
    if (!is_null($c)) {
        $continent = $c->getAttribute("continent");
    }
    if($continent == 'africa'){
        $nomP .=$pays->getAttribute('nom');
        array_push($listpays,$nomP);
        var_dump($listpays);

    }
    $pa=$pa->nextSibling;

}
foreach($president as $pre){
    fwrite($fileopen, "<president nom=\"" .$pre . "\">"."\n");
    foreach($listpays as $lp){
        fwrite($fileopen,"   <pays nom=\"".$lp."\" durée=\"0\"/>"."\n");
    }
    fwrite($fileopen, "</president>"."\n");
}


fwrite($fileopen, "</liste-présidents>\n");
//On ferme le fichier
fclose($fileopen);
?>
