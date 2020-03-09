<?php

namespace Drupal\Tests\back_link\Kernel;

use Drupal\back_link\Referrer;

/**
 * Referrer test helper override.
 */
class TestReferrer extends Referrer {

  /**
   * {@inheritdoc}
   */
  protected function getInternalHostPatterns() {
    return ['^example\.com$'];
  }

}
