{{-- Helpers & state yang dipakai bersama oleh sub-script lain di scope IIFE yang sama --}}
const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

async function adminApi(url, options = {}) {
    const res = await fetch(url, {
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        ...options,
    });
    const data = await res.json();
    if (!res.ok) throw data;
    return data;
}

function isDark() { return document.documentElement.dataset.theme === 'dark'; }

function chartColors() {
    return {
        text:    isDark() ? '#e9edef' : '#111b21',
        subtext: isDark() ? '#8696a0' : '#667781',
        grid:    isDark() ? '#2a3942' : '#e9edef',
        bg:      isDark() ? '#111b21' : '#ffffff',
    };
}
