import { verifyToken } from './auth.js';
import { autoJoinConversations, registerConversationHandlers } from './conversation.js';
import { registerMessageHandlers } from './message.js';
import { addSocket, removeSocket } from './presence.js';
import { conversationRoom } from './rooms.js';
import { registerTypingHandlers } from './typing.js';

/**
 * Titik masuk tunggal untuk setiap koneksi Socket.IO baru (Section 14: connection).
 * Hanya 'authenticate' yang aktif sebelum socket terverifikasi — handler lain
 * (conversation/message/typing) baru didaftarkan SETELAH authenticate sukses,
 * jadi tidak ada permukaan aksi apa pun yang bisa dipakai oleh socket anonim.
 */
export function handleConnection(io, socket) {
  socket.on('authenticate', async ({ token } = {}, callback) => {
    const verified = token ? verifyToken(token) : null;

    if (!verified) {
      const payload = { success: false, message: 'Token tidak valid atau kedaluwarsa.' };

      if (typeof callback === 'function') {
        callback(payload);
      } else {
        socket.emit('error', { event: 'authenticate', ...payload });
      }

      return;
    }

    socket.data.userId = verified.userId;
    socket.data.token = token;

    let conversationIds = [];

    try {
      conversationIds = await autoJoinConversations(socket);
    } catch {
      // Gagal ambil daftar conversation tidak boleh menggagalkan authenticate —
      // client tetap bisa join_conversation manual belakangan.
      socket.data.conversationIds = new Set();
    }

    registerConversationHandlers(socket);
    registerMessageHandlers(io, socket);
    registerTypingHandlers(socket);

    const { wasOffline } = addSocket(verified.userId, socket.id);

    if (wasOffline) {
      for (const conversationId of conversationIds) {
        io.to(conversationRoom(conversationId)).emit('user_online', { user_id: verified.userId });
      }
    }

    if (typeof callback === 'function') {
      callback({ success: true, user_id: verified.userId });
    }

    socket.emit('authenticated', { user_id: verified.userId });
  });

  socket.on('disconnect', () => {
    if (socket.data.userId == null) {
      return;
    }

    const { isNowOffline } = removeSocket(socket.data.userId, socket.id);

    if (isNowOffline) {
      const conversationIds = socket.data.conversationIds ?? new Set();

      for (const conversationId of conversationIds) {
        io.to(conversationRoom(conversationId)).emit('user_offline', { user_id: socket.data.userId });
      }
    }
  });
}
