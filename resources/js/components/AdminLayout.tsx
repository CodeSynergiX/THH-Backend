import React, { useState, useEffect } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    LayoutDashboard,
    FolderKanban,
    Users,
    Layers,
    ShieldCheck,
    Palette,
    Globe,
    LogOut,
    Home,
    Sun,
    Moon,
    Menu,
    X,
    UserCheck,
    Settings,
    Bell,
    ChevronRight,
    Phone,
    Mail,
    ExternalLink,
} from 'lucide-react';
import { useTranslation } from '../lib/i18n';

interface AdminLayoutProps {
    title: string;
    children: React.ReactNode;
}

interface UserAuth {
    id: number;
    name: string;
    email?: string;
    phone?: string;
    roles: string[];
    scope: string;
}

export default function AdminLayout({ title, children }: AdminLayoutProps) {
    const { t, locale, supportedLocales, switchLocale } = useTranslation();
    const { props, url } = usePage<{
        auth?: { user?: UserAuth | null };
        flash?: { success?: string; error?: string };
        unread_notifications?: number;
    }>();

    const currentUser = props.auth?.user;
    const unreadCount = props.unread_notifications ?? 0;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [isDark, setIsDark] = useState(false);

    useEffect(() => {
        const saved = localStorage.getItem('thh_dark_mode');
        const prefersDark = window.matchMedia(
            '(prefers-color-scheme: dark)',
        ).matches;
        const shouldBeDark = saved !== null ? saved === '1' : prefersDark;
        if (shouldBeDark) {
            document.documentElement.classList.add('dark');
            setIsDark(true);
        }
    }, []);

    const toggleDarkMode = () => {
        if (isDark) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('thh_dark_mode', '0');
            setIsDark(false);
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('thh_dark_mode', '1');
            setIsDark(true);
        }
    };

    const navGroups = [
        {
            label: 'Core',
            items: [
                {
                    href: '/admin/dashboard',
                    label: t('nav.dashboard', 'Dashboard'),
                    icon: LayoutDashboard,
                    active: url.startsWith('/admin/dashboard'),
                },
                {
                    href: '/admin/cases',
                    label: t('nav.cases', 'Applications & Cases'),
                    icon: FolderKanban,
                    active: url.startsWith('/admin/cases'),
                },
                {
                    href: '/admin/people',
                    label: t('nav.people', 'People & Scoping'),
                    icon: Users,
                    active: url.startsWith('/admin/people'),
                },
            ],
        },
        {
            label: 'Content',
            items: [
                {
                    href: '/admin/content',
                    label: t('nav.content', 'Content Modules'),
                    icon: Layers,
                    active: url.startsWith('/admin/content'),
                },
                {
                    href: '/admin/theme',
                    label: t('nav.theme', 'Theme & Appearance'),
                    icon: Palette,
                    active: url.startsWith('/admin/theme'),
                },
                {
                    href: '/admin/localization',
                    label: t('nav.localization', 'Translations (i18n)'),
                    icon: Globe,
                    active: url.startsWith('/admin/localization'),
                },
            ],
        },
        {
            label: 'System',
            items: [
                {
                    href: '/admin/audit',
                    label: t('nav.audit', 'Audit Trail'),
                    icon: ShieldCheck,
                    active: url.startsWith('/admin/audit'),
                },
                {
                    href: '/admin/settings',
                    label: t('nav.settings', 'Settings'),
                    icon: Settings,
                    active: url.startsWith('/admin/settings'),
                },
            ],
        },
    ];

    return (
        <div className="bg-thh-bg text-thh-text flex min-h-screen font-sans transition-colors duration-200">
            <Head title={`${title} — GGVT Portal`} />

            {/* Mobile Sidebar Overlay */}
            {sidebarOpen && (
                <div
                    className="animate-fade-in fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}

            {/* Left Navigation Rail */}
            <aside
                className={`bg-thh-surface border-thh-border fixed top-0 z-50 flex h-screen w-64 shrink-0 flex-col justify-between border-r transition-transform duration-300 ease-out lg:sticky lg:translate-x-0 ${
                    sidebarOpen
                        ? 'translate-x-0 shadow-2xl'
                        : '-translate-x-full'
                }`}
            >
                <div className="flex min-h-0 flex-1 flex-col">
                    {/* Brand Banner */}
                    <div className="border-thh-border flex h-16 items-center justify-between border-b px-5">
                        <Link
                            href="/"
                            className="group flex items-center space-x-3"
                        >
                            <div className="bg-thh-primary shadow-thh-primary/30 flex h-9 w-9 items-center justify-center rounded-xl text-[11px] font-black text-white shadow-md transition-transform duration-200 group-hover:scale-105">
                                THH
                            </div>
                            <div>
                                <h1 className="text-thh-text text-sm leading-tight font-black">
                                    Tribal Helping Hand
                                </h1>
                                <p className="text-thh-text-muted text-[10px]">
                                    GGVT Admin Portal
                                </p>
                            </div>
                        </Link>
                        <button
                            type="button"
                            onClick={() => setSidebarOpen(false)}
                            className="text-thh-text-muted hover:text-thh-text hover:bg-thh-bg rounded-lg p-1 transition-colors lg:hidden"
                        >
                            <X className="h-5 w-5" />
                        </button>
                    </div>

                    {/* Navigation Groups */}
                    <nav className="flex-1 space-y-4 overflow-y-auto px-3 py-3">
                        {navGroups.map((group) => (
                            <div key={group.label}>
                                <p className="text-thh-text-muted mb-1 px-2 text-[10px] font-bold tracking-widest uppercase">
                                    {group.label}
                                </p>
                                <div className="space-y-0.5">
                                    {group.items.map((item) => {
                                        const Icon = item.icon;
                                        return (
                                            <Link
                                                key={item.href}
                                                href={item.href}
                                                onClick={() =>
                                                    setSidebarOpen(false)
                                                }
                                                className={`flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all duration-150 ${
                                                    item.active
                                                        ? 'bg-thh-primary shadow-thh-primary/25 text-white shadow-md'
                                                        : 'text-thh-text-muted hover:text-thh-text hover:bg-thh-bg'
                                                }`}
                                            >
                                                <Icon className="h-4 w-4 shrink-0" />
                                                <span>{item.label}</span>
                                                {item.active && (
                                                    <ChevronRight className="ml-auto h-3 w-3 opacity-70" />
                                                )}
                                            </Link>
                                        );
                                    })}
                                </div>
                            </div>
                        ))}
                    </nav>
                </div>

                {/* Rail Bottom: User Account & Logout */}
                <div className="border-thh-border space-y-3 border-t p-4">
                    {currentUser ? (
                        <div className="flex items-center justify-between">
                            <div className="truncate pr-2">
                                <span className="text-thh-text block truncate text-xs font-bold">
                                    {currentUser.name}
                                </span>
                                <span className="text-thh-text-muted text-[10px] font-semibold uppercase">
                                    {currentUser.roles?.[0] || 'User'} •{' '}
                                    {currentUser.scope}
                                </span>
                            </div>
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                className="text-thh-text-muted rounded-lg p-1.5 transition-colors hover:bg-rose-500/10 hover:text-rose-600"
                                title="Sign Out"
                            >
                                <LogOut className="h-4 w-4" />
                            </Link>
                        </div>
                    ) : (
                        <Link
                            href="/login"
                            className="bg-thh-primary flex w-full items-center justify-center gap-2 rounded-xl py-2 text-xs font-bold text-white shadow-sm"
                        >
                            <UserCheck className="h-4 w-4" />
                            <span>Sign In</span>
                        </Link>
                    )}

                    <div className="text-thh-text-muted flex items-center justify-between pt-1 text-[11px]">
                        <Link
                            href="/"
                            className="hover:text-thh-text flex items-center gap-1 transition-colors"
                        >
                            <ExternalLink className="h-3.5 w-3.5" />
                            <span>Public Portal</span>
                        </Link>
                        <span className="text-[10px]">v1.0.0</span>
                    </div>
                </div>
            </aside>

            {/* Content Area */}
            <div className="flex min-w-0 flex-1 flex-col">
                {/* Top Header */}
                <header className="bg-thh-surface border-thh-border sticky top-0 z-30 flex h-16 items-center justify-between border-b px-4 shadow-sm sm:px-6 lg:px-8">
                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={() => setSidebarOpen(true)}
                            className="border-thh-border text-thh-text hover:bg-thh-bg rounded-lg border p-2 transition-colors lg:hidden"
                        >
                            <Menu className="h-5 w-5" />
                        </button>
                        <div>
                            <h2 className="text-thh-text truncate text-base font-bold tracking-tight">
                                {title}
                            </h2>
                        </div>
                    </div>

                    {/* Header Controls */}
                    <div className="flex items-center gap-2">
                        {/* Language Switcher */}
                        {supportedLocales.length > 1 && (
                            <div className="bg-thh-bg border-thh-border flex items-center rounded-xl border p-0.5 text-xs font-medium">
                                {supportedLocales.map((lang) => (
                                    <button
                                        key={lang.code}
                                        type="button"
                                        onClick={() => switchLocale(lang.code)}
                                        className={`rounded-lg px-2.5 py-1 transition-all ${
                                            locale === lang.code
                                                ? 'bg-thh-surface text-thh-primary font-bold shadow-sm'
                                                : 'text-thh-text-muted hover:text-thh-text'
                                        }`}
                                    >
                                        {lang.native_name || lang.name}
                                    </button>
                                ))}
                            </div>
                        )}

                        {/* Notification Bell */}
                        <Link
                            href="/admin/notifications"
                            className="border-thh-border bg-thh-surface text-thh-text hover:bg-thh-bg relative rounded-xl border p-2 transition-colors"
                            title="Notifications"
                        >
                            <Bell className="h-4 w-4" />
                            {unreadCount > 0 && (
                                <span className="absolute -top-1 -right-1 flex h-4 w-4 animate-pulse items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white">
                                    {unreadCount > 9 ? '9+' : unreadCount}
                                </span>
                            )}
                        </Link>

                        {/* Dark/Light Toggle */}
                        <button
                            type="button"
                            onClick={toggleDarkMode}
                            className="border-thh-border bg-thh-surface text-thh-text hover:bg-thh-bg rounded-xl border p-2 transition-colors"
                            title={
                                isDark
                                    ? 'Switch to Light Mode'
                                    : 'Switch to Dark Mode'
                            }
                        >
                            {isDark ? (
                                <Sun className="h-4 w-4 text-amber-500" />
                            ) : (
                                <Moon className="text-thh-primary h-4 w-4" />
                            )}
                        </button>
                    </div>
                </header>

                {/* Flash Messages */}
                {props.flash?.success && (
                    <div className="animate-slide-down mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="flex items-center gap-2 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                            <ShieldCheck className="h-4 w-4 shrink-0" />
                            <span>{props.flash.success}</span>
                        </div>
                    </div>
                )}

                {props.flash?.error && (
                    <div className="animate-slide-down mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="rounded-xl border border-rose-500/30 bg-rose-500/10 p-3.5 text-xs font-semibold text-rose-700 dark:text-rose-300">
                            {props.flash.error}
                        </div>
                    </div>
                )}

                {/* Page View Body */}
                <main className="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
                    {children}
                </main>

                {/* Footer */}
                <footer className="border-thh-border bg-thh-surface border-t px-4 py-5 sm:px-6 lg:px-8">
                    <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 sm:flex-row">
                        <div className="flex items-center gap-2">
                            <div className="bg-thh-primary flex h-5 w-5 items-center justify-center rounded-md text-[7px] font-black text-white">
                                T
                            </div>
                            <span className="text-thh-text-muted text-[11px]">
                                <span className="text-thh-text font-semibold">
                                    Global Gramin Vikas Trust (GGVT)
                                </span>{' '}
                                &mdash; Tribal Helping Hand &copy;{' '}
                                {new Date().getFullYear()}
                            </span>
                        </div>
                        <div className="text-thh-text-muted flex items-center gap-4 text-[11px]">
                            <a
                                href="mailto:support@ggvt.org"
                                className="hover:text-thh-text flex items-center gap-1 transition-colors"
                            >
                                <Mail className="h-3 w-3" />
                                support@ggvt.org
                            </a>
                            <span className="flex items-center gap-1">
                                <Phone className="h-3 w-3" />
                                Helpline available
                            </span>
                            <Link
                                href="/"
                                className="hover:text-thh-text flex items-center gap-1 transition-colors"
                            >
                                <Home className="h-3 w-3" />
                                Portal
                            </Link>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    );
}
