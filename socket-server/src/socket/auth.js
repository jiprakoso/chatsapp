import jwt from 'jsonwebtoken';
import { JWT_SECRET } from '../config/env.js';

/**
 * Verifikasi token secara lokal (stateless, sama seperti JwtAuthFilter di CI4) —
 * tidak perlu round-trip ke CI4 hanya untuk cek signature/expiry. Otorisasi per-aksi
 * (membership, dst) tetap diverifikasi ulang oleh CI4 saat request diteruskan.
 *
 * HARUS pakai secret yang sama persis dengan Config\Jwt di CI4 (JWT_SECRET di .env).
 */
export function verifyToken(token) {
  try {
    const payload = jwt.verify(token, JWT_SECRET, { algorithms: ['HS256'] });
    const userId = Number(payload.sub);

    if (!Number.isInteger(userId) || userId <= 0) {
      return null;
    }

    return { userId };
  } catch {
    return null;
  }
}
