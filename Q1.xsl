<?xml version="1.0" encoding="UTF-8" ?>
<xsl:transform version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions">
  <xsl:output omit-xml-declaration="yes" encoding="UTF-8" indent="yes"/>
  <xsl:strip-space elements="*"/>

  <xsl:template match = "/">
    <liste-présidents>
      <xsl:apply-templates select="/déplacements/liste-personnes/personne[./fonction/@type = 'Président de la République']"/>
    </liste-présidents>
  </xsl:template>

  <xsl:template match="personne">
    <xsl:element name="président">
      <xsl:attribute name="nom">
        <xsl:value-of select="@nom"/>
      </xsl:attribute>
      <xsl:apply-templates select="/déplacements/liste-pays/pays[./encompassed/@continent='africa']">
        <xsl:with-param name="nom" select="./fonction/@xml:id">
        </xsl:with-param>
      </xsl:apply-templates>
    </xsl:element>
  </xsl:template>


  <xsl:template match="pays">
    <xsl:param name="nom"/>

    <xsl:element name="pays">
      <xsl:attribute name="nom">
        <xsl:value-of select="@nom"/>
      </xsl:attribute>
      <xsl:attribute name="durée">
        <xsl:choose>
          <xsl:when test="sum(/déplacements/liste-visites/visite[./@personne = $nom and ./@pays = current()/@xml:id]/fn:days-from-duration(xs:date(./@fin) - xs:date(./@debut)))+count(/déplacements/liste-visites/visite[./@personne = $nom and ./@pays = current()/@xml:id]) >0">
            <xsl:value-of select="concat('P', sum(/déplacements/liste-visites/visite[./@personne = $nom and ./@pays = current()/@xml:id]/fn:days-from-duration(xs:date(./@fin) - xs:date(./@debut)))+count(/déplacements/liste-visites/visite[./@personne = $nom and ./@pays = current()/@xml:id]),'D')"/>
          </xsl:when>
          <xsl:when test="sum(/déplacements/liste-visites/visite[./@personne = $nom and ./@pays = current()/@xml:id]/fn:days-from-duration(xs:date(./@fin) - xs:date(./@debut)))+count(/déplacements/liste-visites/visite[./@personne = $nom and ./@pays = current()/@xml:id]) = 0">
            <xsl:value-of select="sum(/déplacements/liste-visites/visite[./@personne = $nom and ./@pays = current()/@xml:id]/fn:days-from-duration(xs:date(./@fin) - xs:date(./@debut)))+count(/déplacements/liste-visites/visite[./@personne = $nom and ./@pays = current()/@xml:id])"/>
          </xsl:when>
        </xsl:choose>
      </xsl:attribute>
      <xsl:choose>
        <xsl:when test="./language[./text()='French'][./@percentage > 30]">
          <xsl:attribute name="franchophone"><xsl:text>En-partie</xsl:text></xsl:attribute>
        </xsl:when>
        <xsl:when test="./language[./text()='French'][not(./@percentage)]">
          <xsl:attribute name="franchophone"><xsl:text>Officiel</xsl:text></xsl:attribute>
        </xsl:when>
      </xsl:choose>
    </xsl:element>
  </xsl:template>


</xsl:transform>
