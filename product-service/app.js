const express = require("express");
const cors = require("cors");

const app = express();

app.use(cors());
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
