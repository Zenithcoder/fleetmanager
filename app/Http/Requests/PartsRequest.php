<?php

namespace App\Http\Requests;
use Auth;
use Illuminate\Foundation\Http\FormRequest;

class PartsRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		if (Auth::user()->user_type == "S" || Auth::user()->user_type == "O") {
			return true;
		} else {
			abort(404);
		}
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		return [
			'barcode' => 'required',
			'number' => 'required',
			'description' => 'required',
			'unit_cost' => 'required',
			'vendor_id' => 'required',
			'stock' => 'required',
			'title' => 'required',
			'category_id' => 'required',
			'year' => 'required',
			'model' => 'required',
			'image' => 'nullable|image|mimes:jpg,png,jpeg',
		];
	}
}
