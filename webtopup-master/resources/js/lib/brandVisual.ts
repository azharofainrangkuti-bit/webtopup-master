import type { Component } from 'vue';
import {
    Clapperboard,
    CreditCard,
    Facebook,
    Flame,
    Gamepad2,
    Gem,
    Gift,
    Instagram,
    Music,
    Share2,
    Smartphone,
    Swords,
    Ticket,
    Wallet,
    Wifi,
    Youtube,
    Zap,
} from 'lucide-vue-next';

export interface BrandVisual {
    icon: Component;
    gradient: string;
}

/**
 * Keyword to visual mapping, checked in order. Uses generic open-source
 * icons instead of copyrighted game/brand logos.
 */
const KEYWORD_VISUALS: Array<{ pattern: RegExp; icon: Component; gradient: string }> = [
    { pattern: /free ?fire/, icon: Flame, gradient: 'from-orange-500/30 via-red-500/20 to-background' },
    { pattern: /mobile legends|moba|honor of kings|arena of valor/, icon: Swords, gradient: 'from-blue-500/30 via-indigo-500/20 to-background' },
    { pattern: /genshin|honkai|zenless|tower of fantasy|ragnarok/, icon: Gem, gradient: 'from-purple-500/30 via-fuchsia-500/20 to-background' },
    { pattern: /youtube/, icon: Youtube, gradient: 'from-red-500/30 via-rose-500/20 to-background' },
    { pattern: /netflix|disney|vidio|viu|iqiyi|wetv|hbo|prime video|orange tv|rcti|nex parabola|vision plus/, icon: Clapperboard, gradient: 'from-slate-500/30 via-zinc-500/20 to-background' },
    { pattern: /spotify|music|resso/, icon: Music, gradient: 'from-emerald-500/30 via-green-500/20 to-background' },
    { pattern: /instagram/, icon: Instagram, gradient: 'from-pink-500/30 via-rose-500/20 to-background' },
    { pattern: /facebook/, icon: Facebook, gradient: 'from-blue-600/30 via-blue-500/20 to-background' },
    { pattern: /twitter|tiktok|telegram|whatsapp|likee/, icon: Share2, gradient: 'from-sky-500/30 via-cyan-500/20 to-background' },
    { pattern: /voucher|gift card|steam|itunes|razer|roblox|psn|playstation/, icon: Gift, gradient: 'from-amber-500/30 via-yellow-500/20 to-background' },
    { pattern: /pln|listrik|token/, icon: Zap, gradient: 'from-yellow-500/30 via-amber-500/20 to-background' },
    { pattern: /dana|ovo|gopay|shopeepay|linkaja|e-money|ewallet|e-wallet|brizzi|tapcash|e-toll/, icon: Wallet, gradient: 'from-teal-500/30 via-cyan-500/20 to-background' },
    { pattern: /paket internet|wifi|internet/, icon: Wifi, gradient: 'from-indigo-500/30 via-blue-500/20 to-background' },
    { pattern: /pulsa|paket telepon|kartu/, icon: Smartphone, gradient: 'from-violet-500/30 via-purple-500/20 to-background' },
    { pattern: /tiket|ticket|ancol|jungleland/, icon: Ticket, gradient: 'from-lime-500/30 via-green-500/20 to-background' },
    { pattern: /kredit|credit|topup saldo|e-toll/, icon: CreditCard, gradient: 'from-cyan-500/30 via-sky-500/20 to-background' },
];

const DEFAULT_VISUAL: BrandVisual = {
    icon: Gamepad2,
    gradient: 'from-primary/20 via-background to-muted',
};

/**
 * Resolve a copyright-safe icon + gradient for a brand/product name.
 * Falls back to a generic game controller icon when no keyword matches.
 */
export function getBrandVisual(name: string): BrandVisual {
    const normalized = name.toLowerCase();

    for (const { pattern, icon, gradient } of KEYWORD_VISUALS) {
        if (pattern.test(normalized)) {
            return { icon, gradient };
        }
    }

    return DEFAULT_VISUAL;
}
