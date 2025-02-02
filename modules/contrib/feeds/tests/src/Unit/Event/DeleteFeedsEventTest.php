<?php

namespace Drupal\Tests\feeds\Unit\Event;

use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;
use Drupal\feeds\Event\DeleteFeedsEvent;

/**
 * @coversDefaultClass \Drupal\feeds\Event\DeleteFeedsEvent
 * @group feeds
 */
class DeleteFeedsEventTest extends FeedsUnitTestCase {

  /**
   * @covers ::getFeeds
   */
  public function testGetFeeds() {
    $feeds = [$this->createMock('Drupal\feeds\FeedInterface')];
    $event = new DeleteFeedsEvent($feeds);

    $this->assertSame($feeds, $event->getFeeds());
  }

}
