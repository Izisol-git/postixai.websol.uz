<?php

namespace App\Application\Handlers;

use App\Models\User;
use App\Models\Catalog;
use App\Models\UserPhone;
use App\Jobs\TelegramAuthJob;
use App\Jobs\TelegramVerifyJob;
use App\Jobs\CleanupScheduledJob;
use App\Application\Bot\BotContext;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Keyboard\Keyboard;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\FileUpload\InputFile;
use App\Models\MinutePackage\MinutePackage;
use App\Application\Services\TelegramService;

class MessageHandler
{
    protected $telegram;
    protected TelegramService $tgService;

    public function __construct($telegram, TelegramService $tgService)
    {
        $this->telegram = $telegram;
        $this->tgService = $tgService;
    }

    public function handle(BotContext $ctx)
    {
        $update = $ctx->update;
        $message = $update->getMessage();

        if ($update->getMessage()) {
            $message = $update->getMessage();
            $from = $message->get('from');
            $chat = $message->get('chat');
            $text = trim($message->getText() ?? '');

            $chatId = $chat['id'] ?? null;
            $firstName = $from['first_name'] ?? null;
            $telegramUserId = $from['id'] ?? null;
            $contact = $message->getContact();

            $user = User::where('telegram_id', "$telegramUserId")->first();
            $userState = $user?->state ?? null;
        }
        if (!$user && $text !== '/start') {
            $this->tgService->tg(
                fn() =>
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Iltimos, boshlash uchun /start ni bosing ✅',
                ])
            );
            return 'ok';
        }
        if (!$message) {
            return 'ok';
        }
        if ($userState === 'creating_catalog' && $text) {
            $catalog = \App\Models\Catalog::create([
                'user_id' => $user->id,
                'title' => $text,
                'peers' => json_encode([]),
            ]);

            $user->state = 'adding_peers_to_catalog';
            $user->value = $catalog->id;
            $user->save();
            $keyboard = (new Keyboard)->inline()
                ->row([
                    Keyboard::inlineButton([
                        'text' => 'To‘xtatish',
                        'callback_data' => 'cancel_catalog', // callback ishlaydi
                    ])
                ]);

            $this->tgService->tg(fn() =>

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Catalog yaratildi! Endi peerlarni alohida qo‘shing.  Masalan: \n @grouplink yoki -100123456789 (group/channel ID). Yakunlash uchun /done yozing.",
                'reply_markup' => $keyboard,
            ]));
            return "ok";
        }
        


        if ($user && $user->state === 'editing_catalog_name') {
            $catalogId = (int) $user->value;
            $catalog = Catalog::where('id', $catalogId)
                ->where('user_id', $user->id)
                ->first();
            if ($catalog) {
                $catalog->title = $text;
                $catalog->save();
            }
            $user->state = null;
            $user->value = null;
            $user->save();
            $peers = json_decode($catalog->peers ?? '[]', true);

            // 📌 Text
            $text  = "📂 *Catalog:* {$catalog->title}\n\n";
            $text .= "👥 *Peerlar:*\n";

            if (empty($peers)) {
                $text .= "— Peerlar yo‘q\n";
            } else {
                foreach ($peers as $i => $peer) {
                    $text .= ($i + 1) . ". `{$peer}`\n";
                }
            }

            $text .= "\n📌 Peerlar soni: " . count($peers);
            $text .= "\n\nQuyidagi amalni tanlang:";

            // 🔘 Keyboard
            $keyboard = (new Keyboard)->inline()
                ->row([
                    Keyboard::inlineButton([
                        'text' => '▶️ Xabar yuborish',
                        'callback_data' => 'catalog_start_' . $catalog->id
                    ]),
                    Keyboard::inlineButton([
                        'text' => '🗑 Catalogni o‘chirish',
                        'callback_data' => 'catalog_delete_' . $catalog->id
                    ])
                ])
                ->row([
                    Keyboard::inlineButton([
                        'text' => '✏️ Tahrirlash',
                        'callback_data' => 'catalog_edit_' . $catalog->id
                    ]),
                    Keyboard::inlineButton([
                        'text' => '⬅️ Orqaga',
                        'callback_data' => 'catalog_page_1'
                    ])
                ]);

            $this->tgService->tg(
                fn() =>
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'Markdown',
                    'reply_markup' => $keyboard
                ])
            );

            return 'ok';
        }

        if (($text === 'Menyuga qaytish' && $user) || ($text === 'Menyu' && $user)) {
            return $this->tgService->cancelAuth($user, $chatId);
        }
        if ($text === '/start') {
            Log::info('work');

            if (!$user) {
                $this->tgService->tg(
                    fn() =>
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "Siz tizimda ro‘yxatdan o‘tmadingiz. Tizimga kirish uchun adminlarga Telegram ID’ingizni yuboring: <code>$telegramUserId</code> va so‘ng /start komandasi bilan boshlang.",
                        'parse_mode' => 'HTML',
                    ])
                );
                return 'ok';
            }

            // eski jarayonlarni tozalash
            $user->state = null;
            $user->save();

            $user->phones()
                ->whereIn('state', [
                    'waiting_code',
                    'waiting_password',
                    'waiting_code2'
                ])
                ->update([
                    'state' => 'cancelled',
                    'code' => null
                ]);

            $hasActivePhone = $user->phones()
                ->where('is_active', true)
                ->exists();
            $this->tgService->tg(fn() =>

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Salom, $firstName!",
                'reply_markup' => $this->tgService->mainMenuWithHistoryKeyboard($hasActivePhone)
            ]));

            return;
        }
        if (($text === '📂 Cataloglar' && $user) || ($text === '/catalogs' && $user)) {
            $this->tgService->tg(fn() =>

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Iltimos, catalog tanlang:',
                'reply_markup' => $this->tgService->buildCatalogKeyboard($user->id, 1)
            ]));

            return 'ok';
        }
        if (($text === '🚀 Habar yuborish' && $user) || ($text === '/send' && $user)) {
            $this->tgService->tg(fn() =>

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Iltimos, xabar yuboriladigan catalogni tanlang:',
                'reply_markup' => $this->tgService->buildCatalogKeyboardForSend($user->id, 1)
            ]));

            return 'ok';
        }
        // if ($contact || ($user->state === 'waiting_phone' && $text)) {
        //     $keyboard = Keyboard::make()
        //         ->setResizeKeyboard(true)
        //         ->setOneTimeKeyboard(true);
        //     if (!$user->oferta_read) {
        //         $keyboard->row([
        //             Keyboard::button([
        //                 'text' => 'Oferta bilan tanishib chiqdim',
        //             ])
        //         ]);
        //         $user->state = null;

        //         $user->save();
        //         $this->tgService->tg(
        //             fn() =>
        //             $this->telegram->sendMessage([
        //                 'chat_id' => $chatId,
        //                 'text' => 'Avval Ofertani o‘qib chiqing',
        //                 'reply_markup' => $keyboard,
        //             ])
        //         );
        //         return 'ok';
        //     }

        //     if ($contact) {
        //         $phoneNumber = $contact->getPhoneNumber();
        //     } else {
        //         $phoneNumber = $text;
        //     }

        //     $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);

        //     if (!str_starts_with($phoneNumber, '+')) {
        //         $phoneNumber = '+' . $phoneNumber;
        //     }

        //     if (!$contact) {
        //         if (!preg_match('/^\+[1-9]\d{6,14}$/', $phoneNumber)) {
        //             $this->tgService->tg(
        //                 fn() =>
        //                 $this->telegram->sendMessage([
        //                     'chat_id' => $chatId,
        //                     'text' => "Telefon raqami noto‘g‘ri formatda ❌\n Masalan: +998901234567",
        //                     'reply_markup' => $this->tgService->cancelInlineKeyboard()

        //                 ])
        //             );
        //             return 'ok';
        //         }
        //     }
        //     $lockKey = "telegram_verify_lock_{$phoneNumber}_{$user->id}";

        //     if (Cache::has($lockKey)) {
        //         return 'ok';
        //     }

        //     Cache::put($lockKey, true, now()->addMinutes(10));

        //     TelegramAuthJob::dispatch($phoneNumber, $user->id)
        //         ->onQueue('telegram');

        //     UserPhone::updateOrCreate(
        //         ['user_id' => $user->id, 'phone' => $phoneNumber],
        //         [
        //             'state' => 'waiting_code'
        //         ]
        //     );
        //     $user->state = 'waiting_code';
        //     $user->save();

        //     $cancelKeyboard = (new Keyboard)->inline()
        //         ->row([
        //             Keyboard::inlineButton([
        //                 'text' => "Menyuga qaytish",
        //                 'callback_data' => 'cancel_auth'
        //             ]),
        //         ]);
        //     $this->tgService->tg(fn() =>

        //     $this->telegram->sendMessage([
        //         'chat_id' => $chatId,
        //         'text' => "Rahmat, $firstName! Iltimos, sizga kelgan code-ni ikkiga bo‘lib ketma-ket kiriting.\n\n" .
        //             "Masalan, code 12345 bo‘lsa, birinchi 123 kiriting, keyin ikkinchi qismini: 45.",
        //         'reply_markup' => $cancelKeyboard

        //     ]));
        //     return 'ok';
        // }
        if ($user->state === 'waiting_code' && $text) {
            $phone = $user->phones()->where('state', 'waiting_code')->latest()->first();
                $activePhone = $user->phones()->where('is_active', true)->exists();

            if (!$phone) {
                $user->state = null;
                $user->save();
                $this->tgService->tg(fn() =>

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Hatolik",
                    'reply_markup' => $this->tgService->mainMenuWithHistoryKeyboard($activePhone)
                ]));
            }

            if (strlen($text) >= 5) {

                $cancelKeyboard = (new Keyboard)->inline()
                    ->row([
                        Keyboard::inlineButton([
                            'text' => "Menyuga qaytish",
                            'callback_data' => 'cancel_auth'
                        ]),
                    ]);

                $this->tgService->tg(fn() =>

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "⚠️ Xatolik: Iltimos, code-ni ikki qismga bo‘lib ketma-ket kiriting! \n Bu code boshqa ishlamaydi. Jarayonni boshidan boshlang.",
                    'reply_markup' => $cancelKeyboard
                ]));
                return 'error';
            }

            $phone->code = $text;
            $phone->state = 'waiting_code2';
            $phone->save();
            $user->state = 'waiting_code2';
            $user->save();

            $cancelKeyboard = (new Keyboard)->inline()
                ->row([
                    Keyboard::inlineButton([
                        'text' => "Menyuga qaytish",
                        'callback_data' => 'cancel_auth'
                    ]),
                ]);

            $this->tgService->tg(fn() =>

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "✅Yaxshi! Endi code-ning ikkinchi qismini kiriting:",
                'reply_markup' => $cancelKeyboard
            ]));
            return 'ok';
        }
        // if ($user->state === 'waiting_code2' && $text) {
        //     $phone = $user->phones()->where('state', 'waiting_code2')->latest()->first();
        //     if (!$phone) {
        //         $user->state = null;
        //         $user->save();
        //         $this->tgService->tg(fn() =>

        //         $this->telegram->sendMessage([
        //             'chat_id' => $chatId,
        //             'text' => "Hatolik",
        //             'reply_markup' => $this->tgService->mainMenuWithHistoryKeyboard(true)
        //         ]));
        //     }
        //     $phone->code = $phone->code . $text;

        //     if (strlen($phone->code) < 5 || strlen($phone->code) > 5) {
        //         $cancelKeyboard = (new Keyboard)->inline()
        //             ->row([
        //                 Keyboard::inlineButton([
        //                     'text' => "Menyuga qaytish",
        //                     'callback_data' => 'cancel_auth'
        //                 ]),
        //             ]);

        //         $this->tgService->tg(fn() =>

        //         $this->telegram->sendMessage([
        //             'chat_id' => $chatId,
        //             'text' => "⚠️ Xatolik: Code umumiy 5 ta raqamdan iborat bo‘lishi kerak. Iltimos, jarayonni boshidan boshlang.",
        //             'reply_markup' => $cancelKeyboard
        //         ]));
        //         return 'error';
        //     }


        //     TelegramVerifyJob::dispatch($phone->phone, $user->id, $phone->code, null)
        //         ->onQueue('telegram');

        //     $phone->code = null;
        //     $phone->state = 'loggin_process';
        //     $phone->save();

        //     $user->state = null;
        //     $user->save();

        //     $keyboard = Keyboard::make()
        //         ->setResizeKeyboard(true)
        //         ->setOneTimeKeyboard(true)
        //         ->row([
        //             Keyboard::button('📱 Telefonlarim'),
        //         ]);
        //     $this->tgService->tg(
        //         fn() =>

        //         $this->telegram->sendMessage([
        //             'chat_id' => $chatId,
        //             'text' => "Telefon raqamingiz qoshildi",
        //             'reply_markup' => $keyboard
        //         ])
        //     );
        // }
        // if ($user->state === 'waiting_password') {

        //     if ($text) {
        //         $phone = $user->phones()->where('state', 'waiting_password')->latest()->first();

        //         if ($phone) {

        //             TelegramVerifyJob::dispatch($phone->phone, $user->id, $phone->code, null)
        //                 ->onQueue('telegram');
        //             $phone->code = null;
        //             $phone->state = 'loggin_process';
        //             $phone->save();
        //             $reply_markup = Keyboard::make()
        //                 ->setResizeKeyboard(true)
        //                 ->setOneTimeKeyboard(true)
        //                 ->row([
        //                     Keyboard::button('📱 Telefonlarim'),
        //                 ]);
        //             $this->tgService->tg(fn() =>

        //             $this->telegram->sendMessage([
        //                 'chat_id' => $chatId,
        //                 'text' => "Tasdiqlash jarayonini boshlandi🎉",
        //                 'reply_markup' => $reply_markup
        //             ]));
        //             return 'ok';
        //         }
        //     }
        //     return 'ok';
        // }
        if ($text === '📱 Telefonlarim' || $text === '/phones') {

            $userPhones = $user->phones()->where('is_active', true)->get();

            if ($userPhones->isEmpty()) {
                $this->tgService->tg(
                    fn() =>
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "Sizda telefon raqamlar yo‘q.👇",
                        'reply_markup' => Keyboard::make()
                            ->setResizeKeyboard(true)
                            ->setOneTimeKeyboard(true)
                            ->row([
                                Keyboard::button([
                                    'text' => 'Menyuga qaytish',
                                ])
                            ])
                    ])
                );
                return 'ok';
            }

            $this->tgService->tg(
                fn() =>
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "📱 Telefon raqamini tanlang:",
                    'reply_markup' => $this->tgService->buildPhoneSelectKeyboard($userPhones),
                ])
            );

            return 'ok';
        }
        if ($userState === 'phone_selected' && $text) {
            $phoneData = json_decode($user->value, true);
            $phoneId = $phoneData['phone_id'] ?? null;
            $phone = UserPhone::find($phoneId);
            if (!$phone) {
                $this->tgService->tg(fn() =>

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Telefon topilmadi. Iltimos, qaytadan tanlang."
                ]));
                return 'ok';
            }

            $phoneData['message_text'] = $text;
            $user->value = json_encode($phoneData, JSON_UNESCAPED_UNICODE);
            $user->state = 'message_configured';
            $user->save();
            $this->tgService->tg(fn() =>

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Xabar matni saqlandi! Endi necha marta yuborilishini kiriting:",
                'reply_markup' => $this->tgService->cancelInlineKeyboard()
            ]));
        }
        if ($userState === 'message_configured' && is_numeric($text)) {

            $loopCount = (int) $text;
            $phoneData = json_decode($user->value, true);

            // Minimal va maksimal limit: 1..100
            if ($loopCount < 1 || $loopCount > 100) {
                $this->tgService->tg(function () use ($chatId) {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "❌ Xabarlar soni 1 dan 100 gacha bo‘lishi kerak."
                    ]);
                });
                return 'ok';
            }

            $phoneData['loop_count'] = $loopCount;
            $user->value = json_encode($phoneData, JSON_UNESCAPED_UNICODE);

            // Agar loopCount > 1 bo‘lsa — intervalni tanlash
            if ($loopCount > 1) {

                $user->state = 'loop_count_configured';
                $user->save();

                // --- Minute packages (agar mavjud va aktiv bo'lsa) ---
                $minuteButtons = [];
                if (isset($user->minuteAccess) && $user->minuteAccess->is_active) {
                    $packages = MinutePackage::orderBy('minutes')->get();
                    foreach ($packages as $p) {
                        $minuteButtons[] = $p->minutes . ' min';
                    }
                }

                // Hour buttons (avval chiqadi)
                $hourButtons = [
                    ['🕐 1 soat', '🕑 2 soat'],
                    ['🕒 3 soat', '🕓 4 soat'],
                    ['🕕 6 soat', 'Menyuga qaytish']
                ];

                $keyboard = Keyboard::make()->setResizeKeyboard(true);

                // ✅ Avval soatlar
                foreach ($hourButtons as $row) {
                    $keyboard->row($row);
                }

                // ✅ Keyin minute paketlari
                if (!empty($minuteButtons)) {
                    $chunks = array_chunk($minuteButtons, 2);
                    foreach ($chunks as $row) {
                        $keyboard->row($row);
                    }
                }

                $this->tgService->tg(function () use ($chatId, $keyboard) {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "Intervalni tanlang yoki daqiqada kiriting (kamida 60), yoki mavjud minute paketlardan tanlang:",
                        'reply_markup' => $keyboard
                    ]);
                });

                return 'ok';
            }

            // loopCount = 1 bo‘lsa — progress bilan xabar yaratamiz
            $phoneData['interval'] = 0;
            $user->value = json_encode($phoneData, JSON_UNESCAPED_UNICODE);
            $user->state = 'ready_to_create';
            $user->save();

            // ======= Yangi qo‘shimcha: total xabarlarni tekshirish =======
            $catalog = \App\Models\Catalog::find($phoneData['catalog_id'] ?? null);
            $peers = $catalog ? json_decode($catalog->peers ?? '[]', true) : [];


            // ======= Xabar jadvalini yaratish va progress =======
            return $this->tgService->createMessageGroup($user, $chatId);
        }



        $intervalMap = [
            '🕐 1 soat' => 64,
            '🕑 2 soat' => 120,
            '🕒 3 soat' => 180,
            '🕓 4 soat' => 240,
            '🕕 6 soat' => 360,
        ];
        $minutePackageValues = [];
        if (isset($user->minuteAccess) && $user->minuteAccess->is_active) {
            $packages = MinutePackage::orderBy('minutes')->get();
            foreach ($packages as $p) {
                $label = $p->minutes . ' min';
                $intervalMap[$label] = (int) $p->minutes;
                $minutePackageValues[] = (int) $p->minutes;
            }
        }
        if ($userState === 'loop_count_configured') {

            // 🔹 Button orqali
            if (isset($intervalMap[$text])) {

                $interval = $intervalMap[$text];

                // 🔹 Qo‘lda yozilgan raqam
            } elseif (is_numeric($text)) {
                $num = (int)$text;

                // agar minute paketlari aktiv bo'lsa va raqam paketlardan biriga to'g'ri kelsa qabul qilamiz
                if (!empty($minutePackageValues) && in_array($num, $minutePackageValues, true)) {
                    $interval = $num;
                }
                // yoki umumiy qoida: minimal 60 daqiqa
                elseif ($num >= 60) {
                    $interval = $num;
                } else {
                    // noto'g'ri qiymat
                    $this->tgService->tg(function () use ($chatId) {
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => 'Iltimos, intervalni to‘g‘ri tanlang (kamida 60 daqiqa) yoki faol minute paketlardan birini tanlang.'
                        ]);
                    });
                    return 'ok';
                }
            } else {
                // noto'g'ri qiymat (na button, na raqam)
                $this->tgService->tg(function () use ($chatId) {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Iltimos, intervalni to‘g‘ri tanlang (kamida 60 daqiqa) yoki faol minute paketlardan birini tanlang.'
                    ]);
                });
                return 'ok';
            }

            $phoneData = json_decode($user->value, true);
            $phoneData['interval'] = $interval;

            $user->value = json_encode($phoneData, JSON_UNESCAPED_UNICODE);
            $user->state = 'ready_to_create';
            $user->save();

            return $this->tgService->createMessageGroup($user, $chatId);
        }
        if ($text === '📊 Yuborilgan xabarlar tarixi' || $text === '/history') {
            $this->tgService->tg(
                fn() =>

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => '📨 Xabarlar:',
                    'reply_markup' => $this->tgService->buildGroupKeyboard($user, 1)
                ])
            );

            return 'ok';
        }
        if ($text == "/help") {
            if ($user) {
                $user->state = null;
                $user->save();
                $user->phones()
                    ->whereIn('state', ['waiting_code', 'waiting_password', 'waiting_code2'])
                    ->update([
                        'state' => 'cancelled',
                        'code' => null
                    ]);
            }
            $activePhone = $user->phones()->where('is_active', true)->exists();

            $this->tgService->tg(
                fn() =>

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' =>
                    "📌 Buyruqlar ro‘yxati:\n\n" .
                        "/start — Botni qayta ishga tushirish\n" .
                        "/history —  Yuborilgan habarlarni korish\n" .
                        "/phones — Telefonlarim\n" .
                        "/catalogs — 📂 Cataloglar ro‘yxati\n" .
                        "/help — Yordam olish\n",
                    'reply_markup' => $this->tgService->mainMenuWithHistoryKeyboard($activePhone)

                ])
            );
        }
        if (preg_match('/^❌ To‘xtatish (\d+)$/', $text, $matches)) {
            $groupId = (int) $matches[1];
            CleanupScheduledJob::dispatch($groupId)->onQueue('telegram');
            // sleep(2);
            Log::info('work');
            $this->tgService->handleGroupSelect($groupId, $chatId);
        }
        if (preg_match('/^🔄 Malumotlarni yangilash (\d+)$/', $text, $matches)) {
            $groupId = (int) $matches[1];
            $this->tgService->handleGroupSelect($groupId, $chatId);
        }
        if ($text === '📘 Qo‘llanma') {

            // 1. Fayl path (keyin o‘zing o‘zgartirasan)
            $filePath = '' . resource_path('docs/postix_ai_guide.pdf');
            $manualPath = resource_path('texts/manual.md');

            $manualText = file_exists($manualPath)
                ? file_get_contents($manualPath)
                : 'Qo‘llanma topilmadi.';
            // 2. Fayl mavjudligini tekshirish
            if (!file_exists($filePath)) {
                $this->tgService->tg(
                    fn() =>
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => '📕 Qo‘llanma topilmadi.',
                        'reply_markup' => $this->tgService->cancelInlineKeyboard(),
                    ])
                );

                return 'ok';
            }

            // 3. PDF yuborish
            $this->tgService->tg(
                fn() =>
                $this->telegram->sendDocument([
                    'chat_id'   => $chatId,
                    'document' => InputFile::create($filePath),
                    'caption'   => $manualText,
                    'reply_markup' => $this->tgService->cancelInlineKeyboard(),
                ])
            );

            return 'ok';
        }

        if ($text === '💼 Oferta') {

            $ofertaPath = resource_path('texts/offer.md');

            $ofertaText = file_exists($ofertaPath)
                ? file_get_contents($ofertaPath)
                : 'Oferta topilmadi.';

            $keyboard = Keyboard::make()
                ->setResizeKeyboard(true)
                ->setOneTimeKeyboard(true);
            $keyboard->row([
                Keyboard::button([
                    'text' => 'Oferta bilan tanishib chiqdim',
                ])
            ]);

            $this->tgService->tg(
                fn() =>
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $ofertaText,
                    'parse_mode' => 'Markdown',
                    'reply_markup' => $keyboard
                ])
            );

            return 'ok';
        }


        if ($text === 'Oferta bilan tanishib chiqdim') {
            $user->oferta_read = true;
            $user->save();
            $keyboard = Keyboard::make()
                ->setResizeKeyboard(true)
                ->setOneTimeKeyboard(true);
            $keyboard->row([
                Keyboard::button([
                    'text' => '📱 Telefon raqamini yuborish',
                    'request_contact' => true,
                ])
            ]);
            $activePhone = $user->phones()->where('is_active', true)->exists();

            $this->tgService->tg(
                fn() =>
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Rahmat!',
                    'reply_markup' => $this->tgService->mainMenuWithHistoryKeyboard($activePhone)
                ])
            );
        }
        
        if ($text === '📱 Telefon Raqam Qoshish') {
            $keyboard = Keyboard::make()
                ->setResizeKeyboard(true)
                ->setOneTimeKeyboard(true);
            $keyboard->row([
                Keyboard::button([
                    'text' => 'Oferta bilan tanishib chiqdim',
                ])
            ]);

            if (!$user->oferta_read) {
                $this->tgService->tg(
                    fn() =>
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Avval Ofertani o‘qib chiqing',
                        'reply_markup' => $keyboard,
                    ])
                );

                return "ok";
            }
            $user->state = 'waiting_phone';
            $user->save();
            $keyboard->row([
                Keyboard::button([
                    'text' => '📱 Telefon raqamini yuborish',
                    'request_contact' => true,
                ])
            ]);
            $this->tgService->tg(
                fn() =>
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Iltimos Telofon raqamini Conntact uslida yoki +998991234567 shu formatda jonating',
                    'reply_markup' => $this->tgService->cancelInlineKeyboard()

                ])
            );
        }
        if (preg_match('/^❌ Failed lar (\d+)$/', $text, $m)) {
            $this->tgService->showFailedPeers($m[1], $chatId);
            return;
        }

        if ($userState === 'adding_peers_to_catalog' && $text) {

            if ($text === '/done') {
                $user->state = null;
                $user->value = null;
                $user->save();
                $activePhone = $user->phones()->where('is_active', true)->exists();

                $this->tgService->tg(
                    fn() =>
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "✅ Jarayon yakunlandi!",
                        'reply_markup' => $this->tgService->mainMenuWithHistoryKeyboard($activePhone)
                    ])
                );

                return 'ok';
            }
            

            // 🔹 Peerlarni ajratib olish (newline, vergul, probel)
            $rawPeers = preg_split('/[\s,]+/', trim($text));
            $rawPeers = array_filter($rawPeers);

            // 🔒 Limit (test uchun)
            if (count($rawPeers) > 100) {
                $this->tgService->tg(
                    fn() =>
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "❌ Maksimum 100 ta peer yuborish mumkin (hozir " . count($rawPeers) . " ta yuborildi)."
                    ])
                );
                return 'ok';
            }

            $validPeers = [];
            $invalidPeers = [];

            foreach ($rawPeers as $peer) {
                $peer = trim($peer);

                // olib tashlash: oxirgi nuqta/vergul kabi belgilarning xato kiritilishini kamaytirish uchun
                $peer = rtrim($peer, " \t\n\r\0\x0B.,;:!");

                // 🔹 Agar t.me bilan boshlangan har qanday path bo'lsa — o'zgartirmasdan qabul qilamiz.
                // Misollar: https://t.me/username, t.me/username, https://t.me/+anN6..., t.me/c/12345/67
                if (preg_match('~^(?:https?://)?t\.me/\S+$~i', $peer)) {
                    $validPeers[] = $peer;
                    continue;
                }

                // ✅ Agar to'g'ri @username bo'lsa — ham qabul qilamiz
                if (preg_match('/^@[a-zA-Z0-9_]{3,}$/', $peer)) {
                    $validPeers[] = $peer;
                    continue;
                }

                // Agar yuqoridagi ikkitasidan emas — xato deb hisoblaymiz
                $invalidPeers[] = $peer;
            }


            if (empty($validPeers)) {
                $this->tgService->tg(
                    fn() =>
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "❌ Yaroqli peer topilmadi.\n\nRuxsat etilgan formatlar:\n• `@username`\n• `https://t.me/username`",
                        'parse_mode' => 'Markdown'
                    ])
                );
                return 'ok';
            }

            $catalog = \App\Models\Catalog::find($user->value);
            $peers = json_decode($catalog->peers ?? '[]', true);

            // ➕ Takrorlarsiz qo‘shish
            $peers = array_values(array_unique(array_merge($peers, $validPeers)));

            $catalog->peers = json_encode($peers, JSON_UNESCAPED_UNICODE);
            $catalog->save();

            // 📋 Natija
            $listText = "📌 *Peerlar qo‘shildi:* " . count($validPeers) . " ta\n";

            if (!empty($invalidPeers)) {
                $listText .= "\n❌ *Xato peerlar (" . count($invalidPeers) . "):*\n";
                foreach ($invalidPeers as $p) {
                    $listText .= "• `{$p}`\n";
                }
            }

            $listText .= "\n📂 *Joriy peerlar soni:* " . count($peers);
            $listText .= "\n\n➕ Yana peer yuboring yoki /done bilan yakunlang.";

            $this->tgService->tg(
                fn() =>
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $listText,
                    'parse_mode' => 'Markdown'
                ])
            );

            return 'ok';
        }
        return 'ok';
    }
    
}
