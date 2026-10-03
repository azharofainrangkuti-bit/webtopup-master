<script setup lang="ts">
import FloatingCsWidget from '@/components/FloatingCsWidget.vue';
import MainFooter from '@/components/MainFooter.vue';
import MainHeader from '@/components/MainHeader.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Code2, KeyRound, Server, ShieldCheck } from 'lucide-vue-next';

const page = usePage();
const setting = page.props.setting;
</script>

<template>
    <Head>
        <title>{{ `Dokumentasi API - ${setting?.title || 'GameStore'}` }}</title>
    </Head>

    <div class="public-shell flex min-h-screen flex-col bg-background">
        <MainHeader />
        <main class="mx-auto w-full max-w-5xl flex-1 px-4 py-8 md:px-6 lg:px-8">
            <section class="mb-8 rounded-[24px] border border-sky-500/20 bg-gradient-to-r from-slate-950 via-slate-900 to-sky-950/40 p-6 text-white shadow-xl md:p-10">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-sky-500/20 text-sky-300">
                        <Code2 class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="mb-2 text-xs font-bold uppercase tracking-widest text-sky-300">Partner & Reseller</p>
                        <h1 class="text-3xl font-black md:text-4xl">Dokumentasi API</h1>
                        <p class="mt-2 max-w-2xl text-sm text-slate-300">Integrasikan katalog top up game {{ setting?.title }} ke aplikasi, panel, atau bot milikmu.</p>
                    </div>
                </div>
            </section>

            <div class="grid gap-4 md:grid-cols-3">
                <article class="rounded-2xl border border-border/60 bg-card p-5 shadow-sm">
                    <Server class="mb-4 h-6 w-6 text-sky-500" />
                    <h2 class="font-bold text-foreground">Base URL</h2>
                    <code class="mt-3 block break-all rounded-xl bg-muted p-3 text-xs text-muted-foreground">{{ page.props.app_url }}/api/v1</code>
                </article>
                <article class="rounded-2xl border border-border/60 bg-card p-5 shadow-sm">
                    <KeyRound class="mb-4 h-6 w-6 text-amber-500" />
                    <h2 class="font-bold text-foreground">Authentication</h2>
                    <p class="mt-3 text-sm text-muted-foreground">Gunakan API key reseller pada header Authorization. Jangan pernah menaruh key di aplikasi client.</p>
                </article>
                <article class="rounded-2xl border border-border/60 bg-card p-5 shadow-sm">
                    <ShieldCheck class="mb-4 h-6 w-6 text-emerald-500" />
                    <h2 class="font-bold text-foreground">Webhook Aman</h2>
                    <p class="mt-3 text-sm text-muted-foreground">Validasi signature callback dan batasi endpoint webhook hanya untuk server partner.</p>
                </article>
            </div>

            <section class="mt-6 rounded-2xl border border-border/60 bg-card p-6 shadow-sm">
                <h2 class="text-xl font-bold text-foreground">Contoh Request</h2>
                <pre class="mt-4 overflow-x-auto rounded-2xl bg-slate-950 p-5 text-xs leading-6 text-sky-200"><code>curl -X GET "{{ page.props.app_url }}/api/v1/products" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Accept: application/json"</code></pre>
                <p class="mt-4 text-sm text-muted-foreground">Hubungi CS untuk mendapatkan kredensial reseller, daftar endpoint aktif, dan batas rate limit akun Anda.</p>
            </section>
        </main>
        <FloatingCsWidget />
        <MainFooter />
    </div>
</template>
