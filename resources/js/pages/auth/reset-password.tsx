import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { useTranslation } from '../../lib/i18n';

export default function ResetPassword({
    token,
    email,
}: {
    token: string;
    email: string;
}) {
    const { t, locale, supportedLocales, switchLocale } = useTranslation();
    const { data, setData, post, processing, errors } = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    return (
        <div className="bg-thh-bg text-thh-text flex min-h-screen flex-col justify-center px-4 py-12">
            <Head title={t('auth.reset.title', 'Set a new password')} />
            <div className="bg-thh-surface border-thh-border absolute top-4 right-4 flex items-center rounded-xl border p-1 text-xs">
                {supportedLocales.map((lang) => (
                    <button
                        key={lang.code}
                        type="button"
                        onClick={() => switchLocale(lang.code)}
                        className={`rounded-lg px-3 py-1 font-semibold ${
                            locale === lang.code
                                ? 'bg-thh-primary text-white'
                                : 'text-thh-text-muted'
                        }`}
                    >
                        {lang.native_name || lang.name}
                    </button>
                ))}
            </div>
            <div className="mx-auto w-full max-w-md">
                <h1 className="font-serif text-3xl">
                    {t('auth.reset.title', 'Choose a new password')}
                </h1>
                <form
                    className="mt-6 space-y-4"
                    onSubmit={(e) => {
                        e.preventDefault();
                        post('/reset-password');
                    }}
                >
                    <input
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        required
                        className="border-thh-border w-full rounded-xl border px-3 py-2.5 text-base"
                    />
                    <input
                        type="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        required
                        placeholder={t('auth.reset.password', 'New password')}
                        className="border-thh-border w-full rounded-xl border px-3 py-2.5 text-base"
                    />
                    <input
                        type="password"
                        value={data.password_confirmation}
                        onChange={(e) =>
                            setData('password_confirmation', e.target.value)
                        }
                        required
                        placeholder={t(
                            'auth.reset.confirm',
                            'Confirm password',
                        )}
                        className="border-thh-border w-full rounded-xl border px-3 py-2.5 text-base"
                    />
                    {errors.email && (
                        <p className="text-sm text-rose-700">{errors.email}</p>
                    )}
                    {errors.password && (
                        <p className="text-sm text-rose-700">
                            {errors.password}
                        </p>
                    )}
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-thh-primary w-full rounded-xl py-2.5 font-semibold text-white"
                    >
                        {t('auth.reset.submit', 'Update password')}
                    </button>
                </form>
                <Link
                    href="/login"
                    className="mt-4 inline-block text-base underline"
                >
                    {t('nav.login', 'Back to login')}
                </Link>
            </div>
        </div>
    );
}
