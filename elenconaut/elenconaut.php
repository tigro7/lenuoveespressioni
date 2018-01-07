<?php
# Elenconaut è progettato per essere *buttato li* e funzionare.
# Potete ignorare felicemente i contenuti di questo file, e se
# volete limitarvi al contenuto del file di configurazione.

# Elenconaut è una classe statica ed auto-contenuta, che elenca i file
# presenti in una directory, applicando loro una serie di regole per
# assegnare tipi o nascondere file.
# Una volta creato l'elenco, è possibile visualizzarlo con uno dei
# template predefiniti, o accedere ai singoli elementi manualmente

define ('ELENCONAUT_VERSION', '0.3');

// -- Inizializzazione e classe --

Elenconaut::init();

class Elenconaut {

  // $root è la directory da cui EN comincia a visualizzare file
  // E' possibile scendere in sottodirectory, ma non risalire oltre
  // la directory $root; il default è la directory corrente
  private static $root   = './';

  // $path è la sottodirectory di $root da visualizzare
  private static $path   = '';

  // $here è la directory in cui si trovano gli altri file di EN
  // (icone, template, configurazione)
  // La directory di default è elenconaut/, ma viene inizializzata
  // automaticamente chiamando ::init()
  private static $here   = 'elenconaut/';

  // $files è l'array che contiente l'elenco dei file, una volta
  // riempita da ::listFiles()
  public static $files  = array();

  // $totals mantiene i totali relativi all'elenco dei file
  public static $totals = array(
       'directories' => 0,
       'files'       => 0,
       'bytes'       => 0,
      );

  // array di configurazione, per i singoli valori vedere default.ini
  public static $config = array(

   'options' => array(
     'directories-show'     => true,
     'directories-first'    => true,
     'directories-up'       => false,
     'sort-by'              => false,
     'sort-reverse'         => false,
     'template'             => 'default'
   ),

   'paths' => array(
     'files'                => '',
     'url'                  => '',
     'icons'                => 'elenconaut/icons/',
     'get-param'            => 'path',
   ),

   'files' => array(
     'hidden'               => array(),
   ),

   'directories' => array(
     'directory'            => '*',
   ),

  );

  public static function init()
  {
    // inizializzazione class: directory e valori di default

    // directory in cui si trova Elenconaut
    self::$here = dirname(__FILE__) . '/';

    // carica la configurazione esterna di default
    self::config(self::$here . 'default.ini');

    // ed ne estrae la directory radice per l'elenco
    self::$root = self::$config['paths']['files'];
  }

  public static function auto($pattern = '*')
  {
    // l'unica funzione da chiamare, per visualizzare l'elenco seguendo
    // la configurazione caricata (default o esterna)

    // assegna il path, se è stato passato come parametro GET
    $param = @self::$config['paths']['get-param'];
    if (isset($_GET[$param]))
      self::path($_GET[$param], true);

    // carica i file per il percorso determinato
    $files = self::listFiles($pattern);

    // se necessario, aggiunge il link alla directory precedente
    if (self::$config['options']['directory-up'])
      self::addUpOneLevel('&#8624; indietro');

    // mostra l'elenco a schermo
    return self::renderTemplate();
  }

  public static function rootDir($root = null)
  {
    // seleziona una diversa directory radice
    // senza parametri, restituisce quella corrente

    if ($root === null)
      return self::$root;

    self::$root = $root;
  }

  public static function rootURL($url = null)
  {
    // seleziona un nuovo URL radice per i link a sottodirectory
    // senza parametri, restituisce quello corrente

    if ($url === null)
      return self::$config['paths']['url'];

    self::$config['paths']['url'] = $url;
  }

  public static function path($path = null, $redirectOnError = false)
  {
    // seleziona la sottodirectory da visualizzare, o restituisce
    // quella corrente.
    // Se $redirectOnError è true, oppure l'URL di una pagina, il path
    // deve esistere sul server; altrimenti ElencoNaut risalirà
    // automaticamente ad una directory superiore, o redirezionerà
    // alla pagina indicata.

    if ($path === null)
      return self::$path;

    $valid = self::verifyPath($path, $redirectOnError);
    
    if ($valid !== false)
      self::$path = $valid;

    return $valid;
  }

