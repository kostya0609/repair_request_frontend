<template>
  <div id="app">
    <navBar  v-if="hideMenu && isToken" />
    <router-view v-if="isToken"/>
    <br v-else/>
  </div>

</template>
<script>
import navBar from "./components/navBar";
import {ref, computed, provide} from "vue";
import { useRouter, useRoute } from 'vue-router';
import  {ElNotification}  from 'element-plus'

export default {
  name: "List",
  components : {navBar},
  setup(){
    const isToken      = ref(null);
    const timerId      = ref(null);
    const router       = useRouter();
    const hideMenu     = computed(() => {
      return (!(router.currentRoute.value.name === 'detail' || router.currentRoute.value.name === 'add'))
    });

    const userId       = ref(null);
    const userFIO      = ref(null);
    const userRole     = ref(null);
    const token        = ref(null);
    const domain       = ref( process.env.NODE_ENV == 'production' ? window.location.origin + '/api' : 'https://vc.bsi.local');

    const notify       = (title, message, type) => {
      ElNotification({
        title: title,
        message: message,
        type: type,
        duration: 4000,
      })
    };

    const repairId      = ref(null);
    const repairStatus  = ref(null);

    const svg         = `
        <path class="path" d="
          M 30 15
          L 28 17
          M 25.61 25.61
          A 15 15, 0, 0, 1, 15 30
          A 15 15, 0, 1, 1, 27.99 7.5
          L 15 15
        " style="stroke-width: 4px; fill: rgba(0, 0, 0, 0)"/>
      `;
    const loading     = ref(false);

    function loadJson(url , data, type, downloadRequest){
      let body, header = {};

      if(type === 'file'){
        body = data;
      }else{
        body = {...data};
        header['Content-Type'] = 'application/json;charset=utf-8';
      }

      return fetch(
          domain.value + url,
          {
            method: 'post',
            headers: header,
            body: type === 'file' ? body : JSON.stringify(body, function(key, val) {
              return (typeof val === 'function') ? ''+ val : val;
            })
          }
      ).then(res => {
        return new Promise((resolve,reject) => {
          if(!res.ok)
            throw new Error('Response server - status code ' + res.status);
          else{

            if (downloadRequest) {
              resolve(res)
            } else {
              res.json().then(json => {
                resolve(json)
              }).catch(err => {
                reject(err);
              })
            }

          }
        })
      }).catch(err => {
        return new Promise((resolve,reject) => {
          reject({status : 'error',message : 'Некорректный ответ сервера',system : err.message})
        })
      });
    };

    async function initData(){
      //console.log('Попытка авторизоваться')
      window._userId ? userId.value = window._userId : userId.value = null;
      window._token  ? window.token = window._token  : window.token = null;
    }

    async function auth(){
      loading.value = true;
      await initData();
      if (window.token) {
        loading.value = false;
        setTimeout(() => {clearInterval(timerId.value)});

        let result = await loadJson('/repair-requests/get-user', {user_id: userId.value, token : window.token});
        if (result.status == 'success') {
          userRole.value = result.data.role;
          //userRole.value = 'responsible';
          userFIO.value = result.data.user.label;
          isToken.value = true;
          await router.push({name:'list'})
        }
      } else {
        userRole.value = null;
        isToken.value  = false;
        timerId.value  = setInterval(async () => await initData(), 15000 );
      };
    };
    auth();

    provide('domain', domain);
    provide('userId', userId);
    provide('userRole', userRole);
    provide('userFIO', userFIO);
    provide('token', token);
    provide('loadJson', loadJson);
    provide('notify', notify);
    provide('repairId', repairId);
    provide('repairStatus', repairStatus);

    return {hideMenu, isToken, svg, loading}
  },
}
</script>

<style>
/*меню navBar*/
.el-menu--horizontal{
  display: block !important;
}

.menu-right-element{
  float: right !important;
  padding-top: 10px!important;
}

.menu-right-element-button{
  background: #3bc8f5 !important;
  border-color: #3bc8f5 !important;
  color : white !important;
  text-transform: uppercase;
}

.menu-right-element-button:hover{
  background: #3fddff !important;
}

.el-menu--horizontal .el-menu-item:focus{
  background-color: #ffffff!important;
}

.el-menu--horizontal .el-menu-item:hover:last-child{
  background-color: #ffffff!important;
}

.el-menu--horizontal>.el-menu-item.is-active
{
  background-color: #ecf5ff!important;
}


/*разметка форм*/

.body-wrapper {
  padding: 15px;
}

.el-row {
  margin-bottom: 20px;
}

.el-col{
  padding-right: 1%;
}

.add_repairs_label{
  display: block;
  margin-bottom: 5px;
  color : #606266;
}

.repair-save-button{
  width: 100%;
  background: #3bc8f5  !important;
  border-color: #3bc8f5  !important;
  color : white !important;
  text-transform: uppercase;
}

.repairs-save-button:hover{
  background: #3fddff  !important;
}

.repair-delete-button{
  width: 100%;
  background: #ED6161   !important;
  border-color: #ED6161   !important;
  color : white !important;
  text-transform: uppercase;
}


/**/


/*настройки родительского контейнера*/

#workarea {

  overflow-x: hidden!important;
}
#workarea-content {
  overflow: unset !important;
}
.workarea-content-paddings{
  overflow-x: unset !important;
}
/**/


/*если не все поля на форме заполнены*/

small {
  color: #ff2400;
}
.invalid {
  border:solid 1px;
  border-color: #ff2400;
}

h3 {
  margin-top: 0!important;
}

</style>
