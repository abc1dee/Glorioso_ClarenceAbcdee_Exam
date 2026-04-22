import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

// Customer views
import LoginView      from '../views/LoginView.vue'
import RegisterView   from '../views/RegisterView.vue'
import ProductsView   from '../views/ProductsView.vue'

// Admin views
import AdminProducts  from '../views/admin/AdminProducts.vue'
import AdminUsers     from '../views/admin/AdminUsers.vue'
import AdminOrders    from '../views/admin/AdminOrders.vue'
import AdminLogs      from '../views/admin/AdminLogs.vue'

const routes = [
  // Public
  { path: '/login',    name: 'login',    component: LoginView },
  { path: '/register', name: 'register', component: RegisterView },

  // Public landing / Products
  {
    path: '/',
    name: 'products',
    component: ProductsView,
    meta: { requiresAuth: false, guestOnly: false }
  },

  // Admin (requires admin role)
  {
    path: '/admin/products',
    name: 'admin-products',
    component: AdminProducts,
    meta: { requiresAdmin: true }
  },
  {
    path: '/admin/users',
    name: 'admin-users',
    component: AdminUsers,
    meta: { requiresAdmin: true }
  },
  {
    path: '/admin/orders',
    name: 'admin-orders',
    component: AdminOrders,
    meta: { requiresAdmin: true }
  },
  {
    path: '/admin/logs',
    name: 'admin-logs',
    component: AdminLogs,
    meta: { requiresAdmin: true }
  },

  // Catch-all redirect
  { path: '/:pathMatch(.*)*', redirect: '/' }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.requiresAdmin) {
    if (!auth.isLoggedIn)  return next('/login')
    if (!auth.isAdmin)     return next('/')
  }

  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return next('/login')
  }

  // Redirect logged-in admin away from storefront
  if (to.path === '/' && auth.isAdmin) {
    return next('/admin/products')
  }

  next()
})

export default router