  public static function config($filename)
  {
    // carica un file di configurazione con formato .ini

    if (is_readable($filename))
      // quando aggiorneranno il php, basterà la riga sotto, invece
      // dell'intero blocco che segue.
      // $lines = @file($filename) ?: array();
      {
        $lines = @file($filename);
        if (!$lines)
          $lines = array();
      }
    else
      $lines = explode(PHP_EOL, $filename);

    $lines = array_map('trim', $lines);

    $section =& self::$config['options'];

    foreach ($lines as $line)
    {
      // linea vuota o commento, salta
      if (!$line or $line{0} == ';')
        continue;

      // nuova sezione del file .ini, diventa quella corrente
      if (preg_match('#^\[(?P<section>[a-z]+)\]\s*(?P<more>.*)$#', $line, $m))
      {
        if (!isset(self::$config[$m['section']]))
          self::$config[$m['section']] = array();

        $section =& self::$config[$m['section']];

        if ($m['more'])
          $line = $m['more'];
      }

      // assegnamento chiave => valore
      if (preg_match('#^(?P<key>[a-z-]+)(?P<set>\s*=\s*(?P<value>.*))?#', $line, $m))
      {
        if (isset($m['set']))
        {
          if (isset($m['value']) and $m['value'])
          {
            $value = preg_replace('#\\\\\|#', '|', preg_split('#(?<!\\\)\s*\|\s*#', $m['value']) );
            if (count($value) == 1)
              $value = $value[0];

            if (is_numeric($value))
              $value = intval($value);
            elseif ($value == 'false')
              $value = '';
            elseif ($value == 'true')
              $value = 1;
          }
          else
            $value = '';
        }
        else
          $value = 1;

        $section[$m['key']] = $value;
      }
    }
  }

  public static function listFiles($pattern = '*')
  {
    // inizializza gli elenchi di file e directory
    $files = array();
    $dirs  = array();

    // valori utili
    $len   = strlen(self::$root);
    $param = '?' . self::$config['paths']['get-param'] . '=';

    // mettiamo in un array i pattern per i file nascosti
    $hidden = self::$config['files']['hidden'];

    if (!is_array($hidden))
      if ($hidden === false)
        $hidden = array();
      else
        $hidden = array( $hidden );

    // glob della directory richiesta, elenca i file
    $list = glob(self::$root . self::$path . $pattern);

    foreach($list as $file) {

      // informazioni base per ogni elemento
      $info = array(
       'name' => basename($file),
       'size' => filesize($file),
       'time' => filemtime($file),
       'type' => ''
      );

      // controlla che il file sia invisibile
      foreach($hidden as $pattern)
        if ($pattern{0} == '^')
        {
          if ($file === substr($pattern, 1))
            continue 2;
        }
        elseif (fnmatch($pattern, $info['name']))
          continue 2;

      // se il file è una directory, va gestito diversamente
      if (@filetype($file) == 'dir') {

        // prepara il link per l'elenco della sottodirectory
        $info['link'] = $param . substr($file, $len);
        $info['type'] = self::verifyFileType($info['name'], 'directories');

        // a seconda delle opzioni, la dir viene messa assieme ai file,
        // oppure in un array a parte, che viene poi unito alla fine.
        if (@self::$config['options']['directories-show']) {
          if (@self::$config['options']['directories-first'])
            $dirs[] = $info;
          else
            $files[] = $info;

          // incrementa cumulativi
          self::$totals['directories'] += 1;
        }

      } else {

        // informazioni per i normali file
        // $info['link'] = (self::$config['paths']['url'] ?: '') . substr($file, $len); // quando aggiornano
        $info['link'] = (isset(self::$config['paths']['url']) ? self::$config['paths']['url'] : '') . substr($file, $len);
        $info['type'] = self::verifyFileType($info['name'], 'files');

        // accoda all'array dei file
        $files[] = $info;

        // incrementa cumulativi
        self::$totals['files'] += 1;
        self::$totals['bytes'] += $info['size'];
      }

    }

    // riordina file, se necessario
    if (self::$config['options']['sort-by'])
      aasort($files, self::$config['options']['sort-by']);

    // inverte l'ordine
    if (self::$config['options']['sort-reverse'])
      $files = array_reverse($files);

    // aggiunge le directory, se vanno messe per prime
    if (self::$config['options']['directories-first'])
      $files = array_merge($dirs, $files);

    // prepara l'array da restituire, salva internamente
    self::$files = array_merge(self::$files, $files);

    return self::$totals['files'] + self::$totals['directories'];
  }

