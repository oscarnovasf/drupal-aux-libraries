<?php

namespace Drupal\module_template\lib\general;

// phpcs:ignore
use SimpleXMLElement;

/**
 * Funciones para gestionar archivos y directorios.
 */
class FileFunctions {

  const EXCLUDE_LIST = [
    ".",
    "..",
    ".htaccess",
  ];

  /**
   * Elimina todos los archivos anteriores a la fecha dada (en timestamp).
   *
   * @param string $realPath
   *   Ruta completa al directorio donde deseamos buscar.
   *   ( Ej. DRUPAL_ROOT . '/sites/default/private_files'; )
   * @param int $timeStamp
   *   Fecha límite hasta la que se borran los archivos.
   *   ( Ej. strtotime('-6 hour', time()); )
   *
   * @return int
   *   Número de archivos que han sido borrados.
   */
  public static function deleteFilesPreviousTo(string $realPath, int $timeStamp) {

    $delete_files = 0;

    $dir_names = self::getNamesOriginals($realPath);

    if (isset($dir_names[0])) {
      foreach ($dir_names as $i => $resto) {
        $file_name = $dir_names[$i];
        $datetime = filemtime($realPath . '/' . $file_name);

        if ($datetime <= $timeStamp) {
          if (unlink($realPath . '/' . $file_name)) {
            $delete_files++;
          }
        }
      }
    }

    return $delete_files;
  }

  /**
   * Devuelve el contenido de un directorio (ordenado por fecha).
   *
   * @param string $realPath
   *   Ruta completa al directorio donde deseamos buscar.
   *   ( Ej. DRUPAL_ROOT . '/sites/default/private_files'; )
   *
   * @return array
   *   Array con los nombres de los archivos y directorios.
   */
  public static function getDirContentOrderByDateAsc(string $realPath) {
    /* Variables auxiliares */
    $files_return = [];
    $array_filenames_dates = NULL;

    $dir_names = self::getNamesOriginals($realPath);

    if (isset($dir_names[0])) {
      foreach ($dir_names as $i => $resto) {
        $file_name = $dir_names[$i];
        $datetime = filemtime($realPath . '/' . $file_name);
        $array_filenames_dates[$i]['filename'] = $file_name;
        $array_filenames_dates[$i]['datetime'] = $datetime;
      }

      if (is_array($array_filenames_dates)) {
        usort($array_filenames_dates, 'self::dateCompare');

        foreach ($array_filenames_dates as $i => $resto) {
          $files_return[$i] = $array_filenames_dates[$i]['filename'];
        }
      }
    }

    return $files_return;
  }

  /**
   * Devuelve los archivos de un directorio (ordenado por fecha).
   *
   * Esta función excluye los directorios, únicamente listará los archivos
   * de la ruta indicada.
   *
   * @param string $realPath
   *   Ruta completa al directorio donde deseamos buscar.
   *   ( Ej. DRUPAL_ROOT . '/sites/default/privatefiles'; )
   *
   * @return array
   *   Array con los nombres de los archivos.
   */
  public static function getDirFilesOrderByDateAsc(string $realPath) {
    /* Variables auxiliares */
    $files_return = [];
    $array_filenames_dates = NULL;

    $dir_names = self::getNamesOriginals($realPath);

    if (isset($dir_names[0])) {
      foreach ($dir_names as $i => $resto) {
        $file_name = $dir_names[$i];
        if (!is_dir($realPath . '/' . $file_name)) {
          $datetime = filemtime($realPath . '/' . $file_name);
          $array_filenames_dates[$i]['filename'] = $file_name;
          $array_filenames_dates[$i]['datetime'] = $datetime;
        }
      }

      if (is_array($array_filenames_dates)) {
        usort($array_filenames_dates, 'self::dateCompare');

        foreach ($array_filenames_dates as $i => $resto) {
          $files_return[$i] = $array_filenames_dates[$i]['filename'];
        }
      }

    }

    return $files_return;
  }

  /**
   * Obtiene el contenido de un fichero.
   *
   * @param string $realPath
   *   Ruta completa al archivo.
   * @param array $allowedExt
   *   Array que contiene todas las extensiones permitidas.
   *
   * @return Resource|bool
   *   Devuelve el archivo si lo encuentra.
   *   False en caso de no poder leer el archivo.
   */
  public static function readFile(string $realPath, array $allowedExt) {

    $partes_ruta = pathinfo($realPath);

    $extension = $partes_ruta['extension'];

    if (in_array($extension, $allowedExt)) {
      return fopen($realPath, 'r');
    }
    else {
      return FALSE;
    }
  }

  /**
   * Mueve un archivo de un directorio a otro.
   *
   * @param string $origen
   *   Ruta completa al archivo origen.
   * @param string $destino
   *   Ruta completa al archivo destino.
   *
   * @return bool
   *   TRUE si ha podido mover el archivo.
   */
  public static function moveFileTo(string $origen, string $destino) {
    /* Variable de resultado */
    $resultado = FALSE;

    if (!is_dir($origen)) {
      copy($origen, $destino);
      unlink($origen);
      $resultado = TRUE;
    }

    return $resultado;
  }

  /**
   * Genera un archivo de log a partir de un array.
   *
   * @param string $fileName
   *   Nombre del archivo.
   * @param array $fileContent
   *   Array con el contenido para el archivo.
   */
  public static function createFileLog(string $fileName, array $fileContent) {
    $my_file = fopen($fileName, "a+");

    foreach ($fileContent as $row) {
      if (is_array($row)) {
        $content = implode(' => ', $row) . "\n";
      }
      else {
        $content = $row . "\n";
      }
      fwrite($my_file, $content);
    }

    fclose($my_file);
  }

