const productModel = require("../models/productModel");

async function index(req, res) {
  try {
    const products = await productModel.getAllProducts();

    res.json({
      message: "Berhasil mengambil data produk",
      data: products,
    });
  } catch (error) {
    res.status(500).json({
      message: "Gagal mengambil data produk",
      error: error.message,
    });
  }
}

async function show(req, res) {
  try {
    const product = await productModel.getProductById(req.params.id);

    if (!product) {
      return res.status(404).json({
        message: "Produk tidak ditemukan",
      });
    }

    res.json({
      message: "Berhasil mengambil data produk",
      data: product,
    });
  } catch (error) {
    res.status(500).json({
      message: "Gagal mengambil data produk",
      error: error.message,
    });
  }
}

module.exports = {
  index,
  show,
};