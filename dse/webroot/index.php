<?php
$DSE_ROOT="/dse";
include_once ("$DSE_ROOT/include/web_config.php");
dse_print_page_header();

print "<br><center><b class='f10pt'>Welcome to Devity Server Environment's Web Interface!</b></center><br><br>";


print "<br><b class='f10pt'>Sections:</b><br>";

print " * <a href=/code_explorer/>Code Explorer</a><br>";



print "<br><hr>";

print text2html(`dse -s`);

dse_print_page_footer();
?>
