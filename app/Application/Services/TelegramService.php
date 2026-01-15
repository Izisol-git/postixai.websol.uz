<?php

namespace App\Application\Services;

use App\Models\User;
use Telegram\Bot\Api;
use App\Models\Catalog;
use App\Models\UserPhone;
use App\Models\MessageGroup;
use App\Models\TelegramMessage;
use App\Jobs\SendTelegramMessages;
use App\Jobs\RefreshGroupStatusJob;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Exceptions\TelegramResponseException;

class TelegramService
{
    public  Api $telegram;
    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }
    public function mainMenuWithHistoryKeyboard(bool $hasActivePhone = true)
    {
        $keyboard = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $keyboard
            ->row([
                
                Keyboard::button('📱 Telefonlarim'),
            ])
            ->row([
                Keyboard::button('📂 Cataloglar'),
                Keyboard::button('📊 Yuborilgan xabarlar tarixi'),
            ])
            ->row([
                Keyboard::button('Qollanma'),
                Keyboard::button('Oferta'),
            ]);
        if ($hasActivePhone) {
            $keyboard->row([
                Keyboard::button('Habar yuborish'),
            ]);
        }

        

        return $keyboard;
    }
    public function buildSendCatalogInlineKeyboard($user)
{
    $keyboard = Keyboard::make()->inline();

    /** -------------------------
     *  1️⃣ USER O‘Z CATALOG'LARI
     * ------------------------- */
    $myCatalogs = Catalog::where('user_id', $user->id)->get();

    foreach ($myCatalogs as $catalog) {
        $keyboard->row([
            Keyboard::inlineButton([
                'text' => $catalog->title . ' (' . $user->name . ')',
                'callback_data' => 'catalog_start_' . $catalog->id
            ])
        ]);
    }

    /** --------------------------------
     *  2️⃣ DEPARTMENT USERLARI CATALOGI
     * -------------------------------- */
    if ($user->department) {
        foreach ($user->department->users as $depUser) {

            // o‘zini yana qayta qo‘shmaslik
            if ($depUser->id === $user->id) {
                continue;
            }

            foreach ($depUser->catalogs as $catalog) {
                $keyboard->row([
                    Keyboard::inlineButton([
                        'text' => $catalog->title . ' (' . $depUser->name . ')',
                        'callback_data' => 'catalog_start_' . $catalog->id
                    ])
                ]);
            }
        }
    }

    /** 🔙 Cancel */
    $keyboard->row([
        Keyboard::inlineButton([
            'text' => '❌ Bekor qilish',
            'callback_data' => 'cancel_catalog'
        ])
    ]);

    return $keyboard;
}
    // Yangi/yangilangan method: buildCatalogKeyboardForSend
