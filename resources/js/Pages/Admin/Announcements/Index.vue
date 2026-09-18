<template>
  <AdminLayout
    :page-title="`${event.name} - Duyurular`"
    page-subtitle="Mobil uygulamaya duyuru yayınlayın ve isteğe bağlı push gönderin"
    :breadcrumbs="breadcrumbs"
  >
    <Head :title="`${event.name} - Duyurular`" />

    <div class="w-full space-y-6">
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4">
          <div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Duyurular</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              Kayıtlı cihaz: {{ device_count }}
            </p>
          </div>
          <div class="flex items-center gap-3">
            <Link
              :href="route('admin.events.show', event.slug)"
              class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700"
            >
              Etkinlik detayı
            </Link>
            <Link
              v-if="can_create"
              :href="route('admin.events.announcements.create', event.slug)"
              class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700"
            >
              Yeni duyuru
            </Link>
          </div>
        </div>

        <div v-if="announcements.data.length === 0" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400">
          Henüz duyuru yok.
        </div>

        <ul v-else class="divide-y divide-slate-200 dark:divide-slate-700">
          <li v-for="item in announcements.data" :key="item.id" class="px-6 py-4">
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <h4 class="font-semibold text-slate-900 dark:text-white">{{ item.title }}</h4>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300 whitespace-pre-wrap">{{ item.body }}</p>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                  {{ formatDate(item.published_at) }}
                  <span v-if="item.program_session"> · {{ item.program_session.title }}</span>
                  <span v-if="item.push_sent_at"> · Push gönderildi</span>
                  <span v-else> · Push gönderilmedi</span>
                </p>
              </div>
              <button
                v-if="can_create"
                type="button"
                class="text-sm text-red-600 hover:text-red-700"
                @click="destroy(item.id)"
              >
                Sil
              </button>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  event: { type: Object, required: true },
  announcements: { type: Object, required: true },
  can_create: { type: Boolean, default: false },
  device_count: { type: Number, default: 0 },
})

const breadcrumbs = computed(() => [
  { label: 'Etkinlikler', href: route('admin.events.index') },
  { label: props.event.name, href: route('admin.events.show', props.event.slug) },
  { label: 'Duyurular', href: null },
])

const formatDate = (value) => {
  if (!value) return '-'
  return new Date(value).toLocaleString('tr-TR')
}

const destroy = (id) => {
  if (!confirm('Bu duyuruyu silmek istiyor musunuz?')) return
  router.delete(route('admin.events.announcements.destroy', [props.event.slug, id]))
}
</script>
