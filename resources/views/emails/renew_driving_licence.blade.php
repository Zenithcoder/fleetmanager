@component('mail::message')
# Renew Driving Licence

Dear {{$user}},

{!! Hyvikk::email_msg('driving_licence') !!}

	Driver:	{{$driver}}
	Licence Expiry date: {{date('d-m-Y',strtotime($lic_date))}}
	Remaining days: {{$diff_in_days}}




Thanks,<br>
{{ config('app.name') }}
@endcomponent
