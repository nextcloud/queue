<?php
/**
 * @copyright Copyright (c) 2017 Robin Appelman <robin@icewind.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Queue\Queue;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\RedisQueue;
use Illuminate\Queue\Worker;
use OC\Command\AsyncBus;
use OCA\Queue\LaravelAdapter\LaravelExceptionHandler;
use OCA\Queue\LaravelAdapter\LaravelQueueManager;
use OCA\Queue\LaravelAdapter\LaravelRedisFactory;
use OC\RedisFactory;
use OCP\Command\ICommand;

class Bus extends AsyncBus {
	/** @var Queue */
	private $queue;

	/** @var Container */
	private $container;

	public function __construct(RedisFactory $redisFactory, Container $container) {
		$this->queue = new RedisQueue(
			new LaravelRedisFactory($redisFactory)
		);
		$this->container = $container;
		$this->queue->setContainer($container);
	}

	protected function queueCommand($command) {
		$this->queue->push($this->buildCommand($command));
	}

	private function buildCommand($command) {
		if ($command instanceof ICommand) {
			return $command;
		} else if ($command instanceof \Closure) {
			return new ClosureCommand($command);
		} else if (is_callable($command)) {
			return new CallableCommand($command);
		} else {
			return $command;
		}
	}

	public function getWorker() {
		return new Worker(
			new LaravelQueueManager($this->queue),
			new Dispatcher($this->container),
			new LaravelExceptionHandler()
		);
	}
}