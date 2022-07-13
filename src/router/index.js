import { createRouter, createWebHistory } from 'vue-router'
import List from "../views/List"
import Add from "../views/Add"
import Report from "../views/Report"
import Admin from "../views/Admin"
import Detail from "../views/Detail"

const routes = [
  {
    path: '/repair_request/list',
    name: 'list',
    component: List
  },
  {
    path: '/repair_request/list/add',
    name: 'add',
    component: Add
  },
  {
    path: '/repair_request/list/detail',
    name: 'detail',
    component: Detail
  },
  {
    path: '/repair_request/report',
    name: 'report',
    component: Report
  },
  {
    path: '/repair_request/admin',
    name: 'admin',
    component: Admin
  },

]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router
