<script>
import axios from "axios";
import moment from "moment";
export default {
  props: ["PackageUser"],
  data() {
    return {
      package_user: {}
    };
  },
  methods: {
    saveSettings() {
      this.$swal({
        title: this.trans("setting.edit_confirm"),
        type: "warning",
        showCancelButton: true,
        confirmButtonText: this.trans("setting.yes"),
        cancelButtonText: this.trans("setting.no"),
      }).then((result) => {
        if (result.value) {
          //Submit form if its valid 
          new Promise((resolve, reject) => {
            axios
              .post("/admin/libs/push_notification/save", {
                package_user: "teste",
               
              })
              .then((response) => {
                if (response.data.success) {
                    this.$swal({
                      title: this.trans("setting.success_set_gateway"),
                      type: "success",
                    }).then((result) => {});
                } else {
                  this.$swal({
                    title: this.trans("setting.failed_set_gateway"),
                    html:
                      '<label class="alert alert-danger alert-dismissable text-left">' +
                      'Error'
                      "</label>",
                    type: "error",
                  }).then((result) => {});
                }
              })
              .catch((error) => {
                console.log(error);
                reject(error);
                return false;
              });
          });
        }
      });
    },
  },
  created() {
    this.PackageUser ? (this.package_user = JSON.parse(this.PackageUser)) : null;
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
            {{ trans("setting.payment_methods") }}
          </h4>
        </div>
        <div class="card-block">
          <div class="row">
            <div class="col-lg-12">
              <div class="panel-heading">
                <h3 class="panel-title">
                  {{ trans("setting.choose_payment_methods") }}
                </h3>
                <hr />
              </div>
              
            </div>
          </div>
        </div>
      </div>
    </div>

    <!--android-->
    <div class="card-margin-top">
        <div class="card-outline-info">
            <div class="card-header">
            <h4 class="m-b-0 text-white">
                {{ trans("setting.payment_methods") }}
            </h4>
            </div>
            <div class="card-block">
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                        {{ trans("setting.choose_payment_methods") }}
                        </h3>
                        <hr />
                    </div>
                
                </div>
            </div>
            </div>
        </div>
    </div>

    <!--Save-->
    <div
      style="background-color: white; padding-bottom: 2px; padding-right: 10px;"
      class="panel panel-default"
    >
      <div class="form-group text-right">
        <button v-on:click="saveSettings()" class="btn btn-success">
          <span
            class="glyphicon glyphicon-floppy-disk"
            aria-hidden="true"
          ></span>
          {{ trans("setting.save_data") }}
        </button>
      </div>
    </div>
  </div>
</template>

<style>
.card-margin-top {
  margin-top: 30px !important;
}
</style>