public function buildCatalogKeyboardForSend(int $userId, int $page = 1)
{
    // 1) Avval userning o'z kataloglari, keyin departmentdagi users kataloglari:
    $user = User::with('department.users')->find($userId);

    $userIds = [$userId];

    if ($user && $user->department) {
        // department->users may include the user himself; pluck va unique qilish
        $deptUserIds = $user->department->users->pluck('id')->toArray();
        $userIds = array_values(array_unique(array_merge($userIds, $deptUserIds)));
    }

    // 2) Cataloglarni yuklash, user relation bilan (owner nomi uchun)
    $catalogsCollection = Catalog::with('user')
        ->whereIn('user_id', $userIds)
        ->orderBy('id')
        ->get();

    // Map qilib arrayga o'tkazamiz va owner nomini qo'shamiz
    $catalogs = $catalogsCollection->map(function ($c) {
        $ownerName = $c->user->name ?? $c->user->username ?? ('user#' . $c->user_id);
        return [
            'id' => $c->id,
            'title' => $c->title,
            'owner_name' => $ownerName,
            'user_id' => $c->user_id,
        ];
    })->toArray();

    // 3) Pagination va keyboard qurish
    $perPage = 10;
    $chunks = array_chunk($catalogs, $perPage);
    $pageIndex = max(0, $page - 1);
    $pageCatalogs = $chunks[$pageIndex] ?? [];

    $keyboard = (new Keyboard)->inline();

    // Catalog tugmalari: har bir tugmada Title (Owner)
    $catalogButtons = [];
    foreach ($pageCatalogs as $catalog) {
        $text = $catalog['title'] . ' (' . $catalog['owner_name'] . ')';
        $catalogButtons[] = Keyboard::inlineButton([
            'text' => $text,
            'callback_data' => 'catalog_start_' . $catalog['id']
        ]);
    }

    // 2-ta tugma bir qatorda
    foreach (array_chunk($catalogButtons, 2) as $chunk) {
        $keyboard->row($chunk);
    }

    // Navigation tugmalari (prefikslar o'zgartirilgan)
    $navButtons = [];
    $totalPages = count($chunks);
    if ($page > 1) {
        $navButtons[] = Keyboard::inlineButton([
            'text' => '⬅ Previous',
            'callback_data' => 'catalog_send_page_' . ($page - 1)
        ]);
    }
    if ($page < $totalPages) {
        $navButtons[] = Keyboard::inlineButton([
            'text' => 'Next ➡',
            'callback_data' => 'catalog_send_page_' . ($page + 1)
        ]);
    }
    if ($navButtons) {
        $keyboard->row($navButtons);
    }

    // Bekor qilish tugmasi
    $keyboard->row([
        Keyboard::inlineButton([
            'text' => '❌ Catalog tanlashni bekor qilish',
            'callback_data' => 'cancel_catalog'
        ])
    ]);

    return $keyboard;
}

    public function buildCatalogKeyboard(int $userId, int $page = 1)
    {
        // Faqat user_id bo'yicha filtr
        $catalogs = Catalog::where('user_id', $userId)
            ->orderBy('id')
            ->get()
            ->toArray();

        $perPage = 10;
        $chunks = array_chunk($catalogs, $perPage);
        $pageCatalogs = $chunks[$page - 1] ?? [];

        $keyboard = (new Keyboard)->inline();

        $keyboard->row([
            Keyboard::inlineButton([
                'text' => '➕ Yangi Catalog yaratish',
                'callback_data' => 'catalog_create'
            ])
        ]);

        $catalogButtons = [];
        foreach ($pageCatalogs as $catalog) {
            $catalogButtons[] = Keyboard::inlineButton([
                'text' => $catalog['title'],
                'callback_data' => 'catalog_select_' . $catalog['id']
            ]);
        }

        foreach (array_chunk($catalogButtons, 2) as $chunk) {
            $keyboard->row($chunk);
        }

        $navButtons = [];

        if ($page > 1) {
            $navButtons[] = Keyboard::inlineButton([
                'text' => '⬅ Previous',
                'callback_data' => 'catalog_page_' . ($page - 1)
            ]);
        }

        if ($page < count($chunks)) {
            $navButtons[] = Keyboard::inlineButton([
                'text' => 'Next ➡',
                'callback_data' => 'catalog_page_' . ($page + 1)
            ]);
        }

        if ($navButtons) {
            $keyboard->row($navButtons);
        }

        $keyboard->row([
            Keyboard::inlineButton([
                'text' => '❌ Catalog tanlashni bekor qilish',
                'callback_data' => 'cancel_catalog'
            ])
        ]);

        return $keyboard;
    }
    public function buildPhoneKeyboard(array $phones)
    {
        $keyboard = (new Keyboard)->inline();

        if (empty($phones)) {
            // Telefonlar yo'q bo'lsa, shunchaki xabar uchun tugma
            $keyboard = Keyboard::make()
                ->setResizeKeyboard(true)
                ->setOneTimeKeyboard(true)
                ->row([
                    Keyboard::button([
                        'text' => '📱 Telefon raqamini yuborish',
                        'request_contact' => true,
                    ])
                ]);
        } else {
            // Telefonlar mavjud bo'lsa, har biri alohida qatorga
            foreach ($phones as $phone) {
                $keyboard->row([
                    Keyboard::inlineButton([
                        'text' => $phone['phone'],
                        'callback_data' => 'phone_select_' . $phone['id']
                    ])
                ]);
            }

            // Bekor qilish tugmasi
            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => '❌ Tanlashni bekor qilish',
                    'callback_data' => 'cancel_auth'
                ])
            ]);
        }

        return $keyboard;
    }
    public function buildPhoneSelectKeyboard($phones, int $page = 1)
    {
        $perPage = 4;

        // collection → array
        $phonesArray = $phones instanceof \Illuminate\Support\Collection
            ? $phones->values()->toArray()
            : $phones;

        $chunks = array_chunk($phonesArray, $perPage);
        $pagePhones = $chunks[$page - 1] ?? [];

        $keyboard = (new Keyboard)->inline();

        // 📞 Phone buttons
        foreach ($pagePhones as $index => $phone) {

            $status = $phone['is_active'] ? '✅ Faol' : '⚪️ No faol';

            $text = (($page - 1) * $perPage + $index + 1)
                . '. ' . $phone['phone'] . ' ' . $status;

            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => $text,
                    'callback_data' => 'phone_choose_' . $phone['id'],
                ])
            ]);
        }

        // ⬅ ➡ Navigation
        $navButtons = [];

        if ($page > 1) {
            $navButtons[] = Keyboard::inlineButton([
                'text' => '⬅ Previous',
                'callback_data' => 'phone_page_' . ($page - 1),
            ]);
        }

        if ($page < count($chunks)) {
            $navButtons[] = Keyboard::inlineButton([
                'text' => 'Next ➡',
                'callback_data' => 'phone_page_' . ($page + 1),
            ]);
        }

        if ($navButtons) {
            $keyboard->row($navButtons);
        }

        // ❌ Cancel
        $keyboard->row([
            Keyboard::inlineButton([
                'text' => 'Menyuga qaytish',
                'callback_data' => 'cancel_auth',
            ])
        ]);

        return $keyboard;
    }
    public function buildGroupKeyboard(User $user)
    {
        // Foydalanuvchining telefonlari
        $phoneIds = $user->phones()->pluck('id')->toArray();

        // Guruhlarni olish, eng yangi oxirgisini olish uchun latest va take
        $groups = MessageGroup::withCount('messages')
            ->with(['messages' => function ($q) {
                $q->latest();
            }])
            ->whereIn('user_phone_id', $phoneIds)
            ->latest() // eng yangi birinchi
            ->take(10) // oxirgi 10 ta
            ->get();

        $keyboard = (new Keyboard)->inline();

        foreach ($groups as $group) {
            $text = optional($group->messages->first())->message_text ?? 'Xabar yo‘q';

            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => mb_strimwidth($text, 0, 30, '...') . ' — ' . $group->messages_count,
                    'callback_data' => 'group_select_' . $group->id
                ])
            ]);
        }

        // Pagination olib tashlandi
        // $navButtons = [];

        // Yopish tugmasi
        $keyboard->row([
            Keyboard::inlineButton([
                'text' => 'Menyuga qaytish',
                'callback_data' => 'cancel_auth'
            ])
        ]);

        return $keyboard;
    }
    public  function handleGroupSelect(string $groupId, int $chatId)
    {
        // RefreshGroupStatusJob::dispatch($groupId)->onQueue('telegram');

        $group = MessageGroup::with('messages')->find($groupId);

        if (!$group || $group->messages->isEmpty()) {
            $this->tg(
                fn() =>
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "⚠️ Guruh yoki xabarlar topilmadi."
                ])
            );
            return;
        }

        $messages = $group->messages;

        $text  = "📊 Guruh ma'lumotlari\n\n";
        $text .= "📌 Guruh ID: {$group->id}\n";
        $text .= "🕒 Boshlangan: " . optional($messages->min('send_at'))->format('Y-m-d H:i') . "\n";
        $text .= "⏰ Tugashi: " . optional($messages->max('send_at'))->format('Y-m-d H:i') . "\n\n";
        $text .= "⏰ Last sent at: " . optional($messages->where('status', 'sent')->max('updated_at'))->format('Y-m-d H:i') . "\n\n";

        $text .= "📝 Message:\n";
        $text .= $messages->first()->message_text . "\n\n";

        $text .= "👥 Peerlar bo‘yicha holat:\n";
        $messages->groupBy('peer')->each(function ($peerMessages, $peer) use (&$text) {
            $counts = $peerMessages->groupBy('status')->map->count();

            $statusText = collect([
                'pending'   => '🕓',
                'scheduled' => '📅',
                'sent'      => '✅',
                'failed'    => '❌',
                'canceled'  => '🚫',
            ])
                ->filter(fn($icon, $status) => ($counts[$status] ?? 0) > 0)
                ->map(fn($icon, $status) => "{$icon} {$status}: {$counts[$status]}")
                ->implode(' | ');

            $text .= "• {$peer} — {$statusText}\n";
        });

        // Keyboard yaratish
        $replyKeyboard = Keyboard::make()->setResizeKeyboard(true);

        // Agar hammasi 'sent' bo‘lmasa
        $hasPendingOrScheduled = $messages->contains(fn($msg) => in_array($msg->status, ['scheduled', 'pending']));

        if ($hasPendingOrScheduled) {
            $replyKeyboard->row([
                Keyboard::button("❌ To‘xtatish {$group->id}"),
                Keyboard::button("🔄 Malumotlarni yangilash {$group->id}")
            ]);
        }


        // Doimiy menu tugmalari
        $replyKeyboard->row([
            Keyboard::button("📊 Yuborilgan xabarlar tarixi"),
            Keyboard::button("📂 Cataloglar")
        ])->row([
            Keyboard::button("Menyu")
        ]);

        $this->tg(
            fn() =>
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'reply_markup' => $replyKeyboard
            ])
        );
    }
    public  function createMessageGroup($user, $chatId)
    {
        $data = json_decode($user->value, true);

        $phone = UserPhone::find($data['phone_id']);
        if (!$phone) {
            $this->tg(fn() =>

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Telefon topilmadi."
            ]));
            return 'ok';
        }

        $group = MessageGroup::create([
            'user_phone_id' => $phone->id,
            'status' => 'pending'
        ]);

        $catalog = Catalog::find($data['catalog_id']);

        $peers = json_decode($catalog->peers, true);

        $loopCount = $data['loop_count'];
        $interval  = $data['interval']; // 0 bo‘lishi mumkin
        $message   = $data['message_text'];

        foreach ($peers as $peer) {
            for ($i = 0; $i < $loopCount; $i++) {
                TelegramMessage::create([
                    'message_group_id' => $group->id,
                    'peer' => $peer,
                    'message_text' => $message,
                    'send_at' => $interval > 0
                        // ? now()->addSeconds($i * $interval)
                        ? now()->addMinutes($i * $interval)
                        : now(),
                    'status' => 'pending',
                ]);
            }
        }

        SendTelegramMessages::dispatch($group->id)->onQueue('telegram');
        $this->tg(fn() =>

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "✅ Xabarlar jadvali yaratildi va navbatga qo‘yildi. \n/history - orqali ularni korishingiz mumkin ",
            'reply_markup' => $this->mainMenuWithHistoryKeyboard()
        ]));

        $user->state = null;
        $user->value = null;
        $user->save();

        return 'ok';
    }
    public  function cancelInlineKeyboard()
    {
        return (new Keyboard)->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => 'Menyuga qaytish',
                    'callback_data' => 'cancel_auth'
                ])
            ]);
    }
    public function cancelAuth(User $user, int $chatId, ?string $callbackQueryId = null)
    {
        // 🔹 Telefonlardagi auth jarayonlarini bekor qilish
        $user->phones()
            ->whereIn('state', ['waiting_code', 'waiting_password', 'waiting_code2'])
            ->update([
                'state' => 'cancelled',
                'code' => null
            ]);

        // 🔹 User state tozalash
        $user->state = null;
        $user->save();

        // 🔹 Agar callback bo‘lsa — answerCallbackQuery
        if ($callbackQueryId) {
            $this->telegram->answerCallbackQuery([
                'callback_query_id' => $callbackQueryId,
                'text' => 'Bekor qilindi',
                'show_alert' => false,
            ]);
        }

        // 🔹 Asosiy menyu
        $hasActivePhone = $user->phones()->where('is_active', true)->exists();
        $this->tg(fn() =>

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Bekor qilindi.',
            'reply_markup' => $this->mainMenuWithHistoryKeyboard($hasActivePhone)
        ]));

        return 'ok';
    }
    public  function tg(callable $fn)
    {
        try {
            return $fn();
        } catch (TelegramResponseException $e) {

            // 🔕 User botni block qilgan — jim yutamiz
            if (
                str_contains($e->getMessage(), 'bot was blocked by the user') ||
                str_contains($e->getMessage(), 'user is deactivated')
            ) {
                return null;
            }

            // ⚠️ boshqa telegram xatolarni log qilamiz
            Log::warning('Telegram API error', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }


    public function manualText(): string
    {
        return file_get_contents(resource_path('texts/manual.md'));
    }
}
