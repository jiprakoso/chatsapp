import { ci4Request } from './httpClient.js';

const AUTO_JOIN_PAGE_CAP = 20; // batas jumlah halaman saat auto-join, hindari loop tak terbatas untuk user dgn ribuan conversation

/**
 * Ambil seluruh conversation_id milik user (lintas halaman) untuk auto-join room
 * saat authenticate (Section 26 multi-device — setiap socket baru langsung
 * mendengar semua conversation yang sudah diikuti).
 */
export async function listAllConversationIds(token) {
  const ids = [];
  let page = 1;

  while (page <= AUTO_JOIN_PAGE_CAP) {
    const data = await ci4Request(token, 'GET', `/conversations?page=${page}`);
    const conversations = data?.conversations ?? [];

    for (const conversation of conversations) {
      ids.push(Number(conversation.id));
    }

    const pagination = data?.pagination;
    if (!pagination || pagination.currentPage >= pagination.pageCount) {
      break;
    }

    page += 1;
  }

  return ids;
}

/**
 * Verifikasi ulang membership lewat CI4 (bukan cache lokal) sebelum join_conversation
 * diizinkan — Section 27: "Server harus memverifikasi bahwa user memang anggota
 * conversation sebelum mengizinkan join."
 */
export async function getConversation(token, conversationId) {
  return ci4Request(token, 'GET', `/conversations/${conversationId}`);
}
