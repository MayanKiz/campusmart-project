const express = require('express');
const session = require('express-session');
const { Pool } = require('pg');
const bcrypt = require('bcryptjs');
const multer = require('multer');
const path = require('path');
const fs = require('fs');

const app = express();

// Database connection
const pool = new Pool({
    connectionString: process.env.DATABASE_URL,
    ssl: { rejectUnauthorized: false }
});

// Middleware
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(express.static('public'));
app.use(session({
    secret: 'your-secret-key',
    resave: false,
    saveUninitialized: true
}));

// Set views
app.set('view engine', 'ejs');

// Create uploads folder
const uploadDir = './public/uploads';
if (!fs.existsSync(uploadDir)) {
    fs.mkdirSync(uploadDir, { recursive: true });
}

// Multer for image upload
const storage = multer.diskStorage({
    destination: './public/uploads/',
    filename: (req, file, cb) => {
        cb(null, Date.now() + path.extname(file.originalname));
    }
});
const upload = multer({ storage: storage });

// ============ ROUTES ============

// Home page
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'index.html'));
});

// Buyer Login
app.get('/buyer-login', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'buyer-login.html'));
});

app.post('/buyer-login', async (req, res) => {
    const { email, password } = req.body;
    const result = await pool.query('SELECT * FROM buyer WHERE email = $1', [email]);
    
    if (result.rows.length > 0 && await bcrypt.compare(password, result.rows[0].password)) {
        req.session.buyer_id = result.rows[0].buyer_id;
        res.redirect('/buyer-dashboard');
    } else {
        res.send('<script>alert("Invalid credentials"); window.location="/buyer-login";</script>');
    }
});

// Buyer Signup
app.get('/buyer-signup', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'buyer-signup.html'));
});

app.post('/buyer-signup', async (req, res) => {
    const { name, email, phone, password } = req.body;
    const hashedPassword = await bcrypt.hash(password, 10);
    
    await pool.query(
        'INSERT INTO buyer (name, email, phone, password) VALUES ($1, $2, $3, $4)',
        [name, email, phone, hashedPassword]
    );
    res.send('<script>alert("Signup successful"); window.location="/buyer-login";</script>');
});

// Buyer Dashboard
app.get('/buyer-dashboard', async (req, res) => {
    if (!req.session.buyer_id) return res.redirect('/buyer-login');
    
    const products = await pool.query(`
        SELECT p.*, s.name as seller_name, s.phone as seller_phone 
        FROM products p 
        JOIN seller s ON p.seller_id = s.seller_id 
        WHERE p.status = 'Available'
    `);
    
    let html = '<html><head><title>Buyer Dashboard</title><link rel="stylesheet" href="/style.css"></head><body>';
    html += '<div class="container"><div class="header"><h1>🛍️ Available Products</h1><a href="/logout" class="btn btn-black">Logout</a></div>';
    
    for (let product of products.rows) {
        html += `
            <div class="card">
                <h2>${product.item_name}</h2>
                ${product.products_image ? `<img src="/uploads/${product.products_image}" width="200">` : ''}
                <p>💰 Price: ₹${product.price}</p>
                <p>📝 ${product.description}</p>
                <p>👤 Seller: ${product.seller_name}</p>
                <a href="/buy-now?product_id=${product.product_id}" class="btn">Buy Now</a>
            </div>
        `;
    }
    
    html += '</div></body></html>';
    res.send(html);
});

// Seller Login
app.get('/seller-login', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'seller-login.html'));
});

app.post('/seller-login', async (req, res) => {
    const { email, password } = req.body;
    const result = await pool.query('SELECT * FROM seller WHERE email = $1', [email]);
    
    if (result.rows.length > 0 && await bcrypt.compare(password, result.rows[0].password)) {
        req.session.seller_id = result.rows[0].seller_id;
        res.redirect('/seller-dashboard');
    } else {
        res.send('<script>alert("Invalid credentials"); window.location="/seller-login";</script>');
    }
});

// Seller Signup
app.get('/seller-signup', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'seller-signup.html'));
});

