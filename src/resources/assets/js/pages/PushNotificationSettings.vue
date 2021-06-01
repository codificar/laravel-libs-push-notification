<script>
import axios from "axios";
import moment from "moment";
export default {
  props: ["IosP8url", "IosKeyId", "IosTeamId", "PackageUser", "PackageProvider"],
  data() {
    return {
      ios_p8url: '',
      ios_key_id: '',
      ios_team_id: '',
      package_user: '',
      package_provider: '',

      p8FileUpload: '',
      show_upload_btn: false
    };
  },
  methods: {
    handleFileUpload: function(id) {
      console.log(this.$refs.myFiles.files);
      this.p8FileUpload = this.$refs.myFiles.files[0];
    },
    showErrorMsg(msg) {
      this.$swal({
        title: msg,
        type: 'error'
      });
    },
    confirmSendp8FileUpload(id) {
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
          
          axios.post('/admin/libs/push_notification/save_settings', formData, {
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
    this.ios_p8url = this.IosP8url;
    this.ios_key_id = this.IosKeyId;
    this.ios_team_id = this.IosTeamId;
    this.package_user = this.PackageUser;
    this.package_provider = this.PackageProvider;
    
    //if hasn't a file uploaded, so show upload button
    if(!this.IosP8url) {
      this.show_upload_btn = true;
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
                <a class="btn btn-secondary" @click="show_upload_btn = true">{{ 'Trocar' }}</a>
              </div>
              <br>
              <form v-if="show_upload_btn" id="modalFormRet">
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
                <div class="col-lg-12">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                        {{ trans("notification.android_settings") }}
                        </h3>
                        <hr />
                    </div>
                
                </div>
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
