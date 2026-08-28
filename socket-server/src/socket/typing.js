import { conversationRoom } from './rooms.js';

/**
 * Typing bersifat ephemeral (Section 24) — tidak ada panggilan ke CI4/database
 * sama sekali, langsung broadcast ke anggota room lain. Debounce/throttle di sisi
 * client (Flutter) supaya tidak mengirim event untuk setiap karakter — bukan
 * tanggung jawab server.
 */
export function registerTypingHandlers(socket) {
  socket.on('typing_start', ({ conversation_id: conversationId } = {}) => {
    socket.to(conversationRoom(conversationId)).emit('user_typing', {
      conversation_id: Number(conversationId),
      user_id: socket.data.userId,
    });
  });

  socket.on('typing_stop', ({ conversation_id: conversationId } = {}) => {
    socket.to(conversationRoom(conversationId)).emit('user_stopped_typing', {
      conversation_id: Number(conversationId),
      user_id: socket.data.userId,
    });
  });
}
