import { defineStore } from 'pinia'
import api from '../axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user:  JSON.parse(localStorage.getItem('user'))  || null,
    token: localStorage.getItem('token') || null,
  }),

  getters: {
    isLoggedIn: (state) => !!state.token,
    isAdmin:    (state) => state.user?.role === 'admin',
    isGuest:    (state) => state.user?.role === 'guest',
  },

  actions: {
    async login(email, password) {
      const res   = await api.post('/login', { email, password })
      this.token  = res.data.token
      this.user   = res.data.user
      localStorage.setItem('token', this.token)
      localStorage.setItem('user',  JSON.stringify(this.user))
      return res.data.user
    },

    async logout() {
      try { await api.post('/logout') } catch {}
      this.token = null
      this.user  = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    },

    async fetchMe() {
      const res  = await api.get('/me')
      this.user  = res.data
      localStorage.setItem('user', JSON.stringify(this.user))
    }
  }
})