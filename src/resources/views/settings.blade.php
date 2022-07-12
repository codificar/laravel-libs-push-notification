@extends('layout.master')

@section('breadcrumbs')
<div class="row page-titles">
	<div class="col-md-6 col-8 align-self-center">

		<h3 class="text-themecolor m-b-0 m-t-0">{{ trans('push::notification.push_settings')}}</h3>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="javascript:void(0)">{{ trans('push::notification.home') }}</a></li>
			<li class="breadcrumb-item active">{{ trans('push::notification.push_notification') }}</li>
		</ol>
	</div>
</div>
@stop


@section('content')
	<div id="VueJs" class="col-sm-12">

		<settingsnotification
			ios-p8url="{{ $ios_p8url }}"
			ios-key-id="{{ $ios_key_id }}"
			ios-team-id="{{ $ios_team_id }}"
			package-user="{{ $package_user }}"
			package-provider="{{ $package_provider }}"
			gcm-browser-key="{{ $gcm_browser_key }}"
			audio-push-url="{{ $audio_push_url }}"
			audio-beep-url="{{ $audio_beep_url }}"
			audio-url="{{ $audio_url }}"
			audio-push-cancel-url="{{ $audio_push_cancellation }}"

			audio-new-ride-url="{{ $audio_new_ride }}"
			audio-ride-cancellation-url="{{ $audio_ride_cancelation }}"
			audio-push-notification-url="{{ $audio_push_notification }}"
			
		>
		</settingsnotification>

	</div>
@stop

@section('javascripts')
<script src="/libs/push_notification/lang.trans/notification"> </script>
<script src="{{ asset('vendor/codificar/push_notification/push_notification.vue.js') }}"> </script>
@stop
