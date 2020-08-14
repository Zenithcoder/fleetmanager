<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\FrontEndRequest;
use App\Http\Requests\SettingsRequest;
use App\Mail\RenewDrivingLicence;
use App\Mail\RenewInsurance;
use App\Mail\RenewRegistration;
use App\Mail\RenewVehicleLicence;
use App\Mail\ServiceReminder;
use App\Model\ApiSettings;
use App\Model\EmailContent;
use App\Model\FareSettings;
use App\Model\FrontendModel;
use App\Model\ServiceReminderModel;
use App\Model\Settings;
use App\Model\User;
use App\Model\VehicleModel;
use App\Model\VehicleTypeModel;
use Auth;
use Cache;
use Exception;
use Firebase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PushNotification;
use Redirect;
use Storage;

class SettingsController extends Controller {

	public function frontend() {
		return view('utilities.frontend');
	}

	public function store_frontend(FrontEndRequest $request) {
		FrontendModel::where('key_name', 'about_us')->update(['key_value' => $request->about]);
		FrontendModel::where('key_name', 'contact_email')->update(['key_value' => $request->email]);
		FrontendModel::where('key_name', 'contact_phone')->update(['key_value' => $request->phone]);
		FrontendModel::where('key_name', 'customer_support')->update(['key_value' => $request->customer_support]);
		FrontendModel::where('key_name', 'about_description')->update(['key_value' => $request->about_description]);
		FrontendModel::where('key_name', 'about_title')->update(['key_value' => $request->about_title]);
		FrontendModel::where('key_name', 'facebook')->update(['key_value' => $request->facebook]);
		FrontendModel::where('key_name', 'twitter')->update(['key_value' => $request->twitter]);
		FrontendModel::where('key_name', 'instagram')->update(['key_value' => $request->instagram]);
		FrontendModel::where('key_name', 'linkedin')->update(['key_value' => $request->linkedin]);
		FrontendModel::where('key_name', 'faq_link')->update(['key_value' => $request->faq_link]);
		FrontendModel::where('key_name', 'cities')->update(['key_value' => $request->cities]);
		FrontendModel::where('key_name', 'vehicles')->update(['key_value' => $request->vehicles]);
		FrontendModel::where('key_name', 'cancellation')->update(['key_value' => $request->cancellation]);
		FrontendModel::where('key_name', 'terms')->update(['key_value' => $request->terms]);
		FrontendModel::where('key_name', 'privacy_policy')->update(['key_value' => $request->privacy_policy]);
		FrontendModel::where('key_name', 'enable')->update(['key_value' => $request->enable]);
		return redirect('admin/frontend-settings');
	}

	public function index() {
		$data['settings'] = Settings::all();
		$data['languages'] = Storage::disk('views')->directories('');
		return view("utilities.settings", $data);
	}

	private function upload_file($file, $field, $name) {
		$destinationPath = './assets/images'; // upload path
		$extension = $file->getClientOriginalExtension();
		$fileName1 = Str::uuid() . '.' . $extension;

		$file->move($destinationPath, $fileName1);

		$x = Settings::where("name", $name)->update([$field => $fileName1]);

	}

	public function store(SettingsRequest $request) {
		// dd($request->all());
		foreach ($request->get('name') as $key => $val) {
			Settings::where('name', $key)->update(['value' => $val]);
			if ($key == 'language') {
				$user = Auth::user();
				$user->language = $val;
				$user->save();
			}

		}
		$app_name = str_replace(" ", "_", $request->name['app_name']);
		if (!env('APP_NAME')) {

			file_put_contents(base_path('.env'), "APP_NAME=" . $app_name . PHP_EOL, FILE_APPEND);
		}
		if (env('APP_NAME')) {

			file_put_contents(base_path('.env'), str_replace(
				'APP_NAME=' . env('APP_NAME'), 'APP_NAME=' . $app_name, file_get_contents(base_path('.env'))));
		}

		if ($request->file('icon_img') && $request->file('icon_img')->isValid()) {
			$this->upload_file($request->file('icon_img'), "value", 'icon_img');
		}

		if ($request->file('logo_img') && $request->file('logo_img')->isValid()) {
			$this->upload_file($request->file('logo_img'), "value", 'logo_img');
		}

		Cache::flush();
		return Redirect::route("settings.index");
	}

