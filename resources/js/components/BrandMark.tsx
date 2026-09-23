import React from 'react';
import { usePage } from '@inertiajs/react';
import { useTranslation } from '../lib/i18n';

export default function BrandMark({
    compact = false,
    inverted = false,
}: {
    compact?: boolean;
    inverted?: boolean;
}) {
    const { branding } = usePage<{
        branding?: {
            name?: string;
            name_en?: string;
            name_gu?: string;
            logo_url?: string | null;
        };
    }>().props;
    const { loc, t } = useTranslation();
    const name =
        loc(branding?.name_en, branding?.name_gu) ||
        branding?.name ||
        t('app.portal.title', 'Tribal Helping Hand');

    return (
        <span className="flex items-center gap-3">
            {branding?.logo_url ? (
                <img
                    src={branding.logo_url}
                    alt={name}
                    className={`object-contain ${compact ? 'h-10 w-10' : 'h-12 w-12'} rounded-2xl bg-white/90 p-1 shadow-sm`}
                />
            ) : (
                <span
                    className={`relative flex ${compact ? 'h-10 w-10' : 'h-12 w-12'} items-center justify-center overflow-hidden rounded-[1.15rem] shadow-md`}
                    style={{
                        background:
                            'linear-gradient(145deg, var(--color-primary), var(--color-secondary))',
                    }}
                >
                    <span className="absolute -top-2 -right-2 h-7 w-7 rounded-full bg-white/20" />
                    <span className="absolute -bottom-3 -left-1 h-8 w-8 rounded-full bg-black/10" />
                    <svg
                        viewBox="0 0 32 32"
                        className={`${compact ? 'h-6 w-6' : 'h-7 w-7'} text-white`}
                        fill="none"
                        aria-hidden
                    >
                        <path
                            d="M8 18c0-5 3.2-8.5 8-8.5S24 13 24 18"
                            stroke="currentColor"
                            strokeWidth="2.2"
                            strokeLinecap="round"
                        />
                        <path
                            d="M10 20.5c1.4 3.2 3.4 4.8 6 4.8s4.6-1.6 6-4.8"
                            stroke="currentColor"
                            strokeWidth="2.2"
                            strokeLinecap="round"
                        />
                        <circle cx="16" cy="12.2" r="2.1" fill="currentColor" />
                    </svg>
                </span>
            )}
            <span className="text-left leading-tight">
                <span
                    className={`block font-serif font-semibold ${compact ? 'text-base' : 'text-lg'} ${inverted ? 'text-white' : 'text-thh-text'}`}
                >
                    {name}
                </span>
                {!compact && (
                    <span
                        className={`block text-xs ${inverted ? 'text-white/80' : 'text-thh-text-muted'}`}
                    >
                        {t(
                            'app.portal.tagline_short',
                            'Global Gramin Vikas Trust',
                        )}
                    </span>
                )}
            </span>
        </span>
    );
}
