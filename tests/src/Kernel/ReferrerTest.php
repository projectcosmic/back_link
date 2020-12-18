<?php

namespace Drupal\Tests\back_link\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\back_link\Referrer
 * @group back_link
 */
class ReferrerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'back_link',
    'system',
  ];

  /**
   * Make a request with the provided Referer header.
   *
   * @param string $referrer
   *   The Referer HTTP header value.
   */
  protected function makeReferrerRequest($referrer) {
    $request = Request::create('/');
    $request->headers->set('Referer', $referrer);
    \Drupal::requestStack()->pop();
    \Drupal::requestStack()->push($request);
  }

  /**
   * @covers ::parseReferrer
   */
  public function testParseReferrer() {
    $referrer = $this->container->get('back_link.referrer');

    $this->makeReferrerRequest('https://foo.bar/test/path');
    $this->assertEquals(
      '/test/path',
      $referrer->parseReferrer()->toString(),
      'Any referrer host should give a URL object if no trusted hosts set.'
    );

    $this->makeReferrerRequest('https://cosmic.org.uk/test/path?a=b');
    $this->assertEquals(
      '/test/path?a=b',
      $referrer->parseReferrer()->toString(),
      'Any referrer host should give a URL object if no trusted hosts set.'
    );

    $this->makeReferrerRequest('https://cosmic.org.uk');
    $this->assertNull($referrer->parseReferrer(), 'Null path should return null.');

    $referrer_with_trusted = new TestReferrer(
      $this->container->get('request_stack'),
      $this->container->get('path.matcher'),
      $this->container->get('settings')
    );

    $this->makeReferrerRequest('https://foo.bar/test/path');
    $this->assertNull($referrer_with_trusted->parseReferrer(), 'Non-trusted host should return null.');

    $this->makeReferrerRequest('https://example.com/test/path?a=b');
    $this->assertEquals(
      '/test/path?a=b',
      $referrer_with_trusted->parseReferrer()->toString(),
      'Trusted host should return a correct URL object.'
    );
  }

  /**
   * @covers ::matchReferrer
   *
   * @dataProvider getMatchReferrerData
   */
  public function testMatchReferrer($referrer, $pattern_sets) {
    $this->makeReferrerRequest($referrer);
    $utility = $this->container->get('back_link.referrer');

    foreach ($pattern_sets as $patterns => $expected) {
      $this->assertEquals($expected, $utility->matchReferrer($patterns));
    }
  }

  /**
   * Provides test cases for testMatchReferrer().
   */
  public function getMatchReferrerData() {
    return [
      'No referrer' => [
        NULL,
        [
          '/stub' => FALSE,
          '' => FALSE,
        ],
      ],
      'Root path' => [
        'http://example.com/',
        [
          '/stub' => FALSE,
          '/' => TRUE,
          "/stub\n/" => TRUE,
        ],
      ],
      'A path' => [
        'http://example.com/a/b',
        [
          '/a/b' => TRUE,
          '/a/*' => TRUE,
        ],
      ],
    ];
  }

}
