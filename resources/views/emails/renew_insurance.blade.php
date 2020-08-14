@component('mail::message')
# Renew Insurance

Dear {{$user}},

{!! Hyvikk::email_msg('insurance') !!}

	Vehicle: {{$vehicle}}
	Insurance Expiry date: {{date('d-m-Y',strtotime($ins_date))}}
	Remaining days: {{$diff_in_days}}





Thanks,<br>
{{ config('app.name') }}
@endcomponent
