<?php

namespace Drupal\module_template\lib;

/**
 * @file
 * Mailing.php.
 */

use Drupal\node\Entity\Node;
use Drupal\Core\StringTranslation\StringTranslationTrait;

use Drupal\module_template\lib\general\ResponseFunctions;

/**
 * Gestiona todo lo relativo a los envíos de correos.
 *
 * Ej. de uso:
 *   $parametros = [];
 *   $mail_sender = new Mailing();
 *   $parametros['to'] = 'mail@ejemplo.com';
 *   $parametros['subject'] = t('Subject');
 *   $parametros['message'] = t('Message');
 *   $parametros['attach_nid'] = $node_id;
 *   $mail_sender->sendMail('con_generacion_pdf_adjunto', $parametros);
 */
class Mailing {

  use StringTranslationTrait;

  /**
   * Envía un mensaje a partir de los parámetros indicados.
   *
   * @param string $key
   *   Key para usar en el "hook_mail" o identificar el envío de correo.
   * @param array $parametros
   *   Múltiples valores:
   *   ['subject'] => Asunto del mensaje.
   *   ['message'] => Cuerpo del mensaje.
   *   ['to'] => Destinatario del mensaje.
   *   ['cc'] => Destinatario de una copia del mensaje (opcional).
   *   ['bcc'] => Destinatario de una copia oculta del mensaje (opcional).
   *   ['lang_code'] => Idioma del contenido (opcional).
   *   ['attach_nid'] => Identificador del nodo a adjuntar (opcional).
   *   ['attachments'] => Array con archivos adjuntos (opcional).
   *
   * @return Drupal\module_template\lib\general\ResponseFunctions
   *   Array.
   */
  public function sendMail(string $key, array $parametros) {
    /* Return Values */
    $return_value = new ResponseFunctions();

    /* Idioma por defecto */
    $default_langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();

    if ((!isset($parametros['lang_code'])) or (NULL == $parametros['lang_code'])) {
      $parametros['lang_code'] = $default_langcode;
    }

    /* Parámetros de configuración */
    $mail_manager = \Drupal::service('plugin.manager.mail');
    $module = 'module_template';
    $send = TRUE;

    /* Asunto y cuerpo del mensaje */
    $subject = $parametros['subject'];
    $message = $parametros['message'];

    /* Compruebo si se han pasado valores para CC o BCC */
    $params['Cc'] = $parametros['cc'] ?? NULL;
    $params['Bcc'] = $parametros['bcc'] ?? NULL;

    /* Modificaciones propias de cada $key usado */
    switch ($key) {

      /* Usado en: */
      case 'con_generacion_pdf_adjunto':
        /* Asigno el asunto y cuerpo a los parámetros del mensaje */
        $params['subject'] = $subject;
        $params['message'] = $message;

        /* Genero el pdf a partir del id del nodo */
        $params['attachments'] = $this->getPdfAttachment($parametros['attach_nid']);

        /* Mensaje de éxito si procede */
        $parametros['success_message'] = '';
        break;

      /* Usado en: */
      case 'con_adjuntos':
        /* Asigno el asunto, cuerpo y adjuntos a los parámetros del mensaje */
        $params['subject'] = $subject;
        $params['message'] = $message;
        $params['attachments'] = $parametros['attachments'];

        /* Mensaje de éxito si procede */
        $parametros['success_message'] = '';
        break;

      /* Usado en: */
      case 'sin_adjuntos':
      default:
        /* Asigno el asunto y cuerpo a los parámetros del mensaje */
        $params['subject'] = $subject;
        $params['message'] = $message;

        /* Mensaje de éxito si procede */
        $parametros['success_message'] = '';

    }

    /* Realizo el envío del mail */
    $resultado_envio = $mail_manager->mail(
      $module,
      $key,
      $parametros['to'],
      $parametros['lang_code'],
      $params,
      NULL,
      $send
    );

    /* Gestión de errores y retorno de la función */
    if ($resultado_envio['result']) {
      $return_value->setStatus(TRUE);
      $return_value->setResponse([
        'error_code' => '0',
        'message' => $parametros['success_message'],
        'data' => $resultado_envio,
      ]);

      /* INFO Mailing: Aquí van las funciones relacionadas con operaciones tras el envío del correo */

    }
    else {
      $return_value->setResponse([
        'error_code' => '-10',
        'message' => $this->t('Error sending message.'),
        'data' => '',
      ]);
    }

    return $return_value;
  }

  /* ***************************************************************************
   * MÉTODOS Y FUNCIONES PRIVADAS
   * ************************************************************************ */

  /**
   * Genera un PDF y lo almacena en el servidor.
   *
   * Esta función hace uso del módulo "Entity Print" para generar un PDF
   * a partir del id del nodo.
   *
   * @param int $id
   *   Identificador del nodo a convertir en PDF.
   * @param string $schema
   *   Se establece a 'public' o 'private'.
   *
   * @return array
   *   Array con los datos del archivo generado.
   */
  private function getPdfAttachment(int $id, string $schema = 'public') {
    /* Leo el nodo indicado */
    $node = Node::load($id);

    // Create the Print engine plugin.
    $print_engine = \Drupal::service('plugin.manager.entity_print.print_engine')->createSelectedInstance('pdf');
    $print_builder = \Drupal::service('entity_print.print_builder');

    $filename = 'node-' . $id . '.pdf';
    $filename = FALSE;

    // Guardo el archivo.
    $uri = $print_builder->savePrintable([$node], $print_engine, $schema, $filename, TRUE);

    $attachment = [];
    $attachment['filepath'] = $uri;
    $attachment['filename'] = basename($uri);
    $attachment['filemime'] = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $uri);

    return $attachment;
  }

}
