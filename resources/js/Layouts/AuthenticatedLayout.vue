<template>
    <div class="app-shell" dir="rtl">
        <!-- Overlay for Mobile -->
        <div 
            v-if="isMobileMenuOpen" 
            class="sidebar-overlay"
            @click="isMobileMenuOpen = false"
        ></div>

        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'sidebar--open': isMobileMenuOpen }">
            <div class="sidebar-inner">
                <!-- Logo Section -->
                <div class="sidebar-logo">
                    <div class="logo-group">
                        <div class="logo-icon">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div class="logo-text-group">
                            <span class="logo-main">BATU</span>
                            <span class="logo-sub text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] leading-none">Printing System</span>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="sidebar-nav">
                    <Link 
                        :href="route('dashboard')" 
                        class="nav-item group" 
                        :class="{ 'nav-item--active': route().current('dashboard') }"
                        @click="isMobileMenuOpen = false"
                    >
                        <div class="nav-icon-container">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <span class="nav-label">لوحة التحكم الرئيسية</span>
                    </Link>

                    <template v-if="$page.props.auth.user.role === 'admin'">
                        <div class="nav-divider">الإدارة المركزية</div>
                        
                        <Link 
                            :href="route('admin.academic.index')" 
                            class="nav-item group" 
                            :class="{ 'nav-item--active': route().current('admin.academic.*') }"
                            @click="isMobileMenuOpen = false"
                        >
                            <div class="nav-icon-container">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                            </div>
                            <span class="nav-label">الهيكل الأكاديمي</span>
                        </Link>

                        <Link 
                            :href="route('admin.delegates.index')" 
                            class="nav-item group" 
                            :class="{ 'nav-item--active': route().current('admin.delegates.*') }"
                            @click="isMobileMenuOpen = false"
                        >
                            <div class="nav-icon-container">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <span class="nav-label">شؤون المناديب</span>
                        </Link>

                        <Link 
                            :href="route('admin.inventory.index')" 
                            class="nav-item group" 
                            :class="{ 'nav-item--active': route().current('admin.inventory.*') }"
                            @click="isMobileMenuOpen = false"
                        >
                            <div class="nav-icon-container">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <span class="nav-label">المخزون والعهد</span>
                        </Link>

                        <div class="nav-divider">الرقابة والتقارير</div>

                        <Link 
                            :href="route('admin.reports.delegates-stats')" 
                            class="nav-item group" 
                            :class="{ 'nav-item--active': route().current('admin.reports.delegates-stats') }"
                            @click="isMobileMenuOpen = false"
                        >
                            <div class="nav-icon-container">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <span class="nav-label">مراقبة التسليم الحي</span>
                        </Link>

                        <Link 
                            :href="route('admin.reports.logs')" 
                            class="nav-item group" 
                            :class="{ 'nav-item--active': route().current('admin.reports.logs') }"
                            @click="isMobileMenuOpen = false"
                        >
                            <div class="nav-icon-container">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="nav-label">سجل الرقابة</span>
                        </Link>
                    </template>

                    <template v-if="$page.props.auth.user.role === 'general_delegate'">
                        <div class="nav-divider">المهام الموكلة</div>
                        <Link
                            v-if="$page.props.auth.user.assignments?.[0]"
                            :href="route('admin.academic.batch.show', $page.props.auth.user.assignments[0].batch_id)"
                            class="nav-item group"
                            :class="{ 'nav-item--active': route().current('admin.academic.batch.show') }"
                            @click="isMobileMenuOpen = false"
                        >
                            <div class="nav-icon-container">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <span class="nav-label">لوحة إدارة الطلاب</span>
                        </Link>
                    </template>
                </nav>
            </div>

            <!-- User Footer -->
            <div class="sidebar-footer">
                <Link :href="route('profile.edit')" class="user-card group" @click="isMobileMenuOpen = false">
                    <div class="user-avatar-container">
                        <div class="user-avatar">{{ $page.props.auth.user.name.charAt(0) }}</div>
                        <div class="status-indicator"></div>
                    </div>
                    <div class="user-info">
                        <p class="user-name">{{ $page.props.auth.user.name }}</p>
                        <p class="user-email">تعديل الحساب</p>
                    </div>
                    <div class="user-action-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        </svg>
                    </div>
                </Link>
            </div>
        </aside>

        <!-- Main -->
        <div class="main-area">
            <!-- Topbar -->
            <header class="topbar">
                <div class="topbar-left">
                    <button class="mobile-toggle" @click="isMobileMenuOpen = true">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                        </svg>
                    </button>
                    <div class="topbar-breadcrumb">
                        <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest block leading-none mb-1">النظام الحالي</span>
                        <h1 class="topbar-title"><slot name="header" /></h1>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <Link :href="route('logout')" method="post" as="button" class="logout-btn">
                        تسجيل الخروج
                    </Link>
                </div>
            </header>

            <main class="page-content animate-page-entry">
                <!-- Flash Message -->
                <transition
                    enter-active-class="flash-enter-active"
                    enter-from-class="flash-enter-from"
                    enter-to-class="flash-enter-to"
                    leave-active-class="flash-leave-active"
                    leave-from-class="flash-leave-from"
                    leave-to-class="flash-leave-to"
                >
                    <div
                        v-if="$page.props.flash.success || $page.props.flash.error"
                        class="flash-msg"
                        :class="$page.props.flash.success ? 'flash-msg--success' : 'flash-msg--error'"
                    >
                        <div class="flash-icon">
                            <svg v-if="$page.props.flash.success" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <p class="flash-text">{{ $page.props.flash.success || $page.props.flash.error }}</p>
                    </div>
                </transition>

                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const isMobileMenuOpen = ref(false);
