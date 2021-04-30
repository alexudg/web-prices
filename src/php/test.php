<?php
$txt = 'pára cínco són méjor que únos Ñoño';
$txt = utf8_encode(strtr(utf8_decode($txt), utf8_decode('áéíóúÁÉÍÓÚÑ'), utf8_decode('aeiouaeiouñ')));
echo $txt;
?>