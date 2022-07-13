<template>
  <div>
    <el-menu
        :default-active="activeIndex"
        mode="horizontal"
        :router=true
    >
      <el-menu-item v-for="(item,key) in menu" :index="item.path" :key="key">{{item.text}}</el-menu-item>
      <el-menu-item v-if="activeIndex=='/repair_request/list'" class="menu-right-element" index="/repair_request/list/add">
        <el-button
            class="menu-right-element-button"
            @click="claimId = null"
        >
          Добавить заявку
        </el-button>
      </el-menu-item>
    </el-menu>
  </div>
</template>

<script>
import {ref, reactive, watchEffect, inject} from 'vue';
import {useRoute} from 'vue-router'

export default {
  name: "navBar",
  setup(){
    const repairId    = inject('repairId')
    const userRole    = inject('userRole')

    const route       = useRoute()
    const menu        = reactive([
      {path : '/repair_request/list',   text : 'Заявки на ремонт'},
      //{path : '/repair_request/report', text : 'Отчет'},
    ]);
    const activeIndex = ref('/repair_request/list')

    userRole.value === 'admin' ? menu.push({path : '/repair_request/admin',  text : 'Управление доступом'}) : '';

    watchEffect(() => {
      activeIndex.value = route.path
    })

    return {menu, activeIndex}
  },



}
</script>

<style scoped>

</style>