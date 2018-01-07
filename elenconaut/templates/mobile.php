<?php

function makeSize($size) {
  $units = array(' bytes','KB','MB','GB','TB');
  $u = 0;
  while ( (round($size / 1024) > 0) && ($u++ < 4) )
    $size = $size / 1024;
  return (round($size,2) . $units[$u]);
}

$breadcrumbs = Elenconaut::breadcrumbs();
$fullpath = str_replace(' ', '', strip_tags($breadcrumbs));

?><!doctype html>
<html><head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
 <meta description="Index of <?php echo $fullpath ?>" />

 <title><?php echo $fullpath ?></title>
 <style type="text/css">

  *    { margin: 0; padding: 0; }
  html { -webkit-text-size-adjust: none; } 
  body { font: 16px sans-serif; color: #333; background: white; line-height: 3em; }

  a { text-decoration: none; color: #06c; }
  a:visited { color: #04a; }
  a:hover, a:focus { text-decoration: underline; }

  h1   { text-align: right; font-size: 1em; line-height: 2em; margin: 0.5em;} 
  h1 a { padding: 1em 0; }

  ul   { list-style-type: none; border: solid #ccc; border-width: 1px 0; }
  li a { display: block; }
  img  { vertical-align: middle; margin-top: -3px; padding: 0 0.2em; }
  li:nth-child(odd) { background: #eee; }

  #summary, #about { text-align: right; padding-right: 0.5em; }
  #about { font-size: 80%; color: #444; padding-right: 0.625em; }

  @media only screen and (min-width: 640px) {
    body { width: 80%; margin: auto; line-height: 2em; }
    img { padding: 0 0.5em; }
  }

 </style>

</head><body>

 <h1><?php echo Elenconaut::breadcrumbs() ?></h1>

 <ul>
 <?php foreach ($files as $info): extract($info); ?>
  <li><a href="<?php echo $link ?>"><img src="<?php echo $icons . $type ?>.png" title="<?php echo $type ?>"><?php echo $name ?></a></li>
 <?php endforeach; ?>
 </ul>

 <?php extract($totals); ?>
 <p id="summary">
   <?php echo makeSize($bytes); ?>
 ( <?php echo $files ?> file<?php echo $files === 1 ? '':'s'?>,
   <?php echo $directories ?> director<?php echo $directories === 1? 'y' : 'ies' ?> )</p>

 <p id="about">Generated with <a href="http://dreadnaut.altervista.org/progetti/elenconaut/">Elenconaut <?php echo ELENCONAUT_VERSION ?></a></p>

 <script type="text/javascript">
   /mobile/i.test(navigator.userAgent) && window.addEventListener("load", function() { setTimeout(function() { window.scrollTo(0, 1); }, 300); });
 </script>

</body></html>

