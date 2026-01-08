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

class TelegramBotController extends Controller
{
    protected Api $telegram;
    protected CallbackHandler $callbackHandler;
    protected MessageHandler $messageHandler;

    public function __construct()
    {
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
        $this->callbackHandler = new CallbackHandler($this->telegram, app(TelegramService::class));
        $this->messageHandler = new MessageHandler($this->telegram, app(TelegramService::class));
    }

    public function webhook()
    {
        $update = $this->telegram->getWebhookUpdate();

        // get telegram user id
        $telegramUserId = $update->getMessage()?->getFrom()?->getId()
                          ?? $update->getCallbackQuery()?->getFrom()?->getId();

        if (!$telegramUserId) {
            return 'ok'; // unknown update
        }

        // DB dan userni olamiz
        $user = User::where('telegram_id', $telegramUserId)->first();

        // Agar user ban bo‘lsa
        if ($user) {
            $ban = Ban::where('bannable_type', User::class)
                      ->where('bannable_id', $user->id)
                      ->where('active', true)
                      ->first();

            if ($ban) {
                $chatId = $update->getMessage()?->getChat()?->getId()
                          ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text'    => 'Siz bansiz. Admin bilan bog‘laning.'
                ]);

                return 'ok';
            }
        }

        // callback
        if ($update->getCallbackQuery()) {
            $context = $this->makeContextFromCallback($update, $user, $telegramUserId);
            return $this->callbackHandler->handle($context);
        }

        // message
        if ($update->getMessage()) {
            $context = $this->makeContextFromMessage($update, $user, $telegramUserId);
            return $this->messageHandler->handle($context);
        }

        return 'ok';
    }

    private function makeContextFromCallback(Update $update, ?User $user, int $telegramUserId): BotContext
    {
        $cb = $update->getCallbackQuery();
        $chatId = $cb->getMessage()->getChat()->getId();

        return new BotContext(
            telegram: $this->telegram,
            update: $update,
            user: $user,
            chatId: $chatId,
            telegramUserId: $telegramUserId
        );
    }

    private function makeContextFromMessage(Update $update, ?User $user, int $telegramUserId): BotContext
    {
        $msg = $update->getMessage();
        $chatId = $msg->getChat()->getId();

        return new BotContext(
            telegram: $this->telegram,
            update: $update,
            user: $user,
            chatId: $chatId,
            telegramUserId: $telegramUserId
        );
    }
}
