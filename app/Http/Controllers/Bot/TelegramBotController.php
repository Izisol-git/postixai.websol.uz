<?php

namespace App\Http\Controllers\Bot;

use App\Models\User;
use App\Models\Ban;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;
use App\Application\Bot\BotContext;
use App\Http\Controllers\Controller;
use App\Application\Handlers\MessageHandler;
use App\Application\Handlers\CallbackHandler;
use App\Application\Services\TelegramService;
use App\Models\Department;

class TelegramBotController extends Controller
{
    protected Api $telegram;
    protected CallbackHandler $callbackHandler;
    protected MessageHandler $messageHandler;

    public function __construct()
    {
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
        $this->callbackHandler = new CallbackHandler($this->telegram, app(TelegramService::class));
        $this->messageHandler  = new MessageHandler($this->telegram, app(TelegramService::class));
    }

    public function webhook()
    {
        $update = $this->telegram->getWebhookUpdate();

        // telegram user id
        $telegramUserId =
            $update->getMessage()?->getFrom()?->getId()
            ?? $update->getCallbackQuery()?->getFrom()?->getId();

        if (!$telegramUserId) {
            return 'ok';
        }

        $chatId =
            $update->getMessage()?->getChat()?->getId()
            ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();

        // DB dan user
        $user = User::where('telegram_id', $telegramUserId)->first();

        /* =======================
         | BAN TEKSHIRUVLARI
         ======================= */
        if ($user) {

            // 1️⃣ User ban
            $userBan = Ban::where('bannable_type', User::class)
                ->where('bannable_id', $user->id)
                ->where('active', true)
                ->first();

            if ($userBan) {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text'    => '⛔ Siz bansiz. Admin bilan bog‘laning.'
                ]);
                return 'ok';
            }

            // 2️⃣ Department ban
            if ($user->department_id) {
                $deptBan = Ban::where('bannable_type', Department::class)
                    ->where('bannable_id', $user->department_id)
                    ->where('active', true)
                    ->first();

                if ($deptBan) {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text'    => '⛔ Sizning bo‘limingiz banda. Admin bilan bog‘laning.'
                    ]);
                    return 'ok';
                }
            }
        }

        /* =======================
         | CALLBACK
         ======================= */
        if ($update->getCallbackQuery()) {
            $context = $this->makeContextFromCallback($update, $user, $telegramUserId);
            return $this->callbackHandler->handle($context);
        }

        /* =======================
         | MESSAGE
         ======================= */
        if ($update->getMessage()) {
            $context = $this->makeContextFromMessage($update, $user, $telegramUserId);
            return $this->messageHandler->handle($context);
        }

        return 'ok';
    }

    private function makeContextFromCallback(Update $update, ?User $user, int $telegramUserId): BotContext
    {
        $cb = $update->getCallbackQuery();

        return new BotContext(
            telegram: $this->telegram,
            update: $update,
            user: $user,
            chatId: $cb->getMessage()->getChat()->getId(),
            telegramUserId: $telegramUserId
        );
    }

    private function makeContextFromMessage(Update $update, ?User $user, int $telegramUserId): BotContext
    {
        $msg = $update->getMessage();

        return new BotContext(
            telegram: $this->telegram,
            update: $update,
            user: $user,
            chatId: $msg->getChat()->getId(),
            telegramUserId: $telegramUserId
        );
    }
}
