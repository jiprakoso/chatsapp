/**
 * Konvensi nama room Socket.IO per conversation (Section 16 references.md).
 */
export function conversationRoom(conversationId) {
  return `conversation:${conversationId}`;
}
