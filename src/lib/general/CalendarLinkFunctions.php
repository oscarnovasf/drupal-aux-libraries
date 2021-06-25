<?php

namespace Drupal\module_template\lib\general;

use Drupal\Core\File\FileSystemInterface;

/**
 * Genera los enlaces para los calendarios de:
 *   - Google.
 *   - Yahoo.
 *   - Outlook (web).
 *   - ICS (archivo).
 */
class CalendarLinkFunctions {

  /**
   * Genera un link para añadir un evento al calendario de Google.
   *
   * @param string $name
   *   Nombre del evento.
   * @param int $begin
   *   Fecha de inicio del evento en formato timestamp.
   * @param int $end
   *   Fecha de fin del evento en formato timestamp.
   * @param string $location
   *   Cadena con la dirección del evento.
   * @param string $details
   *   Cadena con los detalles del evento.
   *
   * @return string
   *   Cadena con la url del enlace.
   */
  public function linkGoogle(string $name, int $begin, int $end, string $location, string $details) {
    /* Parámetros que debo añadir a la url de google calendar */
    $params = [
      '&dates=',
      '/',
      '&location=',
      '&details=',
      '&sf=true&output=xml',
    ];

    $url = 'https://www.google.com/calendar/render?action=TEMPLATE&text=';

    /* Recorro todos los argumentos de la función y los modifico para luego añadirlos a la url */
    $arg_list = func_get_args();
    for ($i = 0; $i < count($arg_list); $i++) {
      $current = $arg_list[$i];
      if (is_int($current)) {
        $current = date('Ymd\THis\Z', $current);
      }
      else {
        $current = urlencode($current);
      }
      $url .= (string) $current . $params[$i];
    }
    return $url;
  }

  /**
   * Genera un link para añadir un evento al calendario de Yahoo.
   *
   * @param string $name
   *   Nombre del evento.
   * @param int $begin
   *   Fecha de inicio del evento en formato timestamp.
   * @param int $end
   *   Fecha de fin del evento en formato timestamp.
   * @param string $location
   *   Cadena con la dirección del evento.
   * @param string $details
   *   Cadena con los detalles del evento.
   *
   * @return string
   *   Cadena con la url del enlace.
   */
  public function linkYahoo(string $name, int $begin, int $end, string $location, string $details) {
    /* Parámetros que debo añadir a la url de yahoo calendar */
    $params = [
      '&st=',
      '&et=',
      '&in_loc=',
      '&desc=',
      '&uid',
    ];

    $url = 'https://calendar.yahoo.com/?v=60&view=d&type=20&title=';

    /* Recorro todos los argumentos de la función y los modifico para luego añadirlos a la url */
    $arg_list = func_get_args();
    for ($i = 0; $i < count($arg_list); $i++) {
      $current = $arg_list[$i];
      if (is_int($current)) {
        $current = date('Ymd\THis', $current);
      }
      else {
        $current = urlencode($current);
      }
      $url .= (string) $current . $params[$i];
    }
    return $url;
  }

  /**
   * Genera un link para añadir un evento al calendario de Outlook (web).
   *
   * @param string $name
   *   Nombre del evento.
   * @param int $begin
   *   Fecha de inicio del evento en formato timestamp.
   * @param int $end
   *   Fecha de fin del evento en formato timestamp.
   * @param string $location
   *   Cadena con la dirección del evento.
   * @param string $details
   *   Cadena con los detalles del evento.
   *
   * @return string
   *   Cadena con la url del enlace.
   */
  public function linkOutlookWeb(string $name, int $begin, int $end, string $location, string $details) {
    /* Parámetros que debo añadir a la url de outlook calendar (web) */
    $params = [
      '&startdt=',
      '&enddt=',
      '&location=',
      '&body=',
      '&allday=false',
    ];

    $url = 'https://outlook.live.com/calendar/deeplink/compose?path=/calendar/action/compose&rru=addevent&subject=';

    /* Recorro todos los argumentos de la función y los modifico para luego añadirlos a la url */
    $arg_list = func_get_args();
    for ($i = 0; $i < count($arg_list); $i++) {
      $current = $arg_list[$i];
      if (is_int($current)) {
        $current = date('Y-m-d\TH:i:s\Z', $current);
      }
      else {
        $current = urlencode($current);
      }
      $url .= (string) $current . $params[$i];
    }
    return $url;
  }

