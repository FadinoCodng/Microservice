// src/utils/format.js
// Fungsi pembantu untuk menampilkan data.

const rupiah = new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  maximumFractionDigits: 0,
})

// Harga dari product-service berbentuk teks ("450000.00"), jadi diubah ke angka dulu.
export const formatRupiah = (value) => rupiah.format(Number(value))

// Mengubah ApiError menjadi teks yang bisa ditampilkan ke pengguna.
export function describeError(error) {
  const details = error.errors ? Object.values(error.errors).flat() : []
  return details.length ? `${error.message}: ${details.join(', ')}` : error.message
}
