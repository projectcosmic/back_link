<?php

namespace Drupal\Tests\back_link\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Url;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests the back-link extra node field.
 *
 * @group back_link
 */
class BackLinkRenderTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'back_link',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig('system');

    // Create test node bundle.
    NodeType::create(['type' => 'test'])
      ->setThirdPartySetting('back_link', 'fallback_url', '/user/login')
      ->save();

    $this->container
      ->get('entity_display.repository')
      ->getViewDisplay('node', 'test')
      ->setComponent('back_link')
      ->save();
  }

  /**
   * Tests the node back link links with referer values.
   *
   * @dataProvider referrerProvider()
   */
  public function testNodeBackLink($referrer, $expected_url) {
    $referrer_helper = $this->createMock('\Drupal\back_link\Referrer');
    $referrer_helper
      ->method('parseReferrer')
      ->willReturn($referrer ? Url::fromUri($referrer) : NULL);
    $this->container->set('back_link.referrer', $referrer_helper);

    $node = Node::create([
      'type' => 'test',
      'title' => $this->randomString(),
      'uid' => $this->createUser()->id(),
    ]);
    $node->save();

    $build = $this->entityTypeManager->getViewBuilder('node')->view($node);
    $this->render($build);

    $this->assertRaw(
      sprintf('<div><a href="%s">', $expected_url) . t('Back') . '</a></div>'
    );
  }

  /**
   * Provides test cases for ::testBackLink().
   */
  public function referrerProvider() {
    return [
      'Valid Referer value' => ['https://example.com', 'https://example.com'],
      'No Referer value' => [NULL, '/user/login'],
    ];
  }

}
