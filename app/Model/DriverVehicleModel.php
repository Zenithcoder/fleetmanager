<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DriverVehicleModel extends Model {

	protected $table = "driver_vehicle";
	protected $fillable = ['driver_id', 'vehicle_id'];

	function vehicle() {
		return $this->hasOne("App\Model\VehicleModel", "id", "vehicle_id")->withTrashed();
	}
}