<?php

namespace Drutiny\Plugin\Drupal7\Audit;

use Drutiny\Audit\Drupal\ModuleEnabled;
use Drutiny\Sandbox\Sandbox;

/**
 * @Drutiny\Annotation\CheckInfo(
 *  title = "XML sitemap base URL",
 *  description = "The XML sitemap module adds a sitemap on the URL <code>/sitemap.xml</code>. If not properly configured, the sitemap will point to an incorrect or possibly broken site.",
 *  remediation = "Set the variable <code>xmlsitemap_base_url</code> to be the production www URL. e.g. <code>'https://www.govcms.gov.au'</code>. Note there is no trailing slash.",
 *  not_available = "XML sitemap module is disabled.",
 *  success = "XML sitemap base URL is set correctly, currently <code>:base_url</code>.",
 *  failure = "XML sitemap base URL is not correct, currently it is <code>:base_url</code>.",
 *  exception = "Could not determine XML sitemap base URL setting.",
 * )
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
