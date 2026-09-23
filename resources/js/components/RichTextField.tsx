import React, { useEffect, useRef } from 'react';

interface RichTextFieldProps {
    label: string;
    value: string;
    onChange: (value: string) => void;
    rows?: number;
}

export default function RichTextField({
    label,
    value,
    onChange,
}: RichTextFieldProps) {
    const ref = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (ref.current && ref.current.innerHTML !== value) {
            ref.current.innerHTML = value || '<p></p>';
        }
    }, [value]);

    const command = (cmd: string, arg?: string) => {
        document.execCommand(cmd, false, arg);
        onChange(ref.current?.innerHTML ?? '');
    };

    return (
        <div className="space-y-1.5">
            <div className="flex items-center justify-between">
                <label className="text-thh-text text-sm font-semibold">
                    {label}
                </label>
                <div className="flex flex-wrap gap-1">
                    {[
                        ['bold', 'B'],
                        ['italic', 'I'],
                        ['insertUnorderedList', 'List'],
                        ['formatBlock', 'H2'],
                    ].map(([cmd, text]) => (
                        <button
                            key={cmd}
                            type="button"
                            onClick={() =>
                                command(
                                    cmd,
                                    cmd === 'formatBlock' ? 'h2' : undefined,
                                )
                            }
                            className="border-thh-border text-thh-text-muted hover:text-thh-text rounded border px-2 py-0.5 text-xs font-semibold"
                        >
                            {text}
                        </button>
                    ))}
                </div>
            </div>
            <div
                ref={ref}
                contentEditable
                className="border-thh-border bg-thh-bg cms-body min-h-40 w-full rounded-xl border px-3 py-2 text-base leading-7"
                onInput={() => onChange(ref.current?.innerHTML ?? '')}
                suppressContentEditableWarning
            />
        </div>
    );
}
