const express = require('express');
const session = require('express-session');
const { Pool } = require('pg');
const bcrypt = require('bcryptjs');

const app = express();

// Database connection
const pool = new Pool({
    connectionString: process.env.DATABASE_URL,
    ssl: { rejectUnauthorized: false }
});

// Middleware
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(session({
    secret: 'campusmart-secret',
    resave: false,
    saveUninitialized: true
}));

// Test route
app.get('/test', async (req, res) => {
    try {
        const result = await pool.query('SELECT NOW()');
        res.json({ success: true, time: result.rows[0].now });
    } catch (err) {
        res.json({ success: false, error: err.message });
    }
});

// Home
app.get('/', (req, res) => {
    res.send(`
        <html><body style="background:#000;color:#fff;text-align:center;padding:50px;">
        <h1>🏪 Campus Mart</h1>
        <a href="/buyer-login" style="color:#fff;">Buyer Login</a> | 
        <a href="/seller-login" style="color:#fff;">Seller Login</a>
        <p><a href="/test" style="color:#0f0;">Test Database</a></p>
        </body></html>
    `);
});

// Buyer Login
app.get('/buyer-login', (req, res) => {
    res.send(`
        <html><body style="background:#000;color:#fff;padding:20px;">
        <h1>Buyer Login</h1>
        <form method="POST" action="/buyer-login">
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <button type="submit">Login</button>
        </form>
        <a href="/buyer-signup">Signup</a>
        </body></html>
    `);
});

app.post('/buyer-login', async (req, res) => {
    const { email, password } = req.body;
    try {
        const result = await pool.query('SELECT * FROM buyer WHERE email = $1', [email]);
        if (result.rows.length > 0 && await bcrypt.compare(password, result.rows[0].password)) {
            req.session.buyer_id = result.rows[0].buyer_id;
            res.redirect('/buyer-dashboard');
        } else {
            res.send('<script>alert("Invalid login"); window.location="/buyer-login";</script>');
        }
    } catch (err) {
        res.send('<script>alert("Error: ' + err.message + '"); window.location="/buyer-login";</script>');
    }
});

// Buyer Signup
app.get('/buyer-signup', (req, res) => {
    res.send(`
        <html><body style="background:#000;color:#fff;padding:20px;">
        <h1>Buyer Signup</h1>
        <form method="POST" action="/buyer-signup">
            <input type="text" name="name" placeholder="Name" required><br><br>
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="text" name="phone" placeholder="Phone" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <button type="submit">Signup</button>
        </form>
        <a href="/buyer-login">Login</a>
        </body></html>
    `);
});

app.post('/buyer-signup', async (req, res) => {
    const { name, email, phone, password } = req.body;
    const hashed = await bcrypt.hash(password, 10);
    try {
        await pool.query('INSERT INTO buyer (name, email, phone, password) VALUES ($1, $2, $3, $4)', [name, email, phone, hashed]);
        res.send('<script>alert("Signup successful"); window.location="/buyer-login";</script>');
    } catch (err) {
        res.send('<script>alert("Email already exists"); window.location="/buyer-signup";</script>');
    }
});

// Buyer Dashboard
app.get('/buyer-dashboard', async (req, res) => {
    if (!req.session.buyer_id) return res.redirect('/buyer-login');
    try {
        const products = await pool.query('SELECT * FROM products WHERE status = $1', ['Available']);
        let html = '<html><body style="background:#000;color:#fff;padding:20px;"><h1>Available Products</h1><a href="/logout">Logout</a><br><br>';
        for (let p of products.rows) {
            html += `<div style="border:1px solid #fff;padding:10px;margin:10px 0;">
                        <h3>${p.item_name}</h3>
                        <p>₹${p.price}</p>
                        <a href="/buy-now?product_id=${p.product_id}">Buy Now</a>
                    </div>`;
        }
        html += '</body></html>';
        res.send(html);
    } catch (err) {
        res.send('<h3>Error loading products</h3><a href="/">Back</a>');
    }
});

// Seller Login
app.get('/seller-login', (req, res) => {
    res.send(`
        <html><body style="background:#000;color:#fff;padding:20px;">
        <h1>Seller Login</h1>
        <form method="POST" action="/seller-login">
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <button type="submit">Login</button>
        </form>
        <a href="/seller-signup">Signup</a>
        </body></html>
    `);
});

app.post('/seller-login', async (req, res) => {
    const { email, password } = req.body;
    try {
        const result = await pool.query('SELECT * FROM seller WHERE email = $1', [email]);
        if (result.rows.length > 0 && await bcrypt.compare(password, result.rows[0].password)) {
            req.session.seller_id = result.rows[0].seller_id;
            res.redirect('/seller-dashboard');
        } else {
            res.send('<script>alert("Invalid login"); window.location="/seller-login";</script>');
        }
    } catch (err) {
        res.send('<script>alert("Error"); window.location="/seller-login";</script>');
    }
});

