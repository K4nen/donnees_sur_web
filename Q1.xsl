<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema">
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
      <xsl:apply-templates select="/déplacements/liste-pays/pays[./encompassed/@continent='africa']"/>
    </xsl:element>
  </xsl:template>



  <xsl:template match="pays">
    <xsl:element name="pays">
      <xsl:attribute name="nom">
        <xsl:value-of select="@nom"/>
      </xsl:attribute>
      <xsl:attribute name="durée">
        <xsl:apply-templates select="/déplacements/liste-visites/visite[./@pays = current()/fonction/@xml:id]"/>
      </xsl:attribute>
      <xsl:choose>
        <xsl:when test="./language[./text()='French'][./@percentage > 30]">
          <xsl:attribute name="francophone"><xsl:text>En partie</xsl:text></xsl:attribute>
        </xsl:when>
        <xsl:when test="./language[./text()='French'][not(./@percentage)]">
          <xsl:attribute name="francophone"><xsl:text>Officielle</xsl:text></xsl:attribute>
        </xsl:when>
      </xsl:choose>
    </xsl:element>
  </xsl:template>




</xsl:stylesheet>