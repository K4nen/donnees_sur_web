xquery version "1.0" encoding "utf-8";

declare option saxon:output 
"doctype-system=res.dtd";

declare function local:est-president() as element(président)*{ 
for $fonction in fn:doc("tp.xml")/déplacements/liste-personnes/personne/fonction
where some $estPresident in $fonction/@type satisfies $estPresident eq "Président de la République"
return <président nom="{$fonction/../@nom}">{local:est-afrique($fonction/@xml:id)}</président>
};

declare function local:est-afrique($idPresident as xs:string) as element(pays)*{ 
for $pays in fn:doc("tp.xml")/déplacements/liste-pays/pays
where some $estAfrique in $pays/encompassed/@continent satisfies $estAfrique eq "africa"
return local:visite($pays,$idPresident)
};

declare function local:visite($pays as element(pays), $idPresident as xs:string) as element(pays)*{
local:est-francophone($pays, sum(fn:doc("tp.xml")/déplacements/liste-visites/visite[./@personne = $idPresident and ./@pays = $pays/@xml:id]/fn:days-from-duration(xs:date(./@fin) - xs:date(./@debut)))+count(fn:doc("tp.xml")/déplacements/liste-visites/visite[./@personne = $idPresident and ./@pays = $pays/@xml:id])
)
};

declare function local:est-francophone($pays as element(pays), $duree) as element(pays)*{
if ($pays/language[./text() eq 'French'])
    then if ($pays/language[./text() eq 'French'][./@percentage])
         then if (xs:float($pays/language[./text() eq 'French']/@percentage) gt 30)
              then if ($duree eq 0)
                   then  <pays nom='{$pays/@nom}' franchophone='En-partie' durée='{$duree}'/>
                   else <pays nom='{$pays/@nom}' franchophone='En-partie' durée='P{$duree}D'/>
              else if ($duree eq 0)
                    then <pays nom='{$pays/@nom}' durée='{$duree}'/>
                    else <pays nom='{$pays/@nom}' durée='P{$duree}D'/>
         else if($duree eq 0)
                then <pays nom='{$pays/@nom}' franchophone='Officiel' durée='{$duree}'/>
                else <pays nom='{$pays/@nom}' franchophone='Officiel' durée='P{$duree}D'/>
    else    if ($duree eq 0)
            then <pays nom='{$pays/@nom}' durée='{$duree}'/>
            else <pays nom='{$pays/@nom}' durée='P{$duree}D'/>
};

<liste-présidents> 
{
local:est-president()
}
</liste-présidents>

