<?php header('Content-type: text/xml; Encoding: utf-8');

//Chemin vers le fichier texte
$file = "xml/SAX.xml";
//Ouverture en mode écriture
$fileopen=(fopen($file,'a'));
ftruncate($fileopen, 0);


//En-tête pour le document XLM, ouverture de la première balise "liste-présidents"
function startDocument($fileopen) {
    fwrite($fileopen,"<?xml version='1.0' encoding='UTF-8' ?>\n");
    fwrite($fileopen, "<!DOCTYPE liste-présidents SYSTEM 'res.dtd'> \n");
    fwrite($fileopen, "<liste-présidents>\n");
}


function endDocument($fileopen) {
    //Variables pour parcourir les tableaux
    global $tabPays, $tabVisites, $tabPersonnes;
    //On parcours le tableau des personnes pour afficher le nom
    foreach($tabPersonnes as $unePersonne) {
        fwrite($fileopen, "\t<président nom=\"".$unePersonne['NOMPERSONNE']."\">\n");
        //On parcours ensuite les pays
        foreach($tabPays as $unPays) {
            $duree = 0; //durée est initalisé à 0 car s'il n'y à pas de visite, durée = 0
            //On parcours ensuite les visites pour récupérer les pays visité par la personne scannée
            foreach($tabVisites as $uneVisite) {
                //On fait le lien dans le if entre un pays et une visite et une personne et une visite
                if(($uneVisite['PAYS'] == $unPays['IDPAYS']) && ($uneVisite['PERSONNE'] == $unePersonne['IDPERSONNE'])) {
                    //On calcul la durée de la visite, sachant que si la personne est aller 2 fois dans le même pays, on additionne la durée de la visite
                    if($uneVisite['DEBUT'] == $uneVisite['FIN']) {
                        $duree += 1;
                    }else{
                        $debut = strtotime($uneVisite['DEBUT']);
                        $fin = strtotime($uneVisite['FIN']);
                        $diff = $fin - $debut;
                        $duree += 1 + round($diff / 86400);
                    }
                }
            }
            //Permet l'affichage de la balise francophone, officiel si oui, vide si non francophone
            if($unPays['FRANCOPHONE'] != null) {
                $francophone = " francophone=\"".$unPays['FRANCOPHONE']."\"";
            }else{
                $francophone = "";
            }
            //affichage de la balise durée avec la durée entre le "P" et le "D"
            if($duree == 0) {
                $temps = "durée=\"0\"";
            }else{
                $temps = "durée='P".$duree."D'";
            }
            //affichage de la balise nom du pays
            fwrite($fileopen,"\t\t<pays nom=\"".$unPays['NOMPAYS']."\" $temps$francophone/>\n");
        }
        //fermeture de balise xml "présidents"
        fwrite($fileopen,"\t</président>\n");
    }
    //fermeture de la balise xml "liste-présidents"
    fwrite($fileopen,"</liste-présidents>");
}
//fonction qui permet de gérer et lire les noeuds texte
function characters($parser, $txt) {
    global $texte;
    $txt = trim($txt);
    if (!(empty($txt))) $texte = $txt;
}

function startElements($parser, $name, $attrs) {
    global $tabPays, $tabVisites, $elements, $pays, $enAfrique, $pourcentage,$francophone, $tabPersonnes, $personne;

    if(!empty($name)) {
        //Quand on rencontre le noeud 'LISTE-PAYS' on crée un tableau vide "tabPays" pour stocker les pays
        if ($name == 'LISTE-PAYS') {
            $tabPays= array();
        }
        /*
         * Quand on rencontre le noeud 'PAYS', on crée 2 variables "francophone" et "enAfrique" qu'on met à false pour l'initialisation
         * $francophone = vérifie si un pays est francophone selon les règles énoncées dans l'énoncé
         * $enAfrique = vérifie qu'un pays est dans le continent "Africa"
         * $pays = tableau qui stocke les informations pour un pays
         */
        if($name == 'PAYS') {
            $francophone = false;
            $enAfrique = false;
            $pays = ['NOMPAYS' => $attrs['NOM'], 'IDPAYS' => $attrs['XML:ID'], 'FRANCOPHONE' => null];
        }
        //Traitement de la variable $enAfrique
        if($name == 'ENCOMPASSED') {
            if($attrs['CONTINENT'] == 'africa') {
                $enAfrique = true;
            }
        }
        //Traitement pour la variable $francophone
        if($name == 'LANGUAGE' && $enAfrique) {
            if(isset($attrs['PERCENTAGE'])) {
                $pourcentage = $attrs['PERCENTAGE'];
            }else{
                $pourcentage = null;
            }
        }
        //Quand on rencontre le noeud 'LISTE-VISITES' on crée un tableau vide "tabVisites" pour stocker les visites
        if($name == 'LISTE-VISITES') {
            $tabVisites= array();
        }
        /*
         * Quand on rencontre le noeud 'VISITE', on va créer un tableau qui contient les informations pour chaque visites
         * Et on va mettre cette visite dans le tableau "tabVisites"
         */
        if($name == 'VISITE') {
            if(in_array($attrs['PAYS'], array_column($tabPays, 'IDPAYS'))) {
                array_push($tabVisites, ['DEBUT' => $attrs['DEBUT'], 'FIN' => $attrs['FIN'], 'PERSONNE' => $attrs['PERSONNE'], 'PAYS' => $attrs['PAYS']]);
            }
        }
        //Quand on rencontre le noeud 'LISTE-ERSONNES' on crée un tableau vide "tabPersonnes" pour stocker les personnes
        if($name == 'LISTE-PERSONNES') {
            $tabPersonnes = array();
        }
        /*
         * Quand on rencontre le noeud 'VISITE', on va créer un tableau qui contient les informations pour chaque visite
         * On met l'IDPERSONNE à null pour le moment car il va être rempli par l'id rencontré dans la balise fonction juste en-dessous
         */
        if($name == 'PERSONNE') {
            $personne = ['NOMPERSONNE'=> $attrs['NOM'], 'IDPERSONNE' => NULL];

        }
        /*
         * 
         */
        if($name == 'FONCTION') {
            $personne['IDPERSONNE'] = $attrs['XML:ID'];
            if($attrs['TYPE'] == 'Président de la République') {
                array_push($tabPersonnes, $personne);
            }
        }
        $elements = $name;
    }
}

function endElements($parser, $name) {
    global $texte, $tabPays, $enAfrique, $pays, $pourcentage, $francophone;

    if(!empty($name)) {
        if($name == 'PAYS') {
            if(!$francophone && $enAfrique) {
                array_push($tabPays, $pays);
            }
        }
        if($name == 'LANGUAGE') {
            if ($enAfrique) {
                if($texte == 'French') {
                    $francophone = true;
                    if($pourcentage == null) {
                        $pays['FRANCOPHONE'] = "Officiel";
                    }else if($pourcentage > 30) {
                        $pays['FRANCOPHONE'] = "En partie";
                    }
                    array_push($tabPays, $pays);
                }
            }
        }
    }
}


$parser = xml_parser_create();

xml_set_element_handler($parser, "startElements", "endElements");
xml_set_character_data_handler($parser, "characters");

if (!($handle = fopen('tp.xml', "r"))) {
    die("could not open XML input");
}
startDocument($fileopen);
while($data = fread($handle, 4096)) {
    xml_parse($parser, $data);
}
endDocument($fileopen);
xml_parser_free($parser); // deletes the parser
fclose($fileopen);
?>