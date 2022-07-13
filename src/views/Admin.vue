<template>
  <div class="body-wrapper"
       v-loading = "loading"
       element-loading-text="Загрузка данных..."
       :element-loading-spinner="svg"
       element-loading-svg-view-box="-10, -10, 50, 50"
  >
    <el-row :gutter="24">
      <el-col :span="12">
        <h3>Администраторы</h3>
      </el-col>
      <el-col :span="12">
        <h3>Ответственные</h3>
      </el-col>
    </el-row>

    <el-row :gutter="24">
      <el-col :span="7">
        <el-select
            v-model="admin"
            :class="errors.admin ? 'invalid' : ''"
            style="width: 100%"
            filterable
            :remote-method="searchAdmin"
            remote
            reserve-keyword
            placeholder="Укажите ФИО"
        >
          <el-option
              v-for="item in adminsList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
          >
          </el-option>
        </el-select>
        <small v-if="errors.admin">{{ errors.admin}}</small>
      </el-col>
      <el-col :span="3">
        <el-button
            class="claim-save-element-button"
            @click="addAdmin"
            icon="el-icon-circle-plus"
        >
          Добавить
        </el-button>
      </el-col>
      <el-col :span="2">
      </el-col>

      <el-col :span="7">
        <el-select
            v-model="responsible"
            :class="errors.responsible ? 'invalid' : ''"
            :remote-method="searchResponsible"
            style="width: 100%"
            filterable
            remote
            reserve-keyword
            placeholder="Укажите ФИО"
        >
          <el-option
              v-for="item in responsibleList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
          >
          </el-option>
        </el-select>
        <small v-if="errors.responsible">{{ errors.responsible}}</small>
      </el-col>
      <el-col :span="3">
        <el-button
            class="claim-save-element-button"
            @click="addResponsible"
            icon="el-icon-circle-plus"
        >
          Добавить
        </el-button>
      </el-col>
    </el-row>

    <el-row :gutter="24" >
      <el-col :span="10">
        <el-table
            style="width: 100%"
            :data="admins"
        >
          <el-table-column
              prop="label"
          >
          </el-table-column>
          <el-table-column
              align="right"
          >
            <template #default="scope">
              <el-button
                  type="danger"
                  @click="removeAdmin(scope.$index)"
              >
                X
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-col>
      <el-col :span="2">
      </el-col>
      <el-col :span="10">
        <el-table
            :data="responsibles"
            style="width: 100%"
        >
          <el-table-column
              prop="label"
          >
          </el-table-column>
          <el-table-column
              align="right"
          >
            <template #default="scope">
              <el-button
                  type="danger"
                  @click="removeResponsible(scope.$index)"
              >
                X
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-col>
    </el-row>
  </div>

</template>

<script>
import {ref, reactive, inject, watchEffect} from 'vue';
import  {ElMessageBox }  from 'element-plus';
export default {
  name: "Admin",
  setup(){
    const loadJson    = inject('loadJson');
    const notify      = inject('notify');
    const userId      = inject('userId');

    const adminsId    = ref(null);
    const admin       = ref(null);
    const admins      = reactive([]);
    const adminsList  = reactive([]);

    const responsiblesId   = ref(null);
    const responsible      = ref(null);
    const responsibles     = reactive([]);
    const responsibleList  = reactive([]);

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

    const errors      = reactive({
      admin       : null,
      responsible : null,
    })

    async function searchAdmin(query){
      let url = '/repair-requests/search/user';
      let result = await loadJson(url, {q: query});
      if (result.status === 'success') {
        adminsList.length = 0;
        result.data.forEach(el => adminsList.push(el));
      };
    };
    async function searchResponsible(query){
      let url = '/repair-requests/search/user';
      let result = await loadJson(url, {q: query});
      if (result.status === 'success') {
        responsibleList.length = 0;
        result.data.forEach(el => responsibleList.push(el));
      };
    };

    async function addAdmin(){
      if (admin.value) {
        admins.push({value : admin.value, label : 'не важно'});
        await updateAdmin();
      }
      else errors.admin = 'Необходимо указать ФИО';
    };
    function removeAdmin(index){
      ElMessageBox.confirm('Вы уверены, что хотите удалить администратора?')
          .then(async () => {
            admins.splice(index,1);
            await updateAdmin();
          })
          .catch(() => {})
    };
    async function updateAdmin(){
      loading.value = true;
      let id = [];
      id = admins.map(el => {return el.value});

      let result = await loadJson('/repair-requests/role/set', {role_id : adminsId.value, users  : id, user_id : userId.value});
      if (result.status == 'success') {
        admin.value = null;
        await getData();
      };
      loading.value = false;
      notify('Добавление администратора', result.message, result.status);
    };

    async function addResponsible(){
      if (responsible.value){
        responsibles.push({value : responsible.value, label : 'не важно'});
        await updateResponsible();
      }

      else errors.responsible = 'Необходимо указать ФИО';

    };
    function removeResponsible(index){
      ElMessageBox.confirm('Вы уверены, что хотите удалить ответственного?')
          .then(async() => {
            responsibles.splice(index,1);
            await updateResponsible();
          })
          .catch(() => {})
    };
    async function updateResponsible(){
      loading.value = true;
      let id = [];
      id = responsibles.map(el => {return el.value});
      let result = await loadJson('/repair-requests/role/set', {role_id : responsiblesId.value, users  : id, user_id : userId.value});
      if (result.status == 'success') {
        responsible.value = null;
        await getData();
      };
      loading.value = false;
      notify('Добавление ответственного', result.message, result.status)
    };

    async function getData(){
      loading.value = true;
      let result = await loadJson('/repair-requests/role/get', {user_id : userId.value});
      if (result.status == 'success' && result.data.length > 0){
        result.data.forEach(el => {
          if (el.name == 'admin'){
            adminsId.value = el.id;
            admins.length = 0;
            el.users.forEach(item =>admins.push(item));
          }
          if (el.name == 'responsible'){
            responsiblesId.value = el.id;
            responsibles.length = 0;
            el.users.forEach(item =>responsibles.push(item));
          }
        })

      }
      loading.value = false;
    };
    getData()

    watchEffect(() => {
      admin.value  ? errors.admin  = null : '';
      responsible.value ? errors.responsible = null : '';
    })

    return{
      admin, admins, adminsList, responsible, responsibles, responsibleList, errors,
      svg, loading,
      searchAdmin, searchResponsible,
      addAdmin, removeAdmin,
      addResponsible, removeResponsible
    }
  },
}
</script>

<style scoped>

</style>