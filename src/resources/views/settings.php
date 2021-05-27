@extends('layout.master')

@section('breadcrumbs')
<div class="row page-titles">
	<div class="col-md-6 col-8 align-self-center">

		<h3 class="text-themecolor m-b-0 m-t-0">{{ trans('pushNotificationTrans::notification.conf')}}</h3>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="javascript:void(0)">{{ trans('pushNotificationTrans::notification.home') }}</a></li>
			<li class="breadcrumb-item active">{{ trans('pushNotificationTrans::notification.gateways') }}</li>
		</ol>
	</div>
</div>	
@stop


@section('content')
	<div id="VueJs" class="col-sm-12">
		
		<settingsnotification 
			package-user="{{ json_encode($package_user)}}"
		>
		</settingsnotification>
		
	</div>
@stop

@section('javascripts')
<script src="/libs/gateways/lang.trans/notification"> </script> 
<script src="{{ elixir('vendor/codificar/push-notification/push_notification.vue.js') }}"> </script> 
@stop
