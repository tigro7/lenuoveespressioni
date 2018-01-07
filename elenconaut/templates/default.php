<?php

function makeSize($size) {
  $units = array('B','KB','MB','GB','TB');
  $u = 0;
  while ( (round($size / 1024) > 0) && ($u++ < 4) )
    $size = $size / 1024;
  return (round($size,2) . " " . $units[$u]);
}

function makeTime($time) {
  return date('d-M-Y h:i', $time);
}

?><!doctype html>
<html><head>
 <meta charset="utf-8" />
 <title>Indice <?php echo $path ? "di {$path}" : '' ?></title>

 <style type="text/css">
  * { margin: 0; padding: 0; }
  a, a:visited { text-decoration: none; color: blue; }
  html  { text-align: center; }
  body  { padding: 2em 2em 1em 2em; margin: auto; width: 44em; text-align: left; background: white; }
  h1    { padding-bottom: 1em;}
  table { width: 100%; }
  thead td  { border-bottom: 1px solid #d0d0d0; }
  p#summary,p#about { margin: 1em; text-align: right; }
 </style>

</head><body>

 <h1>Indice di <?php echo Elenconaut::breadcrumbs() ?></h1>

 <table><thead>
  <tr>
   <td>Tipo</td>
   <td>Nome</td>
   <td>Ultima modifica</td>
   <td>Dimensione</td>
  </tr>

 </thead><tbody>

 <?php foreach ($files as $info): extract($info); ?>
  <tr>
   <td><img src="<?php echo $icons . $type ?>.png" alt="<?php echo $type ?>" title="<?php echo $type ?>"></td>
   <td><a href="<?php echo $link ?>"><?php echo $name ?></a></td>
   <td><?php echo makeTime($time) ?></td>
   <td><?php echo ($type != 'directory') ? makeSize($size) : '' ?></td>
  </tr>
 <?php endforeach; ?>

 </tbody></table>

 <?php extract($totals); ?>
 <p id="summary"><b>Totale</b>: <?php echo makeSize($bytes); ?> (<?php echo $directories ?> directory e <?php echo $files ?> file)</p>

 <p id="about">Generato con <a href="http://dreadnaut.altervista.org/progetti/elenconaut/">Elenconaut <?php echo ELENCONAUT_VERSION ?></a></p>
</body></html>

