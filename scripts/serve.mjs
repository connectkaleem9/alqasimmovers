#!/usr/bin/env node
/** Local preview of dist/ — mirrors the host's behaviour: pretty URLs, real 404 status. */
import { createServer } from 'node:http';
import { readFile, stat } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const DIST = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..', 'dist');
const PORT = Number(process.env.PORT) || 4173;

const TYPES = {
  '.html': 'text/html; charset=utf-8', '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8', '.svg': 'image/svg+xml',
  '.woff2': 'font/woff2', '.xml': 'application/xml; charset=utf-8',
  '.txt': 'text/plain; charset=utf-8', '.json': 'application/json; charset=utf-8',
  '.png': 'image/png', '.jpg': 'image/jpeg', '.webp': 'image/webp', '.avif': 'image/avif'
};

const send = (res, status, body, type) => {
  res.writeHead(status, { 'content-type': type, 'x-content-type-options': 'nosniff' });
  res.end(body);
};

createServer(async (req, res) => {
  try {
    const urlPath = decodeURIComponent(new URL(req.url, 'http://localhost').pathname);
    let file = path.join(DIST, urlPath);
    if (!file.startsWith(DIST)) return send(res, 403, 'Forbidden', 'text/plain');
    try { if ((await stat(file)).isDirectory()) file = path.join(file, 'index.html'); }
    catch { if (!path.extname(file)) file = path.join(file, 'index.html'); }
    const body = await readFile(file);
    send(res, 200, body, TYPES[path.extname(file)] || 'application/octet-stream');
  } catch {
    try {
      send(res, 404, await readFile(path.join(DIST, '404.html')), TYPES['.html']);
    } catch {
      send(res, 404, 'Not found', 'text/plain');
    }
  }
}).listen(PORT, () => console.log(`\nPreview: http://localhost:${PORT}\n(Ctrl+C to stop)\n`));
