<?php

namespace Drupal\module_template\lib\general;

use Symfony\Component\HttpFoundation\Request;

/**
 * Funciones para trabajar con parámetros GET o POST.
 */
class ParamsFunctions {

  /**
   * Comprueba si existen todos los parámetros y devuelve su valor.
   *
   * @param Symfony\Component\HttpFoundation\Request $request
   *   Drupal Request (\Drupal::request())
   * @param array $params
   *   Array con los parámetros que queremos obtener.
   * @param string $method
   *   GET o POST.
   *
   * @return array|bool
   *   Array con los parámetros y su valor.
   *   FALSE si no se encuentra.
   */
  public static function obtainParams(Request $request, array $params, string $method) {

    $result = [];

    foreach ($params as $key) {
      try {
        if ($method == 'POST') {
          $result[$key] = $request->request->get($key);
        }

        if ($method == 'GET') {
          $result[$key] = $request->query->get($key);
        }
      }
      catch (Exception $e) {
        return FALSE;
      }
    }

    return $result;
  }

}