	public function api_settings() {
		$data['settings'] = ApiSettings::all();

		return view("utilities.api_settings", $data);
	}

	public function store_settings(Request $request) {
		ApiSettings::where('key_name', 'api')->update(['key_value' => 0]);
		ApiSettings::where('key_name', 'anyone_register')->update(['key_value' => 0]);

		ApiSettings::where('key_name', 'driver_review')->update(['key_value' => 0]);

		ApiSettings::where('key_name', 'google_api')->update(['key_value' => 0]);
		foreach ($request->get('name') as $key => $val) {
			ApiSettings::where('key_name', $key)->update(['key_value' => $val]);
		}

		Cache::flush();
		return redirect('admin/api-settings');
	}

	public function fare_settings() {
		$data['settings'] = FareSettings::all();
		$vehicle_types = VehicleTypeModel::get();
		foreach ($vehicle_types as $type) {
			$all[] = $type->vehicletype;
		}
		$data['types'] = array_unique($all);
		return view('utilities.fare_settings', $data);
	}

	public function store_fareSettings(Request $request) {
		foreach ($request->get('name') as $key => $val) {
			FareSettings::where('key_name', $key)->update(['key_value' => $val]);
		}
		$tab = $_GET['tab'];

		return redirect('admin/fare-settings?tab=' . $tab);

	}

	public function send_email() {
		$data['users'] = User::where('user_type', '!=', 'C')->where('user_type', '!=', 'D')->get();
		$selected_users = EmailContent::where('key', 'users')->first();
		$selected_options = EmailContent::where('key', 'options')->first();
		$data['options'] = array();
		$data['selected_users'] = array();
		if ($selected_options->value != null) {
			$data['options'] = unserialize($selected_options->value);
		}
		if ($selected_users->value != null) {
			$data['selected_users'] = unserialize($selected_users->value);
		}

		return view('utilities.send_email', $data);
	}

	public function enable_mail(Request $request) {
		if ($request->email == '1') {
			$email = 1;
		} else {
			$email = 0;
		}
		EmailContent::where('key', 'email')->update(['value' => $email]);
		return redirect()->back();
	}

	public function email_settings(Request $request) {
		EmailContent::where('key', 'users')->update(['value' => serialize($request->get('users'))]);
		EmailContent::where('key', 'options')->update(['value' => serialize($request->get('chk'))]);
		return redirect()->back();

	}