// Seller Signup
app.get('/seller-signup', (req, res) => {
    res.send(`
        <html><body style="background:#000;color:#fff;padding:20px;">
        <h1>Seller Signup</h1>
        <form method="POST" action="/seller-signup">
            <input type="text" name="name" placeholder="Name" required><br><br>
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="text" name="phone" placeholder="Phone" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <button type="submit">Signup</button>
        </form>
        <a href="/seller-login">Login</a>
        </body></html>
    `);
});

app.post('/seller-signup', async (req, res) => {
    const { name, email, phone, password } = req.body;
    const hashed = await bcrypt.hash(password, 10);
    try {
        await pool.query('INSERT INTO seller (name, email, phone, password) VALUES ($1, $2, $3, $4)', [name, email, phone, hashed]);
        res.send('<script>alert("Signup successful"); window.location="/seller-login";</script>');
    } catch (err) {
        res.send('<script>alert("Email already exists"); window.location="/seller-signup";</script>');
    }
});

// Seller Dashboard
app.get('/seller-dashboard', async (req, res) => {
    if (!req.session.seller_id) return res.redirect('/seller-login');
    try {
        const products = await pool.query('SELECT * FROM products WHERE seller_id = $1', [req.session.seller_id]);
        let html = '<html><body style="background:#000;color:#fff;padding:20px;"><h1>My Products</h1><a href="/add-product">Add Product</a> | <a href="/logout">Logout</a><br><br>';
        for (let p of products.rows) {
            html += `<div style="border:1px solid #fff;padding:10px;margin:10px 0;">
                        <h3>${p.item_name}</h3>
                        <p>₹${p.price} | Status: ${p.status}</p>
                    </div>`;
        }
        html += '</body></html>';
        res.send(html);
    } catch (err) {
        res.send('<h3>Error</h3><a href="/">Back</a>');
    }
});

// Add Product
app.get('/add-product', (req, res) => {
    if (!req.session.seller_id) return res.redirect('/seller-login');
    res.send(`
        <html><body style="background:#000;color:#fff;padding:20px;">
        <h1>Add Product</h1>
        <form method="POST" action="/add-product">
            <input type="text" name="item_name" placeholder="Product Name" required><br><br>
            <input type="text" name="category" placeholder="Category" required><br><br>
            <select name="item_condition" required>
                <option value="">Condition</option>
                <option>New</option><option>Like New</option><option>Good</option>
            </select><br><br>
            <input type="number" name="price" placeholder="Price" required><br><br>
            <textarea name="description" placeholder="Description"></textarea><br><br>
            <button type="submit">Add</button>
        </form>
        <a href="/seller-dashboard">Back</a>
        </body></html>
    `);
});

app.post('/add-product', async (req, res) => {
    if (!req.session.seller_id) return res.redirect('/seller-login');
    const { item_name, category, item_condition, price, description } = req.body;
    try {
        await pool.query(`INSERT INTO products (seller_id, item_name, category, item_condition, price, description, status) VALUES ($1, $2, $3, $4, $5, $6, 'Available')`, [req.session.seller_id, item_name, category, item_condition, price, description]);
        res.send('<script>alert("Product added"); window.location="/seller-dashboard";</script>');
    } catch (err) {
        res.send('<script>alert("Error"); window.location="/add-product";</script>');
    }
});

// Buy Now
app.get('/buy-now', async (req, res) => {
    if (!req.session.buyer_id) return res.redirect('/buyer-login');
    const product_id = req.query.product_id;
    try {
        const product = await pool.query('SELECT * FROM products WHERE product_id = $1', [product_id]);
        res.send(`
            <html><body style="background:#000;color:#fff;padding:20px;">
            <h1>${product.rows[0].item_name}</h1>
            <p>Price: ₹${product.rows[0].price}</p>
            <form method="POST" action="/confirm-purchase">
                <input type="hidden" name="product_id" value="${product_id}">
                <select name="delivery_option" required>
                    <option value="">Delivery Option</option>
                    <option>Campus Delivery</option>
                    <option>Self Pickup</option>
                </select><br><br>
                <input type="text" name="details" placeholder="Delivery Details" required><br><br>
                <button type="submit">Confirm Purchase</button>
            </form>
            <a href="/buyer-dashboard">Back</a>
            </body></html>
        `);
    } catch (err) {
        res.send('<h3>Error</h3><a href="/buyer-dashboard">Back</a>');
    }
});

app.post('/confirm-purchase', async (req, res) => {
    if (!req.session.buyer_id) return res.redirect('/buyer-login');
    const { product_id, delivery_option, details } = req.body;
    try {
        await pool.query(`INSERT INTO orders (product_id, buyer_id, delivery_option, delivery_details, payment_method, order_status) VALUES ($1, $2, $3, $4, 'Cash on Delivery', 'Pending')`, [product_id, req.session.buyer_id, delivery_option, details]);
        await pool.query(`UPDATE products SET status = 'Sold' WHERE product_id = $1`, [product_id]);
        res.send('<script>alert("Purchase confirmed"); window.location="/buyer-dashboard";</script>');
    } catch (err) {
        res.send('<script>alert("Error"); window.location="/buyer-dashboard";</script>');
    }
});

// Logout
app.get('/logout', (req, res) => {
    req.session.destroy();
    res.redirect('/');
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log('Server running'));