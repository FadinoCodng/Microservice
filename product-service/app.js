const express = require("express");

const app = express();

app.use(express.json());

// Routes
const productRoutes = require("./routes/productRoutes");

app.use("/products", productRoutes);

app.get("/", (req, res) => {
  res.json({
    message: "Product Service is running",
  });
});

module.exports = app;