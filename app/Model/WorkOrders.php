<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrders extends Model {
	use SoftDeletes;
	protected $dates = ['deleted_at'];
	protected $table = 'work_orders';
	protected $fillable = ['vehicle_id', 'vendor_id', 'required_by', 'status', 'description', 'meter', 'note', 'reference', 'price'];

	function vehicle() {
		return $this->belongsTo("App\Model\VehicleModel", "vehicle_id", "id")->withTrashed();
	}

	function vendor() {
		return $this->belongsTo("App\Model\Vendor", "vendor_id", "id")->withTrashed();
	}

	function parts() {
		return $this->hasMany("App\Model\PartsUsedModel", "work_id", "id");
	}

}