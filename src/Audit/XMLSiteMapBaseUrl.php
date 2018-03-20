<?php

namespace Drutiny\Plugin\Drupal7\Audit;

use Drutiny\Audit\Drupal\ModuleEnabled;
use Drutiny\Sandbox\Sandbox;

/**
 *
 */
class XMLSiteMapBaseUrl extends ModuleEnabled {

  /**
   *
   */
  public function audit(Sandbox $sandbox) {
    // Use the audit from ModuleEnable to validate check.
    $sandbox->setParameter('module', 'xmlsitemap');
    if (!parent::audit($sandbox)) {
      return NULL;
    }

    // This defaults to $GLOBALS['base_url'] which is bad.
    $variables = $sandbox->drush(['format' => 'json'])->variableGet('xmlsitemap_base_url');
    if (empty($variables['xmlsitemap_base_url'])) {
      $sandbox->setParameter('base_url', '[empty]');
      return FALSE;
    }

    $base_url = $variables['xmlsitemap_base_url'];

    $sandbox->setParameter('base_url', $base_url);
    $pattern = $sandbox->getParameter('pattern', '^https?://.+$');
    if (!preg_match("#${pattern}#", $base_url)) {
      return FALSE;
    }

    return TRUE;
  }
}
