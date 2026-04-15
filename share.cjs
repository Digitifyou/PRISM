const { exec, spawn } = require('child_process');
const path = require('path');

// Configuration
const API_PORT = 3001;
const APP_PORT = 5173;
const API_SUBDOMAIN = 'smm-api-' + Math.random().toString(36).substring(7);
const APP_SUBDOMAIN = 'smm-app-' + Math.random().toString(36).substring(7);

console.log('--- SMM Automation: Team Access Engine ---');
console.log('1. Starting Tunnels...');

function startTunnel(port, subdomain, label) {
    return new Promise((resolve, reject) => {
        const lt = spawn('npx', ['localtunnel', '--port', port, '--subdomain', subdomain], { shell: true });
        
        lt.stdout.on('data', (data) => {
            const output = data.toString();
            if (output.includes('.loca.lt')) {
                const url = output.trim();
                console.log(`[${label}] Public URL: ${url}`);
                resolve(url);
            }
        });

        lt.stderr.on('data', (data) => console.error(`[${label} Error]:`, data.toString()));
        
        lt.on('close', (code) => {
            console.log(`[${label}] Tunnel closed with code ${code}`);
        });
    });
}

async function start() {
    try {
        // Start Backend Tunnel First
        const apiUrl = await startTunnel(API_PORT, API_SUBDOMAIN, 'API');
        const publicApiUrl = apiUrl + '/api';

        console.log('2. Starting Backend Services...');
        const backend = spawn('npm', ['run', 'dev'], { 
            cwd: path.join(__dirname, 'backend'),
            shell: true,
            stdio: 'inherit'
        });

        console.log('3. Starting Frontend Services...');
        // Pass the public API URL to the frontend environment
        const frontend = spawn('npm', ['run', 'dev'], {
            cwd: path.join(__dirname, 'frontend'),
            shell: true,
            stdio: 'inherit',
            env: { ...process.env, VITE_API_URL: publicApiUrl }
        });

        // Start Frontend Tunnel
        const appUrl = await startTunnel(APP_PORT, APP_SUBDOMAIN, 'APP');

        console.log('\n\n' + '='.repeat(50));
        console.log('🚀 TEAM ACCESS READY');
        console.log(`SHARE THIS LINK: ${appUrl}`);
        console.log('='.repeat(50) + '\n');

    } catch (err) {
        console.error('Fatal Error starting Team Share:', err);
    }
}

start();
