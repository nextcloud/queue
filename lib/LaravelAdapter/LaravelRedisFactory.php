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

use Illuminate\Contracts\Redis\Factory;
use Illuminate\Redis\Connections\PhpRedisConnection;
use OC\RedisFactory;

/**
 * Dummy laravel redis factory that wraps the existing redis factory
 */
class LaravelRedisFactory implements Factory {
	/** @var RedisFactory */
	private $factory;

	/** @var PhpRedisConnection|null */
	private $redis = null;

	/**
	 * LaravelRedisFactory constructor.
	 *
	 * @param RedisFactory $factory
	 */
	public function __construct($factory) {
		$this->factory = $factory;
	}

	public function connection($name = null) {
		if (is_null($this->redis)) {
			$this->redis = new PhpRedisConnection($this->factory->getInstance());
		}
		return $this->redis;
	}
}