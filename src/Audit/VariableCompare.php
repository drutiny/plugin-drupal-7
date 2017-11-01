<?php

namespace Drutiny\Plugin\Drupal7\Audit;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Driver\DrushFormatException;

/**
 * Check a configuration is set correctly.
 */
class VariableCompare extends Audit {

  const NO_VARIABLE = 'No matching variable found.';

  /**
   * @inheritDoc
   */
  public function audit(Sandbox $sandbox) {
    $key = $sandbox->getParameter('key');
    $value = $sandbox->getParameter('value');

    try {
      $vars = $sandbox->drush([
        'format' => 'json'
        ])->variableGet($key);
    }
    catch (DrushFormatException $e) {
      $sandbox->setParameter('exception', $e->getMessage());
      // If Drush could not find the variable and $value is Falsey and the
      // comparison is equals then we can still returned a successful outcome.
      if (strpos($e->getOutput(), self::NO_VARIABLE) !== FALSE && $value == FALSE && $sandbox->getParameter('comp_type', '==') == '==') {
        return TRUE;
      }
      return FALSE;
    }
    catch (\Exception $e) {
      $sandbox->setParameter('exception', $e->getMessage());
      return FALSE;
    }

    $reading = $vars[$key];

    $sandbox->setParameter('reading', $reading);

    $comp_type = $sandbox->getParameter('comp_type', '==');
    $sandbox->logger()->info('Comparative config values: ' . var_export([
      'reading' => $reading,
      'value' => $value,
      'expression' => 'reading ' . $comp_type . ' value',
    ], TRUE));

    switch ($comp_type) {
      case 'lt':
      case '<':
        return $reading < $value;
      case 'gt':
      case '>':
        return $reading > $value;
      case 'lte':
      case '<=':
        return $reading <= $value;
      case 'gte':
      case '>=':
        return $reading >= $value;
      case 'ne':
      case '!=':
        return $reading != $value;
      case 'nie':
      case '!==':
        return $reading !== $value;
      case 'matches':
      case '~':
        return strpos($reading, $value) !== FALSE;
      case 'identical':
      case '===':
        return $value === $reading;
      case 'equal':
      case '==':
      default:
        return $value == $reading;
    }
  }

}
