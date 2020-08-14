<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\InsuranceRequest;
use App\Http\Requests\VehicleRequest;
use App\Http\Requests\VehiclReviewRequest;
use App\Model\DriverLogsModel;
use App\Model\DriverVehicleModel;
use App\Model\Expense;
use App\Model\FuelModel;
use App\Model\IncomeModel;
use App\Model\ServiceReminderModel;
use App\Model\User;
use App\Model\VehicleGroupModel;
use App\Model\VehicleModel;
use App\Model\VehicleReviewModel;
use App\Model\VehicleTypeModel;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Importer;
use Redirect;

class VehiclesController extends Controller {

	public function importVehicles(ImportRequest $request) {

		$file = $request->excel;
		$destinationPath = './assets/samples/'; // upload path
		$extension = $file->getClientOriginalExtension();
		$fileName = Str::uuid() . '.' . $extension;
		$file->move($destinationPath, $fileName);
		$excel = Importer::make('Excel');
		$excel->load('assets/samples/' . $fileName);
		$collection = $excel->getCollection()->toArray();
		array_shift($collection);
		// dd($collection);
		foreach ($collection as $vehicle) {
			$id = VehicleModel::create([
				'make' => $vehicle[0],
				'model' => $vehicle[1],
				'year' => $vehicle[2],
				'int_mileage' => $vehicle[4],
				'reg_exp_date' => date('Y-m-d', strtotime($vehicle[5])),
				'engine_type' => $vehicle[6],
				'horse_power' => $vehicle[7],
				'color' => $vehicle[8],
				'vin' => $vehicle[9],
				'license_plate' => $vehicle[10],
				'lic_exp_date' => date('Y-m-d', strtotime($vehicle[11])),
				'user_id' => Auth::id(),
				'group_id' => Auth::user()->group_id,
			])->id;

			$meta = VehicleModel::find($id);
			$meta->setMeta([
				'ins_number' => "",
				'ins_exp_date' => "",
				'documents' => "",
			]);
			$meta->average = $vehicle[3];
			$meta->save();
		}
		return back();
	}

	public function index() {
		$user = Auth::user();
		if ($user->group_id == null || $user->user_type == "S") {
			$index['data'] = VehicleModel::get();
		} else {
			$index['data'] = VehicleModel::where('group_id', $user->group_id)->get();}

		return view("vehicles.index", $index);
	}

	public function driver_logs() {
		$user = Auth::user();
		if ($user->group_id == null || $user->user_type == "S") {
			$vehicle_ids = VehicleModel::select('id')->get('id')->pluck('id')->toArray();

		} else {
			$vehicle_ids = VehicleModel::select('id')->where('group_id', $user->group_id)->get('id')->pluck('id')->toArray();
		}
		$data['logs'] = DriverLogsModel::whereIn('vehicle_id', $vehicle_ids)->get();
		return view('vehicles.driver_logs', $data);
	}

	public function create() {
		$index['groups'] = VehicleGroupModel::all();
		$index['types'] = VehicleTypeModel::all();
		return view("vehicles.create", $index);
	}
	public function destroy(Request $request) {
		VehicleModel::find($request->get('id'))->income()->delete();
		VehicleModel::find($request->get('id'))->expense()->delete();
		VehicleModel::find($request->get('id'))->delete();
		VehicleReviewModel::where('vehicle_id', $request->get('id'))->delete();

		ServiceReminderModel::where('vehicle_id', $request->get('id'))->delete();
		FuelModel::where('vehicle_id', $request->get('id'))->delete();
		return redirect()->route('vehicles.index');
	}

	public function edit($id) {
		$assigned = DriverVehicleModel::get();
		$did[] = 0;

		foreach ($assigned as $d) {
			$did[] = $d->driver_id;
		}

		$data = DriverVehicleModel::where('vehicle_id', $id)->first();
		// $except = array_diff($did, array($data->driver_id));
		if ($data != null) {
			$except = array_diff($did, array($data->driver_id));
		} else { $except = $did;}

		$drivers = User::whereUser_type("D")->whereNotIn('id', $except)->get();
		$groups = VehicleGroupModel::all();
		$vehicle = VehicleModel::where('id', $id)->get()->first();
		$udfs = unserialize($vehicle->getMeta('udf'));

		// dd($udfs);
		// foreach ($udfs as $key => $value) {
		// 	# code...
		// 	echo $key . " - " . $value . "<br>";
		// }
		$types = VehicleTypeModel::all();
		return view("vehicles.edit", compact('vehicle', 'groups', 'drivers', 'udfs', 'types'));
	}
	private function upload_file($file, $field, $id) {
		$destinationPath = './uploads'; // upload path
		$extension = $file->getClientOriginalExtension();
		$fileName1 = Str::uuid() . '.' . $extension;

		$file->move($destinationPath, $fileName1);

		$x = VehicleModel::find($id)->update([$field => $fileName1]);

	}

