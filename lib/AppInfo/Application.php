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

namespace OCA\Queue\AppInfo;

use Illuminate\Contracts\Container\Container;
use OC\Server;
use OCA\Queue\LaravelAdapter\LaravelContainer;
use OCA\Queue\Queue\Bus;
use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;

class Application extends App {
	public function __construct(array $urlParams = []) {
		parent::__construct('queue', $urlParams);

		$container = $this->getContainer();
		$container->registerAlias(Container::class, LaravelContainer::class);

		$container->registerService(Bus::class, function (IAppContainer $c) {
			/** @var Server $server */
			$server = $c->getServer();
			return new Bus($server->getGetRedisFactory(), $c->query(LaravelContainer::class));
		});
	}

	public function register() {
		$container = $this->getContainer();
		/** @var Server $server */
		$server = $container->getServer();

		$server->registerService('AsyncCommandBus', function () use ($container) {
			return $container->query(Bus::class);
		});
	}
}
