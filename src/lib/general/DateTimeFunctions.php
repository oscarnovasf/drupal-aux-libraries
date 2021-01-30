<?php

namespace Drupal\module_template\lib\general;

/**
 * @file
 * Librería DateTimeFunctions.
 */

use DateTime;

/**
 * Funciones para gestionar archivos y directorios.
 */
class DateTimeFunctions {

  /**
   * Retorna una fecha en "Hace X días".
   *
   * @param string $fecha
   *   Fecha en formato 'Y-m-d'.
   * @param string $hora
   *   Hora en formato 'H:i:s'.
   *
   * @return string
   *   Cadena formateada.
   */
  public static function convertToAgoDate(string $fecha, string $hora) {
    /* Cadena a ser retornada */
    $resultado = '';

    $start_date = new DateTime($fecha . " " . $hora);
    $since_start = $start_date->diff(new DateTime(date("Y-m-d") . " " . date("H:i:s")));
    $resultado .= "Hace ";
    if ($since_start->y == 0) {
      if ($since_start->m == 0) {
        if ($since_start->d == 0) {
          if ($since_start->h == 0) {
            if ($since_start->i == 0) {
              if ($since_start->s == 0) {
                $resultado .= $since_start->s . ' segundos';
              }
              else {
                if ($since_start->s == 1) {
                  $resultado .= $since_start->s . ' segundo';
                }
                else {
                  $resultado .= $since_start->s . ' segundos';
                }
              }
            }
            else {
              if ($since_start->i == 1) {
                $resultado .= $since_start->i . ' minuto';
              }
              else {
                $resultado .= $since_start->i . ' minutos';
              }
            }
          }
          else {
            if ($since_start->h == 1) {
              $resultado .= $since_start->h . ' hora';
            }
            else {
              $resultado .= $since_start->h . ' horas';
            }
          }
        }
        else {
          if ($since_start->d == 1) {
            $resultado .= $since_start->d . ' día';
          }
          else {
            $resultado .= $since_start->d . ' días';
          }
        }
      }
      else {
        if ($since_start->m == 1) {
          $resultado .= $since_start->m . ' mes';
        }
        else {
          $resultado .= $since_start->m . ' meses';
        }
      }
    }
    else {
      if ($since_start->y == 1) {
        $resultado .= $since_start->y . ' año';
      }
      else {
        $resultado .= $since_start->y . ' años';
      }
    }

    return $resultado;
  }

}
