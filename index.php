<?php
// error_reporting(E_ALL | E_STRICT); // mostra tutti gli errori

require_once('elenconaut/elenconaut.php');

Elenconaut::config('[options] template = mobile');

Elenconaut::auto();

