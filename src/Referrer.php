<?php

namespace Drupal\back_link;

use Drupal\Core\Site\Settings;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Utilities around the 'Referer' HTTP header of the current request.
 */
class Referrer implements ReferrerInterface {

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The settings array.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Creates a new Referrer.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack object.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   * @param \Drupal\Core\Site\Settings $settings
   *   The settings array.
   */
  public function __construct(RequestStack $request_stack, PathMatcherInterface $path_matcher, Settings $settings) {
    $this->requestStack = $request_stack;
    $this->pathMatcher = $path_matcher;
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function matchReferrer($patterns) {
    $url = $this->parseReferrer();
    return $url && $this->pathMatcher->matchPath($url->toString(), $patterns);
  }

  /**
   * {@inheritdoc}
   */
  public function parseReferrer() {
    $referrer = $this->requestStack
      ->getCurrentRequest()
      ->headers
      ->get('Referer');

    $parsed = parse_url($referrer) + ['host' => ''];
    $patterns = $this->getInternalHostPatterns();

    // If host is in the referrer, use trusted_host_patterns to determine if
    // a referrer URL is internal.
    if ($parsed['host'] && !empty($patterns)) {
      $match = FALSE;

      foreach ($patterns as $pattern) {
        if ($match = preg_match("#$pattern#i", $parsed['host'])) {
          break;
        }
      }

      // Did not match any host patterns; bail.
      if (!$match) {
        return NULL;
      }
    }

    if (!$parsed['path']) {
      return NULL;
    }

    return Url::fromUserInput($parsed['path'], UrlHelper::parse($referrer));
  }

  /**
   * Gets trusted host regex patterns.
   *
   * @return array
   *   The regex patterns.
   */
  protected function getInternalHostPatterns() {
    return $this->settings->get('trusted_host_patterns', []);
  }

}
