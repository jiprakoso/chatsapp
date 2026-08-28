import { deleteMessage, editMessage, markDelivered, markRead, sendMessage } from '../services/messageService.js';
import { notifyNewMessage } from '../services/notificationService.js';
import { handleFailure } from './errors.js';
import { conversationRoom } from './rooms.js';

/**
 * Semua handler di sini hanya meneruskan ke REST API CI4 (memakai token milik
 * socket yang bersangkutan, jadi sender_id/otorisasi tetap ditentukan server CI4,
 * bukan payload client — Section 15 & 27) lalu broadcast hasilnya ke room.
 */
export function registerMessageHandlers(io, socket) {
  socket.on('send_message', async (payload = {}, callback) => {
    const conversationId = payload.conversation_id;

    try {
      const message = await sendMessage(socket.data.token, conversationId, {
        type: payload.type,
        message: payload.message,
        reply_message_id: payload.reply_message_id ?? null,
      });

      io.to(conversationRoom(conversationId)).emit('new_message', message);
      notifyNewMessage(conversationId, message, socket.data.userId, socket.data.token);

      callback?.({ success: true, message });
    } catch (err) {
      handleFailure(socket, 'send_message', err, callback);
    }
  });

  socket.on('message_edit', async (payload = {}, callback) => {
    const { message_id: messageId, message: newMessage } = payload;

    try {
      const updated = await editMessage(socket.data.token, messageId, newMessage);

      io.to(conversationRoom(updated.conversation_id)).emit('message_updated', updated);

      callback?.({ success: true, message: updated });
    } catch (err) {
      handleFailure(socket, 'message_edit', err, callback);
    }
  });

  // CI4 tidak mengembalikan conversation_id untuk delete/delivered/read (lihat
  // MessageController), jadi client wajib menyertakannya sendiri untuk keperluan
  // broadcast room di sisi Socket.IO — CI4 tetap yang memvalidasi otorisasi asli.
  socket.on('message_delete', async (payload = {}, callback) => {
    const { message_id: messageId, conversation_id: conversationId } = payload;

    try {
      await deleteMessage(socket.data.token, messageId);

      io.to(conversationRoom(conversationId)).emit('message_deleted', {
        message_id: Number(messageId),
        conversation_id: Number(conversationId),
      });

      callback?.({ success: true });
    } catch (err) {
      handleFailure(socket, 'message_delete', err, callback);
    }
  });

  socket.on('message_delivered', async (payload = {}, callback) => {
    const { message_id: messageId, conversation_id: conversationId } = payload;

    try {
      await markDelivered(socket.data.token, messageId);

      io.to(conversationRoom(conversationId)).emit('message_delivered', {
        message_id: Number(messageId),
        conversation_id: Number(conversationId),
        user_id: socket.data.userId,
      });

      callback?.({ success: true });
    } catch (err) {
      handleFailure(socket, 'message_delivered', err, callback);
    }
  });

  socket.on('message_read', async (payload = {}, callback) => {
    const { message_id: messageId, conversation_id: conversationId } = payload;

    try {
      await markRead(socket.data.token, messageId);

      io.to(conversationRoom(conversationId)).emit('message_read', {
        message_id: Number(messageId),
        conversation_id: Number(conversationId),
        user_id: socket.data.userId,
      });

      callback?.({ success: true });
    } catch (err) {
      handleFailure(socket, 'message_read', err, callback);
    }
  });
}