	public function email_notification(Request $request) {
		$chk = $request->get('chk');
		$users = User::whereIn('id', $request->get('users'))->get();
		$d = VehicleModel::get();

		if (in_array(1, $chk)) {

			foreach ($d as $data) {

				$vehicle = $data->make . '-' . $data->model . '-' . $data->license_plate;
				$reg_date = $data->reg_exp_date;
				$to = \Carbon\Carbon::now();
				$from = \Carbon\Carbon::createFromFormat('Y-m-d', $reg_date);
				$diff_in_days = $to->diffInDays($from);
				if ($diff_in_days <= 20) {
					foreach ($users as $user) {

						Mail::to($user->email)->send(new RenewRegistration($vehicle, $reg_date, $user->name));

					}
				}
			}
		}

		if (in_array(3, $chk)) {

			foreach ($d as $data) {

				$vehicle = $data->make . '-' . $data->model . '-' . $data->license_plate;
				$lic_date = $data->lic_exp_date;
				$to = \Carbon\Carbon::now();
				$from = \Carbon\Carbon::createFromFormat('Y-m-d', $lic_date);
				$diff_in_days = $to->diffInDays($from);
				if ($diff_in_days <= 20) {
					foreach ($users as $user) {
						Mail::to($user->email)->send(new RenewVehicleLicence($vehicle, $lic_date, $user->name));

					}
				}
			}
		}

		if (in_array(4, $chk)) {

			$d1 = User::where('user_type', 'D')->where('deleted_at', null)->get();

			foreach ($d1 as $data) {

				$driver = $data->name;
				$lic_date = $data->getMeta('exp_date');
				$to = \Carbon\Carbon::now();
				$from = \Carbon\Carbon::createFromFormat('Y-m-d', $lic_date);
				$diff_in_days = $to->diffInDays($from);
				if ($diff_in_days <= 20) {
					foreach ($users as $user) {
						Mail::to($user->email)->send(new RenewDrivingLicence($driver, $lic_date, $diff_in_days, $user->name));

					}
				}
			}
		}

		if (in_array(2, $chk)) {

			$v = VehicleModel::get();
			foreach ($v as $vehicle) {
				$ins_date = $vehicle->getMeta('ins_exp_date');
				$vehicle = $vehicle->make . '-' . $vehicle->model . '-' . $vehicle->license_plate;
				$to = \Carbon\Carbon::now();
				$from = \Carbon\Carbon::createFromFormat('Y-m-d', $ins_date);
				$diff_in_days = $to->diffInDays($from);
				if ($diff_in_days <= 20) {
					foreach ($users as $user) {
						Mail::to($user->email)->send(new RenewInsurance($vehicle, $ins_date, $diff_in_days, $user->name));

					}
				}
			}
		}

		if (in_array(5, $chk)) {

			$s = ServiceReminderModel::get();
			foreach ($s as $data) {
				$interval = substr($data->services->overdue_unit, 0, -3);
				$int = $data->services->overdue_time . $interval;
				$date = date('Y-m-d', strtotime($int, strtotime(date('Y-m-d'))));

				$to = \Carbon\Carbon::now();
				$from = \Carbon\Carbon::createFromFormat('Y-m-d', $date);
				$diff_in_days = $to->diffInDays($from);

				$duesoon = substr($data->services->duesoon_unit, 0, -3);
				$int1 = $data->services->duesoon_time . $duesoon;
				$date1 = date('Y-m-d', strtotime($int1, strtotime(date('Y-m-d'))));

				$from1 = \Carbon\Carbon::createFromFormat('Y-m-d', $date1);
				$condition = $to->diffInDays($from1);
				if ($data->services->duesoon_time = null) {
					$condition = 20;
				}
				$detail = $data->services->description;
				$vehicle = $data->vehicle->make . '-' . $data->vehicle->model . '-' . $data->vehicle->license_plate;
				if ($diff_in_days <= $condition) {
					foreach ($users as $user) {
						Mail::to($user->email)->send(new ServiceReminder($detail, $vehicle, $date, $diff_in_days, $user->name));

					}
				}
			}
		}

		return redirect()->back();
	}

	public function set_email() {
		return view('utilities.set_email');
	}

	public function set_content(Request $request, $type) {

		if ($type == "insurance") {
			$validator = $request->validate([
				'insurance' => 'required',
			]);
			EmailContent::where('key', 'insurance')->update(['value' => $request->get('insurance')]);

		} elseif ($type == "vehicle-licence") {
			$request->validate([
				'vehicle_licence' => 'required',
			]);
			EmailContent::where('key', 'vehicle_licence')->update(['value' => $request->get('vehicle_licence')]);
		} elseif ($type == "driver-licence") {
			$request->validate([
				'driving_licence' => 'required',
			]);
			EmailContent::where('key', 'driving_licence')->update(['value' => $request->get('driving_licence')]);
		} elseif ($type == "registration") {
			$request->validate([
				'registration' => 'required',
			]);
			EmailContent::where('key', 'registration')->update(['value' => $request->get('registration')]);
		} elseif ($type == "reminder") {
			$request->validate([
				'service_reminder' => 'required',
			]);
			EmailContent::where('key', 'service_reminder')->update(['value' => $request->get('service_reminder')]);
		}

		// return redirect()->back();
		return redirect('admin/set-email?tab=' . $type);
	}

