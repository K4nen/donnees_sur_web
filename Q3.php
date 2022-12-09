<?php header('Content-type: text/xml; Encoding: utf-8');

function startDocument() {
    echo"<?xml version='1.0' encoding='UTF-8' ?>\n";
    echo "<!DOCTYPE liste-présidents SYSTEM 'tp.dtd'> \n";
    echo "<liste-presidents>\n";
}


function endDocument() {
    global $tabPays, $tabVisites, $tabPersonnes;
    foreach($tabPersonnes as $unePersonne) {
        echo "\t<président nom=\"".$unePersonne['NOMPERSONNE']."\">\n";
        foreach($tabPays as $unPays) {
            $duree = 0;
            foreach($tabVisites as $uneVisite) {
                if(($uneVisite['PAYS'] == $unPays['IDPAYS']) && ($uneVisite['PERSONNE'] == $unePersonne['IDPERSONNE'])) {
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
            if($unPays['FRANCOPHONE'] != null) {
                $francophone = " francophone=\"".$unPays['FRANCOPHONE']."\"";
            }else{
                $francophone = "";
            }
            if($duree == 0) {
                $temps = "duree=\"0\"";
            }else{
                $temps = "duree='P".$duree."D'";
            }
            echo "\t\t<pays nom=\"".$unPays['NOMPAYS']."\" $temps$francophone/>\n";
        }
        echo "\t</président>\n";
    }
    echo "</liste-presidents>";
}

function characters($parser, $txt) {
    global $texte;
    $txt = trim($txt);
    if (!(empty($txt))) $texte = $txt;
}

function startElements($parser, $name, $attrs) {
    global $tabPays, $tabVisites, $elements, $pays, $enAfrique, $pourcentage,$francophone, $tabPersonnes, $personne;

    if(!empty($name)) {
        if ($name == 'LISTE-PAYS') {
            $tabPays= array();
        }
        if($name == 'PAYS') {
            $francophone = false;
            $enAfrique = false;
            $pays = ['NOMPAYS' => $attrs['NOM'], 'IDPAYS' => $attrs['XML:ID'], 'FRANCOPHONE' => null];
        }
        if($name == 'ENCOMPASSED') {
            if($attrs['CONTINENT'] == 'africa') {
                $enAfrique = true;
            }
        }
        if($name == 'LANGUAGE' && $enAfrique) {
            if(isset($attrs['PERCENTAGE'])) {
                $pourcentage = $attrs['PERCENTAGE'];
            }else{
                $pourcentage = null;
            }
        }
        if($name == 'LISTE-VISITES') {
            $tabVisites= array();
        }
        if($name == 'VISITE') {
            if(in_array($attrs['PAYS'], array_column($tabPays, 'IDPAYS'))) {
                array_push($tabVisites, ['DEBUT' => $attrs['DEBUT'], 'FIN' => $attrs['FIN'], 'PERSONNE' => $attrs['PERSONNE'], 'PAYS' => $attrs['PAYS']]);
            }
        }
        if($name == 'LISTE-PERSONNES') {
            $tabPersonnes = array();
        }
        if($name == 'PERSONNE') {
            $personne = ['NOMPERSONNE'=> $attrs['NOM'], 'IDPERSONNE' => NULL];

        }
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
startDocument();
while($data = fread($handle, 4096)) {
    xml_parse($parser, $data);
}
endDocument();
xml_parser_free($parser); // deletes the parser

?>