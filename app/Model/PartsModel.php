<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartsModel extends Model {
	use SoftDeletes;
	protected $dates = ['deleted_at'];
	protected $table = 'parts';
	protected $fillable = [
		'image',
		'barcode',
		'number',
		'description',
		'unit_cost',
		'vendor_id',
		'manufacturer',
		'note',
		'stock',
		'udf',
		'category_id',
		'user_id',
		'status',
		'availability',
		'title',
		'year',
		'model',
	];

	function vendor() {
		return $this->hasOne("App\Model\Vendor", "id", "vendor_id")->withTrashed();
	}

	function category() {
		return $this->hasOne("App\Model\PartsCategoryModel", "id", "category_id")->withTrashed();
	}
}