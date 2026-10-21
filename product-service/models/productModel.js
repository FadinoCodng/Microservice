const db = require("../config/db");

async function getAllProducts() {
  const [rows] = await db.query("SELECT * FROM products");
  return rows;
}

async function getProductById(id) {
  const [rows] = await db.query(
    "SELECT * FROM products WHERE id = ?",
    [id]
  );

  return rows[0];
}

module.exports = {
  getAllProducts,
  getProductById,
};