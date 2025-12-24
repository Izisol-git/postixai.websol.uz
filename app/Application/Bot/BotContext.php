<?php

namespace App\Application\Bot;

use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;
use App\Models\User;

class BotContext
{
    public Api $telegram;
    public Update $update;
    public ?User $user;
    public int $chatId;
    public int $telegramUserId;

    public function __construct(Api $telegram, Update $update, ?User $user, int $chatId, int $telegramUserId)
    {
        $this->telegram = $telegram;
        $this->update = $update;
        $this->user = $user;
        $this->chatId = $chatId;
        $this->telegramUserId = $telegramUserId;
    }
}
