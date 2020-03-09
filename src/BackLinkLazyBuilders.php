<?php

namespace Drupal\back_link;

use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;

/**
 * Defines a service for #lazy_builder callbacks.
 */
class BackLinkLazyBuilders implements TrustedCallbackInterface {

  use StringTranslationTrait;

  /**
   * Referer header parser.
   *
   * @var \Drupal\back_link\ReferrerInterface
   */
  protected $referrer;

  /**
   * Constructs a new BackLinkLazyBuilders object.
   *
   * @param \Drupal\back_link\ReferrerInterface $referrer
   *   Referer header parser.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation manager.
   */
  public function __construct(ReferrerInterface $referrer, TranslationInterface $string_translation) {
    $this->referrer = $referrer;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Lazy builder callback for a back link.
   *
   * @param string $fallback_url
   *   (optional) Fallback URL if not available from the referer HTTP header.
   *
   * @return array
   *   A renderable array containing the back link.
   */
  public function renderBackLink($fallback_url = '') {
    $build = [
      '#cache' => [
        'contexts' => [
          'headers:referer',
        ],
      ],
    ];

    /** @var \Drupal\Core\Url|null $back_url */
    $back_url = $this->referrer->parseReferrer();

    // Return empty build if no valid referer or fallback to link back to.
    if (!$back_url && !$fallback_url) {
      return $build;
    }

    $build['#theme_wrappers'] = ['container'];
    $build['link'] = [
      '#type' => 'link',
      '#title' => $this->t('Back'),
      '#url' => $back_url ?: Url::fromUserInput($fallback_url),
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['renderBackLink'];
  }

}
