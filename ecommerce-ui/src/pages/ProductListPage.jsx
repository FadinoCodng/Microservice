// src/pages/ProductListPage.jsx
// Halaman daftar produk: data produk dari product-service, rating dari review-service.

import { getProductsWithRating } from '../api/aggregate'
import AsyncView from '../components/AsyncView'
import ProductCard from '../components/ProductCard'
import { useAsync } from '../hooks/useAsync'

export default function ProductListPage() {
  const { loading, data, error, reload } = useAsync(getProductsWithRating)

  return (
    <section>
      <h1>Produk</h1>
      <AsyncView loading={loading} error={error} onRetry={reload}>
        {data && (
          <div className="grid">
            {data.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        )}
      </AsyncView>
    </section>
  )
}
