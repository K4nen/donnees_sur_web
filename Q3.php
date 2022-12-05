<?php
include('Sax4php.php');

class ListePresidents extends DefaultHandler {
    private $texte;

    function __construct($a) {
        parent::__construct(); $this->age = $a;
    }

    function startDocument() {
        echo "<?xml version='1.0' encoding='UTF-8' ?>\n"; //<?php
        echo "<!DOCTYPE liste-presidents SYSTEM 'liste-presidents.dtd'>\n";
        echo "<liste-presidents>\n";
    }

    function endDocument() {
        echo "</liste-presidents>\n";
    }

    function characters($txt) {
        $txt = trim($txt);
        if (!(empty($txt))) $this->texte .= $txt;
    }

    function startElement($nom, $att)
    {
        if (!empty($nom)) {
            switch ($nom) {
                case 'liste-pays' :
                    $lpays = [];
                case 'pays' :
                    //var_dump($att);
                    if (!empty($att["nomP"])) {
                        $pa = ["nomP" => $att["nom"], "id" => $att["xml:id"], "francophone" => null];
                        array_push($lpays, $pa);
                        foreach ($lpays as $p) {
                            var_dump($p);
                            //echo $p["nomP"];
                            //echo $p["nomP"]."</br>";
                            /*
                            echo $p["id"]."</br>";
                            */
                        }
                    }
                //echo $att["nom"]."</br>";
                /*
                case 'encompassed' :
                    if ($att["continent"] == "africa")
                    {
                    }
                    ;
    */
                default;

            }
        }
    }

    function endElement($nom) {
        switch($nom) {
            case 'titre' : if ($this->ok) echo " titre='$this->texte'/>\n"; break;
            default :;
        }
    }
}

try {
    $sax = new SaxParser( new ListePresidents(9) );
    $sax->parse('tp.xml');
} catch(SAXException $e){ echo "\n",$e;
} catch(Exception $e) { echo "Capture de l'exception par défaut\n", $e;
}?>

