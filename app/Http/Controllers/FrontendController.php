<?php

namespace App\Http\Controllers;

class FrontendController extends Controller {
	public function index() {
		//dd(1);
		//return view('frontend.index');
		return view('auth.login');
	}
}