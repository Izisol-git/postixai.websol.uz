<?php

return [
    'admin' => [
        'dashboard' => 'Dashboard',
        'main_dashboard' => 'Asosiy Dashboard',
        'navigation' => 'Navigatsiya',
        'users' => 'Foydalanuvchilar',
        'phones' => 'Telefonlar',
        'operations' => 'Operatsiyalar',
        'messages_count' => 'Yuborilgan xabarlar',
        'create_department' => 'Bo‘lim yaratish',
        'deleted' => 'Oʻchirildi',
        'last_active_users' => 'Oxirgi faol foydalanuvchilar',
        'no_recent_activity' => "So‘nggi faollik topilmadi",
        'messages_per_day' => "Kunlik yuborilgan xabarlar",
        'users_by_operations' => "Foydalanuvchilar bo'yicha operatsiyalar",
        'all_year' => "Butun yil",
        'all_time' => "Butun vaqt",
        'month' => "Oy",
        'week' => "Hafta",
        'day' => "Kun",
        'grouped_bar' => "Habarlar boylab aktiv telefonlar",
        'active' => "Faol",
        'edit' => "Tahrirlash",

        // --- Qo'shilgan yangi kalitlar (Users page va confirm va boshqalar)
        'add_user' => 'Foydalanuvchi qo‘shish',
        'search_users' => 'Foydalanuvchini qidirish...',
        'toggle' => 'Tanlash',
        'showing' => 'Ko‘rsatilmoqda',
        'no_telegram' => 'Telegram yo‘q',
        'no_role' => 'Rol belgilanmagan',

        'add_phone' => 'Telefon qo‘shish',
        'details' => 'Batafsil',

        'ban' => 'Bloklash',
        'unban' => 'Blokdan chiqarish',

        'delete' => 'O‘chirish',
        'delete_user' => 'Foydalanuvchini o‘chirish',

        'confirm' => 'Tasdiqlash',
        'cancel' => 'Bekor qilish',
        'continue' => 'Davom etish',
        'confirm_type_name' => 'Tasdiqlash uchun nomni kiriting',
        'confirm_mismatch' => 'Kiritilgan nom mos kelmadi',
        'back' => 'Ortga',

        'success' => 'Muvaffaqiyatli bajarildi',
        'error' => 'Xatolik',
        'server_error' => 'Server xatosi',

        'phone_activated' => 'Telefon faollashtirildi',
        'error_phone_activate' => 'Telefonni faollashtirib bo‘lmadi',
        'year' => 'Yil',
    ],

    'layout' => [
        'profile' => 'Profil',
        'settings' => 'Sozlamalar',
        'logout' => 'Chiqish',
        'dashboard' => 'Dashboard',
        'departments' => 'Bo‘limlar',
        'page_title' => 'Admin Panel',

    ],

    'operations' => [
        'title' => 'Operatsiyalar',
        'subtitle' => 'Bo‘lim uchun operatsiyalar: :dept',
        'search_placeholder' => 'Message text bo‘yicha qidirish...',
        'filter_all_status' => 'Barchasi',
        'status_pending' => 'Pending',
        'status_scheduled' => 'Scheduled',
        'status_sent' => 'Sent',
        'status_canceled' => 'Canceled',
        'status_failed' => 'Failed',
        'group' => 'Operatsiya',
        'by_user' => 'Foydalanuvchi',
        'text_label' => 'Matn',
        'peer_total' => 'Jami',
        'total' => 'ALL',
        'total_sent' => 'TOTAL SENT',
        'rate' => 'RATE',
        'btn_refresh' => 'Refresh',
        'btn_cancel' => 'Cancel',
        'confirm' => 'Tasdiqlash',
        'confirm_text_default' => 'Siz bu amalni bajarishni xohlaysizmi?',
        'confirm_refresh_text' => 'Siz operatsiyani #:id yangilamoqchisiz. Davom etilsinmi?',
        'confirm_cancel_text' => 'Siz operatsiyani #:id bekor qilmoqchisiz. Davom etilsinmi?',
        'btn_search' => 'Qidirish',
        'showing' => 'Ko‘rsatilyapti',
        'total_groups' => 'Operatsiyalar soni',
        'total_messages' => 'Xabarlar soni',
        'refresh_success' => 'Operatsiya #:id yangilandi',
        'refresh_failed' => 'Operatsiya #:id ni yangilashda xatolik',
        'cancel_success' => 'Operatsiya #:id bekor qilindi. :count ta xabar holati o‘zgardi',
        'cancel_failed' => 'Operatsiya #:id ni bekor qilishda xatolik',
        'error_no_permission' => 'Sizda bu amalni bajarish ruxsati yo‘q',
        'cancel' => 'Bekor qilish',
        'continue' => 'Davom etish',
        'show' => 'Ko‘rish',
    ],
    'login' => [
        'title' => 'Kirish',
        'welcome' => 'Xush kelibsiz',
        'subtitle' => 'Hisobingizga kiring',

        'email' => 'Login',
        'email_placeholder' => 'you@example.com',

        'password' => 'Parol',
        'password_placeholder' => '••••••••',

        'submit' => 'Kirish',

        'footer' => 'Postix AI',
    ],
    'users' => [
        'profile' => 'Profil',
        'title' => 'Foydalanuvchi',
        'refresh' => 'Yangilash',
        'delete_user' => 'Foydalanuvchini o‘chirish',
        'delete_confirm' => 'Haqiqatan o‘chirmoqchimisiz?',
        'user_updated' => 'Foydalanuvchi yangilandi',
        'user_deleted' => 'Foydalanuvchi o‘chirildi',
        'edit_user' => 'Foydalanuvchini tahrirlash',
        'name' => 'Ism',
        'email' => 'Login',
        'telegram_id' => 'Telegram ID',
        'role' => 'Rol',
        'no_role' => 'Rol yo‘q',
        'avatar' => 'Avatar',
        'remove_avatar' => 'Avatardan voz kechish',
        'phones' => 'Telefonlar',
        'add_phone' => 'Telefon qo‘shish',
        'add_phone_placeholder' => 'Telefon raqamini kiriting',
        'phone_added' => 'Telefon qo‘shildi',
        'phone_deleted' => 'Telefon o‘chirildi',
        'delete_phone' => 'O‘chirish',
        'phone_delete_confirm' => 'Telefonni o‘chirishni tasdiqlaysizmi?',
        'active' => 'Faol',
        'inactive' => 'Faol emas',
        'set_active' => 'Faollashtirish',
        'new_password' => 'Yangi parol',
        'leave_empty' => 'O‘zgarmasligi uchun bo‘sh qoldiring',
        'save_changes' => 'Saqlash',
        'no_email' => 'Login mavjud emas',
        'no_telegram' => 'Telegram mavjud emas',
        'no_change' => 'O‘zgarmasdan qoldirish',
        'back_to_list' => 'Orqaga',
        'phones_count' => 'telefonlar',
        'registered_at' => 'Ro\'yxatdan o\'tgan',
        'help_edit' => 'Profil ma\'lumotlarini yangilang',
        'manage_phones_hint' => 'Telefonlarni qo\'shish/ochirish',
        'add_phone_label' => 'Telefon qo\'shish',
        'send_sms' => 'Yuborish',
        'enter_code_label' => 'Kodni kiriting',
        'code_placeholder' => 'SMS kod',
        'verify_code' => 'Tasdiqlash',
        'change_phone' => 'Telefonni o\'zgartirish',
        'phone_required' => 'Telefon kiritilishi shart',
        'sms_sent' => 'SMS yuborildi',
        'sms_failed' => 'SMS yuborishda xatolik',
        'code_required' => 'Telefon va kod kiritilishi kerak',
        'verified' => 'Tasdiqlandi',
        'verify_failed' => 'Tasdiqlash muvaffaqiyatsiz',
        'operations_count' => 'Operatsiyalar soni',
        'messages_count' => 'Xabarlar soni',
        'search' => 'Qidirish',
        'read_only' => 'Faqat o‘qish',
    ],





    'ban' => [

        // Errors
        'invalid_type' => 'Noto‘g‘ri ban turi.',
        'not_found' => ':model topilmadi.',
        'admin_department_forbidden' => 'Admin bo‘limni ban qila olmaydi.',
        'admin_to_admin_forbidden' => 'Admin boshqa adminni ban qila olmaydi.',
        'no_permission' => 'Bu amal uchun ruxsat yo‘q.',
        'invalid_date' => 'Sana formati noto‘g‘ri.',
        'internal_error' => 'Serverda ichki xatolik yuz berdi.',

        // Success
        'unbanned' => ':model uchun ban olib tashlandi.',
        'banned_now' => 'Darhol ban qilish.',
        'scheduled' => 'Rejalashtirilgan',

        // Status
        'banned_since' => 'Ban boshlangan sana',
        'until' => 'Tugash vaqti',
        'now' => 'Hozir',

        // UI
        'date_optional' => 'Sana tanlash ixtiyoriy.',
        'sure?' => 'Siz haqiqatan ham bandan chiqarishni xohlaysizmi?',
        'confirm' => 'Bandan chiqarishni tasdiqlash',

        // Buttons
        'unban' => '🔓 Unban',
        'ban' => '🛑 Ban',
    ],



    'telegram' => [
        'phone_invalid' => 'Telefon raqam formati noto‘g‘ri. + bilan boshlanishi va 6-16 raqamdan iborat bo‘lishi kerak.',
        'login' => 'Telegram bilan bog‘lash',
        'phone_label' => 'Telefon raqami',
        'phone_placeholder' => 'Foydalanuvchi telefon raqamini kiriting',
        'phone_required' => 'Telefon raqami kiritilishi shart',
        'send_sms' => 'SMS yuborish',
        'sms_sent' => 'Tasdiqlash kodi yuborildi',

        // Code step
        'code_label' => 'Tasdiqlash kodi',
        'code_placeholder' => 'SMS orqali kelgan kodni kiriting',
        'code_required' => 'Telefon va tasdiqlash kodi kiritilishi kerak',
        'send_code' => 'Kodni tasdiqlash',

        'verifying' => 'Tasdiqlanmoqda, iltimos kuting...',
        'verified' => 'Telegram muvaffaqiyatli bog‘landi',

        'invalid_code' => 'Tasdiqlash kodi noto‘g‘ri',
        'expired_code' => 'Tasdiqlash kodi muddati tugagan',
        'try_again' => 'Qayta urinib ko‘ring',
        'limit' => 'Limitga yetildi. Foydalanuvchi o‘chirilsa, yangi slot bo‘shaydi.',
        'user_exists' => 'Ushbu tizimda ushbu telefon raqamiga ega foydalanuvchi allaqachon mavjud.',
        'already_in_progress' => 'Ushbu telefon raqamiga ega foydalanuvchi bilan Telegram kirish jarayoni allaqachon davom etmoqda.',
        'started' => 'Telegram bilan bog‘lash jarayoni boshlandi. Iltimos, biroz kuting.',
    ],
    'find' => [
        'filter_all_users' => '— Barcha foydalanuvchilar —',
    ],
    ///////////
    'errors' => [
        'flood_wait' => "Juda ko‘p so‘rov yuborildi. Iltimos, birozdan so‘ng qayta urinib ko‘ring.",
        'chat_write_forbidden' => "Bu chatga yozish uchun ruxsat yo‘q.",
        'user_blocked' => "Foydalanuvchi sizni bloklagan yoki akkaunt o‘chirilgan.",
        'peer_flood' => "Bu chat/foydalanuvchiga yuborishda vaqtincha cheklov mavjud (flood).",
        'phone_migrate' => "Telefon ma'lumotlari migratsiya qilinmoqda. Iltimos, sozlamalarni tekshiring.",
        'session_password_needed' => "Sessiya uchun parol talab qilinadi.",
        'network_error' => "Tarmoq xatosi. Iltimos, internet aloqangizni tekshiring.",
        'peer_not_found' => "Foydalanuvchi yoki guruh topilmadi.",
        'chat_guest_send_forbidden' => "Guruhga xabar yuborish uchun avval guruhga qo‘shiling.",
        'unknown_error' => "Noma'lum xatolik yuz berdi.",
        'SCHEDULE_TOO_MUCH' => "Juda ko'p rejalashtirilgan xabarlar mavjud.",



        '403_title' => '403 — Ruxsat yo‘q',
        'forbidden' => 'Kirish taqiqlangan',
        'forbidden_sub' => 'Sizda bu sahifani ko‘rish uchun ruxsat yo‘q.',
        'back' => 'Orqaga',
        'home' => 'Bosh sahifa',
    ],
    'group' => [
        'show_title' => 'Operatsiya #:id',
        'show_subtitle' => 'Xabar matni: :text',
        'cannot_cancel' => "Operatsiya #:id bekor qilib bo‘lmaydi. Faqat 'scheduled' yoki 'pending' holatidagi operatsiyalarni bekor qilish mumkin.",
        'canceled' => "Operatsiya #:id bekor qilindi.",
    ],
    'validation_failed' => 'Tekshiruv muvaffaqiyatsiz',

    'departments' => [
        'edit' => 'Department tahrirlash',
        'edit_title' => 'Department tahrirlash',

        'create' => 'Department yaratish',
        'create_title' => 'Department yaratish',

        'name' => 'Nomi',
        'placeholder' => 'Department nomi',
        'hint' => 'Masalan: Marketing, Sales, Support',
    ],

    'common' => [
        'save' => 'Saqlash',
        'cancel' => 'Bekor qilish',
    ],

];
