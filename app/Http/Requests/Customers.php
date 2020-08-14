<?php

namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class Customers extends FormRequest {
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
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'unique:users,email,' . \Request::get("id"),
			'phone' => 'numeric',
			'gender' => 'required',
			// 'address1' => 'required',

		];
	}
	public function messages() {
		return [
			// 'email.required' => 'email must be required',
			'email.unique' => 'email already taken',

		];
	}
}
