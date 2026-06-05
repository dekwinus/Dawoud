@component('mail::message')
# Two-Factor Authentication

Your verification code is:

@component('mail::panel')
# {{ $otp }}
@endcomponent

This code will expire in 10 minutes. If you did not request this code, please ignore this email.

Regards,<br>
{{ config('app.name') }}
@endcomponent