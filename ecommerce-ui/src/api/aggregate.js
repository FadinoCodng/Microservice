// src/api/aggregate.js
// Penggabungan data dari beberapa service dilakukan di sini, di dalam browser.
// Tidak ada satu pun service yang tahu bentuk data gabungan ini, jadi frontend
// yang harus memanggil satu per satu lalu menyatukannya sendiri.

import { getCart } from './cartApi'
import { getProduct, getProducts } from './productApi'
import { getProductReviews } from './reviewApi'

// Daftar produk beserta ringkasan rating.
// 1 panggilan ke product-service, lalu 1 panggilan ke review-service untuk SETIAP produk.
export async function getProductsWithRating() {
  const products = await getProducts()
  return Promise.all(
    products.map(async (product) => {
      try {
        const { summary } = await getProductReviews(product.id)
        return { ...product, rating: summary }
      } catch {
        // review-service bermasalah: produk tetap ditampilkan, hanya rating-nya kosong.
        return { ...product, rating: null }
      }
    }),
  )
}

// Detail satu produk beserta review-nya. Produk wajib ada, review boleh gagal.
export async function getProductDetail(productId) {
  const [productResult, reviewResult] = await Promise.allSettled([
    getProduct(productId),
    getProductReviews(productId),
  ])

  if (productResult.status === 'rejected') throw productResult.reason

  return {
    product: productResult.value,
    reviews: reviewResult.status === 'fulfilled' ? reviewResult.value : null,
  }
}

// Isi keranjang beserta nama dan harga tiap produk.
// Cart-service hanya menyimpan product_id, jadi detail produk diambil satu per satu.
export async function getCartDetail(userId) {
  const cart = await getCart(userId)
  const items = await Promise.all(
    cart.items.map(async (item) => {
      try {
        const product = await getProduct(item.product_id)
        return { ...item, product }
      } catch {
        // produk sudah dihapus, atau product-service sedang bermasalah.
        return { ...item, product: null }
      }
    }),
  )

  const totalPrice = items.reduce(
    (sum, item) => sum + (item.product ? Number(item.product.price) * item.quantity : 0),
    0,
  )

  return { items, totalPrice }
}
