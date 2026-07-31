import { createRouter, createWebHistory } from 'vue-router';

const routes = [
  {
    path: '/',
    name: 'Home',
    component: () => import('../views/Home.vue')
  },
  {
    path: '/characters',
    name: 'Characters',
    component: () => import('../views/Characters.vue')
  },
  {
    path: '/character/:id',
    name: 'CharacterDetail',
    component: () => import('../views/CharacterDetail.vue')
  },
  {
    path: '/my-characters',
    name: 'MyCharacters',
    component: () => import('../views/MyCharacters.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/transactions',
    name: 'Transactions',
    component: () => import('../views/Transactions.vue')
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/Login.vue')
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../views/Register.vue')
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// 路由守卫
router.beforeEach((to, from, next) => {
  const currentUser = localStorage.getItem('currentUser');
  
  if (to.meta.requiresAuth && !currentUser) {
    next('/login');
  } else {
    next();
  }
});

export default router;
