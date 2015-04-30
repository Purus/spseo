<?php
/**
 * SPSEO - Simple Search Engine Optimization toolkit for Oxwall platform
 * Copyright (C) 2015 SONGPHI LLC.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @author Thao Le <thaolt@songphi.com>
 * @package spseo.bol
 * @since 1.0
 */

class SPSEO_BOL_CacheService
{
	protected static $classInstance = null;

	private $data = null;
	private $changed = false;
	private $cacheDir = null;
	private $cacheFile = null;
	private $hash = null;

	public static function getInstance() {
		if (self::$classInstance === null) {
			self::$classInstance = new self();
		}

		return self::$classInstance;
	}

	protected function __construct() {
		$this->changed = false;
		$uri = OW::getRouter()->getUri();
		$this->hash = crc32($uri);
		$this->cacheDir = ( SPSEO_BOL_Service::getPlugin()->getPluginFilesDir() ) . 'cache';
		$this->cacheFile = $this->cacheDir . DS . $this->hash;

		if (!file_exists($this->cacheDir)) {
			mkdir($this->cacheDir, 0777, true);
		} else {
			if (!is_dir($this->cacheDir)) {
				unlink($this->cacheDir);
				mkdir($this->cacheDir, 0777, true);
			}
		}

		if (file_exists($this->cacheFile) && filesize($this->cacheFile) > 0) {
			$this->data = unserialize(file_get_contents($this->cacheFile));
		} else {
			$this->data = array();
		}
	}

	public function __destruct()  {
		if ($this->changed) {
			file_put_contents($this->cacheFile, serialize($this->data));
		}
	}

	public function findFriendlyUri( $uri ) {
		if (isset($this->data['urls']) && isset($this->data['urls'][$uri]) && !empty($this->data['urls'][$uri]))
			return $this->data['urls'][$uri];
		return false;
	}

	public function updateFriendlyUri( $uri, $friendlyOne ) {
		if (!isset($this->data['urls']))
			$this->data['urls'] = array();

		$this->data['urls'][$uri] = $friendlyOne;
		$this->changed = true;
	}

	public function getSlug() {
		if (isset($this->data['slug']) && !empty($this->data['slug']))
			return $this->data['slug'];
		return false;
	}

	public function updateSlug( $slug ) {
		$this->data['slug'] = $slug;
		$this->changed = true;
	}

	public function getMetaData() {
		if (isset($this->data['meta']) && is_array($this->data['meta']))
			return $this->data['meta'];
		return array();
	}

	public function updateMeta( array $meta ) {
		$this->data['meta'] = $meta;
		$this->changed = true;
	}

	public function pageHash() {
		return $this->hash;
	}
}
