<?php

declare(strict_types = 1);

namespace Drupal\module_template\Element;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\SortArray;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;

/**
 * Permite generar campos múltiples.
 *
 * Propiedades:
 * - #cardinality: número de elementos. Puede ser un número positivo o
 *   MultiValue::CARDINALITY_UNLIMITED para ilimitado. El valor por defecto es
 *   ilimitado.
 * - #add_more_label: etiqueta para el botón "add more". El valor por defecto es
 *   "Add another item".
 *
 * Ejemplo simple:
 * @code
 * $form['job_titles'] = [
 *   '#type' => 'multi-value',
 *   '#title' => $this->t('Job titles'),
 *   'title' => [
 *     '#type' => 'textfield',
 *     '#title' => $this->t('Job title'),
 *     '#title_display' => 'invisible',
 *   ],
 * ];
 * @endcode
 *
 * Ejemplo para campo compuesto:
 * @code
 * $form['contacts'] = [
 *   '#type' => 'multi-value',
 *   '#title' => $this->t('Contacts'),
 *   '#cardinality' => 3,
 *   'name' => [
 *     '#type' => 'textfield',
 *     '#title' => $this->t('Name'),
 *   ],
 *   'mail' => [
 *     '#type' => 'email',
 *     '#title' => $this->t('E-mail'),
 *   ],
 * ];
 * @endCode
 *
 * Ejemplo de cómo establecer valores por defecto:
 * @code
 * $form['contacts'] = [
 *   '#type' => 'multi-value',
 *   '#default_value' => [
 *     0 => ['name' => 'Bob', 'mail' => 'bob@example.com'],
 *     1 => ['name' => 'Ted', 'mail' => 'ted@example.com'],
 *   ],
 *   ...
 * ];
 * @endCode
 *
 * Ejemplo de cómo establecer campos obligatorios:
 * @code
 * $form['contacts'] = [
 *   '#type' => 'multi-value',
 *   '#title' => $this->t('Contacts'),
 *   'name' => [
 *     '#type' => 'textfield',
 *     '#title' => $this->t('Name'),
 *     '#required' => TRUE,
 *   ],
 *   'mail' => [
 *     '#type' => 'email',
 *     '#title' => $this->t('E-mail'),
 *   ],
 * ];
 * @endCode
 *
 * @FormElement("multi-value")
 *
 * @todo No permite anidar contenedores dentro del multi-value. Error al asignar
 * los valores por defecto.
 * @todo Incluir botón para eliminar elemento.
 *
 * @see https://www.drupal.org/project/multivalue_form_element
 */
class MultiValue extends FormElement {

