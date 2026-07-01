<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Organizations",
 *     description="Organizasyon yönetimi endpoints"
 * )
 */
class OrganizationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/organizations",
     *     tags={"Organizations"},
     *     summary="Organizasyonları listele",
     *     description="Mevcut organizasyonları sayfalama, filtreleme ve arama ile birlikte listeler",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Arama terimi (isim, açıklama, email)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Durum filtresi",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive"})
     *     ),
     *     @OA\Parameter(
     *         name="user_role",
     *         in="query",
     *         description="Kullanıcı rolü filtresi",
     *         required=false,
     *         @OA\Schema(type="string", enum={"admin", "editor", "viewer"})
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sıralama alanı",
     *         required=false,
     *         @OA\Schema(type="string", enum={"name", "created_at", "events_count", "participants_count", "sponsors_count"}, default="name")
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sıralama yönü",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="asc")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Sayfa başına öğe sayısı (max: 100)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/OrganizationSummary")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="to", type="integer")
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string"),
     *                 @OA\Property(property="last", type="string"),
     *                 @OA\Property(property="prev", type="string", nullable=true),
     *                 @OA\Property(property="next", type="string", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Sunucu hatası",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        // Method implementation...
    }

    /**
     * @OA\Post(
     *     path="/api/v1/organizations",
     *     tags={"Organizations"},
     *     summary="Yeni organizasyon oluştur",
     *     description="Yeni bir organizasyon oluşturur ve mevcut kullanıcıyı admin olarak ekler",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", maxLength=255, description="Organizasyon adı", example="Akademik Kongreler Derneği"),
     *                 @OA\Property(property="description", type="string", maxLength=5000, description="Organizasyon açıklaması", nullable=true),
     *                 @OA\Property(property="contact_email", type="string", format="email", maxLength=255, description="İletişim email", nullable=true),
     *                 @OA\Property(property="contact_phone", type="string", maxLength=20, description="İletişim telefonu", nullable=true),
     *                 @OA\Property(property="website_url", type="string", format="url", maxLength=255, description="Website URL", nullable=true),
     *                 @OA\Property(property="logo", type="string", format="binary", description="Logo dosyası (jpeg,png,jpg,gif - max: 2MB)", nullable=true),
     *                 @OA\Property(property="is_active", type="boolean", description="Aktif durumu", default=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Organizasyon başarıyla oluşturuldu",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Organizasyon başarıyla oluşturuldu."),
     *             @OA\Property(property="data", ref="#/components/schemas/OrganizationDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validasyon hatası",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Sunucu hatası",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        // Method implementation...
    }

    /**
     * @OA\Get(
     *     path="/api/v1/organizations/{organization}",
     *     tags={"Organizations"},
     *     summary="Organizasyon detaylarını görüntüle",
     *     description="Belirtilen organizasyonun detaylı bilgilerini döner",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="organization",
     *         in="path",
     *         description="Organizasyon ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/OrganizationDetailWithRelations")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Yetki hatası",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Organizasyon bulunamadı",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Sunucu hatası",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show(Organization $organization): JsonResponse
    {
        // Method implementation...
    }

    /**
     * @OA\Put(
     *     path="/api/v1/organizations/{organization}",
     *     tags={"Organizations"},
     *     summary="Organizasyonu güncelle",
     *     description="Mevcut organizasyonun bilgilerini günceller",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="organization",
     *         in="path",
     *         description="Organizasyon ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", maxLength=255, description="Organizasyon adı"),
     *                 @OA\Property(property="description", type="string", maxLength=5000, description="Organizasyon açıklaması", nullable=true),
     *                 @OA\Property(property="contact_email", type="string", format="email", maxLength=255, description="İletişim email", nullable=true),
     *                 @OA\Property(property="contact_phone", type="string", maxLength=20, description="İletişim telefonu", nullable=true),
     *                 @OA\Property(property="website_url", type="string", format="url", maxLength=255, description="Website URL", nullable=true),
     *                 @OA\Property(property="logo", type="string", format="binary", description="Logo dosyası (jpeg,png,jpg,gif - max: 2MB)", nullable=true),
     *                 @OA\Property(property="remove_logo", type="boolean", description="Mevcut logoyu kaldır", default=false),
     *                 @OA\Property(property="is_active", type="boolean", description="Aktif durumu")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Organizasyon başarıyla güncellendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Organizasyon başarıyla güncellendi."),
     *             @OA\Property(property="data", ref="#/components/schemas/OrganizationDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Yetki hatası",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validasyon hatası",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Sunucu hatası",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(Request $request, Organization $organization): JsonResponse
    {
        // Method implementation...
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/organizations/{organization}",
     *     tags={"Organizations"},
     *     summary="Organizasyonu sil",
     *     description="Organizasyonu soft delete ile siler. Aktif etkinlikleri olan organizasyonlar silinemez.",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="organization",
     *         in="path",
     *         description="Organizasyon ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Organizasyon başarıyla silindi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Organizasyon başarıyla silindi.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Yetki hatası",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Aktif etkinlikleri olan organizasyon silinemez",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Bu organizasyonun aktif etkinlikleri bulunduğu için silinemez.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Sunucu hatası",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy(Organization $organization): JsonResponse
    {
        // Method implementation...
    }

    /**
     * @OA\Post(
     *     path="/api/v1/organizations/{organization}/users",
     *     tags={"Organizations"},
     *     summary="Organizasyona kullanıcı ekle",
     *     description="Belirtilen kullanıcıyı organizasyona belirtilen rolle ekler",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="organization",
     *         in="path",
     *         description="Organizasyon ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", description="Kullanıcı ID"),
     *             @OA\Property(property="role", type="string", enum={"admin", "editor", "viewer"}, description="Kullanıcı rolü")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kullanıcı başarıyla eklendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kullanıcı başarıyla organizasyona eklendi."),
     *             @OA\Property(property="data", ref="#/components/schemas/OrganizationUser")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Yetki hatası",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Kullanıcı zaten organizasyona ekli veya validasyon hatası",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function attachUser(Request $request, Organization $organization): JsonResponse
    {
        // Method implementation...
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/organizations/{organization}/users/{user}",
     *     tags={"Organizations"},
     *     summary="Organizasyondan kullanıcı çıkar",
     *     description="Belirtilen kullanıcıyı organizasyondan çıkarır. Son admin kullanıcı çıkarılamaz.",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="organization",
     *         in="path",
     *         description="Organizasyon ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="Kullanıcı ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kullanıcı başarıyla çıkarıldı",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kullanıcı başarıyla organizasyondan çıkarıldı.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Yetki hatası",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kullanıcı organizasyonda bulunamadı",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Son admin kullanıcı çıkarılamaz",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Son yönetici kullanıcı çıkarılamaz.")
     *         )
     *     )
     * )
     */
    public function detachUser(Organization $organization, User $user): JsonResponse
    {
        // Method implementation...
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/organizations/{organization}/users/{user}/role",
     *     tags={"Organizations"},
     *     summary="Kullanıcı rolünü güncelle",
     *     description="Organizasyondaki kullanıcının rolünü günceller. Son admin kullanıcının rolü değiştirilemez.",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="organization",
     *         in="path",
     *         description="Organizasyon ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="Kullanıcı ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="role", type="string", enum={"admin", "editor", "viewer"}, description="Yeni kullanıcı rolü")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kullanıcı rolü başarıyla güncellendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kullanıcı rolü başarıyla güncellendi."),
     *             @OA\Property(property="data", ref="#/components/schemas/OrganizationUser")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Yetki hatası",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kullanıcı organizasyonda bulunamadı",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Son admin kullanıcının rolü değiştirilemez",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Son yönetici kullanıcının rolü değiştirilemez.")
     *         )
     *     )
     * )
     */
    public function updateUserRole(Request $request, Organization $organization, User $user): JsonResponse
    {
        // Method implementation...
    }

    /**
     * @OA\Get(
     *     path="/api/v1/organizations/{organization}/available-users",
     *     tags={"Organizations"},
     *     summary="Organizasyona eklenebilir kullanıcıları listele",
     *     description="Henüz organizasyona eklenmemiş aktif kullanıcıları listeler",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="organization",
     *         in="path",
     *         description="Organizasyon ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/UserSummary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Yetki hatası",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function getAvailableUsers(Organization $organization): JsonResponse
    {
        // Method implementation...
    }

    /**
     * @OA\Get(
     *     path="/api/v1/organizations/{organization}/export",
     *     tags={"Organizations"},
     *     summary="Organizasyon verilerini dışa aktar",
     *     description="Organizasyonun tüm verilerini JSON formatında dışa aktarır",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="organization",
     *         in="path",
     *         description="Organizasyon ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/OrganizationExport"),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="format", type="string", example="json"),
     *                 @OA\Property(property="exported_at", type="string", format="date-time"),
     *                 @OA\Property(property="organization_name", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Yetki hatası",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function export(Organization $organization): JsonResponse
    {
        // Method implementation...
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/organizations/{organization}/toggle-status",
     *     tags={"Organizations"},
     *     summary="Organizasyon durumunu değiştir",
     *     description="Organizasyonun aktif/pasif durumunu tersine çevirir",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="organization",
     *         in="path",
     *         description="Organizasyon ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Durum başarıyla değiştirildi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Organizasyon aktif hale getirildi."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="is_active", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Yetki hatası",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function toggleStatus(Organization $organization): JsonResponse
    {
        // Method implementation...
    }

    /**
     * @OA\Post(
     *     path="/api/v1/organizations/{organization}/duplicate",
     *     tags={"Organizations"},
     *     summary="Organizasyonu kopyala",
     *     description="Mevcut organizasyonu kopyalar (etkinlikler hariç)",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="organization",
     *         in="path",
     *         description="Organizasyon ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Organizasyon başarıyla kopyalandı",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Organizasyon başarıyla kopyalandı."),
     *             @OA\Property(property="data", ref="#/components/schemas/OrganizationSummary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Yetki hatası",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function duplicate(Organization $organization): JsonResponse
    {
        // Method implementation...
    }
}

/**
 * @OA\Schema(
 *     schema="OrganizationSummary",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Akademik Kongreler Derneği"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="logo_url", type="string", nullable=true),
 *     @OA\Property(property="contact_email", type="string", nullable=true),
 *     @OA\Property(property="contact_phone", type="string", nullable=true),
 *     @OA\Property(property="website_url", type="string", nullable=true),
 *     @OA\Property(property="is_active", type="boolean"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="statistics",
 *         type="object",
 *         @OA\Property(property="events_count", type="integer"),
 *         @OA\Property(property="participants_count", type="integer"),
 *         @OA\Property(property="sponsors_count", type="integer"),
 *         @OA\Property(property="users_count", type="integer")
 *     ),
 *     @OA\Property(property="user_role", type="string", nullable=true, enum={"admin", "editor", "viewer"}),
 *     @OA\Property(
 *         property="permissions",
 *         type="object",
 *         @OA\Property(property="can_edit", type="boolean"),
 *         @OA\Property(property="can_delete", type="boolean"),
 *         @OA\Property(property="can_manage_users", type="boolean")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="OrganizationDetail",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/OrganizationSummary")
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="OrganizationDetailWithRelations",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/OrganizationDetail"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(
 *                 property="users",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/OrganizationUser")
 *             ),
 *             @OA\Property(
 *                 property="recent_events",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/EventSummary")
 *             ),
 *             @OA\Property(
 *                 property="recent_participants",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/ParticipantSummary")
 *             ),
 *             @OA\Property(
 *                 property="sponsors",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/SponsorSummary")
 *             )
 *         )
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="OrganizationUser",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="role", type="string", enum={"admin", "editor", "viewer"}),
 *     @OA\Property(property="joined_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="UserSummary",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string")
 * )
 */