	public function firebase(Request $request) {
		$db_url = $request->get('db_url');
		$db_secret = $request->get('db_secret');
		$url = "db_url=" . $db_url;

		if (!env('db_url')) {
			// dd('test');
			file_put_contents(base_path('.env'), $url . PHP_EOL, FILE_APPEND);
		}
		if (env('db_url')) {

			file_put_contents(base_path('.env'), str_replace(
				'db_url=' . env('db_url'), 'db_url=' . $db_url, file_get_contents(base_path('.env'))));
		}

		$secret = "db_secret=" . $db_secret;
		if (!env('db_secret')) {
			// dd('not exist');
			file_put_contents(base_path('.env'), $secret . PHP_EOL, FILE_APPEND);
		}
		if (env('db_secret')) {
			// dd("exist");
			file_put_contents(base_path('.env'), str_replace(
				'db_secret=' . env('db_secret'), 'db_secret=' . $db_secret, file_get_contents(base_path('.env'))));
		}
		// dd(env('db_secret'));
		return redirect()->route('fb');

	}

	public function fb_create() {

		Firebase::set('/test/', ["testing"]);
		$data = Firebase::get('/test/');
		$details = json_decode($data, true);

		if (isset($details['error']) || $details == null) {
			// dd("no records");
			$success = "0";
		} else {
			// dd("not null");
			// dd(env('db_secret'));
			$success = "1";
			ApiSettings::where('key_name', 'db_secret')->update(['key_value' => env('db_secret')]);
			ApiSettings::where('key_name', 'db_url')->update(['key_value' => env('db_url')]);
		}

		return redirect('admin/api-settings?tab=firebase&success=' . $success);
	}

	public function store_key(Request $request) {
		$key = $request->get('server_key');
		$env = "server_key=" . $key;
		if (!env('server_key')) {
			// dd('test');
			file_put_contents(base_path('.env'), $env . PHP_EOL, FILE_APPEND);
		}
		if (env('server_key')) {

			file_put_contents(base_path('.env'), str_replace(
				'server_key=' . env('server_key'), 'server_key=' . $key, file_get_contents(base_path('.env'))));
		}

		return redirect('admin/test-key');
	}

	public function test_key() {
		try {

			$notification = PushNotification::app('appNameAndroid')
				->to('d5Av2XvAAns:APA91bGH34jdo6UlKCLsf724FMGhlZhTFGCBhmP2pON5fNit7p245RFLjGF24wa_4kIO3kJ-6hHM3aYHPPAfVvFyUX78KbzrPMY18TynUHuYREr3HJuIHbu56BmSNViw6-CnUYn3DZST')
				->send('testing');
			ApiSettings::where('key_name', 'server_key')->update(['key_value' => env('server_key')]);
			// dd($notification);
			return redirect('admin/api-settings?tab=serverkey&key=1');
		} catch (Exception $e) {
			// dd($e);
			return redirect('admin/api-settings?tab=serverkey&key=0');
		}
	}

	public function store_api(Request $request) {
		$key = $request->get('api_key');
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=40.714224,-73.961452&key=' . $key;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($data, true);
		// dd($response);
		if ($response['status'] != "OK" && $response['error_message']) {
			$msg = $response['error_message'];
			return redirect('admin/api-settings?tab=maps&api_key=0&msg=' . $msg . '&test_key=' . $key);
		}
		if ($response['status'] == "OK") {
			$msg = "API key successfully saved";
			ApiSettings::where('key_name', 'api_key')->update(['key_value' => $key]);
			return redirect('admin/api-settings?tab=maps&api_key=1&msg=' . $msg . '&test_key=' . $key);
		} else {
			$msg = "Something went wrong, please try again";
			return redirect('admin/api-settings?tab=maps&api_key=0&msg=' . $msg . '&test_key=' . $key);
		}
	}

	public function ajax_api_store($api) {
		ApiSettings::where('key_name', 'api_key')->update(['key_value' => $api]);
		return "true";
	}

}