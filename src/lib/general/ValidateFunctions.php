<?php

namespace Drupal\module_template\lib\general;

/**
 * Funciones de validación de datos.
 */
class ValidateFunctions {

  private const IDENTIFICATION_TYPE = [
    'K' => 'Español menor de catorce años o extranjero menor de dieciocho',
    'L' => 'Español mayor de catorce años residiendo en el extranjero',
    'M' => 'Extranjero mayor de dieciocho años sin NIE',
    '0' => 'Español con documento nacional de identidad',
    '1' => 'Español con documento nacional de identidad',
    '2' => 'Español con documento nacional de identidad',
    '3' => 'Español con documento nacional de identidad',
    '4' => 'Español con documento nacional de identidad',
    '5' => 'Español con documento nacional de identidad',
    '6' => 'Español con documento nacional de identidad',
    '7' => 'Español con documento nacional de identidad',
    '8' => 'Español con documento nacional de identidad',
    '9' => 'Español con documento nacional de identidad',
    'T' => 'Extranjero residente en España e identificado por la Policía con un NIE',
    'X' => 'Extranjero residente en España e identificado por la Policía con un NIE',
    'Y' => 'Extranjero residente en España e identificado por la Policía con un NIE',
    'Z' => 'Extranjero residente en España e identificado por la Policía con un NIE',
    'A' => 'Sociedad Anónima',
    'B' => 'Sociedad de responsabilidad limitada',
    'C' => 'Sociedad colectiva',
    'D' => 'Sociedad comanditaria',
    'E' => 'Comunidad de bienes y herencias yacentes',
    'F' => 'Sociedad cooperativa',
    'G' => 'Asociación',
    'H' => 'Comunidad de propietarios en régimen de propiedad horizontal',
    'J' => 'Sociedad Civil => con o sin personalidad jurídica',
    'N' => 'Entidad extranjera',
    'P' => 'Corporación local',
    'Q' => 'Organismo público',
    'R' => 'Congregación o Institución Religiosa',
    'S' => 'Órgano de la Administración del Estado y Comunidades Autónomas',
    'U' => 'Unión Temporal de Empresas',
    'V' => 'Fondo de inversiones o de pensiones, agrupación de interés económico, etc',
    'W' => 'Establecimiento permanente de entidades no residentes en España',
  ];

  /**
   * Recibe un número de documento y lo comprueba con los tipos españoles.
   *
   * @param string $docNumber
   *   El número de Identificación a comprobar.
   *
   * @return bool
   *   TRUE => Si el número es válido.
   *   FALSE => Si el número es incorrecto.
   */
  public static function isValidIdNumber(string $docNumber) {
    $fixedDocNumber = strtoupper($docNumber);
    return (
      self::isValidNif($fixedDocNumber) ||
      self::isValidNie($fixedDocNumber) ||
      self::isValidCif($fixedDocNumber)
    );
  }

