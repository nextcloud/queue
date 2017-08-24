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

use Closure;
use Illuminate\Contracts\Cache\Repository;
use OCP\IMemcache;

class LaravelCache implements Repository {
	/** @var IMemcache */
	private $cache;

	/**
	 * @param IMemcache $cache
	 */
	public function __construct(IMemcache $cache) {
		$this->cache = $cache;
	}

	public function add($key, $value, $minutes) {
		return $this->cache->add($key, $value, $minutes * 60);
	}

	public function get($key, $default = null) {
		return $this->cache->get($key) ?: $default;
	}

	public function has($key) {
		return $this->cache->hasKey($key);
	}

	public function put($key, $value, $minutes) {
		return $this->cache->set($key, $value, $minutes * 60);
	}

	public function pull($key, $default = null) {
		$value = $this->cache->get($key) ?: $default;
		$this->cache->remove($key);
		return $value;
	}

	public function forget($key) {
		return $this->cache->remove($key);
	}

	public function decrement($key, $value = 1) {
		return $this->cache->dec($key, $value);
	}

	public function increment($key, $value = 1) {
		return $this->cache->inc($key, $value);
	}

	public function forever($key, $value) {
		return $this->cache->set($key, $value);
	}

	public function remember($key, $minutes, Closure $callback) {
		$value = $this->get($key);
		if (!$value) {
			$this->cache->set($key, $callback(), $minutes);
		}
		return $value;
	}

	public function rememberForever($key, Closure $callback) {
		$value = $this->get($key);
		if (!$value) {
			$this->cache->set($key, $callback());
		}
		return $value;
	}

	public function sear($key, Closure $callback) {
		return $this->rememberForever($callback, $callback);
	}
}