// src/components/StarRating.jsx
// Menampilkan rata-rata rating dalam bentuk bintang.

export default function StarRating({ average, total, compact = false }) {
  if (!total) return <span className="muted">Belum ada ulasan</span>

  const filled = Math.round(average)

  return (
    <span className="stars" title={`${average} dari 5`}>
      {'★'.repeat(filled)}
      {'☆'.repeat(5 - filled)}
      {!compact && (
        <span className="muted">
          {' '}
          {Number(average).toFixed(1)} ({total} ulasan)
        </span>
      )}
    </span>
  )
}
