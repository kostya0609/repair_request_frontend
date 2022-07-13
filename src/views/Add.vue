<template>
  <div class="body-wrapper"
       v-loading = "loading"
       element-loading-text="Загрузка данных..."
       :element-loading-spinner="svg"
       element-loading-svg-view-box="-10, -10, 50, 50"
  >
    <h3>Создание заявки на ремонт</h3>
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
            v-model="userFIO"
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
            :class="errors.client ? 'invalid' : ''"
            style="width: 100%"
            filterable
            remote
            reserve-keyword
            :remote-method="searchClient"
            placeholder="Организация"
        >

          <el-option
              v-for="item in repairRequest.clientsList"
              :key="item.value"
              :label="item.label"
              :value="item.value">
          </el-option>
        </el-select>

        <small v-if="errors.client">{{errors.client}}</small>
      </el-col>
      <el-col :span="1">
      </el-col>
      <el-col :span="6">
        <label class="add_repairs_label">Объект ремонта</label>
        <el-input
            v-model="repairRequest.object"
            :class="errors.object ? 'invalid' : ''"
            placeholder="Укажите объект ремонта"
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
        ></el-input><small v-if="errors.description">{{errors.description}}</small>

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
        ></el-input>
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
    </el-row>


  </div>
</template>

<script>
import {ref, reactive, inject, computed, watchEffect} from "vue";
import { useRouter } from 'vue-router';


export default {
  name: "Add",
  setup(){
    const repairStatus = inject('repairStatus')
    const repairId     = inject('repairId')

    const loadJson     = inject('loadJson');
    const userId       = inject('userId');
    const userRole     = inject('userRole');
    const userFIO      = inject('userFIO');
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
      date_create    : new Date().toLocaleDateString(),
      creator_id     : userId.value,
      object         : null,
      description    : null,
      client_id      : null,
      clientsList    : [],
      comment        : null,
    });

    const errors = reactive({
      object         : null,
      description    : null,
      client         : null,
    });

    async function saveRepair(){
      if(repairRequest.client_id && repairRequest.object && repairRequest.description){
        let repairToSend ={}
        for (let key in repairRequest){
          if(repairRequest[key] && key !== 'clientsList') {
            repairToSend[key] = repairRequest[key]
          }
        }
        repairToSend.user_id = userId.value;
        loading.value = true
        let result = await loadJson('/repair-requests/add',  repairToSend);
        if (result.status =='success') {
          repairId.value = result.id
          repairStatus.value = 'created';
          await router.push({name:'list'})
        }
        loading.value = false
        notify('Создание заявки на ремонт', result.message, result.status)

      } else {
        repairRequest.client      ? '' : errors.client      = 'Необходимо заполнить Заказчика';
        repairRequest.object      ? '' : errors.object      = 'Необходимо заполнить Объект ремонта';
        repairRequest.description ? '' : errors.description = 'Необходимо заполнить Содержание ремонта';
      }//если не валидна

    };

    function searchClient(query){
      let url = '/repair-requests/search/client';
      if (query !== '')
        search(query,'client', url);
    };

    async function search(query, key, url){
      let result = await loadJson(url, {q: query});
      if (result.status === 'success') {
        if (key === 'client') {
          repairRequest.clientsList.length = 0;
          result.data.forEach(el => repairRequest.clientsList.push(el));
        };
      };
    };


    watchEffect(() => {
      repairRequest.client_id     ? errors.client = null : '';
      repairRequest.object        ? errors.object = null : '';
      repairRequest.description   ? errors.description = null : '';
    });

    return{
      repairRequest, userFIO, userRole, errors, svg, loading,
      saveRepair, searchClient

    }
  },
}
</script>

<style scoped>

</style>