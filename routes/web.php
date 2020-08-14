<?php

Route::get('/', 'FrontendController@index')->middleware('IsInstalled');
Route::get('installation', 'LaravelWebInstaller@index');
Route::post('installed', 'LaravelWebInstaller@install');
Route::get('installed', 'LaravelWebInstaller@index');
Route::get('migrate', 'LaravelWebInstaller@db_migration');
Route::get('migration', 'LaravelWebInstaller@migration');
Route::get('upgrade', 'UpdateVersion@upgrade')->middleware('canInstall');
Route::get('upgrade3', 'UpdateVersion@upgrade3')->middleware('canInstall');
Route::get('upgrade4', 'UpdateVersion@upgrade4')->middleware('canInstall');