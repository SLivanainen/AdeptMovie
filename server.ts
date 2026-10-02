/**
 * Adept Cinema - Node.js Full-Stack Proxy & PHP Server Runner
 *
 * This server binds to PORT (default 3000) as required by Google AI Studio,
 * launches the native PHP 8.2 built-in server on 127.0.0.1:8085,
 * and proxies all HTTP requests, preserving headers, cookies, redirects,
 * and traditional PHP form POST/GET flows.
 */

import express from 'express';
import { spawn, ChildProcess } from 'child_process';
import { createProxyMiddleware } from 'http-proxy-middleware';
import http from 'http';
import path from 'path';

const PORT = parseInt(process.env.PORT || '3000', 10);
const PHP_PORT = 8085;
const PHP_HOST = '127.0.0.1';

let phpProcess: ChildProcess | null = null;

// Function to start PHP built-in web server
function startPhpServer(): Promise<void> {
  return new Promise((resolve, reject) => {
    const rootDir = process.cwd();
    console.log(`Starting PHP built-in server at ${PHP_HOST}:${PHP_PORT} in ${rootDir}...`);

    phpProcess = spawn('php', ['-S', `${PHP_HOST}:${PHP_PORT}`, '-t', rootDir], {
      cwd: rootDir,
      stdio: ['ignore', 'pipe', 'pipe'],
    });

    phpProcess.stdout?.on('data', (data) => {
      // console.log(`[PHP] ${data}`);
    });

    phpProcess.stderr?.on('data', (data) => {
      // console.error(`[PHP] ${data}`);
    });

    phpProcess.on('error', (err) => {
      console.error('Failed to start PHP server:', err);
      reject(err);
    });

    phpProcess.on('exit', (code, signal) => {
      console.log(`PHP server exited with code ${code} and signal ${signal}`);
      phpProcess = null;
    });

    // Check when PHP server is listening
    let attempts = 0;
    const checkInterval = setInterval(() => {
      attempts++;
      const req = http.get(`http://${PHP_HOST}:${PHP_PORT}/index.php`, (res) => {
        clearInterval(checkInterval);
        console.log(`PHP built-in server is ready and responding (HTTP ${res.statusCode})`);
        resolve();
      });

      req.on('error', () => {
        if (attempts > 30) {
          clearInterval(checkInterval);
          reject(new Error('PHP server did not start in time.'));
        }
      });
    }, 200);
  });
}

async function startServer() {
  await startPhpServer();

  const app = express();

  // Disable etag and x-powered-by for raw proxy passthrough
  app.disable('x-powered-by');
  app.set('etag', false);

  // Health check endpoint
  app.get('/_health', (req, res) => {
    res.json({ status: 'ok', server: 'Adept Cinema Full-Stack', phpRunning: !!phpProcess });
  });

  // Proxy all other routes directly to PHP server
  app.use(
    '/',
    createProxyMiddleware({
      target: `http://${PHP_HOST}:${PHP_PORT}`,
      changeOrigin: false,
      autoRewrite: true,
      cookieDomainRewrite: '',
      xfwd: true,
      ws: true,
    })
  );

  const server = app.listen(PORT, '0.0.0.0', () => {
    console.log(`Adept Cinema streaming app server listening on http://0.0.0.0:${PORT}`);
  });

  const cleanup = () => {
    console.log('Shutting down Adept Cinema server...');
    if (phpProcess) {
      phpProcess.kill('SIGTERM');
    }
    server.close(() => {
      process.exit(0);
    });
  };

  process.on('SIGINT', cleanup);
  process.on('SIGTERM', cleanup);
}

startServer().catch((err) => {
  console.error('Failed to initialize Adept Cinema server:', err);
  process.exit(1);
});
