// src/components/Navbar.jsx
// Menu navigasi dan pemilih user. Belum ada login, jadi user dipilih manual.

import { NavLink } from 'react-router-dom'

const USERS = [1, 2, 3, 4, 5]

export default function Navbar({ userId, onUserChange }) {
  return (
    <header className="navbar">
      <nav>
        <strong>Toko Online</strong>
        <NavLink to="/" end>
          Produk
        </NavLink>
        <NavLink to="/cart">Keranjang</NavLink>
      </nav>
      <label className="user">
        User ID
        <select value={userId} onChange={(e) => onUserChange(Number(e.target.value))}>
          {USERS.map((id) => (
            <option key={id} value={id}>
              User {id}
            </option>
          ))}
        </select>
      </label>
    </header>
  )
}
