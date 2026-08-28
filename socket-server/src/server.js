import http from 'node:http';
import { Server } from 'socket.io';
import { CI4_API_BASE_URL, PORT } from './config/env.js';
import { handleConnection } from './socket/connection.js';

const httpServer = http.createServer((req, res) => {
  if (req.url === '/health') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ status: 'ok', ci4ApiBaseUrl: CI4_API_BASE_URL }));

    return;
  }

  res.writeHead(404);
  res.end();
});

const io = new Server(httpServer, {
  cors: {
    origin: '*', // TODO: batasi origin di production
  },
});

io.on('connection', (socket) => {
  handleConnection(io, socket);
});

httpServer.listen(PORT, () => {
  // eslint-disable-next-line no-console
  console.log(`Socket.IO server listening on :${PORT}, forwarding writes to ${CI4_API_BASE_URL}`);
});
