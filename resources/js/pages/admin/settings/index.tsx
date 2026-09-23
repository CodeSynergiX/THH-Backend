import React, { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import {
    Settings,
    Mail,
    Bell,
    Server,
    Send,
    Shield,
    ToggleLeft,
    ToggleRight,
    Eye,
    EyeOff,
    CheckCircle2,
    Info,
    Globe,
} from 'lucide-react';
import AdminLayout from '../../../components/AdminLayout';

interface NotificationTemplate {
    id: number;
    event_key: string;
    channel: string;
    is_enabled: boolean;
    title_template: { gu: string; en: string };
    body_template: { gu: string; en: string };
}

interface SettingsPageProps {
    settings: Record<string, string | boolean | number | null>;
    notification_templates: NotificationTemplate[];
}

type SettingsTab = 'general' | 'smtp' | 'notifications';

function TabButton({
    active,
    onClick,
    icon: Icon,
    label,
}: {
    active: boolean;
    onClick: () => void;
    icon: React.ElementType;
    label: string;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all duration-200 ${
                active
                    ? 'bg-thh-primary shadow-thh-primary/30 text-white shadow-md'
                    : 'text-stone-500 hover:bg-stone-100 dark:text-stone-400 dark:hover:bg-stone-800'
            }`}
        >
            <Icon size={16} />
            {label}
        </button>
    );
}

function SectionCard({
    title,
    description,
    icon: Icon,
    children,
}: {
    title: string;
    description?: string;
    icon: React.ElementType;
    children: React.ReactNode;
}) {
    return (
        <div className="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm dark:border-stone-700 dark:bg-stone-900">
            <div className="flex items-center gap-3 border-b border-stone-100 px-6 py-4 dark:border-stone-800">
                <div className="bg-thh-primary/10 rounded-lg p-2">
                    <Icon className="text-thh-primary" size={18} />
                </div>
                <div>
                    <h3 className="text-sm font-semibold text-stone-800 dark:text-stone-100">
                        {title}
                    </h3>
                    {description && (
                        <p className="mt-0.5 text-xs text-stone-500 dark:text-stone-400">
                            {description}
                        </p>
                    )}
                </div>
            </div>
            <div className="space-y-4 p-6">{children}</div>
        </div>
    );
}

function FormField({
    label,
    id,
    type = 'text',
    value,
    onChange,
    placeholder,
    required,
    error,
    hint,
    suffix,
}: {
    label: string;
    id: string;
    type?: string;
    value: string;
    onChange: (v: string) => void;
    placeholder?: string;
    required?: boolean;
    error?: string;
    hint?: string;
    suffix?: React.ReactNode;
}) {
    const [showPw, setShowPw] = useState(false);
    const resolvedType =
        type === 'password' ? (showPw ? 'text' : 'password') : type;

    return (
        <div className="space-y-1">
            <label
                htmlFor={id}
                className="block text-sm font-medium text-stone-700 dark:text-stone-300"
            >
                {label}
                {required && <span className="ml-1 text-rose-500">*</span>}
            </label>
            <div className="relative">
                <input
                    id={id}
                    type={resolvedType}
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    placeholder={placeholder}
                    className={`focus:ring-thh-primary/50 focus:border-thh-primary w-full rounded-xl border bg-stone-50 px-3 py-2.5 text-sm text-stone-900 transition-all placeholder:text-stone-400 focus:ring-2 focus:outline-none dark:bg-stone-800 dark:text-stone-100 ${error ? 'border-rose-400' : 'border-stone-200 dark:border-stone-700'} ${type === 'password' ? 'pr-10' : ''} ${suffix ? 'pr-20' : ''}`}
                />
                {type === 'password' && (
                    <button
                        type="button"
                        onClick={() => setShowPw(!showPw)}
                        className="absolute top-1/2 right-3 -translate-y-1/2 text-stone-400 hover:text-stone-600"
                    >
                        {showPw ? <EyeOff size={16} /> : <Eye size={16} />}
                    </button>
                )}
                {suffix && (
                    <span className="absolute top-1/2 right-3 -translate-y-1/2 text-xs text-stone-400">
                        {suffix}
                    </span>
                )}
            </div>
            {hint && <p className="text-xs text-stone-400">{hint}</p>}
            {error && <p className="text-xs text-rose-500">{error}</p>}
        </div>
    );
}

function Toggle({
    checked,
    onChange,
    label,
    description,
}: {
    checked: boolean;
    onChange: (v: boolean) => void;
    label: string;
    description?: string;
}) {
    return (
        <div className="flex items-center justify-between border-b border-stone-100 py-3 last:border-0 dark:border-stone-800">
            <div>
                <p className="text-sm font-medium text-stone-800 dark:text-stone-100">
                    {label}
                </p>
                {description && (
                    <p className="mt-0.5 text-xs text-stone-500 dark:text-stone-400">
                        {description}
                    </p>
                )}
            </div>
            <button
                type="button"
                onClick={() => onChange(!checked)}
                className={`relative transition-colors duration-200 focus:outline-none ${
                    checked
                        ? 'text-thh-primary'
                        : 'text-stone-300 dark:text-stone-600'
                }`}
                aria-checked={checked}
                role="switch"
            >
                {checked ? <ToggleRight size={32} /> : <ToggleLeft size={32} />}
            </button>
        </div>
    );
}

export default function SettingsIndex({
    settings,
    notification_templates,
}: SettingsPageProps) {
    const [activeTab, setActiveTab] = useState<SettingsTab>('general');
    const [testEmail, setTestEmail] = useState('');
    const [sending, setSending] = useState(false);

    const generalForm = useForm({
        app_name_en: String(settings.app_name_en ?? 'Tribal Helping Hand'),
        app_name_gu: String(settings.app_name_gu ?? 'ટ્રાઇબલ હેલ્પિંગ હૅન્ડ'),
        app_name_short_en: String(
            settings.app_name_short_en ?? settings.app_name_short ?? 'THH',
        ),
        app_name_short_gu: String(
            settings.app_name_short_gu ?? settings.app_name_short ?? 'THH',
        ),
        support_email: String(settings.support_email ?? ''),
        helpline_phone: String(settings.helpline_phone ?? ''),
        public_portal_enabled: Boolean(settings.public_portal_enabled ?? true),
        app_logo: null as File | null,
        app_favicon: null as File | null,
    });

    const smtpForm = useForm({
        smtp_host: String(settings.smtp_host ?? ''),
        smtp_port: String(settings.smtp_port ?? '587'),
        smtp_username: String(settings.smtp_username ?? ''),
        smtp_password: '',
        smtp_from_name: String(
            settings.smtp_from_name ?? 'Tribal Helping Hand (GGVT)',
        ),
        smtp_from_email: String(settings.smtp_from_email ?? ''),
    });

    const notifForm = useForm({
        email_notifications_enabled: Boolean(
            settings.email_notifications_enabled ?? true,
        ),
        push_notifications_enabled: Boolean(
            settings.push_notifications_enabled ?? true,
        ),
    });

    const handleSendTestEmail = () => {
        if (!testEmail) return;
        setSending(true);
        router.post(
            '/admin/settings/test-email',
            { test_recipient: testEmail },
            {
                onFinish: () => setSending(false),
            },
        );
    };

    return (
        <AdminLayout title="Settings">
            <div className="mx-auto max-w-4xl space-y-6">
                {/* Page Header */}
                <div className="flex items-center gap-3">
                    <div className="bg-thh-primary/10 rounded-2xl p-3">
                        <Settings className="text-thh-primary" size={24} />
                    </div>
                    <div>
                        <h1 className="text-2xl font-bold text-stone-900 dark:text-stone-50">
                            Application Settings
                        </h1>
                        <p className="text-sm text-stone-500 dark:text-stone-400">
                            Configure general settings, SMTP email, and
                            notification channels.
                        </p>
                    </div>
                </div>

                {/* Tab Navigation */}
                <div className="flex w-fit gap-2 rounded-2xl bg-stone-100 p-1.5 dark:bg-stone-800">
                    <TabButton
                        active={activeTab === 'general'}
                        onClick={() => setActiveTab('general')}
                        icon={Globe}
                        label="General"
                    />
                    <TabButton
                        active={activeTab === 'smtp'}
                        onClick={() => setActiveTab('smtp')}
                        icon={Server}
                        label="Email / SMTP"
                    />
                    <TabButton
                        active={activeTab === 'notifications'}
                        onClick={() => setActiveTab('notifications')}
                        icon={Bell}
                        label="Notifications"
                    />
                </div>

                {/* General Tab */}
                {activeTab === 'general' && (
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            generalForm.post('/admin/settings/general', {
                                forceFormData: true,
                            });
                        }}
                        className="space-y-5"
                    >
                        <SectionCard
                            title="Application Identity"
                            description="App name displayed to citizens and staff."
                            icon={Globe}
                        >
                            {/* 2-column: full name + short name for each language */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                {/* English row */}
                                <FormField
                                    label="App Name (English)"
                                    id="app_name_en"
                                    value={generalForm.data.app_name_en}
                                    onChange={(v) =>
                                        generalForm.setData('app_name_en', v)
                                    }
                                    placeholder="Tribal Helping Hand"
                                    required
                                />
                                <FormField
                                    label="Short Name · EN (e.g. THH)"
                                    id="app_name_short_en"
                                    value={generalForm.data.app_name_short_en}
                                    onChange={(v) =>
                                        generalForm.setData(
                                            'app_name_short_en',
                                            v,
                                        )
                                    }
                                    placeholder="THH"
                                />
                                {/* Gujarati row */}
                                <FormField
                                    label="App Name (Gujarati)"
                                    id="app_name_gu"
                                    value={generalForm.data.app_name_gu}
                                    onChange={(v) =>
                                        generalForm.setData('app_name_gu', v)
                                    }
                                    placeholder="ટ્રાઇબલ હેલ્પિંગ હૅન્ડ"
                                    required
                                />
                                <FormField
                                    label="Short Name · GU (e.g. ટીએચએચ)"
                                    id="app_name_short_gu"
                                    value={generalForm.data.app_name_short_gu}
                                    onChange={(v) =>
                                        generalForm.setData(
                                            'app_name_short_gu',
                                            v,
                                        )
                                    }
                                    placeholder="ટીએચએચ"
                                />
                            </div>
                            <div>
                                <p className="mb-2 text-sm font-medium text-stone-700 dark:text-stone-300">
                                    App logo (web + mobile)
                                </p>
                                <div className="flex items-center gap-4">
                                    {typeof settings.app_logo_url ===
                                        'string' &&
                                        settings.app_logo_url && (
                                            <img
                                                src={String(
                                                    settings.app_logo_url,
                                                )}
                                                alt="Current logo"
                                                className="h-16 w-16 rounded-2xl border border-stone-200 bg-white object-contain p-1"
                                            />
                                        )}
                                    <input
                                        type="file"
                                        accept="image/*"
                                        onChange={(e) =>
                                            generalForm.setData(
                                                'app_logo',
                                                e.target.files?.[0] ?? null,
                                            )
                                        }
                                        className="text-sm"
                                    />
                                </div>
                                <p className="mt-1 text-xs text-stone-500">
                                    PNG or SVG, square works best. Theme colors
                                    still come from Theme settings.
                                </p>
                            </div>
                            <div>
                                <p className="mb-2 text-sm font-medium text-stone-700 dark:text-stone-300">
                                    Favicon (browser tab)
                                </p>
                                <div className="flex items-center gap-4">
                                    {typeof settings.app_favicon_url ===
                                        'string' &&
                                        settings.app_favicon_url && (
                                            <img
                                                src={String(
                                                    settings.app_favicon_url,
                                                )}
                                                alt="Current favicon"
                                                className="h-10 w-10 rounded-lg border border-stone-200 bg-white object-contain p-1"
                                            />
                                        )}
                                    <input
                                        type="file"
                                        accept="image/png,image/x-icon,image/svg+xml,image/jpeg,image/webp,.ico"
                                        onChange={(e) =>
                                            generalForm.setData(
                                                'app_favicon',
                                                e.target.files?.[0] ?? null,
                                            )
                                        }
                                        className="text-sm"
                                    />
                                </div>
                                <p className="mt-1 text-xs text-stone-500">
                                    32×32 or 64×64 PNG or ICO. Shows in the
                                    browser tab on web and desk.
                                </p>
                            </div>
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <FormField
                                    label="Support Email"
                                    id="support_email"
                                    type="email"
                                    value={generalForm.data.support_email}
                                    onChange={(v) =>
                                        generalForm.setData('support_email', v)
                                    }
                                    placeholder="support@ggvt.org"
                                    required
                                />
                                <FormField
                                    label="Helpline Phone"
                                    id="helpline_phone"
                                    value={generalForm.data.helpline_phone}
                                    onChange={(v) =>
                                        generalForm.setData('helpline_phone', v)
                                    }
                                    placeholder="+91 98765 43210"
                                />
                            </div>
                        </SectionCard>

                        <SectionCard
                            title="Public Portal Access"
                            description="Control visibility of public website pages and citizen-facing portal routes."
                            icon={Globe}
                        >
                            <Toggle
                                checked={generalForm.data.public_portal_enabled}
                                onChange={(v) =>
                                    generalForm.setData(
                                        'public_portal_enabled',
                                        v,
                                    )
                                }
                                label="Enable Public Portal & Website"
                                description="When disabled, all public-facing pages (home, track case, community modules, static pages) are deactivated and root URL redirects directly to staff login."
                            />
                        </SectionCard>

                        <div className="flex justify-end">
                            <button
                                type="submit"
                                disabled={generalForm.processing}
                                className="bg-thh-primary hover:bg-thh-primary/90 flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all disabled:opacity-60"
                            >
                                <CheckCircle2 size={16} />
                                {generalForm.processing
                                    ? 'Saving…'
                                    : 'Save General Settings'}
                            </button>
                        </div>
                    </form>
                )}

                {/* SMTP Tab */}
                {activeTab === 'smtp' && (
                    <div className="space-y-5">
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                smtpForm.post('/admin/settings/smtp');
                            }}
                        >
                            <SectionCard
                                title="SMTP Email Configuration"
                                description="Configure your outgoing email server for notifications and alerts."
                                icon={Mail}
                            >
                                <div className="mb-4 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
                                    <Info
                                        size={16}
                                        className="mt-0.5 flex-shrink-0 text-amber-600"
                                    />
                                    <p className="text-xs text-amber-700 dark:text-amber-400">
                                        SMTP credentials are stored encrypted.
                                        The password field shows blank for
                                        security — enter a new password only to
                                        change it.
                                    </p>
                                </div>
                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <FormField
                                        label="SMTP Host"
                                        id="smtp_host"
                                        value={smtpForm.data.smtp_host}
                                        onChange={(v) =>
                                            smtpForm.setData('smtp_host', v)
                                        }
                                        placeholder="smtp.gmail.com"
                                        required
                                    />
                                    <FormField
                                        label="SMTP Port"
                                        id="smtp_port"
                                        value={smtpForm.data.smtp_port}
                                        onChange={(v) =>
                                            smtpForm.setData('smtp_port', v)
                                        }
                                        placeholder="587"
                                        suffix="TCP"
                                        required
                                    />
                                    <FormField
                                        label="Username / Email"
                                        id="smtp_username"
                                        value={smtpForm.data.smtp_username}
                                        onChange={(v) =>
                                            smtpForm.setData('smtp_username', v)
                                        }
                                        placeholder="your@email.com"
                                        required
                                    />
                                    <FormField
                                        label="Password"
                                        id="smtp_password"
                                        type="password"
                                        value={smtpForm.data.smtp_password}
                                        onChange={(v) =>
                                            smtpForm.setData('smtp_password', v)
                                        }
                                        placeholder={
                                            settings.smtp_password_masked
                                                ? '••••••••'
                                                : 'Enter password'
                                        }
                                        hint={
                                            settings.smtp_password_masked
                                                ? 'Leave blank to keep existing password'
                                                : ''
                                        }
                                    />
                                    <FormField
                                        label="From Name"
                                        id="smtp_from_name"
                                        value={smtpForm.data.smtp_from_name}
                                        onChange={(v) =>
                                            smtpForm.setData(
                                                'smtp_from_name',
                                                v,
                                            )
                                        }
                                        placeholder="Tribal Helping Hand (GGVT)"
                                        required
                                    />
                                    <FormField
                                        label="From Email Address"
                                        id="smtp_from_email"
                                        type="email"
                                        value={smtpForm.data.smtp_from_email}
                                        onChange={(v) =>
                                            smtpForm.setData(
                                                'smtp_from_email',
                                                v,
                                            )
                                        }
                                        placeholder="noreply@ggvt.org"
                                        required
                                    />
                                </div>
                                <div className="flex justify-end pt-2">
                                    <button
                                        type="submit"
                                        disabled={smtpForm.processing}
                                        className="bg-thh-primary hover:bg-thh-primary/90 flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all disabled:opacity-60"
                                    >
                                        <Shield size={16} />
                                        {smtpForm.processing
                                            ? 'Saving…'
                                            : 'Save SMTP Settings'}
                                    </button>
                                </div>
                            </SectionCard>
                        </form>

                        {/* Test Email */}
                        <SectionCard
                            title="Send Test Email"
                            description="Verify your SMTP settings by sending a test email."
                            icon={Send}
                        >
                            <div className="flex gap-3">
                                <input
                                    type="email"
                                    value={testEmail}
                                    onChange={(e) =>
                                        setTestEmail(e.target.value)
                                    }
                                    placeholder="recipient@example.com"
                                    className="focus:ring-thh-primary/50 focus:border-thh-primary flex-1 rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:outline-none dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100"
                                />
                                <button
                                    type="button"
                                    onClick={handleSendTestEmail}
                                    disabled={sending || !testEmail}
                                    className="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold whitespace-nowrap text-white transition-all hover:bg-emerald-700 disabled:opacity-60"
                                >
                                    <Send size={15} />
                                    {sending ? 'Sending…' : 'Send Test'}
                                </button>
                            </div>
                        </SectionCard>
                    </div>
                )}

                {/* Notifications Tab */}
                {activeTab === 'notifications' && (
                    <div className="space-y-5">
                        {/* Channel Toggles */}
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                notifForm.post('/admin/settings/notifications');
                            }}
                        >
                            <SectionCard
                                title="Notification Channels"
                                description="Enable or disable entire delivery channels globally."
                                icon={Bell}
                            >
                                <Toggle
                                    checked={
                                        notifForm.data
                                            .email_notifications_enabled
                                    }
                                    onChange={(v) =>
                                        notifForm.setData(
                                            'email_notifications_enabled',
                                            v,
                                        )
                                    }
                                    label="Email Notifications"
                                    description="Send email alerts for case events using SMTP configuration above."
                                />
                                <Toggle
                                    checked={
                                        notifForm.data
                                            .push_notifications_enabled
                                    }
                                    onChange={(v) =>
                                        notifForm.setData(
                                            'push_notifications_enabled',
                                            v,
                                        )
                                    }
                                    label="Push Notifications (FCM)"
                                    description="Send Firebase push notifications to the mobile app."
                                />
                                <div className="flex justify-end pt-2">
                                    <button
                                        type="submit"
                                        disabled={notifForm.processing}
                                        className="bg-thh-primary hover:bg-thh-primary/90 flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all disabled:opacity-60"
                                    >
                                        <CheckCircle2 size={16} />
                                        {notifForm.processing
                                            ? 'Saving…'
                                            : 'Save Channel Settings'}
                                    </button>
                                </div>
                            </SectionCard>
                        </form>

                        {/* Per-Event Template Toggles */}
                        <SectionCard
                            title="Notification Event Templates"
                            description="Enable or disable specific notification events. When disabled, no notification is sent for that event."
                            icon={Mail}
                        >
                            {notification_templates.length === 0 ? (
                                <p className="py-4 text-center text-sm text-stone-500 dark:text-stone-400">
                                    No notification templates found. Run the
                                    database seeder to populate templates.
                                </p>
                            ) : (
                                <div className="space-y-0 divide-y divide-stone-100 dark:divide-stone-800">
                                    {notification_templates.map((template) => (
                                        <div
                                            key={template.id}
                                            className="flex items-center justify-between py-3"
                                        >
                                            <div className="flex-1 pr-4">
                                                <p className="font-mono text-sm text-stone-700 dark:text-stone-300">
                                                    {template.event_key}
                                                </p>
                                                <p className="mt-0.5 text-xs text-stone-500 dark:text-stone-400">
                                                    {template.title_template
                                                        ?.en ?? '—'}
                                                </p>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <span
                                                    className={`rounded-full px-2 py-0.5 text-xs font-medium ${
                                                        template.is_enabled
                                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                                            : 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400'
                                                    }`}
                                                >
                                                    {template.is_enabled
                                                        ? 'Enabled'
                                                        : 'Disabled'}
                                                </span>
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        router.post(
                                                            `/admin/settings/notification-template/${template.id}/toggle`,
                                                        )
                                                    }
                                                    className={`relative transition-colors duration-200 focus:outline-none ${
                                                        template.is_enabled
                                                            ? 'text-thh-primary'
                                                            : 'text-stone-300 dark:text-stone-600'
                                                    }`}
                                                    aria-checked={
                                                        template.is_enabled
                                                    }
                                                    role="switch"
                                                >
                                                    {template.is_enabled ? (
                                                        <ToggleRight
                                                            size={28}
                                                        />
                                                    ) : (
                                                        <ToggleLeft size={28} />
                                                    )}
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </SectionCard>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
