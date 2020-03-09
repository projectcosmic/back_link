<?php

namespace Drupal\back_link;

/**
 * Provides an interface for HTTP 'Referer' header utilities.
 */
interface ReferrerInterface {

  /**
   * Checks if the current 'Referer' header matches any patterns.
   *
   * @param string $patterns
   *   A set of patterns separated by newlines.
   *
   * @return bool
   *   TRUE if the 'Referer' header is present and its path matches a pattern,
   *   FALSE otherwise.
   */
  public function matchReferrer($patterns);

  /**
   * Parses a 'Referer' header to a Url.
   *
   * @return \Drupal\Core\Url|null
   *   The Url object as internally routed URL, or NULL if the referrer does not
   *   match trusted host patterns (if set).
   */
  public function parseReferrer();

}
