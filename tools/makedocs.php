#!/usr/bin/php
<?php
error_reporting(E_ALL);

$PHPDOC_PATH = "/usr/bin/phpdoc";
$INPUT_PATH  = "~/eqdkp/inputsec/upload/";
$OUTPUT_PATH = "~/public_html/api/";

$TITLE    = "EQdkp API";
$PACKAGE  = "eqdkp";
$TEMPLATE = "HTML:frames:earthli";
$IGNORE   = "*templates/*";

shell_exec("${PHPDOC_PATH} -i ${IGNORE} -q on -d ${INPUT_PATH} -t ${OUTPUT_PATH} -ti \"${TITLE}\" -dn ${PACKAGE}-o ${TEMPLATE} -pp off");
?>