/**
 * @OA\Schema(
 *     schema="OrganizationExport",
 *     type="object",
 *     @OA\Property(
 *         property="organization",
 *         type="object",
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="description", type="string"),
 *         @OA\Property(property="contact_email", type="string"),
 *         @OA\Property(property="contact_phone", type="string"),
 *         @OA\Property(property="website_url", type="string"),
 *         @OA\Property(property="exported_at", type="string", format="date-time")
 *     ),
 *     @OA\Property(
 *         property="statistics",
 *         type="object",
 *         @OA\Property(property="total_events", type="integer"),
 *         @OA\Property(property="total_participants", type="integer"),
 *         @OA\Property(property="total_sponsors", type="integer"),
 *         @OA\Property(property="total_users", type="integer")
 *     ),
 *     @OA\Property(
 *         property="events",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="start_date", type="string", format="date"),
 *             @OA\Property(property="end_date", type="string", format="date"),
 *             @OA\Property(property="total_days", type="integer"),
 *             @OA\Property(property="total_venues", type="integer"),
 *             @OA\Property(property="total_sessions", type="integer"),
 *             @OA\Property(property="total_presentations", type="integer")
 *         )
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="Error",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string"),
 *     @OA\Property(property="error", type="string", nullable=true)
 * )
 */

/**
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Validasyon hatası."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         additionalProperties={
 *             "type": "array",
 *             "items": {"type": "string"}
 *         }
 *     )
 * )
 */

