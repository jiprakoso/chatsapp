import { getMessaging } from 'firebase-admin/messaging';
import { initializeApp, getApps, cert } from 'firebase-admin/app';
import { CI4_API_BASE_URL, INTERNAL_API_KEY } from '../config/env.js';
import { ci4Request } from './httpClient.js';

let messaging = null;
let fcmDisabled = false;

function initFirebase() {
  if (fcmDisabled) return false;

  try {
    if (getApps().length === 0) {
      const saPath = process.env.FIREBASE_SERVICE_ACCOUNT_PATH;
      if (!saPath) {
        console.warn('[notificationService] FCM disabled: FIREBASE_SERVICE_ACCOUNT_PATH not set in env');
        fcmDisabled = true;
        return false;
      }
      initializeApp({
        credential: cert(saPath),
      });
    }
    messaging = getMessaging();
    return true;
  } catch (err) {
    // Service account hilang/rusak tidak boleh men-crash server socket —
    // pesan tetap terkirim realtime, hanya push notification yang dilewati.
    console.warn('[notificationService] FCM disabled:', err.message);
    fcmDisabled = true;
    return false;
  }
}

export async function notifyNewMessage(conversationId, message, senderId, token) {
  try {
    if (!messaging && !initFirebase()) {
      return;
    }
    const conversationRes = await ci4Request(token, 'GET', `/conversations/${conversationId}`);
    const memberIds = conversationRes.data.members.map(m => m.id).filter(id => id !== senderId);

    if (memberIds.length === 0) return;

    const { isOnline } = await import('../socket/presence.js');
    const offlineMemberIds = memberIds.filter(id => !isOnline(id));

    if (offlineMemberIds.length === 0) return;

    const devicesRes = await ci4Request(
      token,
      'GET',
      `/internal/devices?user_ids=${offlineMemberIds.join(',')}`,
      null,
      { 'X-Internal-Key': INTERNAL_API_KEY }
    );

    const tokens = devicesRes.data.devices
      .filter(d => d.fcm_token)
      .map(d => d.fcm_token);

    if (tokens.length === 0) return;

    const payload = {
      notification: {
        title: message.type === 'image' ? '📷 Foto' : message.type === 'file' ? '📎 File' : 'Pesan baru',
        body: message.content?.substring(0, 100) || '',
      },
      data: {
        conversationId: String(conversationId),
        messageId: String(message.id),
        senderId: String(senderId),
        type: message.type || 'text',
      },
      tokens,
    };

    const response = await messaging.sendEachForMulticast(payload);

    if (response.failureCount > 0) {
      const failedTokens = [];
      response.responses.forEach((resp, idx) => {
        if (!resp.success) {
          const errorCode = resp.error?.code;
          if (errorCode === 'messaging/invalid-registration-token' ||
              errorCode === 'messaging/registration-token-not-registered') {
            failedTokens.push(tokens[idx]);
          }
        }
      });

      if (failedTokens.length > 0) {
        await ci4Request(
          token,
          'POST',
          '/internal/devices/deactivate',
          { tokens: failedTokens },
          { 'X-Internal-Key': INTERNAL_API_KEY }
        );
      }
    }

  } catch (err) {
    console.error('[notificationService] FCM send failed:', err.message);
  }
}