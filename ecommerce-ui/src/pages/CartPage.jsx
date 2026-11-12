// src/pages/CartPage.jsx
// Halaman keranjang: isi dari cart-service, nama dan harga dari product-service.

import { useState } from 'react'
import { getCartDetail } from '../api/aggregate'
import { clearCart, removeCartItem, updateCartItem } from '../api/cartApi'
import AsyncView from '../components/AsyncView'
import { useAsync } from '../hooks/useAsync'
import { describeError, formatRupiah } from '../utils/format'

export default function CartPage({ userId }) {
  const { loading, data, error, reload } = useAsync(() => getCartDetail(userId), [userId])
  const [actionError, setActionError] = useState(null)

  // Menjalankan aksi ke cart-service, lalu memuat ulang keranjang kalau berhasil.
  async function run(action) {
    setActionError(null)
    try {
      await action()
      reload()
    } catch (err) {
      setActionError(err)
    }
  }

  return (
    <section>
      <h1>Keranjang user {userId}</h1>
      {actionError && (
        <p className="alert alert-error">{describeError(actionError)}</p>
      )}
      <AsyncView loading={loading} error={error} onRetry={reload}>
        {data && data.items.length === 0 && (
          <p className="muted">Keranjang kosong.</p>
        )}
        {data && data.items.length > 0 && (
          <>
            <table>
              <thead>
                <tr>
                  <th>Produk</th>
                  <th>Harga</th>
                  <th>Jumlah</th>
                  <th>Subtotal</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {data.items.map((item) => (
                  <tr key={item.id}>
                    <td>
                      {item.product ? (
                        item.product.name
                      ) : (
                        <em>Produk tidak tersedia</em>
                      )}
                    </td>
                    <td>
                      {item.product ? formatRupiah(item.product.price) : '-'}
                    </td>
                    <td>
                      <button
                        type="button"
                        aria-label="Kurangi"
                        disabled={item.quantity <= 1}
                        onClick={() =>
                          run(() => updateCartItem(item.id, item.quantity - 1))
                        }
                      >
                        -
                      </button>
                      <span className="qty">{item.quantity}</span>
                      <button
                        type="button"
                        aria-label="Tambah"
                        onClick={() =>
                          run(() => updateCartItem(item.id, item.quantity + 1))
                        }
                      >
                        +
                      </button>
                    </td>
                    <td>
                      {item.product
                        ? formatRupiah(Number(item.product.price) * item.quantity)
                        : '-'}
                    </td>
                    <td>
                      <button
                        type="button"
                        onClick={() => run(() => removeCartItem(item.id))}
                      >
                        Hapus
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
            <p className="total">
              Total: <strong>{formatRupiah(data.totalPrice)}</strong>
            </p>
            <button type="button" onClick={() => run(() => clearCart(userId))}>
              Kosongkan keranjang
            </button>
          </>
        )}
      </AsyncView>
    </section>
  )
}
