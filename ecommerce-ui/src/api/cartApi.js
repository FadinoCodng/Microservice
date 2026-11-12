// src/api/cartApi.js
// Client untuk cart-service.

import { request } from './http'

const BASE_URL = import.meta.env.VITE_CART_URL

export const getCart = (userId) =>
  request(BASE_URL, `/carts/${userId}`).then((res) => res.data)

export const addToCart = (userId, productId, quantity) =>
  request(BASE_URL, '/carts', {
    method: 'POST',
    body: { user_id: userId, product_id: productId, quantity },
  }).then((res) => res.data)

export const updateCartItem = (itemId, quantity) =>
  request(BASE_URL, `/carts/items/${itemId}`, {
    method: 'PUT',
    body: { quantity },
  }).then((res) => res.data)

export const removeCartItem = (itemId) =>
  request(BASE_URL, `/carts/items/${itemId}`, { method: 'DELETE' })

export const clearCart = (userId) =>
  request(BASE_URL, `/carts/${userId}`, { method: 'DELETE' })
