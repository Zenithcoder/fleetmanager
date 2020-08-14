<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Model\ReviewModel;

class ReviewRatings extends Controller {
	public function index() {
		$data['reviews'] = ReviewModel::get();

		return view('reviews', $data);
	}
}
