@component('mail::message')
# New Company Registered

A new company has been registered on your platform.

**Company Name:** {{ $company->name }} <br>
**Owner:** {{ $company->owner->name }} ({{ $company->owner->email }}) <br>
**Website:** {{ $company->website ?? 'N/A' }} <br>
**Email:** {{ $company->email }}

Thanks,
{{ config('app.name') }}
@endcomponent