  /**
   * Obtiene el contenido de un directorio excluyendo las carpetas del sistema.
   *
   * Las carpetas excluidas son:
   * (. y ..)
   *
   * @param string $real_path
   *   Ruta de la que queremos obtener el contenido.
   *
   * @return array
   *   Array con todos los nombre de los ficheros y directorios.
   */
  public static function getDirContent(string $real_path) {

    /* Variable para el contenido del directorio */
    $dir_filenames = [];
    if ($gestor = opendir($real_path)) {
      $i = 0;
      while (FALSE !== ($file_name = readdir($gestor))) {
        /* Para ver nombres de ficheros con acentos, etc. */
        $file_name = utf8_encode($file_name);

        /* Excluimos "." y ".." */
        if (($file_name != '.') && ($file_name != '..')) {
          $dir_filenames[] = $file_name;
        }
        $i++;
      }
      closedir($gestor);
    }

    return $dir_filenames;
  }

  /**
   * Quita los caracteres "raros" del nombre del fichero.
   *
   * Se debe usar antes de guardar en Drupal para evitar el
   * borrado automático de los ficheros.
   *
   * @param string $filename
   *   Nombre del fichero.
   * @param bool $force_lowercase
   *   Indica si queremos convertirlo a minúsculas.
   *   Por defecto está a TRUE.
   *
   * @return string
   *   Nombre del fichero "sanitizado".
   */
  public static function sanitizeFilename(string $filename, bool $force_lowercase = TRUE) {
    $strip = [
      "~",
      "`",
      "!",
      "@",
      "#",
      "$",
      "%",
      "^",
      "&",
      "*",
      "(",
      ")",
      "_",
      "=",
      "+",
      "[",
      "{",
      "]",
      "}",
      "\\",
      "|",
      ";",
      ":",
      "\"",
      "'",
      "&#8216;",
      "&#8217;",
      "&#8220;",
      "&#8221;",
      "&#8211;",
      "&#8212;",
      "â€”",
      "â€“",
      ",",
      "<",
      ">",
      "/",
      "?",
    ];

    $clean = trim(str_replace($strip, "", strip_tags($filename)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = preg_replace('/[^a-z0-9_.-]+/', '_', $clean);

    return ($force_lowercase) ?
        (function_exists('mb_strtolower')) ?
          mb_strtolower($clean, 'UTF-8') :
          strtolower($clean) :
        $clean;
  }

  /**
   * Elimina un directorio y todo su contenido.
   *
   * @param string $target
   *   Path completo del directorio a limpiar.
   *   Debe incluir la "/" al final.
   */
  public static function cleanDirectory(string $target) {
    $it = new \RecursiveDirectoryIterator($target, \FilesystemIterator::SKIP_DOTS);
    $it = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $file) {
      if ($file->isDir()) {
        rmdir($file->getPathname());
      }
      else {
        unlink($file->getPathname());
      }
    }
    rmdir($target);
  }

  /**
   * Convierte un archivo XML/HTML en array.
   *
   * @param \SimpleXMLElement $buffer
   *   Contenido a procesar.
   *
   * @return array
   *   Array con el contenido del fichero.
   */
  public static function xml2Array(SimpleXMLElement $buffer) {
    /* Ejemplo de llamada a la función:
     * $buffer = file_get_contents($ruta);
     * $pos = strpos($buffer, '<table>');
     * $buffer = substr($buffer, $pos, strlen($buffer));
     *
     * $buffer = strip_tags($buffer, "<table><tr><td>");
     * $buffer = preg_replace('/<(\w+)[^>]*>/', '<$1>', $buffer);
     *
     * $buffer = "<?xml version='1.0'?><document>" . PHP_EOL . trim($buffer) . '</document>';
     *
     * $xml = simplexml_load_string($buffer, "SimpleXMLElement", LIBXML_NOCDATA);
     * $contenido = FileFunctions::xml2Array($xml);
     * ksm($contenido);
     */
    $array = [];

    $json = json_encode($buffer);
    $array = json_decode($json, TRUE);

    return $array;
  }

  /**
   * Obtiene el contenido de un fichero vía curl.
   *
   * @param string $url
   *   Url del fichero.
   *
   * @return string
   *   Cadena con el contenido del fichero.
   */
  public static function fileGetContentsCurl(string $url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
  }

  /* ***************************************************************************
   * FUNCIONES PRIVADAS.
   * ************************************************************************ */

  /**
   * Obtiene los nombres originales de los ficheros de una ruta
   * (no corregidos a UTF-8).
   *
   * @param string $realPath
   *   Ruta completa al directorio donde deseamos buscar.
   *   ( Ej. DRUPAL_ROOT . '/sites/default/private_files'; )
   *
   * @return array
   *   Array con los nombres de los archivos.
   */
  private static function getNamesOriginals(string $realPath) {

    $dir_filenames = [];

    if ($gestor = opendir($realPath)) {
      $i = 0;
      while (FALSE !== ($file_name = readdir($gestor))) {

        /* Excluimos los nombres de archivo que estén presentes en EXCLUDE_LIST */
        if (!in_array($file_name, self::EXCLUDE_LIST)) {
          $dir_filenames[] = $file_name;
        }
        $i++;
      }

      closedir($gestor);
    }
    return $dir_filenames;
  }

  /**
   * Devuelve la resta de dos fechas para saber cual es la mayor.
   *
   * @param array $a
   *   Primera fecha a comparar.
   * @param array $b
   *   Segunda fecha a comparar.
   *
   * @return int
   *   La diferencia entre las fechas.
   */
  private static function dateCompare(array $a, array $b) {
    $t1 = $a['datetime'];
    $t2 = $b['datetime'];
    return $t1 - $t2;
  }

}
