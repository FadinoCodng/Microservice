// src/components/ReviewForm.jsx
// Formulir untuk menulis review. Mengirim ke review-service lewat reviewApi.

import { useState } from 'react'
import { createReview } from '../api/reviewApi'
import { describeError } from '../utils/format'

export default function ReviewForm({ productId, userId, onCreated }) {
  const [rating, setRating] = useState(5)
  const [comment, setComment] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState(null)

  async function handleSubmit(event) {
    event.preventDefault() // cegah browser memuat ulang halaman
    setSubmitting(true)
    setError(null)

    try {
      await createReview({ productId, userId, rating, comment })
      setComment('')
      onCreated() // beri tahu halaman induk supaya daftar review dimuat ulang
    } catch (err) {
      setError(err)
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <form className="form" onSubmit={handleSubmit}>
      <h3>Tulis review sebagai user {userId}</h3>
      <label>
        Rating
        <select value={rating} onChange={(e) => setRating(Number(e.target.value))}>
          {[5, 4, 3, 2, 1].map((n) => (
            <option key={n} value={n}>
              {n} bintang
            </option>
          ))}
        </select>
      </label>
      <label>
        Komentar
        <textarea
          rows="3"
          maxLength="1000"
          value={comment}
          onChange={(e) => setComment(e.target.value)}
        />
      </label>
      {error && <p className="alert alert-error">{describeError(error)}</p>}
      <button type="submit" disabled={submitting}>
        {submitting ? 'Mengirim...' : 'Kirim review'}
      </button>
    </form>
  )
}
