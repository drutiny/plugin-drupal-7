<?php

namespace Drutiny\Plugin\Drupal7\Audit;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;
use Drutiny\AuditResponse\AuditResponse;
use Drutiny\Annotation\Param;

/**
 * Content Owned By Drupal's Anonymous User
 * @Param(
 *  name = "uid",
 *  description = "UID to check content ownership against.",
 *  type = "integer"
 * )
*/
class ContentOwnedByUserID extends Audit {

  /**
   * @inheritdoc
   */
  public function audit(Sandbox $sandbox) {
    $uid = $sandbox->getParameter('uid', 0);
    $sandbox->setParameter('UID', $uid);

    $output = $sandbox->drush()->evaluate(function ($uid) {
      $query = new EntityFieldQuery();
      return count($query->entityCondition("entity_type", "node")->propertyCondition("uid", $uid)->execute()["node"]);
    }, ['uid' => $uid]);

    if (empty($output)) {
      return TRUE;
    }

    // Set the value for total nodes
    $sandbox->setParameter('totalnodes', $output);

    return Audit::FAIL;
  }

}
