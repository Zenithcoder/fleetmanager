<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartsUsedModel extends Model {
	use SoftDeletes;
	protected $dates = ['deleted_at'];
	protected $table = "parts_used";
	protected $fillable = ['part_id', 'work_id', 'qty', 'price', 'total'];

	function part() {
		return $this->hasOne("App\Model\PartsModel", "id", "part_id")->withTrashed();
	}

	function workorder() {
		return $this->hasOne("App\Model\WorkOrders", "id", "work_id")->withTrashed();
	}
}