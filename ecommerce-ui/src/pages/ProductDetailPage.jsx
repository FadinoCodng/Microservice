// src/pages/ProductDetailPage.jsx
// Halaman detail: informasi produk, tombol masuk keranjang, dan daftar review.

import { useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { getProductDetail } from '../api/aggregate'
import { addToCart } from '../api/cartApi'
import AsyncView from '../components/AsyncView'
import ReviewForm from '../components/ReviewForm'
import StarRating from '../components/StarRating'
import { useAsync } from '../hooks/useAsync'
import { describeError, formatRupiah } from '../utils/format'

export default function ProductDetailPage({ userId }) {
  const { id } = useParams() // nilai :id dari alamat /products/:id
  const { loading, data, error, reload } = useAsync(() => getProductDetail(id), [id])

  // Jumlah disimpan sebagai teks, supaya kolomnya bisa dikosongkan saat mengetik.
  const [quantity, setQuantity] = useState('1')
  const [cartMessage, setCartMessage] = useState(null)

  async function handleAddToCart() {
    const amount = Math.max(1, Number(quantity) || 1)
    try {
      await addToCart(userId, Number(id), amount)
      setCartMessage({ type: 'success', text: 'Produk masuk ke keranjang.' })
    } catch (err) {
      setCartMessage({ type: 'error', text: describeError(err) })
    }
  }

  return (
    <section>
      <Link to="/">&larr; Kembali ke daftar produk</Link>
      <AsyncView loading={loading} error={error} onRetry={reload}>
        {data && (
          <>
            <h1>{data.product.name}</h1>
            <p>{data.product.description}</p>
            <p className="price">{formatRupiah(data.product.price)}</p>
            <p className="muted">Stok: {data.product.stock}</p>
            {data.reviews && (
              <StarRating
                average={data.reviews.summary.average_rating}
                total={data.reviews.summary.total_reviews}
              />
            )}
            <div className="actions">
              <input
                type="number"
                min="1"
                value={quantity}
                aria-label="Jumlah"
                onChange={(e) => setQuantity(e.target.value)}
              />
              <button type="button" onClick={handleAddToCart}>
                Masukkan ke keranjang
              </button>
            </div>
            {cartMessage && (
              <p className={`alert alert-${cartMessage.type}`}>{cartMessage.text}</p>
            )}

            <h2>Review</h2>
            {data.reviews ? (
              <ul className="reviews">
                {data.reviews.reviews.map((review) => (
                  <li key={review.id}>
                    <StarRating average={review.rating} total={1} compact />
                    <p>{review.comment || <em className="muted">Tanpa komentar</em>}</p>
                    <small className="muted">
                      User {review.user_id},{' '}
                      {new Date(review.created_at).toLocaleDateString('id-ID')}
                    </small>
                  </li>
                ))}
                {data.reviews.reviews.length === 0 && (
                  <li className="muted">Belum ada review.</li>
                )}
              </ul>
            ) : (
              <p className="alert alert-error">Review tidak dapat dimuat saat ini.</p>
            )}

            <ReviewForm productId={Number(id)} userId={userId} onCreated={reload} />
          </>
        )}
      </AsyncView>
    </section>
  )
}
