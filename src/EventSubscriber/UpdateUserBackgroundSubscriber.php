<?php

namespace Drupal\unl_user\EventSubscriber;

use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\Queue\QueueWorkerInterface;
use Drupal\Core\Queue\SuspendQueueException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Update UNL user data during request shutdown after a user logs in.
 */
class UpdateUserBackgroundSubscriber implements EventSubscriberInterface {

  protected bool $shouldRun = FALSE;

  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::TERMINATE => ['onTerminate', 100],
    ];
  }

  public function queueUpdate($account) {
    $queue = \Drupal::queue('cron_unl_user_update_user_data');
    $item = new \stdClass();
    $item->uid = $account->id();
    $queue->createItem($item);

    $this->shouldRun = TRUE;
  }

  public function onTerminate(TerminateEvent $event): void {
    if (!$this->shouldRun) {
      return;
    }

    $queue_name = 'cron_unl_user_update_user_data';

    /** @var QueueInterface $queue */
    $queue = \Drupal::queue($queue_name);

    /** @var QueueWorkerInterface $worker */
    $worker = \Drupal::service('plugin.manager.queue_worker')->createInstance($queue_name);

    $lease_time = 60;
    $end = time() + 30; // Hard safety limit to avoid runaway in shutdown.

    while (time() < $end && $item = $queue->claimItem($lease_time)) {
      try {
        $worker->processItem($item->data);
        $queue->deleteItem($item);
      }
      catch (SuspendQueueException $e) {
        $queue->releaseItem($item);
        break;
      }
      catch (\Exception $e) {
        \Drupal::logger('unl_user')->error($e->getMessage());
        $queue->releaseItem($item);
      }
    }
  }

}
