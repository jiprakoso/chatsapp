/**
 * State presence in-memory (Section 25 & 26 references.md): mapping user_id ->
 * kumpulan socket.id (mendukung multi-device — satu user bisa punya banyak
 * socket aktif sekaligus, mis. HP + tablet).
 *
 * State ini TIDAK permanen dan hanya valid untuk satu instance Node. Begitu
 * server ini di-scale ke beberapa instance, state ini wajib dipindah ke
 * shared storage/pub-sub seperti Redis (Section 25, 37) — di luar scope tahap ini.
 */
const onlineUsers = new Map(); // userId -> Set<socketId>

/**
 * @returns {{ wasOffline: boolean }} wasOffline = true jika ini socket PERTAMA
 * milik user tsb (baru saja online), dipakai pemanggil untuk memutuskan apakah
 * perlu broadcast user_online.
 */
export function addSocket(userId, socketId) {
  const existing = onlineUsers.get(userId);

  if (!existing) {
    onlineUsers.set(userId, new Set([socketId]));

    return { wasOffline: true };
  }

  existing.add(socketId);

  return { wasOffline: false };
}

/**
 * @returns {{ isNowOffline: boolean }} isNowOffline = true jika ini socket
 * TERAKHIR milik user tsb (baru saja offline sepenuhnya).
 */
export function removeSocket(userId, socketId) {
  const existing = onlineUsers.get(userId);

  if (!existing) {
    return { isNowOffline: false };
  }

  existing.delete(socketId);

  if (existing.size === 0) {
    onlineUsers.delete(userId);

    return { isNowOffline: true };
  }

  return { isNowOffline: false };
}

export function isOnline(userId) {
  return onlineUsers.has(userId);
}
