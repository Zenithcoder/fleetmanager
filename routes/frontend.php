<?php
Route::namespace ('FrontEnd')->middleware(['throttle'])->group(function () {
	Route::get('company-info', 'FrontendApiController@company_info');
	Route::get('vehicle-types', 'FrontendApiController@vehicle_types');
	Route::get('our-services', 'FrontendApiController@our_services');
	Route::get('about', 'FrontendApiController@about_fleet');
	Route::post('user-login', 'FrontendApiController@user_login');
	Route::post('user-register', 'FrontendApiController@user_register');
	Route::get('testimonials', 'FrontendApiController@testimonials');
	Route::get('footer', 'FrontendApiController@footer_data');
	Route::get('vehicles', 'FrontendApiController@vehicles');
	Route::post('message-us', 'FrontendApiController@message_us');
	Route::post('forgot-password', 'FrontendApiController@forgot_password');
	Route::post('reset-password', 'FrontendApiController@reset_password');
});

Route::namespace ('FrontEnd')->middleware(['throttle', 'auth:api'])->group(function () {
	Route::post('booking-history/{id}', 'FrontendApiController@booking_history');
	Route::post('book-now', 'FrontendApiController@book_now');
	Route::post('book-later', 'FrontendApiController@book_later');
});