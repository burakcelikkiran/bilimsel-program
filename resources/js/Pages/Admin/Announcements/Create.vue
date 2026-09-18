<template>
  <AdminLayout
    page-title="Yeni duyuru"
    :page-subtitle="`${event.name} için duyuru oluşturun`"
    :breadcrumbs="breadcrumbs"
  >
    <Head title="Yeni duyuru" />

    <div class="w-full max-w-3xl">
      <form
        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 space-y-6"
        @submit.prevent="submit"
      >
        <div>
          <label for="title" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Başlık *</label>
          <input
            id="title"
            v-model="form.title"
            type="text"
            required
            class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
          />
          <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
        </div>

        <div>
          <label for="body" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Mesaj *</label>
          <textarea
            id="body"
            v-model="form.body"
            rows="6"
            required
            class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
          />
          <p v-if="form.errors.body" class="mt-1 text-sm text-red-600">{{ form.errors.body }}</p>
        </div>

        <div>
          <label for="program_session_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">İlgili oturum (isteğe bağlı)</label>
          <select
            id="program_session_id"
            v-model="form.program_session_id"
            class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
          >
            <option value="">Yok</option>
            <option v-for="session in sessions" :key="session.id" :value="session.id">
              {{ session.title }}
            </option>
          </select>
        </div>

        <label class="flex items-start gap-3">
          <input v-model="form.send_push" type="checkbox" class="mt-1 rounded border-slate-300" />
          <span class="text-sm text-slate-700 dark:text-slate-300">
            Cihazlara push bildirimi gönder
            <span class="block text-slate-500">Kayıtlı {{ device_count }} cihaza FCM mesajı kuyruğa alınır.</span>
          </span>
        </label>

        <div class="flex items-center justify-end gap-3">
          <Link
            :href="route('admin.events.announcements.index', event.slug)"
            class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300"
          >
            İptal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50"
          >
            Yayınla
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  event: { type: Object, required: true },
  sessions: { type: Array, default: () => [] },
  device_count: { type: Number, default: 0 },
})

const form = useForm({
  title: '',
  body: '',
  program_session_id: '',
  send_push: true,
})

const breadcrumbs = computed(() => [
  { label: 'Etkinlikler', href: route('admin.events.index') },
  { label: props.event.name, href: route('admin.events.show', props.event.slug) },
  { label: 'Duyurular', href: route('admin.events.announcements.index', props.event.slug) },
  { label: 'Yeni duyuru', href: null },
])

const submit = () => {
  form.post(route('admin.events.announcements.store', props.event.slug))
}
</script>
