<template>
  <div v-loading = "loading"
       element-loading-text="Загрузка данных..."
       :element-loading-spinner="svg"
       element-loading-svg-view-box="-10, -10, 50, 50"
  >
    <Grid
        :name="name"
        @gridReady="getData"
    />
<!--        <el-button @click="sendDefaultFilterData">-->
<!--          Загрузить дефолтовый фильтр-->
<!--        </el-button>-->

<!--        <el-button @click="addAdmins">-->
<!--          Загрузить дефолтовых админов-->
<!--        </el-button>-->

  </div>
</template>

<script>
import {provide, inject, ref, reactive, computed} from 'vue';
import { useRouter } from 'vue-router';
import initGrid from '@/vdg3/use/initGrid';
import Grid from '@/vdg3/components/grid.n.vue';

export default {
  name: 'list',
  components: {
    Grid
  },
  setup(){
    const domain   = inject('domain');
    const userId   = inject('userId')
    const loadJson = inject('loadJson');
    const notify   = inject('notify');

    const repairId = inject('repairId')

    const router   = useRouter();
    const name     = ref('repairRequestList');
    const content  = reactive({
      setting         : true,
      header          : {
        id: {
          name: 'Номер',
          width: 100,
          show: true,
          sort: 100,
          contentSort: 'asc'
        },
        date_create: {
          name: 'Дата создания',
          width: 200,
          show: true,
          sort: 200,
          contentSort: 'asc'
        },
        status: {
          name: 'Статус заяки',
          width: 200,
          show: true,
          sort: 300,
          contentSort: 'asc'
        },
        creator_id: {
          name: 'Инициатор',
          width: 300,
          show: true,
          sort: 400,
          //contentSort: 'asc'
        },
        client_id: {
          name: 'Заказчик',
          width: 300,
          show: true,
          sort: 500,
          //contentSort: 'asc'
        },
        object: {
          name: 'Объект ремонта',
          width: 400,
          show: true,
          sort: 600,
          //contentSort: 'asc'
        },
        description: {
          name: 'Содержание',
          width: 400,
          show: true,
          sort: 700,
        },
        responsible_id: {
          name: 'Ответственный',
          width: 300,
          show: true,
          sort: 800,
          //contentSort: 'asc'
        },
        date_final: {
          name: 'Дата исполнения',
          width: 200,
          show: true,
          sort: 900,
          contentSort: 'asc'
        },
        comment: {
          name: 'Комментарий',
          width: 400,
          show: true,
          sort: 1000,
        },
      },
      context         : [
        {
          text: 'Открыть заявку на ремонт',
          function: (e, row) => {
            repairId.value = row.row.id[0].value;
            router.push({name:'detail', params: { id: row.row.id[0].value }});
          }
        },
      ],
      pagination      : {
        show: 'full',
        changeSize : function(value){
          getData();
        },
        changePage : function(value){
          getData();
        }
      },
      filter          : {
        profiles: [],
        show: true,
        showProfiles: true,
        filter : function(data){
          let normalFilter = {};
          for (let key in data){
            if ((data[key].type === 'number' || data[key].type === 'date')         && data[key].min)                normalFilter[key] = data[key];
            if ((data[key].type === 'list'   || data[key].type === 'searchList')   && data[key].value.length > 0 )  normalFilter[key] = data[key];
          }
          getData(normalFilter)
        },
        clear : function(data){
          getData(data)
        }
      },
      gridId          : null,
      networkSetting  : {
        domain: '', //домен для модуля грида может отличаться от домена основного приложения. Если домен тут не указать, то примениться домен приложения
        module: 'repairRequests',
        userId: userId.value,
      },
      loading         : false,
      loadJson        : function (url, data) {
        return fetch(
            content.networkSetting.domain ? content.networkSetting.domain + url : domain.value + url,
            {
              method: 'post',
              headers: {
                'Content-Type': 'application/json;charset=utf-8'
              },
              body: JSON.stringify(data, function (key, val) {
                return (typeof val === 'function') ? '' + val : val;
              })
            }
        ).then(res => {
          return new Promise((resolve, reject) => {
            if (!res.ok)
              throw new Error('Response server - status code ' + res.status);
            else {
              res.json().then(json => {
                resolve(json)
              }).catch(err => {
                reject(err);
              })
            }
          })
        }).catch(err => {
          return new Promise((resolve, reject) => {
            reject({status: 'error', message: 'Некорректный ответ сервера', system: err.message})
          })
        });
      },
      sortContent     : function (sort) {
        getData({}, sort)
      },
    });

    const svg      = `
        <path class="path" d="
          M 30 15
          L 28 17
          M 25.61 25.61
          A 15 15, 0, 0, 1, 15 30
          A 15 15, 0, 1, 1, 27.99 7.5
          L 15 15
        " style="stroke-width: 4px; fill: rgba(0, 0, 0, 0)"/>
      `

    const dataGrid = initGrid(content);
    const loading  = computed(() => { return dataGrid.loading })

    provide(name.value, dataGrid);

    async function getData(filter = {}, sort = {}) {
      dataGrid.loading = true;

      //ниже запросили элементы для таблицы
      let gridElements = [];
      let result = await loadJson('/repair-requests/list', {
        user_id : userId.value,
        count   : dataGrid.pagination.defaultSize,
        page    : dataGrid.pagination.page,
        filter  : filter,
        sort    : sort.name ? sort : {name : 'id', order : 'asc'} ,
      });
       if (result.status == 'success') {
        gridElements = result.data
        dataGrid.pagination.total = result.total;
       }
      dataGrid.methods.addElements(gridElements);
      dataGrid.loading = false;
    };

    //ниже чтоб на бэк залить дефолтовый фильтр... надо выше раскоментить нужную кнопку
    async function sendDefaultFilterData() {
      //создаст моковый дефолтный фильтр. запустить один раз при разработке, чтоб он записался на бэк
      let defaultFilterData = {
        id: {
          show: true,
          type: 'number',
          name: 'Номер',
          min: '',
          max: '',
          operation: '=',
        },
        date_create: {
          show: true,
          type: 'date',
          name: 'Дата создания',
          min: '',
          max: '',
          operation: '=',
        },
        status: {
          show: true,
          type: 'list',
          name: 'Статус заявки',
          multiple: true,
          value: [],
          option: [
            {value: 'created',   label: 'Создана'},
            {value: 'performed', label: 'Принята к исполнению'},
            {value: 'completed', label: 'Исполнена'},
            {value: 'rejected',  label: 'Отклонена'},
          ],
        },
        creator_id: {
          show: true,
          type: 'searchList',
          name: 'Инициатор',
          multiple: true,
          value: [],
          option: [],
          query: async function (data) {
            let result = await grid.loadJson('/repair-requests/search/user', {
              q: data,
            });
            if (result.status == 'success') return result.data
          },
        },
        // client_id: {
        //   show: true,
        //   type: 'list',
        //   name: 'Заказчик',
        //   multiple: true,
        //   value: [],
        //   option: [],
        // },
        responsible_id: {
          show: true,
          type: 'searchList',
          name: 'Ответственный',
          multiple: true,
          value: [],
          option: [],
          query: async function (data) {
            let result = await grid.loadJson('/repair-requests/search/user', {
              q: data,
            });
            if (result.status == 'success') return result.data
          },
        },
        date_final: {
          show: true,
          type: 'date',
          name: 'Дата исполнения',
          min: '',
          max: '',
          operation: '=',
        },
      };
      let result = await loadJson('/grid/filter/preset/default/add', {
        grid_name: name.value,
        module: dataGrid.networkSetting.module,
        data: {...defaultFilterData},
      });
      notify('Загрузка дефолтового фильтра', result.message, result.status)
    }

    //ниже чтоб на бэк залить дефолтовых админов... надо выше раскоментить нужную кнопку
    async function addAdmins(){
      let result = await loadJson('/repair-requests/add-admins', {});
      notify('Загрузка дефолтовых админов', result.message, result.status)
    };
    return {dataGrid, name, loading, svg, getData, sendDefaultFilterData, addAdmins}
  },
}
</script>