</script>

<style scoped>
.app-shell {
    display: flex;
    min-height: 100vh;
    background: #0f172a;
    font-family: 'Cairo', sans-serif;
    overflow-x: hidden;
}

/* ── Mobile Elements ── */
.sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 45;
    animation: fadeInOverlay 0.3s ease;
}
@keyframes fadeInOverlay { from { opacity: 0; } to { opacity: 1; } }

.mobile-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    color: #fff;
    margin-left: 1rem;
    cursor: pointer;
}
@media (min-width: 1024px) { .mobile-toggle { display: none; } }

/* ── Sidebar ── */
.sidebar {
    width: 290px;
    background: #0f172a;
    border-left: 1px solid rgba(255,255,255,0.03);
    flex-shrink: 0;
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    height: 100vh;
    display: flex;
    flex-direction: column;
    z-index: 50;
    transform: translateX(100%);
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar--open { transform: translateX(0); }

@media (min-width: 1024px) { 
    .sidebar { position: sticky; transform: translateX(0); }
    .sidebar-overlay { display: none; }
}

.sidebar-inner { 
    padding: 2.5rem 1.25rem; 
    flex: 1; 
    overflow-y: auto; 
    scrollbar-width: none; 
}
.sidebar-inner::-webkit-scrollbar { display: none; }

.sidebar-logo {
    margin-bottom: 3.5rem;
    padding: 0 0.5rem;
}

.logo-group {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.logo-icon {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
    flex-shrink: 0;
}

.logo-text-group {
    display: flex;
    flex-direction: column;
}

.logo-main {
    font-size: 1.5rem;
    font-weight: 900;
    color: #fff;
    letter-spacing: -0.04em;
    line-height: 1;
}

.sidebar-nav { display: flex; flex-direction: column; gap: 0.4rem; }

.nav-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 0.85rem;
    border-radius: 16px;
    color: #64748b;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.nav-item:hover {
    color: #f1f5f9;
    background: rgba(255, 255, 255, 0.03);
}

.nav-item--active {
    color: #fff;
    background: rgba(99, 102, 241, 0.08);
}

.nav-item--active::after {
    content: '';
    position: absolute;
    right: 0;
    top: 25%;
    height: 50%;
    width: 3px;
    background: #6366f1;
    border-radius: 4px 0 0 4px;
    box-shadow: -2px 0 10px rgba(99, 102, 241, 0.5);
}

.nav-icon-container {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.02);
    margin-left: 0.85rem;
    transition: all 0.3s ease;
}

.nav-item:hover .nav-icon-container {
    background: rgba(255, 255, 255, 0.05);
    transform: scale(1.05);
}

.nav-item--active .nav-icon-container {
    background: rgba(99, 102, 241, 0.15);
    color: #818cf8;
}

