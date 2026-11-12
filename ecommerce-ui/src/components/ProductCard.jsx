// src/components/ProductCard.jsx
// Satu kartu produk di halaman daftar.

import { Link } from 'react-router-dom'
import { formatRupiah } from '../utils/format'
import StarRating from './StarRating'

export default function ProductCard({ product }) {
  const { id, name, price, stock, rating } = product

  return (
    <article className="card">
      <h3>
        <Link to={`/products/${id}`}>{name}</Link>
      </h3>
      <p className="price">{formatRupiah(price)}</p>
      <p className="muted">Stok: {stock}</p>
      {rating ? (
        <StarRating average={rating.average_rating} total={rating.total_reviews} />
      ) : (
        <span className="muted">Rating tidak tersedia</span>
      )}
    </article>
  )
}
