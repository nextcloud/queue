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

namespace OCA\Queue\LaravelAdapter;


use Illuminate\Cache\CacheManager;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Support\Fluent;
use OCP\ICacheFactory;

class LaravelContainer extends Container {
	public function __construct(ICacheFactory $cacheFactory) {
		$this['config'] = new Fluent();
		(new EventServiceProvider($this))->register();
		(new CacheServiceProvider($this))->register();
		/** @var CacheManager $cacheManager */
		$cacheManager = $this['cache'];
		$cacheManager->extend('nc', function () use ($cacheFactory) {
			return new LaravelCache($cacheFactory->create('larvel'));
		});
		$this['config']['cache.default'] = 'nc';
		$this['config']['cache.stores.nc'] = ['driver' => 'nc'];
		$this->instance(\Illuminate\Contracts\Bus\Dispatcher::class, new LaravelBusDispatcher());
	}

	public function isDownForMaintenance() {
		return false;
	}
}