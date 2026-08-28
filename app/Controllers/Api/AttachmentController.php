<?php

namespace App\Controllers\Api;

use App\Models\ConversationMemberModel;
use App\Models\MessageAttachmentModel;
use App\Models\MessageModel;

class AttachmentController extends ApiBaseController
{
    /**
     * GET /api/attachments/(:num) — stream file dari writable/uploads (di luar document
     * root). Wajib jwtauth + anggota conversation dari pesan pemilik attachment ini,
     * supaya URL attachment tidak bisa diakses hanya dengan menebak ID (Section 27).
     */
    public function show($id = null)
    {
        $attachment = (new MessageAttachmentModel())->find((int) $id);

        if ($attachment === null) {
            return $this->error('Attachment tidak ditemukan.', 404);
        }

        $message = (new MessageModel())->find((int) $attachment['message_id']);

        if ($message === null) {
            return $this->error('Attachment tidak ditemukan.', 404);
        }

        if (! (new ConversationMemberModel())->isActiveMember((int) $message['conversation_id'], $this->currentUserId())) {
            return $this->error('Anda bukan anggota conversation ini.', 403);
        }

        $path = WRITEPATH . 'uploads/' . $attachment['file_url'];

        if (! is_file($path)) {
            return $this->error('File tidak ditemukan di storage.', 404);
        }

        // $setMime=false: Content-Type ditentukan manual dari mime_type yang sudah
        // divalidasi saat upload, bukan ditebak dari ekstensi nama file (lebih akurat
        // & tidak bisa dipalsukan lewat nama file klien).
        $download = $this->response->download($path, null, false);

        // setFileName()/setContentType() HARUS dipanggil sebelum inline(), karena
        // inline() langsung membangun header Content-Disposition dari state saat itu.
        if (! empty($attachment['file_name'])) {
            $download->setFileName($attachment['file_name']);
        }

        if (! empty($attachment['mime_type'])) {
            $download->setContentType($attachment['mime_type']);
        }

        return $download->inline();
    }
}
