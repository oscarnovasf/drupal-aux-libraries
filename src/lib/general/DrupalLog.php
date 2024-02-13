<?php

namespace Drupal\module_template\lib\general;

use Drupal\user\Entity\User;

/**
 * Simplifica las llamadas al logger de Drupal.
 */
class DrupalLog {

  /**
   * Nombre del módulo actual.
   *
   * @var string
   */
  protected $moduleName;

  /**
   * Constructor de la clase.
   */
  public function __construct() {
    $directorio = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
    $this->moduleName = $directorio[count($directorio) - 4];
  }

  /**
   * Para realizar el overload de algunas funciones.
   *
   * @param string $method_name
   *   Nombre del método llamado.
   *   Los métodos permitidos son:
   *     - log: Realiza llamadas a logArray o logString según el tipo de los
   *            parámetros pasados a la función.
   * @param array $args
   *   Argumentos pasados al método.
   */
  public function __call(string $method_name, array $args) {
    if ($method_name == 'log') {

      if (count($args) > 0) {
        /* Si el primer argumento es un array, llamo a logArray */
        if (is_array($args[0])) {
          $this->logArray($args[0],
                          $args[1] ?? 'info',
                          $args[2] ?? NULL);
        }
        /* Si el primer argumento es un string, llamo a logString */
        elseif (is_string($args[0])) {
          $this->logString($args[0],
                           $args[1] ?? 'info',
                           $args[2] ?? NULL);
        }
        /* Si el primer argumento es un objeto, lo convierto en array y llamo a
         * logArray */
        elseif ($args[0] instanceof \stdClass) {
          $this->logArray($this->arrayCastRecursive($args[0]),
                          $args[1] ?? 'info',
                          $args[2] ?? NULL);
        }
      }
    }
  }

  /**
   * Genera un log formateado de un array.
   *
   * @param array $content
   *   Array con el contenido del log.
   * @param string $type
   *   Tipo de mensaje a almacenar. Permite los siguientes valores:
   *     - info (valor por defecto)
   *     - notice
   *     - error
   *     - warning
   *   En caso de que el valor del parámetro no sea válido, se toma como valor
   *   info.
   * @param string $category
   *   Se usa para sobrescribir la categoría al que pertenece el log, si no se
   *   especifica se usa el nombre del módulo.
   */
  private function logArray(array $content,
                            string $type = 'info',
                            string $category = NULL) {
    /* Valido el $type */
    $validate_type = $this->prepareType($type);

    /* Valido la categoría a usar */
    $use_category = $this->getModuleName();
    if ($category) {
      $use_category = $category;
    }

    /* Añado los valores por defecto */
    $user = \Drupal::currentUser();
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $log['user'] = [
      'id' => $user->id(),
      'name' => $user->getAccountName(),
    ];
    $log['backtrace'] = $backtrace[1] ?? [];
    $log['content'] = $content;

    /* Genero el log */
    \Drupal::logger($use_category)->{$validate_type}('<pre><code>' . print_r($log, TRUE) . '</code></pre>');
  }

  /**
   * Genera un log formateado de un string.
   *
   * @param string $content
   *   Cadena con el contenido del log.
   * @param string $type
   *   Tipo de mensaje a almacenar. Permite los siguientes valores:
   *     - info (valor por defecto)
   *     - notice
   *     - error
   *     - warning
   *   En caso de que el valor del parámetro no sea válido, se toma como valor
   *   info.
   * @param string $category
   *   Se usa para sobrescribir la categoría al que pertenece el log, si no se
   *   especifica se usa el nombre del módulo.
   *
   * @see logArray
   */
  private function logString(string $content,
                             string $type = 'info',
                             string $category = NULL) {
    /* Convierto $content en array y llamo a logArray */
    $this->logArray([$content], $type, $category);
  }

  /**
   * Genera un array recursivo a partir de un objeto.
   *
   * @param array|object $array
   *   Array o objeto a convertir.
   *
   * @return array
   *   Array convertido.
   */
  private function arrayCastRecursive($array): array {
    if (is_array($array)) {
      foreach ($array as $key => $value) {
        if (is_array($value)) {
          $array[$key] = $this->arrayCastRecursive($value);
        }
        if ($value instanceof \stdClass) {
          $array[$key] = $this->arrayCastRecursive((array) $value);
        }
      }
    }
    if ($array instanceof \stdClass) {
      return $this->arrayCastRecursive((array) $array);
    }
    return $array;
  }

  /**
   * Verifica que el tipo de mensaje proporcionado sea válido.
   *
   * @param string $type
   *   Tipo de mensaje. Permite los siguientes valores:
   *     - info (valor por defecto)
   *     - notice
   *     - error
   *     - warning
   *   En caso de que el valor del parámetro no sea válido, se toma como valor
   *   info.
   *
   * @return string
   *   Tipo de mensaje preparado para su uso.
   */
  private function prepareType(string $type) {
    /* Variables auxiliares */
    $valores_type_permitidos = [
      'info',
      'notice',
      'error',
      'warning',
    ];

    /* Pongo el segundo parámetro en minúsculas por si acaso */
    $type_lowercase = strtolower($type);

    /* Me aseguro de que el tipo sea válido */
    if (!in_array($type_lowercase, $valores_type_permitidos)) {
      $type_lowercase = 'info';
    }

    return $type_lowercase;
  }

  /**
   * Obtiene el nombre del módulo que llama a la función.
   *
   * @return string
   *   Nombre del módulo.
   */
  private function getModuleName(): string {
    return $this->moduleName;
  }

}