	private function upload_doc($file, $field, $id) {
		$destinationPath = './uploads'; // upload path
		$extension = $file->getClientOriginalExtension();
		$fileName1 = Str::uuid() . '.' . $extension;

		$file->move($destinationPath, $fileName1);
		$vehicle = VehicleModel::find($id);
		$vehicle->setMeta([$field => $fileName1]);
		$vehicle->save();

	}

	public function update(VehicleRequest $request) {

		$vehicle = $request->get('id');
		if ($request->file('vehicle_image') && $request->file('vehicle_image')->isValid()) {
			$this->upload_file($request->file('vehicle_image'), "vehicle_image", $vehicle);
		}

		$user = VehicleModel::find($request->get("id"));
		$form_data = $request->all();
		unset($form_data['vehicle_image']);
		unset($form_data['documents']);
		unset($form_data['udf']);

		$user->update($form_data);

		if ($request->get("in_service")) {
			$user->in_service = 1;
		} else {
			$user->in_service = 0;
		}
		$user->int_mileage = $request->get("int_mileage");
		$user->lic_exp_date = $request->get('lic_exp_date');
		$user->reg_exp_date = $request->get('reg_exp_date');
		$user->udf = serialize($request->get('udf'));
		$user->average = $request->average;
		$user->save();

		return Redirect::route("vehicles.index");

	}

	public function store(VehicleRequest $request) {
		// dd($request->all());
		$user_id = $request->get('user_id');
		$vehicle = VehicleModel::create([
			'make' => $request->get("make"),
			'model' => $request->get("model"),
			// 'type' => $request->get("type"),
			'year' => $request->get("year"),
			'engine_type' => $request->get("engine_type"),
			'horse_power' => $request->get("horse_power"),
			'color' => $request->get("color"),
			'vin' => $request->get("vin"),
			'license_plate' => $request->get("license_plate"),
			'int_mileage' => $request->get("int_mileage"),
			'group_id' => $request->get('group_id'),
			'user_id' => $request->get('user_id'),
			'lic_exp_date' => $request->get('lic_exp_date'),
			'reg_exp_date' => $request->get('reg_exp_date'),
			'in_service' => $request->get("in_service"),
			'type_id' => $request->get('type_id'),
			// 'vehicle_image' => $request->get('vehicle_image'),
		])->id;
		if ($request->file('vehicle_image') && $request->file('vehicle_image')->isValid()) {
			$this->upload_file($request->file('vehicle_image'), "vehicle_image", $vehicle);
		}

		$meta = VehicleModel::find($vehicle);
		$meta->setMeta([
			'ins_number' => "",
			'ins_exp_date' => "",
			'documents' => "",
		]);
		$meta->udf = serialize($request->get('udf'));
		$meta->average = $request->average;
		$meta->save();

		$vehicle_id = $vehicle;

		return redirect("admin/vehicles/" . $vehicle_id . "/edit?tab=vehicle");
	}

	public function store_insurance(InsuranceRequest $request) {
		$vehicle = VehicleModel::find($request->get('vehicle_id'));
		$vehicle->setMeta([
			'ins_number' => $request->get("insurance_number"),
			'ins_exp_date' => $request->get('exp_date'),
			// 'documents' => $request->get('documents'),
		]);
		$vehicle->save();
		if ($request->file('documents') && $request->file('documents')->isValid()) {
			$this->upload_doc($request->file('documents'), 'documents', $vehicle->id);
		}

		return $vehicle;
		// return redirect('admin/vehicles/' . $request->get('vehicle_id') . '/edit?tab=insurance');
	}

	public function view_event($id) {

		$data['vehicle'] = VehicleModel::where('id', $id)->get()->first();
		return view("vehicles.view_event", $data);
	}

	public function assign_driver(Request $request) {
		$records = User::meta()->where('users_meta.key', '=', 'vehicle_id')->where('users_meta.value', '=', $request->get('vehicle_id'))->get();
		// remove records of this vehicle which are assigned to other drivers
		foreach ($records as $record) {
			$record->vehicle_id = null;
			$record->save();
		}
		$vehicle = VehicleModel::find($request->get('vehicle_id'));
		$vehicle->driver_id = $request->get('driver_id');
		$vehicle->save();
		DriverVehicleModel::updateOrCreate(['vehicle_id' => $request->get('vehicle_id')], ['vehicle_id' => $request->get('vehicle_id'), 'driver_id' => $request->get('driver_id')]);
		DriverLogsModel::create(['driver_id' => $request->get('driver_id'), 'vehicle_id' => $request->get('vehicle_id'), 'date' => date('Y-m-d H:i:s')]);
		$driver = User::find($request->get('driver_id'));
		if ($driver != null) {
			$driver->vehicle_id = $request->get('vehicle_id');
			$driver->save();}
		return redirect('admin/vehicles/' . $request->get('vehicle_id') . '/edit?tab=driver');
	}

