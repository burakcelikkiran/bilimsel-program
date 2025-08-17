<?php

namespace App\Http\Controllers\API;

/**
 * @OA\Info(
 *     title="Event Management System API",
 *     version="1.0.0",
 *     description="Comprehensive API for managing events, participants, venues, sponsors and program sessions",
 *     @OA\Contact(
 *         email="support@eventmanagement.com",
 *         name="API Support Team"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * @OA\Server(
 *     url="/api/v1",
 *     description="API Version 1"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Laravel Sanctum authentication"
 * )
 */

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="Kullanıcı modeli",
 *     required={"id", "name", "email", "role"},
 *     @OA\Property(property="id", type="integer", example=1, description="Kullanıcı ID"),
 *     @OA\Property(property="name", type="string", example="Ahmet Yılmaz", description="Ad Soyad"),
 *     @OA\Property(property="email", type="string", format="email", example="ahmet@example.com", description="E-posta adresi"),
 *     @OA\Property(property="role", type="string", enum={"admin", "organizer", "editor", "user"}, example="organizer", description="Kullanıcı rolü"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Aktif durumu"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, description="Email doğrulama tarihi"),
 *     @OA\Property(property="profile_photo_url", type="string", nullable=true, example="https://example.com/photos/user1.jpg", description="Profil fotoğrafı URL"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Oluşturma tarihi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Güncellenme tarihi")
 * )
 *
 * @OA\Schema(
 *     schema="UserSummary",
 *     type="object",
 *     title="User Summary",
 *     description="Kullanıcı özet bilgileri",
 *     required={"id", "name", "email"},
 *     @OA\Property(property="id", type="integer", example=1, description="Kullanıcı ID"),
 *     @OA\Property(property="name", type="string", example="Ahmet Yılmaz", description="Kullanıcı adı"),
 *     @OA\Property(property="email", type="string", format="email", example="ahmet@example.com", description="E-posta")
 * )
 *
 * @OA\Schema(
 *     schema="Organization",
 *     type="object",
 *     title="Organization",
 *     description="Organizasyon modeli",
 *     required={"id", "name", "slug"},
 *     @OA\Property(property="id", type="integer", example=1, description="Organizasyon ID"),
 *     @OA\Property(property="name", type="string", example="Türk Kardiyoloji Derneği", description="Organizasyon adı"),
 *     @OA\Property(property="slug", type="string", example="tkd", description="URL dostu kısa ad"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Türkiye'nin önde gelen kardiyoloji derneği", description="Açıklama"),
 *     @OA\Property(property="website", type="string", format="url", nullable=true, example="https://tkd.org.tr", description="Website adresi"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true, example="info@tkd.org.tr", description="İletişim e-postası"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+90 212 555 0123", description="Telefon numarası"),
 *     @OA\Property(property="address", type="string", nullable=true, example="İstanbul, Türkiye", description="Adres"),
 *     @OA\Property(property="logo", type="string", nullable=true, example="organizations/tkd-logo.png", description="Logo dosya yolu"),
 *     @OA\Property(property="logo_url", type="string", nullable=true, example="https://example.com/storage/organizations/tkd-logo.png", description="Logo URL"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Aktif durumu"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Oluşturma tarihi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Güncellenme tarihi")
 * )
 *
 * @OA\Schema(
 *     schema="OrganizationSummary",
 *     type="object",
 *     title="Organization Summary",
 *     description="Organizasyon özet bilgileri",
 *     required={"id", "name"},
 *     @OA\Property(property="id", type="integer", example=1, description="Organizasyon ID"),
 *     @OA\Property(property="name", type="string", example="Akademik Kongreler Derneği", description="Organizasyon adı"),
 *     @OA\Property(property="slug", type="string", example="akademik-kongreler-dernegi", description="URL dostu kısa ad"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Akademik kongreler düzenleyen dernek", description="Organizasyon açıklaması"),
 *     @OA\Property(property="logo_url", type="string", nullable=true, example="https://example.com/storage/organizations/logo.png", description="Logo URL"),
 *     @OA\Property(property="contact_email", type="string", format="email", nullable=true, example="info@akademik.org.tr", description="İletişim e-postası"),
 *     @OA\Property(property="contact_phone", type="string", nullable=true, example="+90 212 555 0123", description="İletişim telefonu"),
 *     @OA\Property(property="website_url", type="string", format="url", nullable=true, example="https://akademik.org.tr", description="Website adresi"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Aktif durumu"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Oluşturma tarihi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Güncellenme tarihi"),
 *     @OA\Property(
 *         property="statistics",
 *         type="object",
 *         description="İstatistik bilgileri",
 *         @OA\Property(property="events_count", type="integer", example=15, description="Etkinlik sayısı"),
 *         @OA\Property(property="participants_count", type="integer", example=2500, description="Katılımcı sayısı"),
 *         @OA\Property(property="sponsors_count", type="integer", example=8, description="Sponsor sayısı"),
 *         @OA\Property(property="users_count", type="integer", example=5, description="Kullanıcı sayısı")
 *     ),
 *     @OA\Property(property="user_role", type="string", nullable=true, enum={"admin", "editor", "viewer"}, example="admin", description="Kullanıcının organizasyondaki rolü"),
 *     @OA\Property(
 *         property="permissions",
 *         type="object",
 *         description="Kullanıcı yetkileri",
 *         @OA\Property(property="can_edit", type="boolean", example=true, description="Düzenleme yetkisi"),
 *         @OA\Property(property="can_delete", type="boolean", example=false, description="Silme yetkisi"),
 *         @OA\Property(property="can_manage_users", type="boolean", example=true, description="Kullanıcı yönetimi yetkisi")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="OrganizationDetail",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/OrganizationSummary")
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="OrganizationDetailWithRelations",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/OrganizationDetail"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(
 *                 property="users",
 *                 type="array",
 *                 description="Organizasyon kullanıcıları",
 *                 @OA\Items(ref="#/components/schemas/OrganizationUser")
 *             ),
 *             @OA\Property(
 *                 property="recent_events",
 *                 type="array",
 *                 description="Son etkinlikler",
 *                 @OA\Items(ref="#/components/schemas/EventSummary")
 *             ),
 *             @OA\Property(
 *                 property="recent_participants",
 *                 type="array",
 *                 description="Son katılımcılar",
 *                 @OA\Items(ref="#/components/schemas/ParticipantSummary")
 *             ),
 *             @OA\Property(
 *                 property="sponsors",
 *                 type="array",
 *                 description="Sponsorlar",
 *                 @OA\Items(ref="#/components/schemas/SponsorSummary")
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="OrganizationUser",
 *     type="object",
 *     title="Organization User",
 *     description="Organizasyon kullanıcısı",
 *     required={"id", "name", "email", "role"},
 *     @OA\Property(property="id", type="integer", example=1, description="Kullanıcı ID"),
 *     @OA\Property(property="name", type="string", example="Ahmet Yılmaz", description="Kullanıcı adı"),
 *     @OA\Property(property="email", type="string", format="email", example="ahmet@example.com", description="E-posta"),
 *     @OA\Property(property="role", type="string", enum={"admin", "editor", "viewer"}, example="editor", description="Organizasyondaki rolü"),
 *     @OA\Property(property="joined_at", type="string", format="date-time", description="Katılma tarihi")
 * )
 *
 * @OA\Schema(
 *     schema="OrganizationOption",
 *     type="object",
 *     title="Organization Option",
 *     description="Organizasyon seçenek formatı (dropdown için)",
 *     required={"value", "label"},
 *     @OA\Property(property="value", type="integer", example=1, description="Organizasyon ID"),
 *     @OA\Property(property="label", type="string", example="Türk Kardiyoloji Derneği", description="Organizasyon adı"),
 *     @OA\Property(property="slug", type="string", example="tkd", description="URL dostu kısa ad"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Aktif durumu")
 * )
 *
 * @OA\Schema(
 *     schema="OrganizationExport",
 *     type="object",
 *     title="Organization Export",
 *     description="Organizasyon dışa aktarım verisi",
 *     @OA\Property(
 *         property="organization",
 *         type="object",
 *         description="Organizasyon temel bilgileri",
 *         @OA\Property(property="name", type="string", example="Türk Kardiyoloji Derneği", description="Organizasyon adı"),
 *         @OA\Property(property="description", type="string", nullable=true, example="Kardiyoloji alanında faaliyet gösteren dernek", description="Organizasyon açıklaması"),
 *         @OA\Property(property="contact_email", type="string", format="email", nullable=true, example="info@tkd.org.tr", description="İletişim e-postası"),
 *         @OA\Property(property="contact_phone", type="string", nullable=true, example="+90 212 555 0123", description="İletişim telefonu"),
 *         @OA\Property(property="website_url", type="string", format="url", nullable=true, example="https://tkd.org.tr", description="Website adresi"),
 *         @OA\Property(property="exported_at", type="string", format="date-time", description="Dışa aktarım tarihi")
 *     ),
 *     @OA\Property(
 *         property="events",
 *         type="array",
 *         description="Organizasyonun etkinlikleri",
 *         @OA\Items(ref="#/components/schemas/EventSummary")
 *     ),
 *     @OA\Property(
 *         property="participants",
 *         type="array", 
 *         description="Organizasyonun katılımcıları",
 *         @OA\Items(ref="#/components/schemas/ParticipantSummary")
 *     ),
 *     @OA\Property(
 *         property="sponsors",
 *         type="array",
 *         description="Organizasyonun sponsorları",
 *         @OA\Items(ref="#/components/schemas/SponsorSummary")
 *     ),
 *     @OA\Property(
 *         property="users",
 *         type="array",
 *         description="Organizasyon kullanıcıları",
 *         @OA\Items(ref="#/components/schemas/OrganizationUser")
 *     ),
 *     @OA\Property(
 *         property="statistics",
 *         type="object",
 *         description="Organizasyon istatistikleri",
 *         @OA\Property(property="total_events", type="integer", example=25, description="Toplam etkinlik sayısı"),
 *         @OA\Property(property="total_participants", type="integer", example=1500, description="Toplam katılımcı sayısı"),
 *         @OA\Property(property="total_sponsors", type="integer", example=12, description="Toplam sponsor sayısı"),
 *         @OA\Property(property="total_sessions", type="integer", example=180, description="Toplam oturum sayısı"),
 *         @OA\Property(property="total_presentations", type="integer", example=850, description="Toplam sunum sayısı")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Event",
 *     type="object",
 *     title="Event",
 *     description="Etkinlik modeli",
 *     required={"id", "name", "slug", "organization_id"},
 *     @OA\Property(property="id", type="integer", example=1, description="Etkinlik ID"),
 *     @OA\Property(property="name", type="string", example="38. Ulusal Kardiyoloji Kongresi", description="Etkinlik adı"),
 *     @OA\Property(property="slug", type="string", example="38-ulusal-kardiyoloji-kongresi", description="URL dostu kısa ad"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Türkiye'nin en kapsamlı kardiyoloji kongresi", description="Etkinlik açıklaması"),
 *     @OA\Property(property="start_date", type="string", format="date", example="2024-10-15", description="Başlangıç tarihi"),
 *     @OA\Property(property="end_date", type="string", format="date", example="2024-10-18", description="Bitiş tarihi"),
 *     @OA\Property(property="location", type="string", nullable=true, example="İstanbul Kongre Merkezi", description="Etkinlik yeri"),
 *     @OA\Property(property="max_participants", type="integer", nullable=true, example=2000, description="Maksimum katılımcı sayısı"),
 *     @OA\Property(property="registration_start", type="string", format="date-time", nullable=true, description="Kayıt başlangıç tarihi"),
 *     @OA\Property(property="registration_end", type="string", format="date-time", nullable=true, description="Kayıt bitiş tarihi"),
 *     @OA\Property(property="is_published", type="boolean", example=true, description="Yayın durumu"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Aktif durumu"),
 *     @OA\Property(property="banner_image", type="string", nullable=true, example="events/congress-banner.jpg", description="Banner görsel"),
 *     @OA\Property(property="organization_id", type="integer", example=1, description="Organizasyon ID"),
 *     @OA\Property(property="created_by", type="integer", example=1, description="Oluşturan kullanıcı ID"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Oluşturma tarihi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Güncellenme tarihi"),
 *     @OA\Property(property="organization", ref="#/components/schemas/Organization", description="Bağlı organizasyon"),
 *     @OA\Property(property="creator", ref="#/components/schemas/User", description="Oluşturan kullanıcı")
 * )
 *
 * @OA\Schema(
 *     schema="EventSummary",
 *     type="object",
 *     title="Event Summary",
 *     description="Etkinlik özet bilgileri",
 *     required={"id", "name", "slug"},
 *     @OA\Property(property="id", type="integer", example=1, description="Etkinlik ID"),
 *     @OA\Property(property="name", type="string", example="38. Ulusal Kardiyoloji Kongresi", description="Etkinlik adı"),
 *     @OA\Property(property="slug", type="string", example="38-ulusal-kardiyoloji-kongresi", description="URL dostu kısa ad"),
 *     @OA\Property(property="start_date", type="string", format="date", example="2024-10-15", description="Başlangıç tarihi"),
 *     @OA\Property(property="end_date", type="string", format="date", example="2024-10-18", description="Bitiş tarihi"),
 *     @OA\Property(property="location", type="string", nullable=true, example="İstanbul Kongre Merkezi", description="Lokasyon"),
 *     @OA\Property(property="is_published", type="boolean", example=true, description="Yayın durumu"),
 *     @OA\Property(property="participants_count", type="integer", example=1500, description="Katılımcı sayısı"),
 *     @OA\Property(property="sessions_count", type="integer", example=25, description="Oturum sayısı"),
 *     @OA\Property(property="presentations_count", type="integer", example=120, description="Sunum sayısı"),
 *     @OA\Property(property="date_range", type="string", example="15.10.2024 - 18.10.2024", description="Tarih aralığı")
 * )
 *
 * @OA\Schema(
 *     schema="EventDay",
 *     type="object",
 *     title="Event Day",
 *     description="Etkinlik günü modeli",
 *     required={"id", "title", "date", "event_id"},
 *     @OA\Property(property="id", type="integer", example=1, description="Etkinlik günü ID"),
 *     @OA\Property(property="title", type="string", example="1. Gün", description="Gün başlığı"),
 *     @OA\Property(property="date", type="string", format="date", example="2024-10-15", description="Tarih"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Açılış günü", description="Açıklama"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Aktif durumu"),
 *     @OA\Property(property="sort_order", type="integer", example=1, description="Sıralama"),
 *     @OA\Property(property="event_id", type="integer", example=1, description="Etkinlik ID"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Oluşturma tarihi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Güncellenme tarihi"),
 *     @OA\Property(property="venues_count", type="integer", example=3, description="Salon sayısı"),
 *     @OA\Property(property="sessions_count", type="integer", example=8, description="Oturum sayısı")
 * )
 *
 * @OA\Schema(
 *     schema="Venue",
 *     type="object",
 *     title="Venue",
 *     description="Salon/Mekan modeli",
 *     required={"id", "name", "event_day_id"},
 *     @OA\Property(property="id", type="integer", example=1, description="Salon ID"),
 *     @OA\Property(property="name", type="string", example="Ana Salon", description="Salon adı"),
 *     @OA\Property(property="slug", type="string", example="ana-salon", description="URL dostu kısa ad"),
 *     @OA\Property(property="capacity", type="integer", nullable=true, example=500, description="Kapasite"),
 *     @OA\Property(property="location", type="string", nullable=true, example="Zemin Kat", description="Konum"),
 *     @OA\Property(property="floor", type="string", nullable=true, example="Zemin", description="Kat bilgisi"),
 *     @OA\Property(property="description", type="string", nullable=true, example="En büyük sunum salonu", description="Açıklama"),
 *     @OA\Property(
 *         property="facilities",
 *         type="array",
 *         description="Salon özellikleri",
 *         @OA\Items(type="string"),
 *         example={"Projeksiyon", "Ses Sistemi", "Kablosuz Mikrofon"}
 *     ),
 *     @OA\Property(property="setup_notes", type="string", nullable=true, example="Kurulum öncesi ses testi gerekli", description="Kurulum notları"),
 *     @OA\Property(property="technical_specs", type="string", nullable=true, example="4K projeksiyon, Dolby ses sistemi", description="Teknik özellikler"),
 *     @OA\Property(
 *         property="equipment",
 *         type="array",
 *         description="Mevcut ekipmanlar",
 *         @OA\Items(type="string"),
 *         example={"Laptop", "Projektor", "Mikrofon"}
 *     ),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Aktif durumu"),
 *     @OA\Property(property="sort_order", type="integer", example=1, description="Sıralama"),
 *     @OA\Property(property="event_day_id", type="integer", example=1, description="Etkinlik günü ID"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Oluşturma tarihi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Güncellenme tarihi"),
 *     @OA\Property(property="program_sessions_count", type="integer", example=5, description="Program oturumu sayısı"),
 *     @OA\Property(property="event_day", ref="#/components/schemas/EventDay", description="Bağlı etkinlik günü")
 * )
 *
 * @OA\Schema(
 *     schema="VenueResource",
 *     type="object",
 *     title="Venue Resource",
 *     description="Venue API kaynak formatı",
 *     required={"id", "name", "event_day_id"},
 *     @OA\Property(property="id", type="integer", example=1, description="Salon ID"),
 *     @OA\Property(property="name", type="string", example="Ana Salon", description="Salon adı"),
 *     @OA\Property(property="display_name", type="string", example="Ana Salon - Zemin Kat", description="Görüntülenen tam ad"),
 *     @OA\Property(property="slug", type="string", example="ana-salon", description="URL dostu kısa ad"),
 *     @OA\Property(property="capacity", type="integer", nullable=true, example=500, description="Kapasite"),
 *     @OA\Property(property="location", type="string", nullable=true, example="Zemin Kat", description="Konum"),
 *     @OA\Property(property="floor", type="string", nullable=true, example="Zemin", description="Kat bilgisi"),
 *     @OA\Property(property="description", type="string", nullable=true, example="En büyük sunum salonu", description="Açıklama"),
 *     @OA\Property(
 *         property="facilities",
 *         type="array",
 *         description="Salon özellikleri",
 *         @OA\Items(type="string"),
 *         example={"Projeksiyon", "Ses Sistemi", "Kablosuz Mikrofon"}
 *     ),
 *     @OA\Property(property="setup_notes", type="string", nullable=true, example="Kurulum öncesi ses testi gerekli", description="Kurulum notları"),
 *     @OA\Property(property="technical_specs", type="string", nullable=true, example="4K projeksiyon, Dolby ses sistemi", description="Teknik özellikler"),
 *     @OA\Property(
 *         property="equipment",
 *         type="array",
 *         description="Mevcut ekipmanlar",
 *         @OA\Items(type="string"),
 *         example={"Laptop", "Projektor", "Mikrofon"}
 *     ),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Aktif durumu"),
 *     @OA\Property(property="sort_order", type="integer", example=1, description="Sıralama"),
 *     @OA\Property(property="event_day_id", type="integer", example=1, description="Etkinlik günü ID"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Oluşturma tarihi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Güncellenme tarihi"),
 *     @OA\Property(property="program_sessions_count", type="integer", example=5, description="Program oturumu sayısı"),
 *     @OA\Property(
 *         property="event_day",
 *         type="object",
 *         description="Bağlı etkinlik günü özet bilgileri",
 *         @OA\Property(property="id", type="integer", example=1, description="Etkinlik günü ID"),
 *         @OA\Property(property="title", type="string", example="1. Gün", description="Gün başlığı"),
 *         @OA\Property(property="date", type="string", format="date", example="2024-10-15", description="Tarih"),
 *         @OA\Property(property="event_id", type="integer", example=1, description="Etkinlik ID")
 *     ),
 *     @OA\Property(
 *         property="event",
 *         type="object",
 *         description="Bağlı etkinlik özet bilgileri",
 *         @OA\Property(property="id", type="integer", example=1, description="Etkinlik ID"),
 *         @OA\Property(property="name", type="string", example="38. Ulusal Kardiyoloji Kongresi", description="Etkinlik adı"),
 *         @OA\Property(property="slug", type="string", example="38-ulusal-kardiyoloji-kongresi", description="URL dostu kısa ad")
 *     ),
 *     @OA\Property(
 *         property="program_sessions",
 *         type="array",
 *         description="Bu salondaki program oturumları (isteğe bağlı)",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1, description="Oturum ID"),
 *             @OA\Property(property="title", type="string", example="Kalp Yetersizliği", description="Oturum başlığı"),
 *             @OA\Property(property="start_time", type="string", format="time", example="09:00:00", description="Başlangıç saati"),
 *             @OA\Property(property="end_time", type="string", format="time", example="10:30:00", description="Bitiş saati"),
 *             @OA\Property(property="session_type", type="string", example="plenary", description="Oturum tipi")
 *         )
 *     ),
 *     @OA\Property(
 *         property="availability_status",
 *         type="object",
 *         description="Salon müsaitlik durumu",
 *         @OA\Property(property="is_available", type="boolean", example=true, description="Müsait mi?"),
 *         @OA\Property(property="next_session_time", type="string", format="time", nullable=true, example="14:00:00", description="Sonraki oturum saati"),
 *         @OA\Property(property="current_session", type="string", nullable=true, example="Öğle Arası", description="Şu anki oturum")
 *     ),
 *     @OA\Property(
 *         property="usage_statistics",
 *         type="object",
 *         description="Salon kullanım istatistikleri",
 *         @OA\Property(property="total_sessions", type="integer", example=12, description="Toplam oturum sayısı"),
 *         @OA\Property(property="total_duration_minutes", type="integer", example=720, description="Toplam kullanım süresi (dakika)"),
 *         @OA\Property(property="utilization_percentage", type="number", format="float", example=75.5, description="Kullanım oranı (%)")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="VenueCreateRequest",
 *     type="object",
 *     required={"event_day_id", "name"},
 *     description="Venue creation request schema",
 *     @OA\Property(
 *         property="event_day_id",
 *         type="integer",
 *         description="ID of the event day this venue belongs to",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         description="Venue name (must be unique within the event day)",
 *         example="Main Auditorium"
 *     ),
 *     @OA\Property(
 *         property="display_name",
 *         type="string",
 *         maxLength=255,
 *         nullable=true,
 *         description="Display name for the venue (defaults to name if not provided)",
 *         example="Main Auditorium - Hall A"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         maxLength=1000,
 *         nullable=true,
 *         description="Description of the venue",
 *         example="Large auditorium with theater seating and stage"
 *     ),
 *     @OA\Property(
 *         property="capacity",
 *         type="integer",
 *         minimum=1,
 *         maximum=100000,
 *         nullable=true,
 *         description="Maximum capacity of the venue",
 *         example=500
 *     ),
 *     @OA\Property(
 *         property="color",
 *         type="string",
 *         pattern="^#[0-9A-Fa-f]{6}$",
 *         nullable=true,
 *         description="Color code for venue display (hex format)",
 *         example="#3498db"
 *     ),
 *     @OA\Property(
 *         property="sort_order",
 *         type="integer",
 *         minimum=0,
 *         nullable=true,
 *         description="Sort order for venue display",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="notes",
 *         type="string",
 *         maxLength=1000,
 *         nullable=true,
 *         description="Internal notes about the venue",
 *         example="AV equipment available"
 *     ),
 *     @OA\Property(
 *         property="is_active",
 *         type="boolean",
 *         description="Whether the venue is active",
 *         example=true,
 *         default=true
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="VenueUpdateRequest",
 *     type="object",
 *     description="Venue update request schema",
 *     @OA\Property(
 *         property="event_day_id",
 *         type="integer",
 *         description="ID of the event day this venue belongs to",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         description="Venue name (must be unique within the event day)",
 *         example="Main Auditorium"
 *     ),
 *     @OA\Property(
 *         property="display_name",
 *         type="string",
 *         maxLength=255,
 *         nullable=true,
 *         description="Display name for the venue",
 *         example="Main Auditorium - Hall A"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         maxLength=1000,
 *         nullable=true,
 *         description="Description of the venue",
 *         example="Large auditorium with theater seating and stage"
 *     ),
 *     @OA\Property(
 *         property="capacity",
 *         type="integer",
 *         minimum=1,
 *         maximum=100000,
 *         nullable=true,
 *         description="Maximum capacity of the venue",
 *         example=500
 *     ),
 *     @OA\Property(
 *         property="color",
 *         type="string",
 *         pattern="^#[0-9A-Fa-f]{6}$",
 *         nullable=true,
 *         description="Color code for venue display (hex format)",
 *         example="#3498db"
 *     ),
 *     @OA\Property(
 *         property="sort_order",
 *         type="integer",
 *         minimum=0,
 *         nullable=true,
 *         description="Sort order for venue display",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="notes",
 *         type="string",
 *         maxLength=1000,
 *         nullable=true,
 *         description="Internal notes about the venue",
 *         example="AV equipment available"
 *     ),
 *     @OA\Property(
 *         property="is_active",
 *         type="boolean",
 *         description="Whether the venue is active",
 *         example=true
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="VenueDetailedResource",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/VenueResource"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(
 *                 property="program_sessions",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="title", type="string", example="Opening Session"),
 *                     @OA\Property(property="start_time", type="string", format="time", example="09:00:00"),
 *                     @OA\Property(property="end_time", type="string", format="time", example="10:30:00"),
 *                     @OA\Property(property="session_type", type="string", example="plenary"),
 *                     @OA\Property(property="is_break", type="boolean", example=false),
 *                     @OA\Property(property="moderators_count", type="integer", example=2),
 *                     @OA\Property(property="presentations_count", type="integer", example=3),
 *                     @OA\Property(property="speakers_count", type="integer", example=5)
 *                 )
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="VenueStatistics",
 *     type="object",
 *     description="Venue usage statistics",
 *     @OA\Property(property="total_sessions", type="integer", description="Total number of sessions", example=8),
 *     @OA\Property(property="total_presentations", type="integer", description="Total number of presentations", example=25),
 *     @OA\Property(property="total_speakers", type="integer", description="Total number of speakers", example=30),
 *     @OA\Property(property="capacity_utilization", type="number", format="float", nullable=true, description="Capacity utilization percentage", example=75.5),
 *     @OA\Property(
 *         property="session_types",
 *         type="object",
 *         description="Count of sessions by type",
 *         @OA\Property(property="plenary", type="integer", example=2),
 *         @OA\Property(property="parallel", type="integer", example=4),
 *         @OA\Property(property="workshop", type="integer", example=1),
 *         @OA\Property(property="break", type="integer", example=1)
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="TimeConflict",
 *     type="object",
 *     description="Time conflict information",
 *     @OA\Property(
 *         property="session",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="title", type="string", example="Session A"),
 *         @OA\Property(property="start_time", type="string", format="time", example="09:00:00"),
 *         @OA\Property(property="end_time", type="string", format="time", example="10:30:00")
 *     ),
 *     @OA\Property(
 *         property="conflicts_with",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=2),
 *             @OA\Property(property="title", type="string", example="Session B"),
 *             @OA\Property(property="start_time", type="string", format="time", example="09:30:00"),
 *             @OA\Property(property="end_time", type="string", format="time", example="11:00:00")
 *         )
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="VenueValidationError",
 *     type="object",
 *     description="Validation error response for venue operations",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Girilen bilgiler geçersiz."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         description="Field-specific validation errors",
 *         @OA\Property(
 *             property="event_day_id",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"Etkinlik günü seçimi zorunludur."}
 *         ),
 *         @OA\Property(
 *             property="name",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"Bu etkinlik gününde aynı isimde bir mekan zaten var."}
 *         ),
 *         @OA\Property(
 *             property="capacity",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"Kapasite en az 1 olmalıdır."}
 *         ),
 *         @OA\Property(
 *             property="color",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"Renk geçerli hex formatında olmalıdır (#RRGGBB)."}
 *         )
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="VenueAccessError",
 *     type="object",
 *     description="Access denied error response",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Bu etkinlik gününe mekan ekleyemezsiniz."),
 *     @OA\Property(
 *         property="details",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="required_organization_access", type="boolean", example=true),
 *         @OA\Property(property="user_organizations", type="array", @OA\Items(type="integer")),
 *         @OA\Property(property="event_organization_id", type="integer", example=5)
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Sponsor",
 *     type="object",
 *     title="Sponsor",
 *     description="Sponsor modeli",
 *     required={"id", "name", "sponsor_level", "organization_id"},
 *     @OA\Property(property="id", type="integer", example=1, description="Sponsor ID"),
 *     @OA\Property(property="name", type="string", example="Bayer Türk", description="Sponsor adı"),
 *     @OA\Property(property="sponsor_level", type="string", enum={"platinum", "gold", "silver", "bronze"}, example="gold", description="Sponsor seviyesi"),
 *     @OA\Property(property="website", type="string", format="url", nullable=true, example="https://bayer.com.tr", description="Website adresi"),
 *     @OA\Property(property="contact_email", type="string", format="email", nullable=true, example="info@bayer.com.tr", description="İletişim e-postası"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Dünya çapında ilaç şirketi", description="Açıklama"),
 *     @OA\Property(property="logo", type="string", nullable=true, example="sponsors/bayer-logo.png", description="Logo dosya yolu"),
 *     @OA\Property(property="logo_url", type="string", nullable=true, example="https://example.com/storage/sponsors/bayer-logo.png", description="Logo URL"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Aktif durumu"),
 *     @OA\Property(property="sort_order", type="integer", example=1, description="Sıralama"),
 *     @OA\Property(property="organization_id", type="integer", example=1, description="Organizasyon ID"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Oluşturma tarihi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Güncellenme tarihi"),
 *     @OA\Property(property="organization", ref="#/components/schemas/Organization", description="Bağlı organizasyon"),
 *     @OA\Property(property="program_sessions_count", type="integer", example=3, description="Sponsor olunan oturum sayısı"),
 *     @OA\Property(property="presentations_count", type="integer", example=8, description="Sponsor olunan sunum sayısı")
 * )
 *
 * @OA\Schema(
 *     schema="SponsorSummary",
 *     type="object",
 *     title="Sponsor Summary",
 *     description="Sponsor özet bilgileri",
 *     required={"id", "name", "sponsor_level"},
 *     @OA\Property(property="id", type="integer", example=1, description="Sponsor ID"),
 *     @OA\Property(property="name", type="string", example="Bayer Türk", description="Sponsor adı"),
 *     @OA\Property(property="sponsor_level", type="string", enum={"platinum", "gold", "silver", "bronze"}, example="gold", description="Sponsor seviyesi"),
 *     @OA\Property(property="logo_url", type="string", nullable=true, example="https://example.com/logos/bayer.png", description="Logo URL"),
 *     @OA\Property(property="website", type="string", format="url", nullable=true, example="https://bayer.com.tr", description="Website")
 * )
 *
 * @OA\Schema(
 *     schema="ProgramSession",
 *     type="object",
 *     title="ProgramSession",
 *     description="Program Oturumu modeli",
 *     required={"id", "title", "venue_id", "start_time", "end_time"},
 *     @OA\Property(property="id", type="integer", example=1, description="Oturum ID"),
 *     @OA\Property(property="title", type="string", example="Kalp Yetersizliği", description="Oturum başlığı"),
 *     @OA\Property(property="session_type", type="string", enum={"plenary", "parallel", "workshop", "poster", "break", "lunch", "social"}, example="plenary", description="Oturum tipi"),
 *     @OA\Property(property="start_time", type="string", format="time", example="09:00:00", description="Başlangıç saati"),
 *     @OA\Property(property="end_time", type="string", format="time", example="10:30:00", description="Bitiş saati"),
 *     @OA\Property(property="moderator_title", type="string", nullable=true, example="Oturum Başkanı", description="Moderatör unvanı"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Kalp yetersizliğinde güncel yaklaşımlar", description="Oturum açıklaması"),
 *     @OA\Property(property="max_presentations", type="integer", nullable=true, example=6, description="Maksimum sunum sayısı"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Aktif durumu"),
 *     @OA\Property(property="sort_order", type="integer", example=1, description="Sıralama"),
 *     @OA\Property(property="venue_id", type="integer", example=1, description="Salon ID"),
 *     @OA\Property(property="sponsor_id", type="integer", nullable=true, example=1, description="Sponsor ID"),
 *     @OA\Property(property="category_id", type="integer", nullable=true, example=1, description="Kategori ID"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Oluşturma tarihi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Güncellenme tarihi"),
 *     @OA\Property(property="venue", ref="#/components/schemas/Venue", description="Bağlı salon"),
 *     @OA\Property(property="sponsor", ref="#/components/schemas/Sponsor", description="Sponsor"),
 *     @OA\Property(property="category", ref="#/components/schemas/ProgramSessionCategory", description="Kategori"),
 *     @OA\Property(property="presentations_count", type="integer", example=4, description="Sunum sayısı"),
 *     @OA\Property(property="moderators_count", type="integer", example=2, description="Moderatör sayısı"),
 *     @OA\Property(property="duration_in_minutes", type="integer", example=90, description="Süre (dakika)")
 * )
 *
 * @OA\Schema(
 *     schema="ProgramSessionCategory",
 *     type="object",
 *     title="Program Session Category",
 *     description="Program oturumu kategorisi",
 *     required={"id", "name", "event_id"},
 *     @OA\Property(property="id", type="integer", example=1, description="Kategori ID"),
 *     @OA\Property(property="name", type="string", example="Ana Oturumlar", description="Kategori adı"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Ana konferans oturumları", description="Açıklama"),
 *     @OA\Property(property="color", type="string", example="#3B82F6", description="Kategori rengi (hex)"),
 *     @OA\Property(property="event_id", type="integer", example=1, description="Etkinlik ID"),
 *     @OA\Property(property="sort_order", type="integer", example=1, description="Sıralama"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Aktif durumu"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Oluşturma tarihi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Güncellenme tarihi"),
 *     @OA\Property(property="sessions_count", type="integer", example=12, description="Bu kategorideki oturum sayısı")
 * )
 *
 * @OA\Schema(
 *     schema="Participant",
 *     type="object",
 *     title="Participant",
 *     description="Katılımcı modeli",
 *     required={"id", "first_name", "last_name", "email"},
 *     @OA\Property(property="id", type="integer", example=1, description="Katılımcı ID"),
 *     @OA\Property(property="first_name", type="string", example="Ahmet", description="Ad"),
 *     @OA\Property(property="last_name", type="string", example="Yılmaz", description="Soyad"),
 *     @OA\Property(property="email", type="string", format="email", example="ahmet.yilmaz@example.com", description="E-posta"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+90 555 123 4567", description="Telefon"),
 *     @OA\Property(property="institution", type="string", nullable=true, example="İstanbul Üniversitesi", description="Kurum"),
 *     @OA\Property(property="title", type="string", nullable=true, example="Prof. Dr.", description="Unvan"),
 *     @OA\Property(property="specialty", type="string", nullable=true, example="Kardiyoloji", description="Uzmanlık alanı"),
 *     @OA\Property(property="bio", type="string", nullable=true, example="Kardiyoloji uzmanı", description="Biyografi"),
 *     @OA\Property(property="linkedin", type="string", nullable=true, example="linkedin.com/in/ahmetyilmaz", description="LinkedIn profili"),
 *     @OA\Property(property="twitter", type="string", nullable=true, example="@ahmetyilmaz", description="Twitter"),
 *     @OA\Property(property="orcid", type="string", nullable=true, example="0000-0000-0000-0000", description="ORCID ID"),
 *     @OA\Property(property="registration_status", type="string", enum={"pending", "confirmed", "cancelled"}, example="confirmed", description="Kayıt durumu"),
 *     @OA\Property(property="payment_status", type="string", enum={"pending", "paid", "refunded"}, example="paid", description="Ödeme durumu"),
 *     @OA\Property(property="attendance_status", type="string", enum={"not_attended", "partially_attended", "fully_attended"}, example="fully_attended", description="Katılım durumu"),
 *     @OA\Property(property="registration_date", type="string", format="date-time", nullable=true, description="Kayıt tarihi"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Oluşturma tarihi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Güncellenme tarihi")
 * )
 *
 * @OA\Schema(
 *     schema="ParticipantSummary",
 *     type="object",
 *     title="Participant Summary",
 *     description="Katılımcı özet bilgileri",
 *     required={"id", "first_name", "last_name", "email"},
 *     @OA\Property(property="id", type="integer", example=1, description="Katılımcı ID"),
 *     @OA\Property(property="first_name", type="string", example="Ahmet", description="Ad"),
 *     @OA\Property(property="last_name", type="string", example="Yılmaz", description="Soyad"),
 *     @OA\Property(property="name", type="string", example="Ahmet Yılmaz", description="Tam ad"),
 *     @OA\Property(property="email", type="string", format="email", example="ahmet@example.com", description="E-posta"),
 *     @OA\Property(property="title", type="string", nullable=true, example="Prof. Dr.", description="Unvan"),
 *     @OA\Property(property="institution", type="string", nullable=true, example="İstanbul Üniversitesi", description="Kurum"),
 *     @OA\Property(property="specialty", type="string", nullable=true, example="Kardiyoloji", description="Uzmanlık alanı"),
 *     @OA\Property(property="presentations_count", type="integer", example=3, description="Sunum sayısı"),
 *     @OA\Property(property="moderated_sessions_count", type="integer", example=2, description="Modere ettiği oturum sayısı")
 * )
 *
 * @OA\Schema(
 *     schema="ParticipantDetail",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ParticipantSummary"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="bio", type="string", nullable=true, description="Biyografi"),
 *             @OA\Property(property="linkedin", type="string", nullable=true, description="LinkedIn profili"),
 *             @OA\Property(property="twitter", type="string", nullable=true, description="Twitter kullanıcı adı"),
 *             @OA\Property(property="orcid", type="string", nullable=true, description="ORCID ID"),
 *             @OA\Property(property="phone", type="string", nullable=true, description="Telefon numarası"),
 *             @OA\Property(property="registration_status", type="string", enum={"pending", "confirmed", "cancelled"}, description="Kayıt durumu"),
 *             @OA\Property(property="payment_status", type="string", enum={"pending", "paid", "refunded"}, description="Ödeme durumu"),
 *             @OA\Property(property="attendance_status", type="string", enum={"not_attended", "partially_attended", "fully_attended"}, description="Katılım durumu")
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ParticipantDetailWithStats",
 *     type="object",
 *     title="Participant Detail With Statistics",
 *     description="İstatistikleri ile birlikte katılımcı detayları",
 *     @OA\Property(property="participant", ref="#/components/schemas/ParticipantDetail", description="Katılımcı detayları"),
 *     @OA\Property(
 *         property="statistics",
 *         type="object",
 *         description="Katılımcı istatistikleri",
 *         @OA\Property(property="total_sessions_moderated", type="integer", example=5, description="Toplam moderasyon sayısı"),
 *         @OA\Property(property="total_presentations", type="integer", example=12, description="Toplam sunum sayısı"),
 *         @OA\Property(property="primary_presentations", type="integer", example=8, description="Ana konuşmacı olduğu sunum sayısı"),
 *         @OA\Property(property="co_speaker_presentations", type="integer", example=3, description="Ortak konuşmacı olduğu sunum sayısı"),
 *         @OA\Property(property="discussant_presentations", type="integer", example=1, description="Tartışmacı olduğu sunum sayısı"),
 *         @OA\Property(property="total_participations", type="integer", example=8, description="Toplam etkinlik katılımı")
 *     ),
 *     @OA\Property(
 *         property="participations_by_event",
 *         type="array",
 *         description="Etkinlik bazında katılımlar",
 *         @OA\Items(ref="#/components/schemas/ParticipantEventParticipation")
 *     ),
 *     @OA\Property(
 *         property="recent_activities",
 *         type="array",
 *         description="Son aktiviteler",
 *         @OA\Items(ref="#/components/schemas/ParticipantActivity")
 *     ),
 *     @OA\Property(
 *         property="permissions",
 *         type="object",
 *         description="Kullanıcı yetkileri",
 *         @OA\Property(property="can_edit", type="boolean", example=true, description="Düzenleme yetkisi"),
 *         @OA\Property(property="can_delete", type="boolean", example=false, description="Silme yetkisi")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ParticipantEventParticipation",
 *     type="object",
 *     title="Participant Event Participation",
 *     description="Katılımcının etkinlik katılım bilgileri",
 *     @OA\Property(property="event_id", type="integer", example=1, description="Etkinlik ID"),
 *     @OA\Property(property="event_name", type="string", example="38. Ulusal Kardiyoloji Kongresi", description="Etkinlik adı"),
 *     @OA\Property(property="role", type="string", enum={"speaker", "moderator", "both", "none"}, example="speaker", description="Etkinlikteki rolü"),
 *     @OA\Property(property="presentations_count", type="integer", example=2, description="Sunum sayısı"),
 *     @OA\Property(property="moderated_sessions_count", type="integer", example=1, description="Modere ettiği oturum sayısı")
 * )
 *
 * @OA\Schema(
 *     schema="ParticipantActivity",
 *     type="object",
 *     title="Participant Activity",
 *     description="Katılımcı aktivite bilgisi",
 *     @OA\Property(property="id", type="integer", example=1, description="Aktivite ID"),
 *     @OA\Property(property="type", type="string", enum={"presentation", "moderation", "participation"}, example="presentation", description="Aktivite tipi"),
 *     @OA\Property(property="title", type="string", example="Kalp Yetersizliğinde Yeni Yaklaşımlar", description="Aktivite başlığı"),
 *     @OA\Property(property="event_name", type="string", example="38. Ulusal Kardiyoloji Kongresi", description="Etkinlik adı"),
 *     @OA\Property(property="date", type="string", format="date", example="2024-10-15", description="Aktivite tarihi"),
 *     @OA\Property(property="venue_name", type="string", nullable=true, example="Ana Salon", description="Salon adı")
 * )
 *
 * @OA\Schema(
 *     schema="ParticipantSearchResult",
 *     type="object",
 *     title="Participant Search Result",
 *     description="Katılımcı arama sonucu",
 *     @OA\Property(property="id", type="integer", example=1, description="Katılımcı ID"),
 *     @OA\Property(property="name", type="string", example="Ahmet Yılmaz", description="Tam ad"),
 *     @OA\Property(property="title", type="string", nullable=true, example="Prof. Dr.", description="Unvan"),
 *     @OA\Property(property="institution", type="string", nullable=true, example="İstanbul Üniversitesi", description="Kurum"),
 *     @OA\Property(property="specialty", type="string", nullable=true, example="Kardiyoloji", description="Uzmanlık alanı"),
 *     @OA\Property(property="email", type="string", format="email", example="ahmet@example.com", description="E-posta"),
 *     @OA\Property(property="role_in_organization", type="string", enum={"speaker", "moderator", "both", "none"}, example="speaker", description="Organizasyondaki rolü")
 * )
 *
 * @OA\Schema(
 *     schema="ParticipantStatistics",
 *     type="object",
 *     title="Participant Statistics",
 *     description="Katılımcı istatistikleri",
 *     @OA\Property(
 *         property="overview",
 *         type="object",
 *         description="Genel istatistikler",
 *         @OA\Property(property="total_participants", type="integer", example=1250, description="Toplam katılımcı sayısı"),
 *         @OA\Property(property="active_speakers", type="integer", example=185, description="Aktif konuşmacı sayısı"),
 *         @OA\Property(property="active_moderators", type="integer", example=95, description="Aktif moderatör sayısı"),
 *         @OA\Property(property="registered_participants", type="integer", example=980, description="Kayıtlı katılımcı sayısı"),
 *         @OA\Property(property="confirmed_participants", type="integer", example=850, description="Onaylı katılımcı sayısı")
 *     ),
 *     @OA\Property(
 *         property="by_organization",
 *         type="array",
 *         description="Organizasyon bazında istatistikler",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="organization_id", type="integer", example=1, description="Organizasyon ID"),
 *             @OA\Property(property="organization_name", type="string", example="Türk Kardiyoloji Derneği", description="Organizasyon adı"),
 *             @OA\Property(property="participant_count", type="integer", example=125, description="Katılımcı sayısı"),
 *             @OA\Property(property="speaker_count", type="integer", example=25, description="Konuşmacı sayısı"),
 *             @OA\Property(property="moderator_count", type="integer", example=12, description="Moderatör sayısı")
 *         )
 *     ),
 *     @OA\Property(
 *         property="by_role",
 *         type="object",
 *         description="Rol bazında istatistikler",
 *         @OA\Property(property="speakers_only", type="integer", example=140, description="Sadece konuşmacı"),
 *         @OA\Property(property="moderators_only", type="integer", example=70, description="Sadece moderatör"),
 *         @OA\Property(property="both_roles", type="integer", example=45, description="Hem konuşmacı hem moderatör"),
 *         @OA\Property(property="no_role", type="integer", example=995, description="Rolü olmayan")
 *     ),
 *     @OA\Property(
 *         property="by_specialty",
 *         type="array",
 *         description="Uzmanlık alanı bazında istatistikler",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="specialty", type="string", example="Kardiyoloji", description="Uzmanlık alanı"),
 *             @OA\Property(property="count", type="integer", example=85, description="Bu alandaki katılımcı sayısı"),
 *             @OA\Property(property="percentage", type="number", format="float", example=6.8, description="Yüzde oranı")
 *         )
 *     ),
 *     @OA\Property(
 *         property="by_institution_type",
 *         type="array",
 *         description="Kurum tipi bazında istatistikler",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="type", type="string", example="Üniversite", description="Kurum tipi"),
 *             @OA\Property(property="count", type="integer", example=450, description="Bu tipteki katılımcı sayısı"),
 *             @OA\Property(property="percentage", type="number", format="float", example=36.0, description="Yüzde oranı")
 *         )
 *     ),
 *     @OA\Property(
 *         property="registration_trends",
 *         type="object",
 *         description="Kayıt trendleri",
 *         @OA\Property(
 *             property="by_month",
 *             type="array",
 *             description="Aylık kayıt sayıları",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="month", type="string", example="2024-10", description="Ay"),
 *                 @OA\Property(property="count", type="integer", example=125, description="O aydaki kayıt sayısı")
 *             )
 *         ),
 *         @OA\Property(
 *             property="by_week",
 *             type="array",
 *             description="Haftalık kayıt sayıları (son 8 hafta)",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="week", type="string", example="2024-W42", description="Hafta"),
 *                 @OA\Property(property="count", type="integer", example=35, description="O haftadaki kayıt sayısı")
 *             )
 *         )
 *     ),
 *     @OA\Property(
 *         property="top_institutions",
 *         type="array",
 *         description="En çok katılımcıya sahip kurumlar",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="institution", type="string", example="İstanbul Üniversitesi", description="Kurum adı"),
 *             @OA\Property(property="participant_count", type="integer", example=45, description="Katılımcı sayısı"),
 *             @OA\Property(property="speaker_count", type="integer", example=8, description="Konuşmacı sayısı"),
 *             @OA\Property(property="moderator_count", type="integer", example=3, description="Moderatör sayısı")
 *         )
 *     ),
 *     @OA\Property(
 *         property="most_active_participants",
 *         type="array",
 *         description="En aktif katılımcılar",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="participant_id", type="integer", example=1, description="Katılımcı ID"),
 *             @OA\Property(property="name", type="string", example="Prof. Dr. Ahmet Yılmaz", description="Katılımcı adı"),
 *             @OA\Property(property="total_presentations", type="integer", example=5, description="Toplam sunum sayısı"),
 *             @OA\Property(property="total_moderations", type="integer", example=3, description="Toplam moderasyon sayısı"),
 *             @OA\Property(property="activity_score", type="integer", example=8, description="Aktivite skoru")
 *         )
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Presentation",
 *     type="object",
 *     title="Presentation",
 *     description="Sunum modeli",
 *     required={"id", "title", "program_session_id"},
 *     @OA\Property(property="id", type="integer", example=1, description="Sunum ID"),
 *     @OA\Property(property="title", type="string", example="Kalp Yetersizliğinde Yeni Yaklaşımlar", description="Sunum başlığı"),
 *     @OA\Property(property="abstract", type="string", nullable=true, example="Bu sunumda...", description="Özet"),
 *     @OA\Property(property="language", type="string", enum={"tr", "en", "de", "fr", "es", "it"}, example="tr", description="Sunum dili"),
 *     @OA\Property(property="duration", type="integer", example=20, description="Süre (dakika)"),
 *     @OA\Property(property="status", type="string", enum={"draft", "confirmed", "cancelled"}, example="confirmed", description="Durum"),
 *     @OA\Property(property="sort_order", type="integer", example=1, description="Sıralama"),
 *     @OA\Property(property="program_session_id", type="integer", example=1, description="Program oturumu ID"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Oluşturma tarihi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Güncellenme tarihi"),
 *     @OA\Property(property="program_session", ref="#/components/schemas/ProgramSession", description="Bağlı program oturumu"),
 *     @OA\Property(property="speakers_count", type="integer", example=2, description="Konuşmacı sayısı")
 * )
 *
 * @OA\Schema(
 *     schema="ApiResponse",
 *     type="object",
 *     title="API Response",
 *     description="Standart API yanıt formatı",
 *     required={"success", "message"},
 *     @OA\Property(property="success", type="boolean", example=true, description="İşlem başarı durumu"),
 *     @OA\Property(property="message", type="string", example="İşlem başarılı", description="Yanıt mesajı"),
 *     @OA\Property(property="data", type="object", description="Yanıt verisi"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         description="Doğrulama hataları",
 *         @OA\AdditionalProperties(
 *             type="array",
 *             @OA\Items(type="string")
 *         )
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="PaginatedResponse",
 *     type="object",
 *     title="Paginated Response",
 *     description="Sayfalanmış API yanıt formatı",
 *     required={"current_page", "data", "total", "per_page"},
 *     @OA\Property(property="current_page", type="integer", example=1, description="Mevcut sayfa"),
 *     @OA\Property(property="data", type="array", @OA\Items(type="object"), description="Sayfa verisi"),
 *     @OA\Property(property="first_page_url", type="string", example="http://example.com/api/events?page=1", description="İlk sayfa URL"),
 *     @OA\Property(property="from", type="integer", example=1, description="Başlangıç kayıt numarası"),
 *     @OA\Property(property="last_page", type="integer", example=10, description="Son sayfa numarası"),
 *     @OA\Property(property="last_page_url", type="string", example="http://example.com/api/events?page=10", description="Son sayfa URL"),
 *     @OA\Property(property="next_page_url", type="string", nullable=true, example="http://example.com/api/events?page=2", description="Sonraki sayfa URL"),
 *     @OA\Property(property="path", type="string", example="http://example.com/api/events", description="Temel URL"),
 *     @OA\Property(property="per_page", type="integer", example=15, description="Sayfa başına kayıt"),
 *     @OA\Property(property="prev_page_url", type="string", nullable=true, example=null, description="Önceki sayfa URL"),
 *     @OA\Property(property="to", type="integer", example=15, description="Bitiş kayıt numarası"),
 *     @OA\Property(property="total", type="integer", example=150, description="Toplam kayıt sayısı")
 * )
 *
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *     title="Pagination Meta",
 *     description="Sayfalama meta bilgileri",
 *     required={"current_page", "total", "per_page", "last_page"},
 *     @OA\Property(property="current_page", type="integer", example=1, description="Mevcut sayfa"),
 *     @OA\Property(property="total", type="integer", example=150, description="Toplam kayıt sayısı"),
 *     @OA\Property(property="per_page", type="integer", example=15, description="Sayfa başına kayıt"),
 *     @OA\Property(property="last_page", type="integer", example=10, description="Son sayfa numarası"),
 *     @OA\Property(property="from", type="integer", nullable=true, example=1, description="Başlangıç kayıt numarası"),
 *     @OA\Property(property="to", type="integer", nullable=true, example=15, description="Bitiş kayıt numarası"),
 *     @OA\Property(property="path", type="string", example="http://example.com/api/participants", description="Temel URL"),
 *     @OA\Property(property="first_page_url", type="string", example="http://example.com/api/participants?page=1", description="İlk sayfa URL"),
 *     @OA\Property(property="last_page_url", type="string", example="http://example.com/api/participants?page=10", description="Son sayfa URL"),
 *     @OA\Property(property="next_page_url", type="string", nullable=true, example="http://example.com/api/participants?page=2", description="Sonraki sayfa URL"),
 *     @OA\Property(property="prev_page_url", type="string", nullable=true, example=null, description="Önceki sayfa URL")
 * )
 *
 * @OA\Schema(
 *     schema="PaginationLinks",
 *     type="object",
 *     title="Pagination Links",
 *     description="Sayfalama link bilgileri",
 *     @OA\Property(property="first", type="string", nullable=true, example="http://example.com/api/participants?page=1", description="İlk sayfa linki"),
 *     @OA\Property(property="last", type="string", nullable=true, example="http://example.com/api/participants?page=10", description="Son sayfa linki"),
 *     @OA\Property(property="prev", type="string", nullable=true, example=null, description="Önceki sayfa linki"),
 *     @OA\Property(property="next", type="string", nullable=true, example="http://example.com/api/participants?page=2", description="Sonraki sayfa linki")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     title="Validation Error",
 *     description="Doğrulama hatası yanıtı",
 *     @OA\Property(property="success", type="boolean", example=false, description="İşlem başarı durumu"),
 *     @OA\Property(property="message", type="string", example="Doğrulama hatası", description="Hata mesajı"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         description="Alan bazlı hatalar",
 *         @OA\Property(
 *             property="name",
 *             type="array",
 *             @OA\Items(type="string", example="Name alanı zorunludur.")
 *         ),
 *         @OA\Property(
 *             property="email",
 *             type="array",
 *             @OA\Items(type="string", example="Geçerli bir e-posta adresi giriniz.")
 *         )
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Error",
 *     type="object",
 *     title="Error Response",
 *     description="Hata yanıtı",
 *     required={"success", "message"},
 *     @OA\Property(property="success", type="boolean", example=false, description="İşlem başarı durumu"),
 *     @OA\Property(property="message", type="string", example="Bir hata oluştu", description="Hata mesajı"),
 *     @OA\Property(property="error_code", type="string", nullable=true, example="ORG_001", description="Hata kodu"),
 *     @OA\Property(property="details", type="object", nullable=true, description="Hata detayları")
 * )
 *
 * @OA\Schema(
 *     schema="AuthToken",
 *     type="object",
 *     title="Authentication Token",
 *     description="Kimlik doğrulama token yanıtı",
 *     required={"access_token", "token_type", "user"},
 *     @OA\Property(property="access_token", type="string", example="1|abc123def456...", description="Erişim token'ı"),
 *     @OA\Property(property="token_type", type="string", example="Bearer", description="Token tipi"),
 *     @OA\Property(property="expires_in", type="integer", example=3600, description="Token geçerlilik süresi (saniye)"),
 *     @OA\Property(property="user", ref="#/components/schemas/User", description="Kullanıcı bilgileri")
 * )
 *
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     type="object",
 *     title="Success Response",
 *     description="Başarılı işlem yanıtı",
 *     @OA\Property(property="success", type="boolean", example=true, description="İşlem başarı durumu"),
 *     @OA\Property(property="message", type="string", example="İşlem başarıyla tamamlandı", description="Başarı mesajı"),
 *     @OA\Property(property="data", type="object", description="Yanıt verisi")
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     title="Error Response",
 *     description="Hata yanıtı",
 *     @OA\Property(property="success", type="boolean", example=false, description="İşlem başarı durumu"),
 *     @OA\Property(property="message", type="string", example="Bir hata oluştu", description="Hata mesajı"),
 *     @OA\Property(property="error", type="string", example="Detaylı hata açıklaması", description="Hata detayı")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     type="object",
 *     title="Validation Error Response",
 *     description="Doğrulama hatası yanıtı",
 *     @OA\Property(property="message", type="string", example="The given data was invalid.", description="Ana hata mesajı"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         description="Alan bazlı doğrulama hataları",
 *         @OA\AdditionalProperties(
 *             type="array",
 *             @OA\Items(type="string")
 *         )
 *     )
 * )
 */
class Schemas
{
    // Bu sınıf sadece Swagger annotation'ları için kullanılır
    // Herhangi bir method'a ihtiyaç yoktur
}