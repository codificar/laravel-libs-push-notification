<script>
import axios from "axios";
export default {
  props: ["IosP8url", "IosKeyId", "IosTeamId", "PackageUser", "PackageProvider", "GcmBrowserKey", "AudioPushUrl", "AudioPushCancelUrl", "AudioUrl", "AudioBeepUrl"],
  data() {
    return {
      ios_key_id: '',
      ios_team_id: '',
      package_user: '',
      package_provider: '',
      p8FileUpload: '',
      audioPush: '',
      audioUrl: '',
      audioCancelPush: '',
      gcm_browser_key: '',
      show_upload_btn_p8: false,
      show_upload_btn_audio_push: false,
      show_upload_btn_audio_url: false,
      show_upload_btn_audio_push_cancel: false
    };
  },
  methods: {
    handleFileUpload: function(id) {
      this.p8FileUpload = this.$refs.myFiles.files[0];
    },
    handleFileUploadAudio: function(id) {
      this.audioPush = this.$refs.myFilesAudio.files[0];
    },
    handleFileUploadAudioUrl: function(id) {
      this.audioUrl = this.$refs.myFilesAudioUrl.files[0];
    },
    handleFileUploadAudioCancel: function(id) {
      this.audioCancelPush = this.$refs.myFilesAudioCancel.files[0];
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
            formData.append('audio_push_cancellation', this.audioCancelPush);
          }

          if(this.audioPush) {
            formData.append('audio_push', this.audioPush);
          }

		  if(this.audioUrl) {
            formData.append('audio_url', this.audioUrl);
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

    if(!this.AudioPushUrl) {
      this.show_upload_btn_audio_push = true;
    }

    if(!this.AudioPushCancelUrl) {
      this.show_upload_btn_audio_push_cancel = true;
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
            <div class="col-lg-12">
              <div v-if="AudioPushUrl">
                <p>Arquivo de Áudio já foi enviado</p>
                <a class="btn btn-secondary" :href="AudioPushUrl" download>{{ 'Baixar' }}</a>
                <a class="btn btn-secondary" @click="show_upload_btn_audio_push = true">{{ 'Trocar' }}</a>
              </div>
              <br>
              <form v-if="show_upload_btn_audio_push" id="modalFormRetPush">
                <label for="confirm_withdraw_picture">{{ trans('notification.audio_push') }}</label>
                <input
                  type="file"
                  accept="audio/mp3"
                  :id="'file'"
                  :ref="'myFilesAudio'"
                  class="form-control-file"
                  @change="handleFileUploadAudio"
                >
                <br>
              </form>
            </div>
			<div class="col-lg-12">
              <form v-if="show_upload_btn_audio_url || !AudioUrl" id="modalFormRetUrl">
                <label for="confirm_withdraw_picture">{{ trans('notification.audio_url') }}</label>
                <input
                  type="file"
                  accept="audio/mp3"
                  :id="'file'"
                  :ref="'myFilesAudioUrl'"
                  class="form-control-file"
                  @change="handleFileUploadAudioUrl"
                >
                <br>
                <div v-if="AudioUrl">
                  <h6> Testar </h6>
                  <audio controls id="ringSound">
                      <source od="ringSoundSource" :src="AudioUrl" type="audio/x-wav; audio/x-mp3;" />
                      Seu navegador não tem suporte a reprodução de áudio.
                  </audio>
                  <p>Arquivo de Áudio url já foi enviado</p>
                  <a class="btn btn-secondary" :href="AudioUrl" download>{{ 'Baixar' }}</a>
                  <a class="btn btn-secondary" @click="show_upload_btn_audio_url = true">{{ 'Trocar' }}</a>
                </div>
              </form>
            </div>
            <div class="col-lg-12">
              <div v-if="AudioPushCancelUrl">
                <p>Arquivo de Áudio do cancelamento já foi enviado</p>
                <a class="btn btn-secondary" :href="AudioPushCancelUrl" download>{{ 'Baixar' }}</a>
                <a class="btn btn-secondary" @click="show_upload_btn_audio_push_cancel = true">{{ 'Trocar' }}</a>
              </div>
              <br>
              <form v-if="show_upload_btn_audio_push_cancel" id="modalFormRetCancel">
                <label for="confirm_withdraw_picture">{{ trans('notification.audio_push_cancellation') }}</label>
                <input
                  type="file"
                  accept="audio/mp3"
                  :id="'file'"
                  :ref="'myFilesAudioCancel'"
                  class="form-control-file"
                  @change="handleFileUploadAudioCancel"
                >
                <br>
              </form>
            </div>
          </div>
          <div class="form-group text-right">
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
</style>
