<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\FuelRequest;
use App\Model\Expense;
use App\Model\FuelModel;
use App\Model\VehicleModel;
use App\Model\Vendor;
use Auth;
use Illuminate\Http\Request;

class FuelController extends Controller {
	public function index() {
		if (Auth::user()->group_id == null || Auth::user()->user_type == "S") {
			$vehicle_ids = VehicleModel::pluck('id')->toArray();
		} else {
			$vehicle_ids = VehicleModel::where('group_id', Auth::user()->group_id)->pluck('id')->toArray();
		}

		$data['data'] = FuelModel::orderBy('id', 'desc')->whereIn('vehicle_id', $vehicle_ids)->get();
		return view('fuel.index', $data);
	}
	public function create() {
		if (Auth::user()->group_id == null || Auth::user()->user_type == "S") {
			$data['vehicles'] = VehicleModel::whereIn_service("1")->get();
		} else {
			$data['vehicles'] = VehicleModel::where('group_id', Auth::user()->group_id)->whereIn_service("1")->get();

		}
		$data['vendors'] = Vendor::where('type', 'fuel')->get();
		return view('fuel.create', $data);
	}

	public function store(FuelRequest $request) {
		// dd($request->all());

		$fuel = new FuelModel();
		$fuel->vehicle_id = $request->get('vehicle_id');
		$fuel->user_id = $request->get('user_id');
		$condition = FuelModel::orderBy('id', 'desc')->where('vehicle_id', $request->get('vehicle_id'))->first();
		// dd($condition->qty);
		if ($condition['vehicle_id'] == $request->get('vehicle_id')) {

			$fuel->start_meter = $request->get('start_meter');
			$fuel->end_meter = "0";
			$fuel->consumption = "0";
			$condition->end_meter = $end = $request->get('start_meter');
			// dd($condition->end_meter);
			// $fuel->start_meter = $start = $request->get('start_meter');
			// dd($condition->start_meter);
			// dd($end); //value fetched
			if ($request->get('qty') == 0) {
				$condition->consumption = $con = 0;
			} else {
				$condition->consumption = $con = ($end - $condition->start_meter) / $condition->qty;
			}
			// dd($con);
			$condition->save();

		} else {

			$fuel->start_meter = $request->get('start_meter');
			$fuel->end_meter = "0";
			$fuel->consumption = "0";

		}
		$fuel->reference = $request->get('reference');
		$fuel->province = $request->get('province');
		$fuel->note = $request->get('note');
		$fuel->qty = $request->get('qty');
		$fuel->fuel_from = $request->get('fuel_from');
		$fuel->vendor_name = $request->get('vendor_name');
		$fuel->cost_per_unit = $request->get('cost_per_unit');
		$fuel->date = $request->get('date');
		$fuel->complete = $request->get("complete");
		$fuel->save();

		$expense = new Expense();
		$expense->vehicle_id = $request->get('vehicle_id');
		$expense->user_id = $request->get('user_id');
		$expense->expense_type = '8';
		$expense->comment = $request->get('note');
		$expense->date = $request->get('date');
		$amount = $request->get('qty') * $request->get('cost_per_unit');
		$expense->amount = $amount;
		$expense->exp_id = $fuel->id;
		$expense->save();
		VehicleModel::where('id', $request->vehicle_id)->update(['mileage' => $request->start_meter]);
		return redirect('admin/fuel');
	}

	public function edit($id) {
		// return $id;
		// $data['vehicle'] = VehicleModel::whereId(2)->get();
		$data['data'] = $data = FuelModel::whereId($id)->get()->first();
		$data['vehicle_id'] = $data->vehicle_id;
		$data['vendors'] = Vendor::where('type', 'fuel')->get();
		return view('fuel.edit', $data);
	}

	public function update(FuelRequest $request) {
		$fuel = FuelModel::find($request->get("id"));
		// $form_data = $request->all();
		$old = FuelModel::where('vehicle_id', $fuel->vehicle_id)->where('end_meter', $fuel->start_meter)->first();
		if ($old != null) {
			$old->end_meter = $request->get('start_meter');
			$old->consumption = ($old->end_meter - $old->start_meter) / $old->qty;
			$old->save();
		}

		$fuel->start_meter = $request->get('start_meter');
		$fuel->reference = $request->get('reference');
		$fuel->province = $request->get('province');
		$fuel->note = $request->get('note');
		$fuel->qty = $request->get('qty');
		$fuel->fuel_from = $request->get('fuel_from');
		$fuel->vendor_name = $request->get('vendor_name');
		$fuel->cost_per_unit = $request->get('cost_per_unit');
		$fuel->date = $request->get('date');
		$fuel->complete = $request->get("complete");
		if ($fuel->end_meter != 0) {
			$fuel->consumption = ($fuel->end_meter - $request->get('start_meter')) / $request->get('qty');
		}

		$fuel->save();
		$exp = Expense::where('exp_id', $request->get('id'))->where('expense_type', 8)->first();
		if ($exp != null) {
			$exp->amount = $request->get('qty') * $request->get('cost_per_unit');
			$exp->save();
		}
		VehicleModel::where('id', $request->vehicle_id)->update(['mileage' => $request->start_meter]);
		return redirect()->route('fuel.index');

	}

	public function destroy(Request $request) {
		FuelModel::find($request->get('id'))->delete();
		Expense::where('exp_id', $request->get('id'))->where('expense_type', 8)->delete();
		return redirect()->route('fuel.index');
	}

	public function bulk_delete(Request $request) {
		FuelModel::whereIn('id', $request->ids)->delete();
		Expense::whereIn('exp_id', $request->ids)->where('expense_type', 8)->delete();
		return back();
	}
}
