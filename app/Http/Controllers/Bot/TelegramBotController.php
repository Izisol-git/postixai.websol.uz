<?php

namespace App\Http\Controllers\Bot;

use App\Models\User;
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

    // $this->messageHandler = app(MessageHandler::class);
}
    public function webhook()
    {
        $update = $this->telegram->getWebhookUpdate();

        // callback
        if ($update->getCallbackQuery()) {
            $context = $this->makeContextFromCallback($update);
            return $this->callbackHandler->handle($context);
        }

        // message
        if ($update->getMessage()) {
            $context = $this->makeContextFromMessage($update);
            return $this->messageHandler->handle($context);
        }

        return 'ok';
    }

    private function makeContextFromCallback(Update $update): BotContext
    {
        $cb = $update->getCallbackQuery();

        $telegramUserId = $cb->getFrom()->getId();
        $chatId = $cb->getMessage()->getChat()->getId();

        return new BotContext(
            telegram: $this->telegram,
            update: $update,
            user: User::where('telegram_id', $telegramUserId)->first(),
            chatId: $chatId,
            telegramUserId: $telegramUserId
        );
    }

    private function makeContextFromMessage(Update $update): BotContext
    {
        $msg = $update->getMessage();

        $telegramUserId = $msg->getFrom()->getId();
        $chatId = $msg->getChat()->getId();

        return new BotContext(
            telegram: $this->telegram,
            update: $update,
            user: User::where('telegram_id', $telegramUserId)->first(),
            chatId: $chatId,
            telegramUserId: $telegramUserId
        );
    }
}
