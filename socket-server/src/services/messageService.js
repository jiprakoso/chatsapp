import { ci4Request } from './httpClient.js';

export async function sendMessage(token, conversationId, payload) {
  return ci4Request(token, 'POST', `/conversations/${conversationId}/messages`, payload);
}

export async function editMessage(token, messageId, message) {
  return ci4Request(token, 'PUT', `/messages/${messageId}`, { message });
}

export async function deleteMessage(token, messageId) {
  return ci4Request(token, 'DELETE', `/messages/${messageId}`);
}

export async function markDelivered(token, messageId) {
  return ci4Request(token, 'POST', `/messages/${messageId}/delivered`);
}

export async function markRead(token, messageId) {
  return ci4Request(token, 'POST', `/messages/${messageId}/read`);
}
