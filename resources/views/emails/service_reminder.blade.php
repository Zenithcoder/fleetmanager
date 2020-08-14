@component('mail::message')
# Service Reminder

Dear {{$user}},

{!! Hyvikk::email_msg('service_reminder') !!}

	Vehicle: {{$vehicle}}
	Service Item: {{$detail}}
	Next due date: {{date('d-m-Y',strtotime($date))}}
	Remaining days: {{$diff_in_days}}



Thanks,<br>
{{ config('app.name') }}
@endcomponent
