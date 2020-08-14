<?php

namespace App\Helpers;

class InstalledFileManager {

	public function create() {
		file_put_contents(storage_path('installed'), 'version4.0.1');
	}

	public function update() {
		return $this->create();
	}
}