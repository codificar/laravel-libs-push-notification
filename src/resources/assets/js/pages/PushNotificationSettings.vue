<script>
import axios from "axios";
export default {
  props: [
    "IosP8url", 
    "IosKeyId", 
    "IosTeamId", 
    "PackageUser", 
    "PackageProvider", 
    "GcmBrowserKey", 
    "AudioNewRideUrl", 
    "AudioRideCancellationUrl", 
    "AudioPushNotificationUrl"
  ],
  data() {
    return {
      ios_key_id: '',
      ios_team_id: '',
      package_user: '',
      package_provider: '',
      p8FileUpload: '',
      audioPushNewRide: '',
      audioCancelPush: '',
      audioPushNotify: '',
      gcm_browser_key: '',
      sizeLimit: 300000, // in bytes
      show_upload_btn_p8: false,
      showUpaloadAudioNewRide: false,
      showUpaloadAudioCancel: false,
      showUpaloadAudioPushNotification: false
    };
  },
  methods: {
    handleFileUpload: function(id) {
      this.p8FileUpload = this.$refs.myFiles.files[0];
    },
    handleFileUploadAudioNewRide: function(id) {
      this.audioPushNewRide = this.$refs.myFilesAudioNewRide.files[0];

      if(this.audioPushNewRide && 
        (this.audioPushNewRide.size > this.sizeLimit || 
        this.audioPushNewRide.type != 'audio/mpeg')// mpeg = mp3 
      ) {  
        var message = this.trans('notification.audio_size_error')
        if(this.audioPushNewRide.type != 'audio/mpeg')// mpeg = mp3  
          message = this.trans('notification.audio_type_error');
        this.showErrorMsg(message);
        this.audioPushNewRide = '';
        this.$refs.myFilesAudioNewRide.files = null
        return false;
      }
    },
    handleFileUploadAudioCancelRide: function(id) {
      this.audioCancelPush = this.$refs.myFilesAudioCancel.files[0];

      if(this.audioCancelPush && 
        (this.audioCancelPush.size > this.sizeLimit || 
        this.audioCancelPush.type != 'audio/mpeg')// mpeg = mp3 
      ) {
        var message = this.trans('notification.audio_size_error')
        if(this.audioCancelPush.type != 'audio/mpeg')// mpeg = mp3  
          message = this.trans('notification.audio_type_error');
        this.showErrorMsg(message);
        this.audioCancelPush = '';
        this.$refs.myFilesAudioCancel.files = null
        return false;
      }
    },
    handleFileUploadAudioPushNotify: function(id) {
      this.audioPushNotify = this.$refs.myFilesAudioPushNotify.files[0];
      
      if(this.audioPushNotify && 
        (this.audioPushNotify.size > this.sizeLimit || 
        this.audioPushNotify.type != 'audio/mpeg')// mpeg = mp3 
      ) {
        var message = this.trans('notification.audio_size_error')
        if(this.audioPushNotify.type != 'audio/mpeg')// mpeg = mp3  
          message = this.trans('notification.audio_type_error');
        this.showErrorMsg(message);
        this.audioPushNotify = '';
        this.$refs.myFilesAudioPushNotify.files = null;
        return false;
      }
    },
    showErrorMsg(msg) {
      this.$swal({
        title: msg,
        type: 'error'
      });
    },
    confirmSendp8FileUpload() {
      this.$swal({
        title: this.trans('notification.save_ios_settings'),
        type: 'question',
        showCancelButton: true,
        confirmButtonText: this.trans('notification.yes'),
        cancelButtonText: this.trans('notification.no')
      }).then((result) => {
        if (result.value) {

          let formData = new FormData();
          formData.append('ios_key_id', this.ios_key_id);
          formData.append('ios_team_id', this.ios_team_id);
          formData.append('package_user', this.package_user);
          formData.append('package_provider', this.package_provider);

          if(this.p8FileUpload) {
            formData.append('p8_file_upload', this.p8FileUpload);
          }

          axios.post('/admin/libs/push_notification/save_settings/ios', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }).then(response => {
            console.log("resoponse", response);
            if(response.data.success) {
              this.$swal({
                title: this.trans('notification.settings_saved'),
                type: 'success'
              }).then((result) => {
                window.location.reload();
              })
            } else {
              if(response.data.errors && response.data.errors [0]) {
                this.showErrorMsg(response.data.errors[0]);
              } else {
                this.showErrorMsg("Error");
              }
            }
          })
          .catch(error => {
            console.log(error);
            this.showErrorMsg("Error");
          });
        }
      });
    },
    confirmSaveAndroidSettings() {
      this.$swal({
        title: this.trans('notification.save_android_settings'),
        type: 'question',
        showCancelButton: true,
        confirmButtonText: this.trans('notification.yes'),
        cancelButtonText: this.trans('notification.no')
      }).then((result) => {
        if (result.value) {

          let formData = new FormData();
          formData.append('gcm_browser_key', this.gcm_browser_key);

          if(this.audioCancelPush) {
            formData.append('audio_ride_cancelation', this.audioCancelPush);
          }

          if(this.audioPushNewRide) {
            formData.append('audio_new_ride', this.audioPushNewRide);
          }

		      if(this.audioPushNotify) {
            formData.append('audio_push_notification', this.audioPushNotify);
          }

          axios.post('/admin/libs/push_notification/save_settings/android', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }).then(response => {
            console.log("resoponse", response);
            if(response.data.success) {
              this.$swal({
                title: this.trans('notification.settings_saved'),
                type: 'success'
              }).then((result) => {
                console.log(result);
                document.location.reload(true);
              });
            } else {
              if(response.data.errors && response.data.errors [0]) {
                this.showErrorMsg(response.data.errors[0]);
              } else {
                this.showErrorMsg("Error");
              }
            }
          })
          .catch(error => {
            console.log(error);
            this.showErrorMsg("Error");
          });
        }
      });
    },
  },
  created() {
    this.ios_key_id = this.IosKeyId;
    this.ios_team_id = this.IosTeamId;
    this.package_user = this.PackageUser;
    this.package_provider = this.PackageProvider;

    this.gcm_browser_key = this.GcmBrowserKey;
    //if hasn't a file uploaded, so show upload button
    if(!this.IosP8url) {
      this.show_upload_btn_p8 = true;
    }

    if(!this.AudioRideCancellationUrl) {
      this.showUpaloadAudioCancel = true;
    }
    
    if(!this.AudioNewRideUrl) {
      this.showUpaloadAudioNewRide = true;
    }

    if(!this.AudioPushNotificationUrl) {
      this.showUpaloadAudioPushNotification = true;
    }

  },
};
</script>
<template>
  <div>

    <!--ios-->
    <div class="tab-content">
      <div class="card-outline-info">
        <div class="card-header">
          <h4 class="m-b-0 text-white">
            {{ trans("notification.ios_settings") }}
          </h4>
        </div>
        <div class="card-block">
          <div class="row">
            <div class="col-lg-6">
              <label>{{ trans('notification.team_id') }} *</label>
              <input v-model="ios_team_id" type="text" class="form-control" :placeholder="trans('notification.team_id')">
            </div>
            <div class="col-lg-6">
              <label>{{ trans('notification.key_id') }} *</label>
              <input v-model="ios_key_id" type="text" class="form-control" :placeholder="trans('notification.key_id')">
            </div>
          </div>
          <br>
          <div class="row">
            <div class="col-lg-6">
              <label>{{ trans('notification.provider_package') }} *</label>
              <input v-model="package_provider" type="text" class="form-control" placeholder="br.com.packagename">
            </div>
            <div class="col-lg-6">
              <label>{{ trans('notification.user_package') }} *</label>
              <input v-model="package_user" type="text" class="form-control" placeholder="br.com.packagename">
            </div>
          </div>
          <br>
          <div class="row">
            <div class="col-lg-12">
              <div v-if="IosP8url">
                <p>Arquivo .p8 já foi enviado</p>
                <a class="btn btn-secondary" :href="IosP8url" download>{{ 'Baixar' }}</a>
                <a class="btn btn-secondary" @click="show_upload_btn_p8 = true">{{ 'Trocar' }}</a>
              </div>
              <br>
              <form v-if="show_upload_btn_p8" id="modalFormRet">
                <label for="confirm_withdraw_picture">Envie a chave privada .p8</label>
                <input
                  type="file"
                  :id="'file'"
                  :ref="'myFiles'"
                  class="form-control-file"
                  @change="handleFileUpload"
                >
                <br>
              </form>
            </div>
          </div>
          <div class="form-group text-right">
            <button v-on:click="confirmSendp8FileUpload()" class="btn btn-success">
              <span
                class="glyphicon glyphicon-floppy-disk"
                aria-hidden="true"
              ></span>
              {{ trans("notification.save_ios_settings") }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!--android-->
    <div class="card-margin-top">
      <div class="card-outline-info">
        <div class="card-header">
          <h4 class="m-b-0 text-white">
            {{ trans("notification.android_settings") }}
          </h4>
        </div>
        <div class="card-block">
          <div class="row">
            <div class="col-lg-6">
              <label>{{ trans('notification.gcm_browser_key') }} *</label>
              <input v-model="gcm_browser_key" type="text" class="form-control">
            </div>
          </div>
          <br>
          
          <div class="row">

            <!--audio New Ride -->
            <div class="col-lg-12 audio-container">
              <h3 for="confirm_withdraw_picture">{{ trans('notification.audio_new_ride') }}</h3>
              <div v-if="AudioNewRideUrl">
                <p>{{ trans('notification.audio_uploaded') }}</p>
                <audio controls id="ringSound">
                    <source od="ringSoundSource" :src="AudioNewRideUrl" type="audio/x-wav; audio/x-mp3;" />
                    Seu navegador não tem suporte a reprodução de áudio.
                </audio>
                <div class="container-options">
                  <a class="btn btn-secondary" :href="AudioNewRideUrl" download>{{ 'Baixar' }}</a>
                  <a class="btn btn-secondary" @click="showUpaloadAudioNewRide = true">{{ 'Trocar' }}</a>
                </div>
              </div>
              <form v-if="showUpaloadAudioNewRide" id="modalFormRetPush">
                <input
                  type="file"
                  accept="audio/mp3"
                  :id="'file'"
                  :ref="'myFilesAudioNewRide'"
                  class="form-control-file"
                  @change="handleFileUploadAudioNewRide"
                >
                <br>
              </form>
            </div>

            <!--audio Ride Cancelation -->
            <div class="col-lg-12 audio-container">
              <h3 for="confirm_withdraw_picture">{{ trans('notification.audio_cancellation_ride') }}</h3>
              <div v-if="AudioRideCancellationUrl">
                <p>{{ trans('notification.audio_uploaded') }}</p>
                <audio controls id="ringSound">
                    <source od="ringSoundSource" :src="AudioRideCancellationUrl" type="audio/x-wav; audio/x-mp3;" />
                    Seu navegador não tem suporte a reprodução de áudio.
                </audio>
                <div class="container-options">
                  <a class="btn btn-secondary" :href="AudioRideCancellationUrl" download>{{ 'Baixar' }}</a>
                  <a class="btn btn-secondary" @click="showUpaloadAudioCancel = true">{{ 'Trocar' }}</a>
                </div>
              </div>
              <form v-if="showUpaloadAudioCancel" id="modalFormRetCancel">
                <input
                  type="file"
                  accept="audio/mp3"
                  :id="'file'"
                  :ref="'myFilesAudioCancel'"
                  class="form-control-file"
                  @change="handleFileUploadAudioCancelRide"
                >
                <br>
              </form>
            </div>

            <!--audio Push Notify -->
			      <div class="col-lg-12 audio-container">
              <h3 for="confirm_withdraw_picture">{{ trans('notification.audio_push_notify') }}</h3>
              <div v-if="AudioPushNotificationUrl">
                <p>{{ trans('notification.audio_uploaded') }}</p>
                <audio controls id="ringSound">
                    <source od="ringSoundSource" :src="AudioPushNotificationUrl" type="audio/x-wav; audio/x-mp3;" />
                    Seu navegador não tem suporte a reprodução de áudio.
                </audio>
                <div class="container-options">
                  <a class="btn btn-secondary" :href="AudioPushNotificationUrl" download>{{ 'Baixar' }}</a>
                  <a class="btn btn-secondary" @click="showUpaloadAudioPushNotification = true">{{ 'Trocar' }}</a>
                </div>
              </div>
              <form v-if="showUpaloadAudioPushNotification" id="modalFormRetUrl">
                <input
                  type="file"
                  accept="audio/mp3"
                  :id="'file'"
                  :ref="'myFilesAudioPushNotify'"
                  class="form-control-file"
                  @change="handleFileUploadAudioPushNotify"
                >
                <br>
              </form>
            </div>
          </div>
          <div class="form-group text-right save-android-container">
            <button v-on:click="confirmSaveAndroidSettings()" class="btn btn-success">
              <span
                class="glyphicon glyphicon-floppy-disk"
                aria-hidden="true"
              ></span>
              {{ trans("notification.save_android_settings") }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
.card-margin-top {
  margin-top: 30px !important;
}

.audio-container {
  padding-top: 5px;
  padding-bottom: 5px;
  border-width: 1px;
  border-style: solid;
  border-color: #DDD;
  border-radius: 5px;
  margin-top: 2px;
}

.save-android-container {
  margin-top: 5px;
}

.container-options {
  margin-bottom: 10px;
}

audio {
  height: 33px;
}
</style>