  /**
   * Número de elementos ilimitado.
   */
  const CARDINALITY_UNLIMITED = -1;

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#theme' => 'field_multiple_value_form',
      '#cardinality_multiple' => TRUE,
      '#description' => NULL,
      '#cardinality' => self::CARDINALITY_UNLIMITED,
      '#add_more_label' => $this->t('Add another item'),
      '#process' => [
        [$class, 'processMultiValue'],
        [$class, 'processAjaxForm'],
      ],
      '#element_validate' => [
        [$class, 'validateMultiValue'],
      ],
    ];
  }

  /**
   * Procesa el elemento.
   *
   * @param array $element
   *   Elemento a procesar.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Objeto con los valores del formulario.
   * @param array $form
   *   Formulario.
   *
   * @return array
   *   Elemento procesado.
   */
  public static function processMultiValue(array &$element, FormStateInterface $form_state, array &$form): array {
    $element_name = end($element['#array_parents']);
    $parents = $element['#parents'];
    $cardinality = $element['#cardinality'];

    $element['#tree'] = TRUE;
    $element['#field_name'] = $element_name;

    $element_state = static::getElementState($parents, $element_name, $form_state);
    if ($element_state === NULL) {
      $element_state = [
        'items_count' => count($element['#default_value'] ?? []),
      ];
      static::setElementState($parents, $element_name, $form_state, $element_state);
    }

    // Número de elementos a mostrar.
    $max = $cardinality === self::CARDINALITY_UNLIMITED ? $element_state['items_count'] : ($cardinality - 1);

    // Obtengo los elementos que se repiten.
    $children = [];
    foreach (Element::children($element) as $child) {
      $children[$child] = $element[$child];
      unset($element[$child]);
    }

    $value = is_array($element['#value']) ? $element['#value'] : [];
    $value = array_values($value);

    /* INFO: Si se desea que aparezca un elemento vacío por defecto basta con
     * cambiar la condición por $i <= $max */
    for ($i = 0; $i < $max; $i++) {
      $element[$i] = $children;

      if (isset($value[$i])) {
        static::setDefaultValue($element[$i], $value[$i]);
      }

      static::setRequiredProperty($element[$i], $i, $element['#required']);

      $element[$i]['_weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for row @number', ['@number' => $i + 1]),
        '#title_display' => 'invisible',
        '#default_value' => $i,
        '#weight' => 100,
      ];
    }

    if ($cardinality === self::CARDINALITY_UNLIMITED && !$form_state->isProgrammed()) {
      $id_prefix = implode('-', $parents);
      $wrapper_id = Html::getUniqueId($id_prefix . '-add-more-wrapper');
      $element['#prefix'] = '<div id="' . $wrapper_id . '">';
      $element['#suffix'] = '</div>';
      $element['add_more'] = [
        '#type' => 'submit',
        '#name' => strtr($id_prefix, '-', '_') . '_add_more',
        '#value' => $element['#add_more_label'],
        '#attributes' => ['class' => ['multivalue-add-more-submit']],
        '#limit_validation_errors' => [$element['#array_parents']],
        '#submit' => [[static::class, 'addMoreSubmit']],
        '#ajax' => [
          'callback' => [static::class, 'addMoreAjax'],
          'wrapper' => $wrapper_id,
          'effect' => 'fade',
        ],
      ];
    }

    return $element;
  }

  /**
   * Validación del elemento.
   *
   * Se usa para limpiar y ordenar los valores enviados en el formulario.
   *
   * @param array $element
   *   Elemento a procesar.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Objeto con los valores del formulario.
   * @param array $form
   *   Formulario.
   */
  public static function validateMultiValue(array &$element, FormStateInterface $form_state, array &$form): void {
    $input_exists = FALSE;
    $values = NestedArray::getValue($form_state->getValues(), $element['#parents'], $input_exists);

    if (!$input_exists) {
      return;
    }

    /* Elimina el 'value' del botón 'add more'. */
    unset($values['add_more']);

    /* Ordena los valores según el valor de weight. */
    usort($values, function ($a, $b) {
      return SortArray::sortByKeyInt($a, $b, '_weight');
    });

    foreach ($values as $delta => &$delta_values) {
      /* Elimina el valor de weight del dato enviado */
      unset($delta_values['_weight']);

      /* Comprueba si todos los elementos están vacíos */
      $is_empty_delta = array_reduce($delta_values, function (bool $carry, $value): bool {
        if (is_array($value)) {
          return $carry && empty(array_filter($value));
        }
        else {
          return $carry && ($value === NULL || $value === '');
        }
      }, TRUE);

      /* Si todos están vacíos lo elimino */
      if ($is_empty_delta) {
        unset($values[$delta]);
      }
    }

    /* Reordeno el array */
    $values = array_values($values);

    /* Retorno el valor al formulario */
    $form_state->setValueForElement($element, $values);
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input !== FALSE) {
      return $input;
    }

    $value = [];
    $element += ['#default_value' => []];

    $children_keys = Element::children($element, FALSE);
    $first_child = reset($children_keys);
    $children_count = count($children_keys);

    foreach ($element['#default_value'] as $delta => $default_value) {
      if (!is_numeric($delta)) {
        continue;
      }

      if ($children_count === 1 && !is_array($default_value)) {
        $value[$delta] = [$first_child => $default_value];
      }
      else {
        $value[$delta] = $default_value;
      }
    }

    return $value;
  }

  /**
   * Submit del botón 'add more' vía AJAX..
   *
   * @param array $form
   *   Formulario.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Objeto con los valores del formulario.
   *
   * @see \Drupal\Core\Field\WidgetBase::addMoreSubmit()
   */
  public static function addMoreSubmit(array $form, FormStateInterface $form_state): void {
    $button = $form_state->getTriggeringElement();

    /* Obtengo el contenedor del widget (uno arriba). */
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -1));
    $element_name = $element['#field_name'];
    $parents = $element['#parents'];

    /* Incremento el número de items. */
    $element_state = static::getElementState($parents, $element_name, $form_state);
    $element_state['items_count']++;
    static::setElementState($parents, $element_name, $form_state, $element_state);

    $form_state->setRebuild();
  }

  /**
   * Ajax callback para el botón 'add more'.
   *
   * @param array $form
   *   Formulario.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Objeto con los valores del formulario.
   *
   * @return array|null
   *   El elemento.
   *
   * @see \Drupal\Core\Field\WidgetBase::addMoreAjax()
   */
  public static function addMoreAjax(array $form, FormStateInterface $form_state): ?array {
    $button = $form_state->getTriggeringElement();

    /* Obtengo el contenedor del widget (uno arriba). */
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -1));

    /* Compruebo que puedo añadir más elementos. */
    if ($element['#cardinality'] != FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
      return NULL;
    }

    return $element;
  }

  /**
   * Establece el valor por defecto de todos los elementos.
   *
   * @param array $elements
   *   Array con todos los elementos hijo.
   * @param array $value
   *   Array de valores. Cada clave se corresponde al nombre del elemento hijo.
   */
  public static function setDefaultValue(array &$elements, array $value): void {
    /* TODO: Comprobar si tiene más niveles de hijos */
    foreach (Element::children($elements, FALSE) as $child) {
      if (isset($value[$child])) {
        $elements[$child]['#default_value'] = $value[$child];
      }
    }
  }

  /**
   * Establece la obligatoriedad de cada elemento.
   *
   * @param array $elements
   *   Array con todos los elementos hijo.
   * @param int $delta
   *   Identificador del elemento procesado.
   * @param bool $required
   *   Indica si el elemento es obligatorio o no.
   */
  protected static function setRequiredProperty(array &$elements, int $delta, bool $required): void {
    if ($delta === 0 && $required) {

      foreach ($elements as $element) {
        if (isset($element['#required']) && $element['#required'] === TRUE) {
          return;
        }
      }

      foreach ($elements as &$element) {
        $element['#required'] = TRUE;
      }

      return;
    }

    foreach ($elements as &$element) {
      $element['#required'] = FALSE;
    }
  }

  /**
   * Devuelve la información procesada sobre el elemento en $form_state.
   *
   * Este método, al ser estático, sólo puede ser usado en callbacks estáticos
   * del Form API.
   *
   * @param array $parents
   *   Array con los elementos padre del formulario.
   * @param string $element_name
   *   Nombre del elemento.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Objeto con los valores del formulario.
   *
   * @return array
   *   Array con la estructura key/value:
   *   - items_count: Número de sub-elementos del elemento.
   *
   * @see \Drupal\Core\Field\WidgetBase::getWidgetState()
   */
  public static function getElementState(array $parents, string $element_name, FormStateInterface $form_state): ?array {
    return NestedArray::getValue($form_state->getStorage(), static::getElementStateParents($parents, $element_name));
  }

  /**
   * Almacena la información procesada del elemento en $form_state.
   *
   * Este método, al ser estático, sólo puede ser usado en callbacks estáticos
   * del Form API.
   *
   * @param array $parents
   *   Array con los elementos padre del formulario.
   * @param string $element_name
   *   Nombre del elemento.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Objeto con los valores del formulario.
   * @param array $field_state
   *   Array con los datos a almacenar. Ver getElementState() para la estructura
   *   y contenido del array.
   *
   * @see \Drupal\Core\Field\WidgetBase::setWidgetState()
   */
  public static function setElementState(array $parents, string $element_name, FormStateInterface $form_state, array $field_state): void {
    NestedArray::setValue($form_state->getStorage(), static::getElementStateParents($parents, $element_name), $field_state);
  }

  /**
   * Obtiene la localización de la información procesada sin usar $form_state.
   *
   * @param array $parents
   *   Array con los elementos padre del elemento.
   * @param string $element_name
   *   Nombre del elemento.
   *
   * @return array
   *   Localización de la información procesada sin usar $form_state.
   *
   * @see \Drupal\Core\Field\WidgetBase::getWidgetStateParents()
   */
  protected static function getElementStateParents(array $parents, string $element_name): array {
    return array_merge(['multivalue_form_element_storage', '#parents'],
      $parents,
      ['#elements', $element_name],
    );
  }

}
