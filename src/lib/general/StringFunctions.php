<?php

namespace Drupal\module_template\lib\general;

/**
 * @file
 * Librería StringFunctions.
 */

/**
 * Funciones para trabajar con cadenas de texto.
 */
class StringFunctions {

  /**
   * Función after().
   *
   * Devuelve el texto que se encuentra desde la primera aparición de $chr.
   *
   * @param string $chr
   *   Caracter a buscar.
   * @param string $inthat
   *   String dónde buscar el caracter.
   *
   * @return string
   *   Devuelve la cadena resultante.
   *
   * @see README.md
   */
  public static function after(string $chr, string $inthat) {
    if (!is_bool(strpos($inthat, $chr))) {
      return substr($inthat, strpos($inthat, $chr) + strlen($chr));
    }
    else {
      return $inthat;
    }
  }

  /**
   * Función afterLast().
   *
   * Devuelve el texto que se encuentra desde la última aparición de $chr.
   *
   * @param string $chr
   *   Caracter a buscar.
   * @param string $inthat
   *   String dónde buscar el caracter.
   *
   * @return string
   *   Devuelve la cadena resultante.
   *
   * @see README.md
   */
  public static function afterLast(string $chr, string $inthat) {
    if (!is_bool(self::strRevPos($inthat, $chr))) {
      return substr($inthat, self::strRevPos($inthat, $chr) + strlen($chr));
    }
    else {
      return $inthat;
    }
  }

  /**
   * Función before().
   *
   * Devuelve el texto que se encuentra hasta la primera aparición de $chr.
   *
   * @param string $chr
   *   Caracter a buscar.
   * @param string $inthat
   *   String dónde buscar el caracter.
   *
   * @return string
   *   Devuelve la cadena resultante.
   *
   * @see README.md
   */
  public static function before(string $chr, string $inthat) {
    if (strpos($inthat, $chr)) {
      return substr($inthat, 0, strpos($inthat, $chr));
    }
    else {
      return $inthat;
    }
  }

  /**
   * Función beforeLast().
   *
   * Devuelve el trexto que se encuentra hasta la última aparición de $chr.
   *
   * @param string $chr
   *   Caracter a buscar.
   * @param string $inthat
   *   String dónde buscar el caracter.
   *
   * @return string
   *   Devuelve la cadena resultante.
   *
   * @see README.md
   */
  public static function beforeLast(string $chr, string $inthat) {
    return substr($inthat, 0, self::strRevPos($inthat, $chr));
  }

  /**
   * Función between().
   *
   * Devuelve el texto que se encuentra entre dos caracteres.
   *
   * @param string $chr
   *   Primer caracter a encontrar.
   * @param string $that
   *   Segundo caracter a buscar.
   * @param string $inthat
   *   String dónde buscar los caracteres.
   *
   * @return string
   *   Devuelve la cadena resultante.
   *
   * @see README.md
   */
  public static function between(string $chr, string $that, string $inthat) {
    return self::before($that, self::after($chr, $inthat));
  }

  /**
   * Función betweenLast().
   *
   * Devuelve el texto que se encuentra entre dos caracteres (última aparición).
   *
   * @param string $chr
   *   Primer caracter a encontrar.
   * @param string $that
   *   Segundo caracter a buscar.
   * @param string $inthat
   *   String dónde buscar los caracteres.
   *
   * @return string
   *   Devuelve la cadena resultante.
   *
   * @see README.md
   */
  public static function betweenLast($chr, $that, $inthat) {
    return self::afterLast($chr, self::beforeLast($that, $inthat));
  }

  /**
   * Función stripWordHtml().
   *
   * Elimina el HTML que es guardado cuando se pega desde MS Word.
   *
   * @param string $text
   *   Texto que queremos limpiar.
   * @param string $allowed_tags
   *   Etiquetas que queremos salvaguardar.
   *
   * @return string
   *   Devuelve el texto limpio.
   *
   * @see README.md
   */
  public static function stripWordHtml(string $text, string $allowed_tags = '<b><i><sup><sub><em><strong><u><br>') {
    mb_regex_encoding('UTF-8');

    // Replace MS special characters first.
    $search = [
      '/&lsquo;/u',
      '/&rsquo;/u',
      '/&ldquo;/u',
      '/&rdquo;/u',
      '/&mdash;/u',
    ];
    $replace = [
      '\'',
      '\'',
      '"',
      '"',
      '-',
    ];
    $text = preg_replace($search, $replace, $text);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

    if (mb_stripos($text, '/*') !== FALSE) {
      $text = mb_eregi_replace('#/\*.*?\*/#s', '', $text, 'm');
    }

    $text = preg_replace(['/<([0-9]+)/'], ['< $1'], $text);
    $text = strip_tags($text, $allowed_tags);
    $text = preg_replace(['/^\s\s+/', '/\s\s+$/', '/\s\s+/u'], ['', '', ' '], $text);

    $search = [
      '#<(strong|b)[^>]*>(.*?)</(strong|b)>#isu',
      '#<(em|i)[^>]*>(.*?)</(em|i)>#isu',
      '#<u[^>]*>(.*?)</u>#isu',
    ];
    $replace = [
      '<b>$2</b>',
      '<i>$2</i>',
      '<u>$1</u>',
    ];
    $text = preg_replace($search, $replace, $text);

    $num_matches = preg_match_all("/\<!--/u", $text, $matches);
    if ($num_matches) {
      $text = preg_replace('/\<!--(.)*--\>/isu', '', $text);
    }
    return $text;
  }

  /**
   * Función generateKey().
   *
   * Genera una clave a partir de los parámetros recibidos.
   *
   * @param int $itemsGroup
   *   Número de dígitos de cada grupo.
   * @param int $groupNumber
   *   Número de grupos a crear.
   * @param string $divider
   *   Separador a usar entre los grupos (por defecto ninguno).
   *
   * @return string
   *   La clave generada.
   *
   * @see README.md
   */
  public static function generateKey(int $itemsGroup, int $groupNumber, string $divider = '') {
    $generar = $groupNumber * $itemsGroup;

    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $generar; $i++) {
      $index = rand(0, strlen($characters) - 1);
      $randomString .= $characters[$index];

      /* Añado como separador un guión */
      if (((($i + 1) % $itemsGroup) == 0) and (($i + 1) < $generar)) {
        $randomString .= $divider;
      }
    }

    return $randomString;
  }

  /**
   * Función truncate().
   *
   * Recorta una cadena añadiendo puntos suspensivos según la longitud indicada.
   *
   * @param string $str
   *   Cadena a recortar.
   * @param int $width
   *   Número de caracteres máximo que se desea mostrar.
   *
   * @return string
   *   Truncate string.
   */
  public static function truncate(string $str, int $width) {
    return strtok(wordwrap($str, $width, "...\n"), "\n");
  }

  /* ***************************************************************************
   * FUNCIONES PRIVADAS.
   * ************************************************************************ */

  /**
   * Función strRevPos().
   *
   *   Devuelve la posición de un caracter contando desde el final.
   *
   * @param string $instr
   *   Caracter a buscar.
   * @param string $needle
   *   String dónde buscar el caracter.
   *
   * @return int
   *   Devuelve la posición buscada.
   */
  private static function strRevPos(string $instr, string $needle) {
    $rev_pos = strpos(strrev($instr), strrev($needle));
    if ($rev_pos === FALSE) {
      return FALSE;
    }
    else {
      return strlen($instr) - $rev_pos - strlen($needle);
    }
  }

}
