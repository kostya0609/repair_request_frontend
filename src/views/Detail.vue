<template>
  <div class="body-wrapper"
       v-loading = "loading"
       element-loading-text="Загрузка данных..."
       :element-loading-spinner="svg"
       element-loading-svg-view-box="-10, -10, 50, 50"
  >
    <h3>Редактирование заявки на ремонт № {{repairId}}. Статус {{map(repairStatus)}}</h3>
    <el-row>
      <el-col :span="6">
        <label class="add_repairs_label">Дата создания заявки</label>
        <el-date-picker
            v-model="repairRequest.date_create"
            style="width: 100%"
            type="date"
            format="DD.MM.YYYY"
            valueFormat="DD.MM.YYYY"
            placeholder="Выберите дату"
            :disabled="userRole!=='admin'"
        >
        </el-date-picker>
      </el-col>
      <el-col :span="1">
      </el-col>
      <el-col :span="6">
        <label class="add_repairs_label">Инициатор заявки</label>
        <el-input
            v-model="repairRequest.creator"
            placeholder="ФИО"
            disabled
        ></el-input>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="6">
        <label class="add_repairs_label">Заказчик</label>
        <el-select
            v-model="repairRequest.client_id"
            :class="errors.client_id ? 'invalid' : ''"
            style="width: 100%"
            filterable
            remote
            reserve-keyword
            :remote-method="searchClient"
            placeholder="Организация"
            :disabled="isDisable"
        >
          <el-option
              v-for="item in repairRequest.clientsList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
          >
          </el-option>
        </el-select>
        <small v-if="errors.client_id">{{errors.client_id}}</small>
      </el-col>
      <el-col :span="1">
      </el-col>
      <el-col :span="6">
        <label class="add_repairs_label">Объект ремонта</label>
        <el-input
            v-model="repairRequest.object"
            :class="errors.object ? 'invalid' : ''"
            placeholder="Укажите объект ремонта"
            :disabled="isDisable"
        ></el-input>
        <small v-if="errors.object">{{errors.object}}</small>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="6">
        <label class="add_repairs_label">Содержание ремонта</label>
        <el-input
            v-model="repairRequest.description"
            :class="errors.description ? 'invalid' : ''"
            type="textarea"
            rows="4"
            placeholder="Укажите содержание ремонта"
            :disabled="isDisable"
        >
        </el-input>
        <small v-if="errors.description">{{errors.description}}</small>
      </el-col>
      <el-col :span="1">
      </el-col>
      <el-col :span="6">
        <label class="add_repairs_label">Комментратий</label>
        <el-input
            v-model="repairRequest.comment"
            type="textarea"
            rows="4"
            placeholder="Комментарий"
            :disabled="isDisable"
        >
        </el-input>
      </el-col>
    </el-row>
    <hr style="opacity: 0.5"/>

    <el-row>
      <el-col :span="6">
        <label class="add_repairs_label">Статус заявки</label>
        <el-select
            v-model="repairRequest.status"
            :class="errors.status ? 'invalid' : ''"
            style="width: 100%"
            placeholder="Установить"
            :disabled="isResponsible"
        >
          <el-option
              v-for="item in repairRequest.statusList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
          >
          </el-option>
        </el-select>
        <small v-if="errors.status">{{errors.status}}</small>
      </el-col>
      <el-col :span="1">
      </el-col>
      <el-col :span="6">
        <label class="add_repairs_label">Дата исполнения / отклонения заявки</label>
        <el-date-picker
            v-model="repairRequest.date_final"
            :class="errors.date_final ? 'invalid' : ''"
            style="width: 100%"
            type="date"
            format="DD.MM.YYYY"
            valueFormat="DD.MM.YYYY"
            placeholder="Выберите дату"
            :disabled="userRole!=='admin'"
        >
        </el-date-picker>
        <small v-if="errors.date_final">{{errors.date_final}}</small>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="6">
        <label class="add_repairs_label">Ответственный</label>
        <el-select
            v-model="repairRequest.responsible_id"
            :class="errors.responsible_id ? 'invalid' : ''"
            filterable
            remote
            reserve-keyword
            :remote-method="searchResponsible"
            style="width: 100%"
            placeholder="Установить"
            :disabled="isResponsible"
        >
          <el-option
              v-for="item in repairRequest.responsibleList"
              :key="item.value"
              :label="item.label"
              :value="item.value">
          </el-option>
        </el-select>
        <small v-if="errors.responsible_id">{{errors.responsible_id}}</small>
      </el-col>
    </el-row>

    <el-row style="padding-top: 15px" >
      <el-col :span="6">
        <el-button
            @click="saveRepair"
            class="repair-save-button"
        >
          Сохранить
        </el-button>
      </el-col>
      <el-col :span="1">
      </el-col>
      <el-col :span="6">
        <el-button
            v-if="isVisible"
            @click="deleteRepair"
            class="repair-delete-button"
        >
          Удалить
        </el-button>
      </el-col>
    </el-row>


  </div>
