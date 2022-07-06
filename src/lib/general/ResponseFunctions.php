<?php

namespace Drupal\module_template\lib\general;

use Drupal\Core\Cache\CacheableJsonResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Funciones para ser usadas como retorno de otras funciones.
 */
class ResponseFunctions {

  /**
   * Propiedad status.
   *
   * Indica si la función llamada ha tenido algún problema o no.
   *
   * @var bool
   *   TRUE indica que todo ha ido bien
   *   FALSE indica que ha ocurrido un error.
   */
  protected $status;

  /**
   * Propiedad error.
   *
   * Almacena el posible texto de error.
   *
   * @var string|null
   *   Texto del error.
   */
  protected $error;

  /**
   * Propiedad errorCode.
   *
   * Almacena el posible número de error.
   *
   * @var int|null
   *   Código del error.
   */
  protected $errorCode;

  /**
   * Propiedad response.
   *
   * Array que contiene los mensajes de errores en caso que $status = FALSE
   * o los datos que deseamos devolver.
   *
   * Se trata de una array con 'clave' => 'valor'.
   *
   * @var array
   *   La estructura depende de los que queremos devolver y del
   *   valor de $status
   */
  protected $response;

  /**
   * Constructor de la clase.
   *
   * Establece las propiedades usadas por defecto.
   */
  public function __construct() {
    $this->setStatus(FALSE);
    $this->error = NULL;
    $this->errorCode = NULL;
  }

  /**
   * Establece el valor de "status".
   *
   * @param bool $status
   *   TRUE or FALSE.
   *   Si no se indica este parámetro se inicializa a FALSE.
   */
  public function setStatus(bool $status = FALSE) {
    $this->status = $status;
  }

  /**
   * Devuelve el valor de "status".
   *
   * @return bool
   *   TRUE o FALSE.
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * Establece el valor de "error".
   *
   * @param string $error
   *   Cadena con el texto del error.
   */
  public function setError(string $error) {
    $this->error = $error;
  }

  /**
   * Devuelve el valor de "error".
   *
   * @return string|null
   *   Cadena con el texto del error.
   *   NULL si el error no ha sido definido.
   */
  public function getError() {
    return $this->error;
  }

  /**
   * Establece el valor de "errorCode".
   *
   * @param int $error
   *   Código del error.
   */
  public function setErrorCode(int $error) {
    $this->errorCode = $error;
  }

  /**
   * Devuelve el valor de "errorCode".
   *
   * @return int|null
   *   Código del error.
   *   NULL si el error no ha sido definido.
   */
  public function getErrorCode() {
    return $this->errorCode;
  }

  /**
   * Establece el valor de "response".
   *
   * @param array $response_data
   *   ARRAY con los valores a establecer.
   */
  public function setResponse(array $response_data) {
    $this->response = $response_data;
  }

  /**
   * Devuelve un valor del array response.
   *
   * Si no se pasa el parámetro devuelve todos los valores.
   *
   * @param string $key
   *   Clave del array que queremos obtener.
   *
   * @return mixed|null
   *   Si $key no está definido devuelve todos los valores.
   *   Si $key está definido y no existe devuelve NULL.
   *   Si $key está definido y existe devuelve el valor almacenado.
   */
  public function getResponse(string $key = NULL) {
    if ($key) {
      return $this->response[$key] ?? NULL;
    }
    return $this->response;
  }

  /**
   * Devuelve todas las propiedades en formato Json.
   *
   * @return Drupal\Core\Cache\CacheableJsonResponse
   *   Json.
   */
  public function getJson() {
    $response = [];
    $response['status'] = $this->status;
    $response['error']['code'] = $this->errorCode;
    $response['error']['message'] = $this->error;
    $response['response'] = $this->response;

    $returnValue = new CacheableJsonResponse($response);
    return $returnValue;
  }

  /**
   * Devuelve todas las propiedades en formato Json.
   *
   * @return Symfony\Component\HttpFoundation\JsonResponse
   *   Json.
   */
  public function getJsonNoCacheable() {
    $response = [];
    $response['status'] = $this->status;
    $response['error']['code'] = $this->errorCode;
    $response['error']['message'] = $this->error;
    $response['response'] = $this->response;

    $returnValue = new JsonResponse($response);
    return $returnValue;
  }

}
