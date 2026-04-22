import { defineStore } from 'pinia'
import api from '../axios'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
    total: 0,
  }),

  getters: {
    itemCount: (state) => state.items.reduce((sum, i) => sum + i.quantity, 0),
  },

  actions: {
    async fetchCart() {
      const res   = await api.get('/cart')
      this.items  = res.data.items
      this.total  = res.data.total
    },

    async addToCart(productId, quantity = 1) {
      for (let i = 0; i < quantity; i++) {
        await api.post('/cart', { product_id: productId })
      }
      await this.fetchCart()
    },

    async removeItem(cartId) {
      await api.delete(`/cart/${cartId}`)
      await this.fetchCart()
    },

    async checkout() {
      await api.post('/checkout')
      this.items = []
      this.total = 0
    }
  }
})