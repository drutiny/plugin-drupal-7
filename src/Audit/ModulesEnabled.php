<?php

namespace Drutiny\Plugin\Drupal7\Audit;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;
use Drutiny\RemediableInterface;

/**
 * Generic modules are enabled check.
 */
class ModulesEnabled extends Audit implements RemediableInterface {

  /**
   * @inheritdoc
   */
  public function audit(Sandbox $sandbox) {
    $modules = $sandbox->getParameter('modules');
    if (empty($modules)) {
      return TRUE;
    }

    $notEnabled = [];
    foreach ($modules as $moduleName) {
      try {
        if (!$sandbox->drush()->moduleEnabled($moduleName)) {
          throw new \Exception($moduleName);
        }
      }
      catch (\Exception $e) {
        $notEnabled[] = $moduleName;
      }
    }
    if (!empty($notEnabled)) {
      $sandbox->setParameter('notEnabled', $notEnabled);
      return FALSE;
    }
    // Seems like the best way to comma separate things.
    else {
      $sandbox->setParameter('enabled', '`' . implode('`, `', $modules) . '`');
    }

    return TRUE;
  }

  /**
   * @inheritdoc
   */
  public function remediate(Sandbox $sandbox) {
    $modules = $sandbox->getParameter('modules');
    $sandbox->drush()->en(implode(' ', $modules), '-y');
    return $this->check($sandbox);
  }

}
