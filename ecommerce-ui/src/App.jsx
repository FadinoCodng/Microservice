// src/App.jsx
// Kerangka aplikasi: navigasi, pemilih user, dan peta halaman (routes).

import { useState } from 'react'
import { Route, Routes } from 'react-router-dom'
import Navbar from './components/Navbar'
import CartPage from './pages/CartPage'
import ProductDetailPage from './pages/ProductDetailPage'
import ProductListPage from './pages/ProductListPage'

export default function App() {
  // User aktif disimpan di localStorage supaya tidak kembali ke 1 saat halaman dimuat ulang.
  const [userId, setUserId] = useState(
    () => Number(localStorage.getItem('userId')) || 1,
  )

  function changeUser(value) {
    setUserId(value)
    localStorage.setItem('userId', String(value))
  }

  return (
    <>
      <Navbar userId={userId} onUserChange={changeUser} />
      <main className="container">
        <Routes>
          <Route path="/" element={<ProductListPage />} />
          <Route
            path="/products/:id"
            element={<ProductDetailPage userId={userId} />}
          />
          <Route path="/cart" element={<CartPage userId={userId} />} />
          <Route path="*" element={<p>Halaman tidak ditemukan.</p>} />
        </Routes>
      </main>
    </>
  )
}