  /**
   * Genera un archivo ICS (link) para añadir eventos a otro tipo de calendario.
   *
   * @param string $name
   *   Nombre del evento.
   * @param int $begin
   *   Fecha de inicio del evento en formato timestamp.
   * @param int $end
   *   Fecha de fin del evento en formato timestamp.
   * @param string $location
   *   Cadena con la dirección del evento.
   * @param string $details
   *   Cadena con los detalles del evento.
   *
   * @return string
   *   Url que apunta al archivo generado.
   */
  public function linkIcs(string $name, int $begin, int $end, string $location, string $details) {

    /* Datos para el archivo */
    $url = [
      'BEGIN:VCALENDAR',
      'VERSION:2.0',
      'METHOD:PUBLISH',
      'PRODID:-//Gcommons//AddCalendarLinks//EN',
      'X-MS-OLK-FORCEINSPECTOROPEN:TRUE',
      'BEGIN:VEVENT',

      'DTSTAMP:' . date('Ymd\THis\Z'),
      'DTSTART;TZID=Europe/Madrid:' . date('Ymd\THis', $begin),
      'DTEND;TZID=Europe/Madrid:' . date('Ymd\THis', $end),
      'SUMMARY:' . $this->escapeString($name),
      'LOCATION:' . $this->escapeString($location),
      'UID:' . $this->generateEventUid($name, $begin, $end, $location),
      'DESCRIPTION:' . $this->escapeString($details),

      'BEGIN:VALARM',
      'TRIGGER:-PT15M',
      'ACTION:DISPLAY',
      'DESCRIPTION:Reminder',
      'END:VALARM',
      'END:VEVENT',
      'END:VCALENDAR',
    ];

    /* Descomentar esta línea si se quisiera sacar el archivo directamente */
    /* $file_content = 'data:text/calendar;charset=utf8;base64,' . base64_encode(implode("\r\n", $url)); */

    $file_content = implode("\r\n", $url);

    /* Guardo el archivo .ics */
    $directory = \Drupal::config('system.file')->get('default_scheme') . '://calendario-eventos';
    \Drupal::service('file_system')->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    $file_name = str_replace(' ', '_', trim($name));
    $file_name = str_replace('¡', '', $file_name);
    $file_name = str_replace('!', '', $file_name);
    $file_name = str_replace('¿', '', $file_name);
    $file_name = str_replace('?', '', $file_name);

    $file_location = $directory . '/' . $file_name . ".ics";
    $file = file_save_data($file_content, $file_location, FileSystemInterface::EXISTS_REPLACE);

    if ($file) {
      /* $absolute_path = \Drupal::service('file_system')->realpath($file_location); */
      $file_path = '/sites/default/files/calendario-eventos/' . $file_name . '.ics';

      return $file_path;
    }
    else {
      return FALSE;
    }
  }

  /* ***************************************************************************
   * FUNCIONES PRIVADAS
   ************************************************************************** */

  /**
   * Genera una cadena MD5 para los archivos ICS.
   *
   * @param string $name
   *   Nombre del evento.
   * @param int $begin
   *   Fecha de inicio del evento en formato timestamp.
   * @param int $end
   *   Fecha de fin del evento en formato timestamp.
   * @param string $location
   *   Cadena con la dirección del evento.
   *
   * @return string
   *   Cadena codificada en MD5.
   */
  private function generateEventUid(string $name, int $begin, int $end, string $location) {
    return md5(sprintf(
      '%s%s%s%s',
      date('Y-m-d\TH:i:sP', $begin),
      date('Y-m-d\TH:i:sP', $end),
      $name,
      $location
    ));
  }

  /**
   * Formatea la cadena al estilo C.
   *
   * @param string $field
   *   Cadena a formatear.
   *
   * @return string
   *   Cadena formateada.
   */
  private function escapeString(string $field) {
    return addcslashes($field, "\r\n,;");
  }

}
