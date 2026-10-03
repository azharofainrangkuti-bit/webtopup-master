<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { CircleAlert, Loader2, ShieldAlert } from 'lucide-vue-next';
import { computed } from 'vue';
import InputError from '../InputError.vue';

const props = defineProps<{
    inputType: 'id' | 'id+server' | 'login';
    labelId: string;
    labelServer: string;
    serverOptions: string[];
    accountId: string;
    serverId: string;
    formErrors: any;
    resolvedUsername?: string | null;
    checkError?: string | null;
    verificationPending?: boolean;
    isLoadingCheck?: boolean;
    instructions?: string | null;
}>();

const emit = defineEmits<{
    'update:accountId': [value: string];
    'update:serverId': [value: string];
}>();

const hasServerDropdown = computed(
    () => props.inputType === 'id+server' && props.serverOptions.length > 0,
);
const hasSecondaryField = computed(() => props.inputType !== 'id');
const isLoginType = computed(() => props.inputType === 'login');
</script>

<template>
    <section class="rounded-2xl border border-[#0877e8]/10 bg-gradient-to-br from-white via-[#f8fbff] to-white p-5 shadow-md sm:p-6">
        <h2
            class="mb-5 flex items-center gap-3 text-lg font-bold text-[#1d1d1f]"
        >
            <span
                class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-[#0877e8] to-[#0757b7] text-sm font-bold text-white shadow-md"
                >1</span
            >
            Masukkan Data Akun
        </h2>

        <p
            class="mb-6 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-sm leading-5 text-amber-900"
        >
            <CircleAlert class="mt-0.5 h-4 w-4 flex-shrink-0 text-amber-600" />
            Pastikan data akun yang kamu masukkan benar.
        </p>

        <p
            v-if="isLoginType"
            class="mb-6 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 p-3.5 text-sm leading-5 text-red-900"
        >
            <ShieldAlert class="mt-0.5 h-4 w-4 flex-shrink-0 text-red-600" />
            {{ instructions || 'Nonaktifkan verifikasi 2 langkah (2FA/A2F) dan logout dari perangkat lain sebelum melakukan top up, karena akun akan diakses langsung oleh admin/sistem.' }}
        </p>

        <div
            class="grid gap-4"
            :class="hasSecondaryField ? 'sm:grid-cols-2' : ''"
        >
            <!-- ID Input -->
            <div>
                <Label :for="'account-id'">{{ labelId }}</Label>
                <Input
                    id="account-id"
                    :type="isLoginType ? 'email' : 'text'"
                    :model-value="accountId"
                    :placeholder="`Masukkan ${labelId}`"
                    class="mt-2 border-black/10 bg-white shadow-sm focus:border-[#0877e8] focus:ring-[#0877e8]/20"
                    :disabled="isLoadingCheck"
                    @update:model-value="
                        emit('update:accountId', String($event))
                    "
                />
                <InputError :message="formErrors.account_id" class="mt-1" />
            </div>

            <!-- Server Input/Dropdown (conditional) -->
            <div v-if="hasSecondaryField">
                <Label :for="'server-id'">{{ labelServer }}</Label>

                <!-- Dropdown if servers available -->
                <Select
                    v-if="hasServerDropdown"
                    :model-value="serverId"
                    @update:model-value="
                        emit('update:serverId', String($event))
                    "
                >
                    <SelectTrigger
                        id="server-id"
                        class="mt-2 border-black/10 bg-white shadow-sm focus:border-[#0877e8] focus:ring-[#0877e8]/20"
                        :disabled="isLoadingCheck"
                    >
                        <SelectValue :placeholder="`Pilih ${labelServer}`" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="server in serverOptions"
                            :key="server"
                            :value="server"
                        >
                            {{ server }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <!-- Text input if no servers -->
                <Input
                    v-else
                    id="server-id"
                    :type="isLoginType ? 'password' : 'text'"
                    autocomplete="off"
                    :disabled="isLoadingCheck"
                    :model-value="serverId"
                    :placeholder="`Masukkan ${labelServer}`"
                    class="mt-2 border-black/10 bg-white shadow-sm focus:border-[#0877e8] focus:ring-[#0877e8]/20"
                    @update:model-value="
                        emit('update:serverId', String($event))
                    "
                />
                <InputError :message="formErrors.server_id" class="mt-1" />
            </div>
        </div>

        <div
            v-if="isLoadingCheck || resolvedUsername || checkError || verificationPending"
            class="mt-4 flex items-center gap-2 rounded-lg border bg-muted/50 p-3 text-sm"
        >
            <Loader2
                v-if="isLoadingCheck"
                class="h-4 w-4 animate-spin text-muted-foreground"
            />
            <div v-if="resolvedUsername" class="font-medium text-green-600">
                Username: {{ resolvedUsername }}
            </div>
            <div v-else-if="verificationPending" class="font-medium text-amber-600">
                {{ checkError || 'ID diterima. Nickname akan diverifikasi ViPayment saat transaksi diproses.' }}
            </div>
            <div v-else-if="checkError" class="font-medium text-destructive">
                {{ checkError }}
            </div>
            <div v-else-if="isLoadingCheck" class="text-muted-foreground">
                Mengecek ID...
            </div>
        </div>
    </section>
</template>