	public function vehicle_review() {
		$user = Auth::user();
		if ($user->group_id == null || $user->user_type == "S") {
			$data['vehicles'] = VehicleModel::get();
		} else {
			$data['vehicles'] = VehicleModel::where('group_id', $user->group_id)->get();
		}
		return view('vehicles.vehicle_review', $data);
	}

	public function store_vehicle_review(VehiclReviewRequest $request) {
		// dd($request->all());
		$petrol_card = array('flag' => $request->get('petrol_card'), 'text' => $request->get('petrol_card_text'));
		$lights = array('flag' => $request->get('lights'), 'text' => $request->get('lights_text'));
		$invertor = array('flag' => $request->get('invertor'), 'text' => $request->get('invertor_text'));
		$car_mats = array('flag' => $request->get('car_mats'), 'text' => $request->get('car_mats_text'));
		$int_damage = array('flag' => $request->get('int_damage'), 'text' => $request->get('int_damage_text'));
		$int_lights = array('flag' => $request->get('int_lights'), 'text' => $request->get('int_lights_text'));
		$ext_car = array('flag' => $request->get('ext_car'), 'text' => $request->get('ext_car_text'));
		$tyre = array('flag' => $request->get('tyre'), 'text' => $request->get('tyre_text'));
		$ladder = array('flag' => $request->get('ladder'), 'text' => $request->get('ladder_text'));
		$leed = array('flag' => $request->get('leed'), 'text' => $request->get('leed_text'));
		$power_tool = array('flag' => $request->get('power_tool'), 'text' => $request->get('power_tool_text'));
		$ac = array('flag' => $request->get('ac'), 'text' => $request->get('ac_text'));
		$head_light = array('flag' => $request->get('head_light'), 'text' => $request->get('head_light_text'));
		$lock = array('flag' => $request->get('lock'), 'text' => $request->get('lock_text'));
		$windows = array('flag' => $request->get('windows'), 'text' => $request->get('windows_text'));
		$condition = array('flag' => $request->get('condition'), 'text' => $request->get('condition_text'));
		$oil_chk = array('flag' => $request->get('oil_chk'), 'text' => $request->get('oil_chk_text'));
		$suspension = array('flag' => $request->get('suspension'), 'text' => $request->get('suspension_text'));
		$tool_box = array('flag' => $request->get('tool_box'), 'text' => $request->get('tool_box_text'));

		VehicleReviewModel::create([
			'user_id' => $request->get('user_id'),
			'vehicle_id' => $request->get('vehicle_id'),
			'reg_no' => $request->get('reg_no'),
			'kms_outgoing' => $request->get('kms_out'),
			'kms_incoming' => $request->get('kms_in'),
			'fuel_level_out' => $request->get('fuel_out'),
			'fuel_level_in' => $request->get('fuel_in'),
			'datetime_outgoing' => $request->get('datetime_out'),
			'datetime_incoming' => $request->get('datetime_in'),
			'petrol_card' => serialize($petrol_card),
			'lights' => serialize($lights),
			'invertor' => serialize($invertor),
			'car_mats' => serialize($car_mats),
			'int_damage' => serialize($int_damage),
			'int_lights' => serialize($int_lights),
			'ext_car' => serialize($ext_car),
			'tyre' => serialize($tyre),
			'ladder' => serialize($ladder),
			'leed' => serialize($leed),
			'power_tool' => serialize($power_tool),
			'ac' => serialize($ac),
			'head_light' => serialize($head_light),
			'lock' => serialize($lock),
			'windows' => serialize($windows),
			'condition' => serialize($condition),
			'oil_chk' => serialize($oil_chk),
			'suspension' => serialize($suspension),
			'tool_box' => serialize($tool_box),
		]);

		return redirect()->route('vehicle_reviews');
	}

	public function vehicle_review_index() {
		$data['reviews'] = VehicleReviewModel::get();
		return view('vehicles.vehicle_review_index', $data);
	}

	public function review_edit($id) {
		// dd($id);
		$data['review'] = VehicleReviewModel::find($id);
		$user = Auth::user();
		if ($user->group_id == null || $user->user_type == "S") {
			$data['vehicles'] = VehicleModel::get();
		} else {
			$data['vehicles'] = VehicleModel::where('group_id', $user->group_id)->get();
		}
		return view('vehicles.vehicle_review_edit', $data);
	}

