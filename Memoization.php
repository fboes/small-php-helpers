<?php
# namespace fboes\SmallPhpHelpers;

/**
 * @class Memoization
 * You may notice the similiarities to http://www.php.net/manual/en/book.memcache.php.
 * This is quite intentional, so you may replace this functionality here with Memcache instead.
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */
class Memoization {
	protected $table = array();

	/**
	 * Get data for $key
	 * @param  string $key The key to fetch.
	 * @return mixed       [description]
	 */
	public function get ($key) {
		$key = (string)$key;
		if (!empty($this->table[$key])) {
			$memo = $this->table[$key];
			if (!empty($memo->expire) && $memo->expire < time()) {
				$this->memoizationDelete($key);
				return NULL;
			}
			else {
				return $memo->var;
			}
		}
		return NULL;
	}

	/**
	 * [memoizationSet description]
	 * @param  string $key    The key that will be associated with the item.
	 * @param  mixed  $data   The variable to store.
	 * @param  int    $expire Expiration time of the item. If it's equal to zero, the item will never expire. You can also use Unix timestamp or a number of seconds starting from current time, but in the latter case the number of seconds may not exceed 2592000 (30 days).
	 * @return bool           [description]
	 */
	public function set ($key, $var, $expire = 0) {
		if (!empty($expire) && $expire < 2592000) {
			$expire += time();
		}
		$this->table[(string)$key] = (object)array(
			'expire' => $expire,
			'var'    => $var
		);
		return TRUE;
	}

	/**
	 * Delete specific key
	 * @param  string $key [description]
	 * @return bool        [description]
	 */
	public function delete ($key) {
		$key = (string)$key;
		if (!empty($key) && !empty($this->table[$key])) {
			unset($this->table[$key]);
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * [memoizationFlush description]
	 * @return bool [description]
	 */
	public function flush () {
		$this->table = array();
		return TRUE;
	}
}
