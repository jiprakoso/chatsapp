import 'dotenv/config';

function required(name) {
  const value = process.env[name];

  if (!value) {
    throw new Error(`Environment variable ${name} wajib diisi (lihat .env.example).`);
  }

  return value;
}

export const PORT = Number(process.env.PORT ?? 4000);
export const JWT_SECRET = required('JWT_SECRET');
export const CI4_API_BASE_URL = (process.env.CI4_API_BASE_URL ?? 'http://localhost:8080/api').replace(/\/+$/, '');
export const INTERNAL_API_KEY = required('INTERNAL_API_KEY');
export const FIREBASE_SERVICE_ACCOUNT_PATH = process.env.FIREBASE_SERVICE_ACCOUNT_PATH ?? null;

// Valkey/Redis (for Socket.IO adapter & presence in multi-instance)
export const VALKEY_HOST = process.env.VALKEY_HOST ?? '127.0.0.1';
export const VALKEY_PORT = Number(process.env.VALKEY_PORT ?? 6379);