/**
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token"
 * )
 */

// ==================== PARTICIPANT SCHEMAS ====================

/**
 * @OA\Schema(
 *     schema="ParticipantSummary",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="first_name", type="string", example="Ahmet"),
 *     @OA\Property(property="last_name", type="string", example="Yılmaz"),
 *     @OA\Property(property="full_name", type="string", example="Ahmet Yılmaz"),
 *     @OA\Property(property="title", type="string", nullable=true, example="Prof. Dr."),
 *     @OA\Property(property="email", type="string", example="ahmet.yilmaz@example.com"),
 *     @OA\Property(property="phone", type="string", nullable=true),
 *     @OA\Property(property="affiliation", type="string", nullable=true, example="Ankara Üniversitesi"),
 *     @OA\Property(property="is_speaker", type="boolean"),
 *     @OA\Property(property="is_moderator", type="boolean"),
 *     @OA\Property(property="website", type="string", nullable=true),
 *     @OA\Property(property="photo_url", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="total_participations", type="integer", description="Toplam katılım sayısı"),
 *     @OA\Property(
 *         property="role_badge",
 *         type="array",
 *         @OA\Items(type="string"),
 *         description="Roller",
 *         example={"Konuşmacı", "Moderatör"}
 *     ),
 *     @OA\Property(property="presentations_count", type="integer", description="Sunum sayısı"),
 *     @OA\Property(property="moderated_sessions_count", type="integer", description="Modere ettiği oturum sayısı"),
 *     @OA\Property(property="organization", ref="#/components/schemas/OrganizationSummary")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ParticipantDetail",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ParticipantSummary"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="bio", type="string", nullable=true, description="Biyografi"),
 *             @OA\Property(property="linkedin", type="string", nullable=true, description="LinkedIn profili"),
 *             @OA\Property(property="twitter", type="string", nullable=true, description="Twitter kullanıcı adı"),
 *             @OA\Property(property="orcid", type="string", nullable=true, description="ORCID ID")
 *         )
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="ParticipantDetailWithStats",
 *     type="object",
 *     @OA\Property(property="participant", ref="#/components/schemas/ParticipantDetail"),
 *     @OA\Property(
 *         property="statistics",
 *         type="object",
 *         @OA\Property(property="total_sessions_moderated", type="integer"),
 *         @OA\Property(property="total_presentations", type="integer"),
 *         @OA\Property(property="primary_presentations", type="integer"),
 *         @OA\Property(property="co_speaker_presentations", type="integer"),
 *         @OA\Property(property="discussant_presentations", type="integer"),
 *         @OA\Property(property="total_participations", type="integer")
 *     ),
 *     @OA\Property(
 *         property="participations_by_event",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ParticipantEventParticipation")
 *     ),
 *     @OA\Property(
 *         property="recent_activities",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ParticipantActivity")
 *     ),
 *     @OA\Property(
 *         property="permissions",
 *         type="object",
 *         @OA\Property(property="can_edit", type="boolean"),
 *         @OA\Property(property="can_delete", type="boolean")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="ParticipantSearchResult",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string", example="Ahmet Yılmaz"),
 *     @OA\Property(property="title", type="string", nullable=true, example="Prof. Dr."),
 *     @OA\Property(property="affiliation", type="string", nullable=true, example="Ankara Üniversitesi"),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(
 *         property="roles",
 *         type="object",
 *         @OA\Property(property="speaker", type="boolean"),
 *         @OA\Property(property="moderator", type="boolean")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="ParticipantStatistics",
 *     type="object",
 *     @OA\Property(property="total_participants", type="integer"),
 *     @OA\Property(property="speakers", type="integer"),
 *     @OA\Property(property="moderators", type="integer"),
 *     @OA\Property(property="both_roles", type="integer"),
 *     @OA\Property(property="no_role", type="integer"),
 *     @OA\Property(
 *         property="by_organization",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="organization", type="string"),
 *             @OA\Property(property="total", type="integer"),
 *             @OA\Property(property="speakers", type="integer"),
 *             @OA\Property(property="moderators", type="integer")
 *         )
 *     ),
 *     @OA\Property(
 *         property="top_affiliations",
 *         type="object",
 *         additionalProperties={"type": "integer"}
 *     ),
 *     @OA\Property(
 *         property="recent_additions",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="created_at", type="string", format="date-time")
 *         )
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="ParticipantEventParticipation",
 *     type="object",
 *     @OA\Property(property="event_id", type="integer"),
 *     @OA\Property(property="event_name", type="string"),
 *     @OA\Property(
 *         property="participations",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ParticipantActivity")
 *     ),
 *     @OA\Property(property="total_count", type="integer"),
 *     @OA\Property(property="moderator_count", type="integer"),
 *     @OA\Property(property="speaker_count", type="integer")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ParticipantActivity",
 *     type="object",
 *     @OA\Property(property="event_id", type="integer"),
 *     @OA\Property(property="event_name", type="string"),
 *     @OA\Property(property="type", type="string", enum={"moderator", "speaker"}),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="date", type="string", format="date"),
 *     @OA\Property(property="venue", type="string"),
 *     @OA\Property(property="time", type="string", nullable=true),
 *     @OA\Property(property="speaker_role", type="string", nullable=true, enum={"primary", "secondary", "discussant"})
 * )
 */

/**
 * @OA\Schema(
 *     schema="OrganizationOption",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string")
 * )
 */

/**
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *     @OA\Property(property="current_page", type="integer"),
 *     @OA\Property(property="per_page", type="integer"),
 *     @OA\Property(property="total", type="integer"),
 *     @OA\Property(property="last_page", type="integer"),
 *     @OA\Property(property="from", type="integer"),
 *     @OA\Property(property="to", type="integer")
 * )
 */

/**
 * @OA\Schema(
 *     schema="PaginationLinks",
 *     type="object",
 *     @OA\Property(property="first", type="string"),
 *     @OA\Property(property="last", type="string"),
 *     @OA\Property(property="prev", type="string", nullable=true),
 *     @OA\Property(property="next", type="string", nullable=true)
 * )
 */