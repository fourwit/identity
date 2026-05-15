<?php

namespace Modules\Identity\Contracts;

interface HasAvatar
{
    public function getAvatarUrl(): ?string;

    public function setAvatar(string $path): void;

    public function removeAvatar(): void;
}