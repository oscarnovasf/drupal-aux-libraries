<?php

namespace Drupal\module_template\lib\general;

// phpcs:ignoreFile
// @codingStandardsIgnoreStart

/**
 * Formateador de XML.
 *
 * Uso:
 *   $p = new XMLParser($xml);
 *   $p->getOutput();
 *
 * @see https://www.php.net/manual/en/function.xml-parse-into-struct.php
 */
class XmlParser {

  private $rawXML;

  private $parser = NULL;

  private $valueArray = [];
  private $keyArray = [];

  private $duplicateKeys = [];

  private $output = [];
  private $status;

  public function __construct($xml) {
    $this->rawXML = $xml;
    $this->parser = xml_parser_create();
    return $this->parse();
  }

  private function parse() {
    $parser = $this->parser;

    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    if (!xml_parse_into_struct($parser, $this->rawXML, $this->valueArray, $this->keyArray)) {
      $this->status = 'error: ' . xml_error_string(xml_get_error_code($parser)) . ' at line ' . xml_get_current_line_number($parser);
      return FALSE;
    }
    xml_parser_free($parser);

    $this->findDuplicateKeys();

    $stack = [];
    $increment = 0;

    foreach ($this->valueArray as $val) {
      if ($val['type'] == "open") {
        if (array_key_exists($val['tag'], $this->duplicateKeys)) {
          array_push($stack, $this->duplicateKeys[$val['tag']]);
          $this->duplicateKeys[$val['tag']]++;
        }
        else {
          array_push($stack, $val['tag']);
        }
      }
      elseif ($val['type'] == "close") {
        array_pop($stack);
        if (array_key_exists($val['tag'], $stack)) {
          $this->duplicateKeys[$val['tag']] = 0;
        }
      }
      elseif ($val['type'] == "complete") {
        if (array_key_exists($val['tag'], $this->duplicateKeys)) {
          array_push($stack, $this->duplicateKeys[$val['tag']]);
          $this->duplicateKeys[$val['tag']]++;
        }
        else {
          array_push($stack, $val['tag']);
        }
        $this->setArrayValue($this->output, $stack, $val['value']);
        array_pop($stack);
      }
      $increment++;
    }

    $this->status = 'success: xml was parsed';
    return TRUE;
  }

  private function findDuplicateKeys() {

    for ($i = 0; $i < count($this->valueArray); $i++) {
      if ($this->valueArray[$i]['type'] == "complete") {
        if ($i + 1 < count($this->valueArray)) {
          if ($this->valueArray[$i + 1]['tag'] == $this->valueArray[$i]['tag'] && $this->valueArray[$i + 1]['type'] == "complete") {
            $this->duplicateKeys[$this->valueArray[$i]['tag']] = 0;
          }
        }
      }

      if ($this->valueArray[$i]['type'] == "close") {
        if ($i + 1 < count($this->valueArray)) {
          if ($this->valueArray[$i + 1]['type'] == "open" && $this->valueArray[$i + 1]['tag'] == $this->valueArray[$i]['tag']) {
            $this->duplicateKeys[$this->valueArray[$i]['tag']] = 0;
          }
        }
      }
    }
  }

  private function setArrayValue(&$array, $stack, $value) {
    if ($stack) {
      $key = array_shift($stack);
      $this->setArrayValue($array[$key], $stack, $value);
      return $array;
    }
    else {
      $array = $value;
    }
  }

  public function getOutput() {
    return $this->output;
  }

  public function getStatus() {
    return $this->status;
  }

}