.nav-icon { width: 20px; height: 20px; }

.nav-label {
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: -0.01em;
}

.nav-divider {
    font-size: 0.65rem;
    font-weight: 900;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 0.2em;
    padding: 2rem 1rem 0.75rem;
}

/* ── Sidebar Footer ── */
.sidebar-footer {
    padding: 1.5rem 1.25rem;
    border-top: 1px solid rgba(255,255,255,0.03);
}

.user-card {
    display: flex; align-items: center; gap: 0.85rem;
    padding: 0.85rem;
    border-radius: 20px;
    background: rgba(30, 41, 59, 0.4);
    border: 1px solid rgba(255,255,255,0.03);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
}

.user-card:hover {
    background: rgba(30, 41, 59, 0.8);
    border-color: rgba(99, 102, 241, 0.2);
    transform: translateY(-2px);
}

.user-avatar-container {
    position: relative;
    flex-shrink: 0;
}

.user-avatar {
    width: 42px; height: 42px;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 900; color: #fff; font-size: 1rem;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
}

.status-indicator {
    position: absolute;
    bottom: -2px;
    left: -2px;
    width: 12px;
    height: 12px;
    background: #10b981;
    border: 2px solid #0f172a;
    border-radius: 50%;
}

.user-info { overflow: hidden; flex: 1; }
.user-name  { font-size: 0.85rem; font-weight: 800; color: #f8fafc; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-email { font-size: 0.7rem; font-weight: 600; color: #64748b; margin: 0; }

.user-action-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    color: #475569;
    transition: all 0.3s ease;
}

.user-card:hover .user-action-icon {
    color: #818cf8;
    background: rgba(99, 102, 241, 0.1);
}

/* ── Main Area ── */
.main-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    min-width: 0;
}

/* ── Topbar ── */
.topbar {
    height: 72px;
    background: rgba(15,23,42,0.7);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255,255,255,0.03);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.5rem;
    position: sticky;
    top: 0;
    z-index: 40;
}
@media (min-width: 1024px) { .topbar { padding: 0 3rem; } }

.topbar-left { display: flex; align-items: center; }
.topbar-title { font-size: 1.1rem; font-weight: 900; color: #fff; margin: 0; letter-spacing: -0.01em; }

.logout-btn {
    padding: 0.6rem 1.25rem;
    font-size: 0.8rem;
    font-weight: 800;
    color: #f87171;
    background: rgba(239,68,68,0.05);
    border: 1px solid rgba(239,68,68,0.1);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.logout-btn:hover {
    background: #ef4444;
    color: #fff;
    box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
    transform: translateY(-1px);
}

/* ── Page Content ── */
.page-content {
    flex: 1;
    padding: 1.5rem;
    max-width: 1600px;
    width: 100%;
    margin: 0 auto;
}
@media (min-width: 1024px) { .page-content { padding: 3.5rem; } }

.animate-page-entry {
    animation: pageEntry 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes pageEntry {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.topbar-breadcrumb {
    display: flex;
    flex-direction: column;
}

/* ── Flash ── */
.flash-msg {
    position: fixed;
    top: 92px; left: 1.5rem; right: 1.5rem;
    z-index: 100;
    max-width: 420px;
    padding: 1rem 1.5rem;
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    display: flex; align-items: center; gap: 1rem;
    backdrop-filter: blur(12px);
    animation: flashSlideIn 0.4s cubic-bezier(0.22, 1, 0.36, 1);
}

@keyframes flashSlideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@media (min-width: 1024px) { .flash-msg { left: auto; right: 3rem; } }

.flash-msg--success { background: rgba(16, 185, 129, 0.95); border: 1px solid rgba(255,255,255,0.2); }
.flash-msg--error   { background: rgba(239, 68, 68, 0.95); border: 1px solid rgba(255,255,255,0.2); }
.flash-icon {
    width: 32px; height: 32px; border-radius: 10px;
    background: rgba(255,255,255,0.2);
    display: flex; align-items: center; justify-content: center;
    color: #fff; flex-shrink: 0;
}
.flash-text { font-size: 0.9rem; font-weight: 800; color: #fff; margin: 0; flex: 1; }
</style>
