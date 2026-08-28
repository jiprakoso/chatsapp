import { CI4_API_BASE_URL } from '../config/env.js';

/**
 * Error terstruktur dari respons non-2xx CI4 API, supaya socket handler bisa
 * membedakan 401/403/404/422 dan meneruskan pesan yang relevan ke client.
 */
export class ApiError extends Error {
  constructor(status, message, errors = null) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.errors = errors;
  }
}

/**
 * Semua operasi tulis (send message, edit, delete, delivered/read, dst) lewat
 * REST API CI4 yang sudah ada — bukan akses database langsung — supaya business
 * rule (validasi membership, sender_id dari JWT, dst) tidak dobel dua tempat
 * (lihat references.md Section 29).
 */
export async function ci4Request(token, method, path, body = undefined, extraHeaders = {}) {
  const response = await fetch(`${CI4_API_BASE_URL}${path}`, {
    method,
    headers: {
      Authorization: `Bearer ${token}`,
      ...(body !== undefined ? { 'Content-Type': 'application/json' } : {}),
      ...extraHeaders,
    },
    body: body !== undefined ? JSON.stringify(body) : undefined,
  });

  let json = null;

  try {
    json = await response.json();
  } catch {
    // respons tanpa body (jarang terjadi untuk API ini, tapi jaga-jaga)
  }

  if (!response.ok) {
    const message = json?.message ?? `CI4 API error (HTTP ${response.status})`;
    throw new ApiError(response.status, message, json?.errors ?? null);
  }

  return json?.data ?? null;
}
