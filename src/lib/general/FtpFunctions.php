<?php

namespace Drupal\module_template\lib\general;

/**
 * @file
 * Librería FtpFunctions.
 */

/**
 * Funciones para gestionar archivos y directorios a través de FTP.
 */
class FtpFunctions {

  /**
   * Descarga un archivo por FTP.
   *
   * @param string $ftp_server
   *   Dirección del servidor.
   * @param array $params
   *   Parámetros de conexión:
   *   - username: Nombre de usuario.
   *   - password: Contraseña del usuario.
   *   - local_file: Nombre que daremos al archivo descargado (con ruta).
   *   - remote_file: Nombre del fichero remoto (con ruta).
   *
   * @return bool
   *   TRUE si se ha podido descargar el archivo.
   */
  public static function getFileFromFtp(string $ftp_server, array $params) {
    /* Variables */
    $ftp_user_name = $params['username'];
    $ftp_user_pass = $params['password'];

    $local_file = $params['local_file'];
    $remote_file = $params['remote_file'];

    /* Establecer una conexión básica. */
    $conn_id = ftp_connect($ftp_server);

    /* Iniciar sesión con nombre de usuario y contraseña. */
    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

    if ($login_result) {
      /* Intenta descargar $remote_file y guardarlo en $local_file. */
      if (ftp_get($conn_id, $local_file, $remote_file, FTP_BINARY)) {
        /* Cerrar la conexión ftp. */
        ftp_close($conn_id);

        return TRUE;
      }
      else {
        /* Cerrar la conexión ftp. */
        ftp_close($conn_id);

        return FALSE;
      }

      /* Cerrar la conexión ftp. */
      ftp_close($conn_id);
    }
    else {
      /* Cerrar la conexión ftp. */
      ftp_close($conn_id);

      return FALSE;
    }
  }

  /**
   * Obtiene el contenido de un archivo por FTP.
   *
   * @param string $ftp_server
   *   Dirección del servidor.
   * @param array $params
   *   Parámetros de conexión:
   *   - username: Nombre de usuario.
   *   - password: Contraseña del usuario.
   *   - remote_file: Nombre del fichero remoto (con ruta).
   *
   * @return string
   *   Cadena con el contenido del archivo.
   */
  public static function getFileContentFromFtp(string $ftp_server, array $params) {
    $contents = file_get_contents('ftp://' . $params['username'] . ':' . $params['password'] . '@' . $ftp_server . $params['remote_file']);

    return $contents;
  }

}
