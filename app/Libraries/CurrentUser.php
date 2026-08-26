<?php

namespace App\Libraries;

/**
 * Menyimpan identitas user yang sedang login selama satu request lifecycle.
 * Diisi oleh JwtAuthFilter, dibaca oleh PermissionFilter/Controller.
 */
class CurrentUser
{
    private ?int $id = null;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function isAuthenticated(): bool
    {
        return $this->id !== null;
    }
}
