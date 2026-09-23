import React from 'react';

export type EditorLocale = 'en' | 'gu';

export default function LocaleTabs({
    value,
    onChange,
}: {
    value: EditorLocale;
    onChange: (locale: EditorLocale) => void;
}) {
    return (
        <div className="bg-thh-bg border-thh-border mb-3 inline-flex rounded-xl border p-1 text-sm">
            <button
                type="button"
                onClick={() => onChange('en')}
                className={`rounded-lg px-3 py-1.5 font-semibold ${
                    value === 'en' ? 'bg-thh-primary text-white' : ''
                }`}
            >
                English
            </button>
            <button
                type="button"
                onClick={() => onChange('gu')}
                className={`rounded-lg px-3 py-1.5 font-semibold ${
                    value === 'gu' ? 'bg-thh-primary text-white' : ''
                }`}
            >
                ગુજરાતી
            </button>
        </div>
    );
}
