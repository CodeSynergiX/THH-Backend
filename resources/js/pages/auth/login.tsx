import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowRight, Lock, Mail, UserCheck } from 'lucide-react';
import { useTranslation } from '../../lib/i18n';

export default function Login() {
    const { t, locale, supportedLocales, switchLocale } = useTranslation();

    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: true,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/login');
    };

    const demoRoles = [
        {
            role: 'super_admin',
            label: 'Super Admin',
            desc: 'GGVT Director (Full Access)',
            color: 'bg-rose-500/10 text-rose-700 border-rose-500/30',
        },
        {
            role: 'admin',
            label: 'Admin',
            desc: 'Tribal Case Coordinator',
            color: 'bg-amber-500/10 text-amber-700 border-amber-500/30',
        },
        {
            role: 'staff',
            label: 'Field Staff',
            desc: 'Dediapada Taluka Officer',
            color: 'bg-emerald-500/10 text-emerald-700 border-emerald-500/30',
        },
        {
            role: 'mentor',
            label: 'Mentor',
            desc: 'Education & Career Advisor',
            color: 'bg-indigo-500/10 text-indigo-700 border-indigo-500/30',
        },
        {
            role: 'partner',
            label: 'Partner',
            desc: 'Tribal Solar & Health Partner',
            color: 'bg-teal-500/10 text-teal-700 border-teal-500/30',
        },
    ];

    return (
        <div className="bg-thh-bg text-thh-text flex min-h-screen flex-col justify-center py-12 font-sans sm:px-6 lg:px-8">
            <Head title="Sign In - Tribal Helping Hand" />

            {/* Top Language Switcher */}
            <div className="bg-thh-surface border-thh-border absolute top-4 right-4 flex items-center rounded-xl border p-1 text-xs">
                {supportedLocales.map((lang) => (
                    <button
                        key={lang.code}
                        type="button"
                        onClick={() => switchLocale(lang.code)}
                        className={`rounded-lg px-3 py-1 font-semibold transition-all ${
                            locale === lang.code
                                ? 'bg-thh-primary text-white shadow-xs'
                                : 'text-thh-text-muted hover:text-thh-text'
                        }`}
                    >
                        {lang.native_name || lang.name}
                    </button>
                ))}
            </div>

            <div className="space-y-3 text-center sm:mx-auto sm:w-full sm:max-w-md">
                <Link href="/" className="inline-flex items-center space-x-3">
                    <div className="bg-thh-primary flex h-12 w-12 items-center justify-center rounded-2xl text-xl font-black text-white shadow-md">
                        THH
                    </div>
                </Link>
                <h2 className="text-thh-text text-2xl font-black tracking-tight sm:text-3xl">
                    {t('app.portal.title', 'Tribal Helping Hand')}
                </h2>
                <p className="text-thh-text-muted text-xs">
                    {t('app.portal.tagline', 'Global Gramin Vikas Trust')} •
                    Portal Sign In
                </p>
            </div>

            <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                <div className="bg-thh-surface border-thh-border space-y-6 rounded-2xl border px-6 py-8 shadow-md sm:px-10">
                    {/* Credentials Form */}
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label className="text-thh-text mb-1 block text-xs font-bold tracking-wider uppercase">
                                {t(
                                    'auth.login.admin_email_label',
                                    'Email Address',
                                )}
                            </label>
                            <div className="relative">
                                <Mail className="text-thh-text-muted absolute top-3 left-3 h-4 w-4" />
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) =>
                                        setData('email', e.target.value)
                                    }
                                    placeholder="e.g. admin@ggvt.org"
                                    required
                                    className="border-thh-border bg-thh-bg text-thh-text focus:ring-thh-primary w-full rounded-xl border py-2.5 pr-3.5 pl-9 text-xs focus:ring-1 focus:outline-hidden"
                                />
                            </div>
                            {errors.email && (
                                <p className="mt-1 text-xs font-semibold text-rose-600">
                                    {errors.email}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="text-thh-text mb-1 block text-xs font-bold tracking-wider uppercase">
                                {t('auth.login.password_label', 'Password')}
                            </label>
                            <div className="relative">
                                <Lock className="text-thh-text-muted absolute top-3 left-3 h-4 w-4" />
                                <input
                                    type="password"
                                    value={data.password}
                                    onChange={(e) =>
                                        setData('password', e.target.value)
                                    }
                                    placeholder="••••••••"
                                    required
                                    className="border-thh-border bg-thh-bg text-thh-text focus:ring-thh-primary w-full rounded-xl border py-2.5 pr-3.5 pl-9 text-xs focus:ring-1 focus:outline-hidden"
                                />
                            </div>
                            {errors.password && (
                                <p className="mt-1 text-xs font-semibold text-rose-600">
                                    {errors.password}
                                </p>
                            )}
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-thh-primary inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold text-white shadow-xs transition-all hover:opacity-95 disabled:opacity-50"
                        >
                            <span>
                                {processing
                                    ? 'Authenticating...'
                                    : t(
                                          'auth.login.admin_login_button',
                                          'Sign In to Portal',
                                      )}
                            </span>
                            <ArrowRight className="h-4 w-4" />
                        </button>
                    </form>

                    {/* Quick Demo Role Switcher */}
                    <div className="border-thh-border space-y-3 border-t pt-4">
                        <span className="text-thh-text-muted block text-center text-[11px] font-bold tracking-wider uppercase">
                            Instant Role Switcher (Development & Testing)
                        </span>

                        <div className="space-y-1.5">
                            {demoRoles.map((d) => (
                                <a
                                    key={d.role}
                                    href={`/dev/quick-login/${d.role}`}
                                    className={`flex w-full items-center justify-between rounded-xl border p-2.5 text-xs font-semibold transition-all hover:scale-[1.01] ${d.color}`}
                                >
                                    <div className="flex items-center gap-2">
                                        <UserCheck className="h-4 w-4 shrink-0" />
                                        <span>Login as {d.label}</span>
                                    </div>
                                    <span className="text-[10px] opacity-75">
                                        {d.desc}
                                    </span>
                                </a>
                            ))}
                        </div>
                    </div>
                </div>

                <p className="text-thh-text-muted mt-4 text-center text-xs">
                    <Link href="/" className="hover:text-thh-text underline">
                        ← Back to Public Portal
                    </Link>
                </p>
            </div>
        </div>
    );
}