</template>

<script>
import {ref, reactive, inject, computed, watchEffect} from "vue";
import { useRouter } from 'vue-router';


export default {
  name: "Detail",
  setup(){
    const repairStatus = inject('repairStatus')
    const repairId     = inject('repairId')

    const loadJson     = inject('loadJson');
    const userId       = inject('userId');
    const userRole     = inject('userRole');
    const notify       = inject('notify');

    const router        = useRouter();
    const svg           = `
        <path class="path" d="
          M 30 15
          L 28 17
          M 25.61 25.61
          A 15 15, 0, 0, 1, 15 30
          A 15 15, 0, 1, 1, 27.99 7.5
          L 15 15
        " style="stroke-width: 4px; fill: rgba(0, 0, 0, 0)"/>
      `;
    const loading       = ref(false);

    const repairRequest = reactive({
      date_create    : null,
      date_final     : null,
      status         : null,
      statusList     : [
        {value: 'created',   label: 'Создана'},
        {value: 'performed', label: 'Принята к исполнению'},
        {value: 'completed', label: 'Исполнена'},
        {value: 'rejected',  label: 'Отклонена'},
      ],
      creator        : null,
      creator_id     : null,
      responsible_id : null,
      responsibleList: [],
      object         : null,
      description    : null,
      client_id      : null,
      clientsList    : [{value : '1', label : 'asdgasdbg'}],
      comment        : null,
    });

    const errors = reactive({
      object         : null,
      description    : null,
      client_id      : null,
      responsible_id : null,
      date_final     : null,
      repairStatus   : null,
      status         : null,
    });

    async function getData() {
      let result = await loadJson('/repair-requests/get', {request_id: repairId.value});
      if (result.status === 'success') {
        repairRequest.date_create     = result.data.date_create;
        repairRequest.creator_id      = result.data.creator_id.value;
        repairRequest.creator         = result.data.creator_id.label;
        repairRequest.client_id       = result.data.client_id.value;
        repairRequest.clientsList.length = 0;
        result.data.client_id         ? repairRequest.clientsList.push( result.data.client_id) : '';
        repairRequest.object          = result.data.object;
        repairRequest.description     = result.data.description;
        repairRequest.status          = result.data.status;
        repairRequest.date_final      = result.data.date_final;
        repairRequest.responsible_id  = result.data.responsible_id.value;
        repairRequest.responsibleList.length = 0;
        result.data.responsible_id    ?  repairRequest.responsibleList.push(result.data.responsible_id) : '';
        repairStatus.value            = result.data.status;
      }
    };
    getData()

    function map(key){
      let dictionary = {
      'created'   : 'Создана',
      'performed' : 'Принята к исполнению',
      'completed' : 'Исполнена',
      'rejected'  : 'Отклонена',
      }
      return dictionary[key]
    };

    const isDisable = computed(() => {
      let disable = false;
      if (repairStatus.value !== 'created') disable = true;
      if (userRole.value === 'admin' || userRole.value ==='responsible') disable = false;
      return disable
    });

    const isResponsible = computed(() => {
      let disable = false;
      if (userRole.value !== 'admin' && userRole.value !=='responsible') disable = true;
      return disable
    });

    const isVisible = computed(() => {
      let visible = true
      if (repairStatus.value !== 'created') visible = false;
      if (userRole.value === 'admin') visible = true;
      return visible
    })

    function searchResponsible(query){
      let url = '/repair-requests/search/user';
      if (query !== '')
        search(query,'responsible', url);
    };

    function searchClient(query){
      let url = '/repair-requests/search/client';
      if (query !== '')
        search(query,'client', url);
    };

    async function search(query, key, url){
      let result = await loadJson(url, {q: query});
      if (result.status === 'success') {
        if (key === 'responsible') {
          repairRequest.responsibleList.length = 0;
          result.data.forEach(el => repairRequest.responsibleList.push(el));
        };
        if (key === 'client') {
          repairRequest.clientsList.length = 0;
          result.data.forEach(el => repairRequest.clientsList.push(el));
        };
      };

    };

    function isValid(){
      let valid = true;
      if (!repairRequest.client_id)  { errors.client_id   = 'Необходимо заполнить Заказчика'; valid = false};
      if (!repairRequest.object)     { errors.object      = 'Необходимо заполнить Объект ремонта'; valid = false}
      if (!repairRequest.description){ errors.description = 'Необходимо заполнить Содержание ремонта'; valid = false}

      if (repairRequest.status == 'performed' && !repairRequest.responsible_id){
        errors.responsible_id = 'Необходимо заполнить Ответственного';
        valid = false
      }

      if ((repairRequest.status == 'completed' || repairRequest.status == 'rejected') && !repairRequest.date_final){
          errors.date_final     = 'Необходимо заполнить Дату исполнения / отклонения';
          valid = false
      }

      return valid
    }

    async function saveRepair() {
     if (isValid()){
       let repairToSend = {}
       for (let key in repairRequest) {
         if (repairRequest[key] && key !== 'statusList' && key !== 'responsibleList' && key !== 'clientsList' ) {
           repairToSend[key] = repairRequest[key]
         }
       }
       repairToSend.request_id = repairId.value;
       repairToSend.user_id    = userId.value;

       loading.value = true
       let result = await loadJson('/repair-requests/edit', repairToSend);
       if (result.status == 'success') {
         repairId.value = result.id
         repairStatus.value = repairRequest.status;
         await router.push({name: 'list'})
       }
       loading.value = false
       notify('Редактирование заявки на ремонт', result.message, result.status)
     }
    };
    async function deleteRepair(){
      loading.value = true
      let result = await loadJson('/repair-requests/delete',{
        request_id : repairId.value,
        user_id    : userId.value,

      });
      if (result.status == 'success') {
        await router.push({name: 'list'})
      }
      loading.value = false
      notify('Удаление заявки на ремонт', result.message, result.status)
    };

    watchEffect(() => {
        repairRequest.client_id      ? errors.client_id = null : '';
        repairRequest.object         ? errors.object = null : '';
        repairRequest.description    ? errors.description = null : '';
        repairRequest.date_final     ? errors.date_final = null : '';
        repairRequest.status         ? errors.status = null : '';
        repairRequest.responsible_id ? errors.responsible_id = null : '';
        (repairRequest.status == 'completed' || repairRequest.status == 'rejected') ? repairRequest.date_final = new Date().toLocaleDateString() : repairRequest.date_final = '';
      });

    return {
        repairRequest, userRole, errors, svg, loading, repairId, repairStatus,
        saveRepair, deleteRepair, searchResponsible, searchClient, map, isDisable, isResponsible, isVisible,
      }
    },

}
</script>

<style scoped>

</style>