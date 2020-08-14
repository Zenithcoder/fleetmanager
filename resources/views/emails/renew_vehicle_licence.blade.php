@component('mail::message')
# Renew Vehicle Licence

Dear {{$user}},

	@php   ($to = \Carbon\Carbon::now())
    @php ($from = \Carbon\Carbon::createFromFormat('Y-m-d', $lic_date))
    @php ($diff_in_days = $to->diffInDays($from))


{!! Hyvikk::email_msg('vehicle_licence') !!}

 	Vehicle: {{$vehicle}}
	Vehicle Expiry date: {{date('d-m-Y',strtotime($lic_date))}}
    Remaining days: {{$diff_in_days}}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
