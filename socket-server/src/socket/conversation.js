import { getConversation, listAllConversationIds } from '../services/conversationService.js';
import { handleFailure } from './errors.js';
import { conversationRoom } from './rooms.js';

/**
 * Dipanggil sekali setelah authenticate sukses: join semua room conversation
 * yang sudah diikuti user (Section 26 — setiap socket baru langsung "dengar"
 * seluruh conversation-nya tanpa perlu join_conversation manual satu-satu).
 */
export async function autoJoinConversations(socket) {
  const conversationIds = await listAllConversationIds(socket.data.token);

  socket.data.conversationIds = new Set(conversationIds);

  for (const conversationId of conversationIds) {
    socket.join(conversationRoom(conversationId));
  }

  return conversationIds;
}

export function registerConversationHandlers(socket) {
  socket.on('join_conversation', async ({ conversation_id: conversationId } = {}, callback) => {
    try {
      // Verifikasi ulang membership lewat CI4 (bukan percaya cache lokal) —
      // Section 27: server wajib mengecek sebelum mengizinkan join.
      await getConversation(socket.data.token, conversationId);

      socket.join(conversationRoom(conversationId));
      socket.data.conversationIds.add(Number(conversationId));

      callback?.({ success: true });
    } catch (err) {
      handleFailure(socket, 'join_conversation', err, callback);
    }
  });

  socket.on('leave_conversation', ({ conversation_id: conversationId } = {}, callback) => {
    socket.leave(conversationRoom(conversationId));
    socket.data.conversationIds.delete(Number(conversationId));

    callback?.({ success: true });
  });
}
