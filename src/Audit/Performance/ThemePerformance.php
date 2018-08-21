<?php

namespace Drutiny\Plugin\Drupal7\Audit\Performance;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Annotation\Param;
use Drutiny\Annotation\Token;

/**
 * Perform a basic performance review of theme code.
 *
 * @Token(
 *  name = "messages",
 *  description = "An array that maybe used by a policy in the outcome messaging.",
 *  type = "array",
 *  default = {}
 * )
 */
class ThemePerformance extends Audit {

  public function audit(Sandbox $sandbox) {
    $stat = $sandbox->drush(['format' => 'json'])->status();

    $root = $stat['root'];
    $command = sprintf(
      'find $(readlink -f %s) -type d -name themes -exec grep %s -RIEHn "(%s|->execute)" {} \;',
      $root,
      '--exclude-dir=.{svn,git} --exclude=\*.{css,js}',
      implode(
        '|',
        [
          'mysql_',
          'mysqli',
          'sqlite',
          'db_query',
          'db_fetch',
          'db_result',
          'pager_query',
          'db_set_active',
          'db_select',
          'db_insert',
          'db_update',
          'db_delete',
          'fetchAll',
          'fetchField',
          'fetchObject',
          'fetchAssoc',
          'countQuery',
        ]
      )
    );

    $output = $sandbox->exec($command);
    $data = explode("\n", trim($output));
    $sandbox->setParameter('messages', $data);

    if (sizeof($data)) {
      return FALSE;
    }
    return TRUE;
  }
}

?>
