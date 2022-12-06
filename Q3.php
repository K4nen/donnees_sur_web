<?php
function startElements($parser, $name, $attrs) {
    global $listePays, $listeVisites, $elements, $pays, $nomPays, $isAfrica, $pourcentage,$isFrench, $isPresident, $listePersonnes, $personne;

    if(!empty($name)) {
        if ($name == 'LISTE-PAYS') {
            $listePays []= array();
        }
        if($name == 'PAYS') {
            $isFrench = false;
            $isAfrica = false;
            $pays = ['NOM' => $attrs['NOM'], 'ID' => $attrs['XML:ID'], 'FRANCOPHONE' => null];
        }
        if($name == 'ENCOMPASSED') {
            if($attrs['CONTINENT'] == 'africa') {
                $isAfrica = true;
            }
        }
        if($name == 'LANGUAGE' && $isAfrica) {
            if(isset($attrs['PERCENTAGE'])) {
                $pourcentage = $attrs['PERCENTAGE'];
            }else{
                $pourcentage = null;
            }
        }
        if($name == 'LISTE-VISITES') {
            $listeVisites []= array();
        }
        if($name == 'VISITE') {
            if(in_array($attrs['PAYS'], array_column($listePays, 'NOM'))) {
                array_push($listeVisites, ['DEBUT' => $attrs['DEBUT'], 'FIN' => $attrs['FIN'], 'PERSONNE' => $attrs['PERSONNE'], 'PAYS' => $attrs['PAYS']]);
            }
        }
        if($name == 'LISTE-PERSONNES') {
            $listePersonnes []= array();
        }
        if($name == 'PERSONNE') {
            $isPresident = false;
            $personne = ['NOMP'=> $attrs['NOM'], 'IDP' => $attrs['XML:ID']];

        }
        if($name == 'FONCTION') {
            if($attrs['TYPE'] == 'Président de la République') {
                $isPresident = true;
            }
        }

        $elements = $name;
    }
}

function characterData($parser, $data) {
    global $listePays, $listeVisites, $elements, $pays, $isAfrica, $pays, $pourcentage, $isFrench, $isPresident, $listePersonnes, $personne;

    if(!empty($data)) {
        if ($elements == 'LANGUAGE' && $isAfrica) {
            if($data == 'French') {
                $isFrench = true;
                if($pourcentage == null) {
                    $pays['FRANCOPHONE'] = "Officiel";
                }else if($pourcentage > 30) {
                    $pays['FRANCOPHONE'] = "En partie";
                }
                array_push($listePays, $pays);
            }
        }
    }
}


function endElements($parser, $name) {
    global $listePays, $elements, $pays, $isAfrica, $pays, $pourcentage, $isFrench, $isPresident, $listePersonnes, $personne;

    if(!empty($name)) {
        if($name == 'PAYS') {
            if(!$isFrench && $isAfrica) {
                array_push($listePays, $pays);
            }
        }
        if($name == 'FONCTION') {
            if($isPresident) {
                array_push($listePersonnes,$personne);
            }
        }
    }
}


$parser = xml_parser_create();

xml_set_element_handler($parser, "startElements", "endElements");
xml_set_character_data_handler($parser, "characterData");

if (!($handle = fopen('tp.xml', "r"))) {
    die("could not open XML input");
}

while($data = fread($handle, 4096)) {
    xml_parse($parser, $data);
}

foreach($listePays as $tPays) {
    //var_dump($tPays);
    echo $tPays['NOM'];
    echo "<br/>";
}
foreach($listeVisites as $tVisite) {
    //var_dump($tVisite);
    echo $tVisite['PERSONNE'];
    echo $tVisite['PAYS'];
    echo "<br/>";
}

foreach($listePersonnes as $tPersonne) {
    var_dump($tPersonne);
    echo "<br/>";
}

xml_parser_free($parser); // deletes the parser


?>