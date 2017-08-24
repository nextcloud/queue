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

namespace OCA\Queue\Tests\Queue;

use Illuminate\Container\Container;
use Illuminate\Queue\RedisQueue;
use OC\Command\LaravelBus;
use OC\RedisFactory;
use Test\Command\AsyncBusTest;

class Dispatcher implements \Illuminate\Contracts\Bus\Dispatcher {
	public function dispatch($command) {
		$this->dispatchNow($command);
	}

	public function dispatchNow($command, $handler = null) {
		$command->handle();
	}

	public function pipeThrough(array $pipes) {
	}

	public function getCommandHandler() {
		return null;
	}
}

class LaravelBusTest extends AsyncBusTest {
	/**
	 * @var RedisFactory
	 */
	private $redisFactory;


	public function setUp() {
		parent::setUp();

		$this->redisFactory = \OC::$server->getGetRedisFactory();

		if (!$this->redisFactory->isAvailable()) {
			$this->markTestSkipped('redis not available');
		}
	}

	protected function createBus() {
		return new LaravelBus($this->redisFactory);
	}

	protected function runJobs() {
		/** @var RedisQueue $queue */
		$queue = $this->invokePrivate($this->getBus(), 'queue');
		$container = new Container();
		$container->instance(\Illuminate\Contracts\Bus\Dispatcher::class, new Dispatcher());
		$queue->setContainer($container);

		while ($job = $queue->pop()) {
			$job->fire();
		}
	}
}