<?php

namespace Drupal\module_template\lib\general;

/**
 * @file
 * Librería FileFunctions.
 */

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
   * Función deleteFilesPreviousTo().
   *
   * Elimina todos los archivos anteriores a la fecha dada.
   * (Fecha en formato timestamp).
   *
   * @param string $realPath
   *   Ruta completa al directorio donde deseamos buscar.
   *   ( Ej. DRUPAL_ROOT . '/sites/default/privatefiles'; )
   * @param int $timeStamp
   *   Fecha límite hasta la que se borran los archivos.
   *   ( Ej. strtotime('-6 hour', time()); )
   *
   * @return int
   *   Número de archivos que han sido borrados.
   *
   * @see README.md
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
   * Función getNamesByDateAsc().
   *
   * Devuelve el listado de archivos de un directorio
   * ordenado por fecha.
   *
   * @param string $realPath
   *   Ruta completa al directorio donde deseamos buscar.
   *   ( Ej. DRUPAL_ROOT . '/sites/default/privatefiles'; )
   *
   * @return array
   *   Array con los nombres de los archivos.
   *
   * @see README.md
   */
  public static function getNamesByDateAsc(string $realPath) {

    $dir_names = self::getNamesOriginals($realPath);

    if (isset($dir_names[0])) {
      foreach ($dir_names as $i => $resto) {
        $file_name = $dir_names[$i];
        $datetime = filemtime($realPath . '/' . $file_name);
        $array_filenames_dates[$i]['filename'] = $file_name;
        $array_filenames_dates[$i]['datetime'] = $datetime;
      }

      usort($array_filenames_dates, 'self::dateCompare');

      foreach ($array_filenames_dates as $i => $resto) {
        $dir_names[$i] = $array_filenames_dates[$i]['filename'];
      }
    }

    return $dir_names;
  }

  /**
   * Función readFile().
   *
   * @param string $realPath
   *   Ruta completa al archivo.
   * @param array $allowedExt
   *   Array que contiene todas las extensiones permitidas.
   *
   * @return Resource|bool
   *   Devuelve el archivo si lo encuentra.
   *   False en caso de no poder leer el archivo.
   *
   * @see README.md
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
   * Función createFileLog().
   *
   * Genera un archivo de log a partir de un array.
   *
   * @param string $fileName
   *   Nombre del archivo.
   * @param array $fileContent
   *   Array con el contenido para el archivo.
   */
  public static function createFileLog(string $fileName, array $fileContent) {
    $myfile = fopen($fileName, "a+") or die("Unable to open file!");

    foreach ($fileContent as $row) {
      $content = implode(' => ', $row) . "\n";
      fwrite($myfile, $content);
    }

    fclose($myfile);
  }

  /**
   * Función getDirContent().
   *
   * Obtiene el contenido de un directorio excluyendo las carpetas del sistema:
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
   * Función sanitizeFilename().
   *
   * Quita los caracteres "raros" del nombre del fichero.
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
    return ($force_lowercase) ?
        (function_exists('mb_strtolower')) ?
          mb_strtolower($clean, 'UTF-8') :
          strtolower($clean) :
        $clean;
  }

  /**
   * Elimina el contenido de un directorio.
   *
   * @param string $target
   *   Path completo del directorio a limpiar.
   */
  function cleanDirectory(string $target) {
    if (is_dir($target)) {
      $files = glob($target . '*', GLOB_MARK);

      foreach ($files as $file) {
        cleanDirectory($file);
      }

      rmdir($target);
    }
    elseif (is_file($target)) {
      unlink($target);
    }
  }

  /* ***************************************************************************
   * FUNCIONES PRIVADAS.
   * ************************************************************************ */

  /**
   * Función getNamesOriginals().
   *
   * Obtiene los nombres originales de los ficheros de una ruta
   * (no corregidos a UTF-8).
   *
   * @param string $realPath
   *   Ruta completa al directorio donde deseamos buscar.
   *   ( Ej. DRUPAL_ROOT . '/sites/default/privatefiles'; )
   *
   * @return array
   *   Array con los nombres de los archivos.
   */
  private static function getNamesOriginals(string $realPath) {

    $dir_filenames = [];

    if ($gestor = opendir($realPath)) {
      $i = 0;
      while (FALSE !== ($file_name = readdir($gestor))) {

        /* Excluímos los nombres de archivo que estén presentes en EXCLUDE_LIST */
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
   * Función dateCompare().
   *
   * Devuelve la resta de dos fechas para saber
   * cual es la mayor.
   *
   * @param int $a
   *   Primera fecha a comparar.
   * @param int $b
   *   Segunda fecha a comparar.
   *
   * @return int
   *   La diferencia entre las fechas.
   */
  private static function dateCompare(int $a, int $b) {
    $t1 = $a['datetime'];
    $t2 = $b['datetime'];
    return $t1 - $t2;
  }

}