	public function update_vehicle_review(VehiclReviewRequest $request) {
		// dd($request->all());
		$petrol_card = array('flag' => $request->get('petrol_card'), 'text' => $request->get('petrol_card_text'));
		$lights = array('flag' => $request->get('lights'), 'text' => $request->get('lights_text'));
		$invertor = array('flag' => $request->get('invertor'), 'text' => $request->get('invertor_text'));
		$car_mats = array('flag' => $request->get('car_mats'), 'text' => $request->get('car_mats_text'));
		$int_damage = array('flag' => $request->get('int_damage'), 'text' => $request->get('int_damage_text'));
		$int_lights = array('flag' => $request->get('int_lights'), 'text' => $request->get('int_lights_text'));
		$ext_car = array('flag' => $request->get('ext_car'), 'text' => $request->get('ext_car_text'));
		$tyre = array('flag' => $request->get('tyre'), 'text' => $request->get('tyre_text'));
		$ladder = array('flag' => $request->get('ladder'), 'text' => $request->get('ladder_text'));
		$leed = array('flag' => $request->get('leed'), 'text' => $request->get('leed_text'));
		$power_tool = array('flag' => $request->get('power_tool'), 'text' => $request->get('power_tool_text'));
		$ac = array('flag' => $request->get('ac'), 'text' => $request->get('ac_text'));
		$head_light = array('flag' => $request->get('head_light'), 'text' => $request->get('head_light_text'));
		$lock = array('flag' => $request->get('lock'), 'text' => $request->get('lock_text'));
		$windows = array('flag' => $request->get('windows'), 'text' => $request->get('windows_text'));
		$condition = array('flag' => $request->get('condition'), 'text' => $request->get('condition_text'));
		$oil_chk = array('flag' => $request->get('oil_chk'), 'text' => $request->get('oil_chk_text'));
		$suspension = array('flag' => $request->get('suspension'), 'text' => $request->get('suspension_text'));
		$tool_box = array('flag' => $request->get('tool_box'), 'text' => $request->get('tool_box_text'));

		$review = VehicleReviewModel::find($request->get('id'));
		$review->user_id = $request->get('user_id');
		$review->vehicle_id = $request->get('vehicle_id');
		$review->reg_no = $request->get('reg_no');
		$review->kms_outgoing = $request->get('kms_out');
		$review->kms_incoming = $request->get('kms_in');
		$review->fuel_level_out = $request->get('fuel_out');
		$review->fuel_level_in = $request->get('fuel_in');
		$review->datetime_outgoing = $request->get('datetime_out');
		$review->datetime_incoming = $request->get('datetime_in');
		$review->petrol_card = serialize($petrol_card);
		$review->lights = serialize($lights);
		$review->invertor = serialize($invertor);
		$review->car_mats = serialize($car_mats);
		$review->int_damage = serialize($int_damage);
		$review->int_lights = serialize($int_lights);
		$review->ext_car = serialize($ext_car);
		$review->tyre = serialize($tyre);
		$review->ladder = serialize($ladder);
		$review->leed = serialize($leed);
		$review->power_tool = serialize($power_tool);
		$review->ac = serialize($ac);
		$review->head_light = serialize($head_light);
		$review->lock = serialize($lock);
		$review->windows = serialize($windows);
		$review->condition = serialize($condition);
		$review->oil_chk = serialize($oil_chk);
		$review->suspension = serialize($suspension);
		$review->tool_box = serialize($tool_box);
		$review->save();

		return redirect()->route('vehicle_reviews');
	}

	public function destroy_vehicle_review(Request $request) {
		VehicleReviewModel::find($request->get('id'))->delete();
		return redirect()->route('vehicle_reviews');
	}

	public function view_vehicle_review($id) {
		$data['review'] = VehicleReviewModel::find($id);
		return view('vehicles.view_vehicle_review', $data);

	}

	public function print_vehicle_review($id) {
		$data['review'] = VehicleReviewModel::find($id);
		return view('vehicles.print_vehicle_review', $data);
	}

	public function bulk_delete(Request $request) {
		VehicleModel::whereIn('id', $request->ids)->delete();
		IncomeModel::whereIn('vehicle_id', $request->ids)->delete();
		Expense::whereIn('vehicle_id', $request->ids)->delete();
		VehicleReviewModel::whereIn('vehicle_id', $request->ids)->delete();
		ServiceReminderModel::whereIn('vehicle_id', $request->ids)->delete();
		FuelModel::whereIn('vehicle_id', $request->ids)->delete();
		return back();
	}

	public function bulk_delete_reviews(Request $request) {
		VehicleReviewModel::whereIn('id', $request->ids)->delete();
		return back();
	}

}