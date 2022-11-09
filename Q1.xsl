<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
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
    </xsl:element>
  </xsl:template>

</xsl:stylesheet>