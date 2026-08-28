import { ApiError } from '../services/httpClient.js';

/**
 * Terjemahkan error (dari panggilan CI4 API atau validasi lokal) ke response
 * client: lewat ack callback kalau event-nya pakai emitWithAck (Section 33),
 * atau lewat event 'error' generik kalau tidak ada callback.
 *
 * 401 dari CI4 берarti token JWT sudah kedaluwarsa selagi socket tetap terbuka
 * (Section 27/28) — dikirim juga sebagai event terpisah 'token_expired' supaya
 * client tahu harus re-authenticate dengan token baru, bukan sekadar retry.
 */
export function handleFailure(socket, event, err, callback) {
  const status = err instanceof ApiError ? err.status : 500;
  const message = err instanceof ApiError ? err.message : 'Terjadi kesalahan pada server.';
  const errors = err instanceof ApiError ? err.errors : null;

  const payload = { success: false, event, status, message, errors };

  if (typeof callback === 'function') {
    callback(payload);
  } else {
    socket.emit('error', payload);
  }

  if (status === 401) {
    socket.emit('token_expired');
  }
}
