<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Event Management System API",
 *     description="Etkinlik Yönetim Sistemi REST API Dokümantasyonu - Türkiye'nin önde gelen etkinlik yönetim platformu",
 *     @OA\Contact(
 *         email="admin@eventmanagement.com",
 *         name="Event Management Support Team",
 *         url="https://eventmanagement.com/support"
 *     ),
 *     @OA\License(
 *         name="MIT License",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Yerel Geliştirme Sunucusu"
 * )
 * 
 * @OA\Server(
 *     url="https://api.eventmanagement.com",
 *     description="Production API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Laravel Sanctum Bearer Token Authentication. Format: 'Bearer {token}'"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="api_key",
 *     type="apiKey",
 *     in="header",
 *     name="X-API-Key",
 *     description="API Key Authentication for external integrations"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="Kullanıcı kimlik doğrulama ve token yönetimi"
 * )
 * 
 * @OA\Tag(
 *     name="Events",
 *     description="Etkinlik yönetimi - oluşturma, düzenleme, listeleme"
 * )
 * 
 * @OA\Tag(
 *     name="Organizations",
 *     description="Organizasyon yönetimi - şirketler, dernekler, kurumlar"
 * )
 * 
 * @OA\Tag(
 *     name="Venues",
 *     description="Mekan/Salon yönetimi - konferans salonları, toplantı odaları"
 * )
 * 
 * @OA\Tag(
 *     name="Sponsors",
 *     description="Sponsor yönetimi - platinum, gold, silver, bronze sponsorlar"
 * )
 * 
 * @OA\Tag(
 *     name="Program Sessions",
 *     description="Program oturumları - ana oturumlar, uydu sempozyumları, sunumlar"
 * )
 * 
 * @OA\Tag(
 *     name="Presentations",
 *     description="Sunum yönetimi - bildiriler, konuşmalar, posterler"
 * )
 * 
 * @OA\Tag(
 *     name="Participants",
 *     description="Katılımcı yönetimi - kayıt, profil, katılım durumu"
 * )
 * 
 * @OA\Tag(
 *     name="Speakers",
 *     description="Konuşmacı yönetimi - davetli konuşmacılar, moderatörler"
 * )
 * 
 * @OA\Tag(
 *     name="Public Events",
 *     description="Genel erişilebilir etkinlik bilgileri - herkesin görebileceği içerikler"
 * )
 * 
 * @OA\Tag(
 *     name="Analytics",
 *     description="İstatistikler ve analitik veriler - raporlar, grafikler"
 * )
 * 
 * @OA\Tag(
 *     name="Search",
 *     description="Arama ve filtreleme işlemleri"
 * )
 * 
 * @OA\Tag(
 *     name="Export/Import",
 *     description="Veri dışa/içe aktarma işlemleri - Excel, CSV, PDF"
 * )
 * 
 * @OA\Tag(
 *     name="Notifications",
 *     description="Bildirim sistemi - email, SMS, push notifications"
 * )
 * 
 * @OA\Tag(
 *     name="Webhooks",
 *     description="Webhook entegrasyonları - ödeme, email, harici sistemler"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}