  /**
   * Recibe un código postal y lo comprueba con los tipos españoles.
   *
   * @param string $postal_code
   *   El número a comprobar.
   *
   * @return bool
   *   TRUE => Si el número es válido.
   *   FALSE => Si el número es incorrecto.
   */
  public static function isValidPostalCode(string $postal_code) {
    $re = '/^(?:0[1-9]|[1-4]\d|5[0-2])\d{3}$/';

    preg_match($re, $postal_code, $matches, PREG_OFFSET_CAPTURE, 0);

    if (count($matches) > 0) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Verifica si es un color hexadecimal válido.
   *
   * @param string $color
   *   Color a verificar.
   *
   * @return string|bool
   *   Devuelve el color o FALSE si no es un color válido.
   */
  public static function isHexColor(string $color) {
    if (preg_match('/^#[a-f0-9]{6}$/i', $color)) {
      return $color;
    }
    elseif (preg_match('/^[a-f0-9]{6}$/i', $color)) {
      return '#' . $color;
    }

    return FALSE;
  }

  /**
   * Recibe una url y verifica si tiene el formato correcto.
   *
   * @param string $url
   *   URL a verificar.
   *
   * @return bool
   *   TRUE indica que es correcta.
   *   FALSE indica que no es correcta.
   */
  public static function isValidUrl(string $url) {
    if (trim($url) == '') {
      return FALSE;
    }
    else {
      if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return FALSE;
      }
      if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|](\.)[a-z]{2}/i", $url)) {
        return FALSE;
      }
      else {
        return TRUE;
      }
    }
  }

  /**
   * Verifica que el formato coincide con un formato válido de email.
   *
   * @param string $email
   *   Email a verificar.
   *
   * @return bool
   *   TRUE indica que el formato es válido.
   */
  public static function isValidEmailFormat(string $email) {
    return (FALSE !== filter_var($email, FILTER_VALIDATE_EMAIL));
  }

  /**
   * Verificador de correo electrónico.
   *
   * Verifica que el formato coincide con un formato válido de email,
   * además verifica que el dominio cuenta con un registro MX.
   * No es recomendable usarla si son muchos los emails a verificar pues
   * ralentiza el proceso.
   *
   * @param string $email
   *   Email a verificar.
   *
   * @return bool
   *   TRUE indica que el formato y el dominio son válidos.
   */
  public static function isValidEmail(string $email) {
    $result = self::isValidEmailFormat($email);

    if ($result) {
      $data = explode('@', $email);
      $result = checkdnsrr($data[1], 'MX');
    }

    return $result;
  }

  /* ***************************************************************************
   * FUNCIONES PRIVADAS.
   * ************************************************************************ */

  /**
   * Valida el número del NIF con la letra.
   *
   * @param string $docNumber
   *   Número del NIF.
   *
   * @return bool
   *   TRUE => Si el número es válido.
   *   FALSE => Si el número es incorrecto.
   */
  private static function isValidNif(string $docNumber) {
    $isValid = FALSE;
    $fixedDocNumber = "";
    $correctDigit = "";
    $writtenDigit = "";

    if (!preg_match("/^[A-Z]+$/i", substr($fixedDocNumber, 1, 1))) {
      $fixedDocNumber = strtoupper(substr("000000000" . $docNumber, -9));
    }
    else {
      $fixedDocNumber = strtoupper($docNumber);
    }

    $writtenDigit = strtoupper(substr($docNumber, -1, 1));
    if (self::isValidNifFormat($fixedDocNumber)) {
      $correctDigit = self::getNifCheckDigit($fixedDocNumber);
      if ($writtenDigit == $correctDigit) {
        $isValid = TRUE;
      }
    }
    return $isValid;
  }

  /**
   * Valida el número del NIE con la letra.
   *
   * @param string $docNumber
   *   Número del NIE.
   *
   * @return bool
   *   TRUE => Si el número es válido.
   *   FALSE => Si el número es incorrecto.
   */
  private static function isValidNie(string $docNumber) {
    $isValid = FALSE;
    $fixedDocNumber = "";
    if (!preg_match("/^[A-Z]+$/i", substr($fixedDocNumber, 1, 1))) {
      $fixedDocNumber = strtoupper(substr("000000000" . $docNumber, -9));
    }
    else {
      $fixedDocNumber = strtoupper($docNumber);
    }
    if (self::isValidNieFormat($fixedDocNumber)) {
      if (substr($fixedDocNumber, 1, 1) == "T") {
        $isValid = TRUE;
      }
      else {
        $numberWithoutLast = substr($fixedDocNumber, 0, strlen($fixedDocNumber) - 1);
        $lastDigit = substr($fixedDocNumber, strlen($fixedDocNumber) - 1, strlen($fixedDocNumber));
        $numberWithoutLast = str_replace('Y', '1', $numberWithoutLast);
        $numberWithoutLast = str_replace('X', '0', $numberWithoutLast);
        $numberWithoutLast = str_replace('Z', '2', $numberWithoutLast);
        $fixedDocNumber = $numberWithoutLast . $lastDigit;
        $isValid = self::isValidNif($fixedDocNumber);
      }
    }
    return $isValid;
  }

  /**
   * Valida el número del CIF con la letra.
   *
   * @param string $docNumber
   *   Número del CIF.
   *
   * @return bool
   *   TRUE => Si el número es válido.
   *   FALSE => Si el número es incorrecto.
   */
  private static function isValidCif(string $docNumber) {
    $isValid = FALSE;
    $fixedDocNumber = "";
    $correctDigit = "";
    $writtenDigit = "";
    $fixedDocNumber = strtoupper($docNumber);
    $writtenDigit = substr($fixedDocNumber, -1, 1);
    if (self::isValidCifFormat($fixedDocNumber) == 1) {
      $correctDigit = self::getCifCheckDigit($fixedDocNumber);
      if ($writtenDigit == $correctDigit) {
        $isValid = TRUE;
      }
    }
    return $isValid;
  }

  /**
   * Valida el formato del número del NIF.
   *
   * @param string $docNumber
   *   Número del NIF.
   *
   * @return bool
   *   TRUE => Si el formato es válido.
   *   FALSE => Si el formato es incorrecto.
   */
  private static function isValidNifFormat(string $docNumber) {
    return self::respectsDocPattern(
      $docNumber,
      '/^[KLM0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][a-zA-Z0-9]/');
  }

  /**
   * Valida el formato del número del NIE.
   *
   * @param string $docNumber
   *   Número del NIE.
   *
   * @return bool
   *   TRUE => Si el formato es válido.
   *   FALSE => Si el formato es incorrecto.
   */
  private static function isValidNieFormat(string $docNumber) {
    return self::respectsDocPattern(
      $docNumber,
      '/^[XYZT][0-9][0-9][0-9][0-9][0-9][0-9][0-9][A-Z0-9]/');
  }

  /**
   * Valida el formato del número del CIF.
   *
   * @param string $docNumber
   *   Número del CIF.
   *
   * @return bool
   *   TRUE => Si el formato es válido.
   *   FALSE => Si el formato es incorrecto.
   */
  private static function isValidCifFormat(string $docNumber) {
    return (
      self::respectsDocPattern(
        $docNumber,
        '/^[PQSNWR][0-9][0-9][0-9][0-9][0-9][0-9][0-9][A-Z0-9]/')
      or
      self::respectsDocPattern(
        $docNumber,
        '/^[ABCDEFGHJUV][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]/')
      );
  }

  /**
   * Obtiene la letra de check del número del NIF.
   *
   * @param string $docNumber
   *   Número del NIF sin letra.
   *
   * @return string
   *   [LETRA CORRESPONDIENTE] => Devuelve la letra si el formato es válido.
   *   "" => En cualquier otro caso.
   */
  private static function getNifCheckDigit(string $docNumber) {
    $keyString = 'TRWAGMYFPDXBNJZSQVHLCKE';
    $fixedDocNumber = "";
    $position = 0;
    $correctLetter = "";
    if (!preg_match("/^[A-Z]+$/i", substr($fixedDocNumber, 1, 1))) {
      $fixedDocNumber = strtoupper(substr("000000000" . $docNumber, -9));
    }
    else {
      $fixedDocNumber = strtoupper($docNumber);
    }
    if (self::isValidNifFormat($fixedDocNumber)) {
      if (self::isValidNifFormat($fixedDocNumber)) {
        $fixedDocNumber = str_replace('K', '0', $fixedDocNumber);
        $fixedDocNumber = str_replace('L', '0', $fixedDocNumber);
        $fixedDocNumber = str_replace('M', '0', $fixedDocNumber);
        $position = substr($fixedDocNumber, 0, 8) % 23;
        $correctLetter = substr($keyString, $position, 1);
      }
    }
    return $correctLetter;
  }

  /**
   * Obtiene la letra de check del número del CIF.
   *
   * @param string $docNumber
   *   Número del CIF conn letra.
   *
   * @return string
   *   [LETRA CORRESPONDIENTE] => Devuelve la letra si el formato es válido.
   *   "" => En cualquier otro caso.
   */
  private static function getCifCheckDigit(string $docNumber) {
    $fixedDocNumber = "";
    $centralChars = "";
    $firstChar = "";
    $evenSum = 0;
    $oddSum = 0;
    $totalSum = 0;
    $lastDigitTotalSum = 0;
    $correctDigit = "";
    $fixedDocNumber = strtoupper($docNumber);
    if (self::isValidCifFormat($fixedDocNumber)) {
      $firstChar = substr($fixedDocNumber, 0, 1);
      $centralChars = substr($fixedDocNumber, 1, 7);
      $evenSum =
        substr($centralChars, 1, 1) +
        substr($centralChars, 3, 1) +
        substr($centralChars, 5, 1);
      $oddSum =
        self::sumDigits(substr($centralChars, 0, 1) * 2) +
        self::sumDigits(substr($centralChars, 2, 1) * 2) +
        self::sumDigits(substr($centralChars, 4, 1) * 2) +
        self::sumDigits(substr($centralChars, 6, 1) * 2);
      $totalSum = $evenSum + $oddSum;
      $lastDigitTotalSum = substr($totalSum, -1);
      if ($lastDigitTotalSum > 0) {
        $correctDigit = 10 - ($lastDigitTotalSum % 10);
      }
      else {
        $correctDigit = 0;
      }
    }
    /* Si el número comienza por P, Q, S, N, W ó R,
     * verificamos que el dígito debería ser una letra */
    if (preg_match('/[PQSNWR]/', $firstChar)) {
      $correctDigit = substr("JABCDEFGHI", $correctDigit, 1);
    }
    return $correctDigit;
  }

  /**
   * Verifica que la cadena proporcionada respeta el patrón dado.
   *
   * @param string $givenString
   *   Cadena a validar.
   * @param string $pattern
   *   Patrón a usar en la validación.
   *
   * @return bool
   *   TRUE => Si se respeta el patrón.
   *   FALSE => Si no se respeta el patrón.
   */
  private static function respectsDocPattern(string $givenString, string $pattern) {
    $isValid = FALSE;
    $fixedString = strtoupper($givenString);
    if (is_int(substr($fixedString, 0, 1))) {
      $fixedString = substr("000000000" . $givenString, -9);
    }
    if (preg_match($pattern, $fixedString)) {
      $isValid = TRUE;
    }
    return $isValid;
  }

  /**
   * Realiza el sumatorio del número proporcionado.
   *
   * @param int $digits
   *   Número al que se le realizará el sumatorio.
   *
   * @return int
   *   La suma de los dígitos del parámetro proporcionado.
   */
  private static function sumDigits(int $digits) {
    $total = 0;
    $i = 1;
    while ($i <= strlen($digits)) {
      $thisNumber = substr($digits, $i - 1, 1);
      $total += $thisNumber;
      $i++;
    }
    return $total;
  }

}
