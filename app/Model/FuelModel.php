<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelModel extends Model {
	use SoftDeletes;
	protected $dates = ['deleted_at'];
	protected $table = "fuel";
	protected $fillable = ['vehicle_id', 'user_id', 'start_meter', 'reference', 'provience', 'note', 'qty', 'fuel_from', 'cost_per_unit', 'complete', 'date', 'vendor_name', 'mileage_type'];

	function vehicle_data() {

		return $this->belongsTo("App\Model\VehicleModel", "vehicle_id", "id")->withTrashed();
	}

}