app.post('/seller-signup', async (req, res) => {
    const { name, email, phone, password } = req.body;
    const hashedPassword = await bcrypt.hash(password, 10);
    
    await pool.query(
        'INSERT INTO seller (name, email, phone, password) VALUES ($1, $2, $3, $4)',
        [name, email, phone, hashedPassword]
    );
    res.send('<script>alert("Signup successful"); window.location="/seller-login";</script>');
});

// Seller Dashboard
app.get('/seller-dashboard', async (req, res) => {
    if (!req.session.seller_id) return res.redirect('/seller-login');
    
    const products = await pool.query('SELECT * FROM products WHERE seller_id = $1', [req.session.seller_id]);
    
    let html = '<html><head><title>Seller Dashboard</title><link rel="stylesheet" href="/style.css"></head><body>';
    html += '<div class="container"><div class="header"><h1>📦 My Products</h1><a href="/add-product" class="btn">+ Add Product</a><a href="/logout" class="btn btn-black">Logout</a></div>';
    
    for (let product of products.rows) {
        html += `
            <div class="card">
                <h2>${product.item_name}</h2>
                <p>💰 ₹${product.price} | Status: ${product.status}</p>
                ${product.status === 'Sold' ? '<p class="sold">SOLD ✓</p>' : ''}
            </div>
        `;
    }
    
    html += '</div></body></html>';
    res.send(html);
});

// Add Product
app.get('/add-product', (req, res) => {
    if (!req.session.seller_id) return res.redirect('/seller-login');
    res.sendFile(path.join(__dirname, 'views', 'add-product.html'));
});

app.post('/add-product', upload.single('product_image'), async (req, res) => {
    const { item_name, category, item_condition, price, description } = req.body;
    const imageName = req.file ? req.file.filename : null;
    
    await pool.query(
        'INSERT INTO products (seller_id, item_name, category, item_condition, price, description, products_image, status) VALUES ($1, $2, $3, $4, $5, $6, $7, $8)',
        [req.session.seller_id, item_name, category, item_condition, price, description, imageName, 'Available']
    );
    res.send('<script>alert("Product added"); window.location="/seller-dashboard";</script>');
});

// Buy Now
app.get('/buy-now', async (req, res) => {
    if (!req.session.buyer_id) return res.redirect('/buyer-login');
    
    const product = await pool.query('SELECT * FROM products WHERE product_id = $1', [req.query.product_id]);
    
    let html = '<html><head><title>Buy Now</title><link rel="stylesheet" href="/style.css"></head><body>';
    html += '<div class="container"><div class="header"><h1>Confirm Purchase</h1></div>';
    html += `<div class="card"><h2>${product.rows[0].item_name}</h2><p>💰 ₹${product.rows[0].price}</p>`;
    html += `
        <form method="POST" action="/confirm-purchase">
            <input type="hidden" name="product_id" value="${product.rows[0].product_id}">
            <select name="delivery_option" required>
                <option value="">Select Delivery</option>
                <option value="Campus Delivery">Campus Delivery</option>
                <option value="Self Pickup">Self Pickup</option>
            </select>
            <input type="text" name="details" placeholder="Delivery Details" required>
            <button type="submit" class="btn">Confirm Purchase</button>
        </form>
    `;
    html += '</div></div></body></html>';
    res.send(html);
});

app.post('/confirm-purchase', async (req, res) => {
    const { product_id, delivery_option, details } = req.body;
    
    await pool.query(
        'INSERT INTO orders (product_id, buyer_id, delivery_option, delivery_details, payment_method, order_status) VALUES ($1, $2, $3, $4, $5, $6)',
        [product_id, req.session.buyer_id, delivery_option, details, 'Cash on Delivery', 'Pending']
    );
    await pool.query('UPDATE products SET status = $1 WHERE product_id = $2', ['Sold', product_id]);
    
    res.send('<script>alert("Purchase confirmed"); window.location="/buyer-dashboard";</script>');
});

// Logout
app.get('/logout', (req, res) => {
    req.session.destroy();
    res.redirect('/');
});

// ============ START SERVER ============
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});