  public static function renderTemplate($template = null)
  {
    // se non è stato scelto un template, usa quello configurato
    // internamente
    if ($template === null)
      $template = self::$config['options']['template'];

    // prepara le variabili per il template
    extract(array(
     'root'  => self::$root,
     'path'  => self::$path,
     'icons' => self::$config['paths']['icons'],
    ));

    // prepara le informazioni sui file per i template
    extract( array(
     'files'  => self::$files,
     'totals' => self::$totals,
    ) );

    // richiama il template
    require(self::$here . 'templates/' . $template . '.php');
  }

  public static function addUpOneLevel($text = '&#8624; up one level')
  {
    // se non siamo nella radice, aggiunge la directory superiore
    if (self::$path)
    {
      if (count(explode('/', trim(self::$path, '/'))) == 1)
        $target = '.';
      else
        $target = '?' . self::$config['paths']['get-param'] . '=' . substr(self::$path, 0, strrpos(self::$path, '/', -2));

      // aggiunge un nuovo elemento all'inizio dell'elenco dei file
      array_unshift(self::$files, array(
       'link' => $target,
       'name' => $text,
       'type' => 'directory',
       'size' => filesize(self::$root . self::$path . '..'),
       'time' => filemtime(self::$root . self::$path . '..'),
      ) );

      return true;
    }

    return false;
  }

  public static function breadcrumbs($path = null)
  {
    // crea un elenco di link corrispondenti alle directory fra la radice
    // e la directory corrente, di cui viene visualizzato l'elenco.
    if ($path === null)
      $path = self::$path;
    
    // url della pagina, rimossi i parametri $_GET
    $root = $_SERVER['SERVER_NAME'] . '/' . $_SERVER['REQUEST_URI'];
    $root = preg_replace('#\?.*$#', '', $root);
    $root = basename(trim($root));

    // se siamo nella radice, non c'è nulla da elencare
    if (!$path)
      return $root;
    
    $root = '<a href=".">' . $root . '</a>';

    // un link per ogni directory...
    $dirs = explode('/', trim($path, '/'));
    // ...eccetto quella corrente, l'ultima nell'array
    $last = array_pop($dirs);

    $param = self::$config['paths']['get-param'];
    $sum = '';
    foreach ($dirs as &$dir)
    {
      $link = "<a href=\"?{$param}={$sum}{$dir}\">{$dir}</a>";
      $sum .= $dir . '/';
      $dir  = $link;
    }

    // reinseriamo la directory corrente
    $dirs[] = $last;

    // mettiamo assieme tutti i pezzi
    return '<a href=".">' . $root . '</a> / ' . implode(' / ', $dirs) . ' /';
  }

  private static function verifyPath($path = null, $redirectOnError = true)
  {
    // verifica l'esistenza di una sottodirectory, eventualmente
    // redireziona ad una diversa pagina

    $path = preg_replace('#\.\./#', '', $path);

    if ($path)
      $path = trim($path, '/') . '/';

    if (!$path or is_dir(self::$root . $path))
    {
      self::$path = $path;
      return $path;
    }

    // se non è stato specificato un URL, ma solo true, redirezioniamo
    // alla pagina corrente, senza parametri (cioè la directory radice)
    if ($redirectOnError === true)
      $redirectOnError = '.';

    if ($redirectOnError)
    {
      header('Location: ' . $redirectOnError);
      die();
    }
    
    return false;
  }

  private static function verifyFileType($filename, $type = 'files')
  {
    // confronta il nome di un file con i tipi configurati,
    // restituisce il primo il cui pattern coincide.

    foreach(self::$config[$type] as $type => $patterns)
    {
      // se c'è solo un pattern, mettiamo in un array comunque che è più facile
      if (!is_array($patterns))
        if ($patterns === false)
          $patterns = array();
        else
          $patterns = array( $patterns );

      // match con i pattern nell'array
      foreach ($patterns as $p)
        if (fnmatch($p, $filename))
          return $type;
    }

    return 'unknown';
  }

}


// -- funzioni di supporto --

// defininisce fnmatch(), se non esite (sotto windows, o in php < 4.3.0)
// vedi documentazione: http://pnp.net/fnmatch
if (!function_exists('fnmatch')) {
  function fnmatch($pattern, $string) {
    return @preg_match(
      '/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'),
        array('*' => '.*', '?' => '.?')) . '$/i', $string
    );
  }
}

// definisce aasort(), se non esiste; aasort ordina un array di array
// associativi in base ad uno dei campi dei sotto-array
if (!function_exists('aasort')) {
  function aasort(&$mixed, $key) {
    uasort ( $mixed,  create_function('$a,$b', "return @strcmp(\$a['$key'], \$b['$key']);") );
  }
}

// - eof -
