require('dotenv').config();
const express = require('express');
const cors = require('cors');

const { connectDB } = require('./config/database');

const path = require('path');
const app = express();

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use('/post-images', express.static(path.join(__dirname, '../public/post-images')));

// Connect to Database
connectDB();

// API Routes
const apiRoutes = require('./routes');
app.use('/api', apiRoutes);

// --- AUTOMATION JOBS (Prism Chronos) ---
const PublisherJob = require('./jobs/PublisherJob');
PublisherJob.start();

// Basic Health Check Route
app.get('/api/health', (req, res) => {
    res.json({ status: 'ok', message: 'SMM Automation API is running' });
});

// Start Server
const PORT = process.env.PORT || 3001;
app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});

module.exports = app;
