import React, { useState } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import {
    Layers,
    Plus,
    Pencil,
    Trash2,
    ArrowRight,
    GraduationCap,
    Briefcase,
    HeartPulse,
    Shield,
    BookOpen,
    Activity,
    Droplets,
    Users,
    Leaf,
    Home,
    Sun,
    Zap,
    Wrench,
    Package,
    Sparkles,
    Smile,
    Coins,
    Bike,
    Ambulance,
    Navigation,
    Lightbulb,
    MapPin,
    HelpCircle,
    Scale,
    ClipboardList,
    HandHeart,
    Landmark,
    Sprout,
    ShoppingBag,
    Star,
    Building2,
    Award,
    Search,
} from 'lucide-react';
import AdminLayout from '../../../components/AdminLayout';
import LocaleTabs, { EditorLocale } from '../../../components/LocaleTabs';
import { useTranslation } from '../../../lib/i18n';

interface ModuleItem {
    id: number;
    slug: string;
    title: string;
    title_en: string;
    title_gu: string;
    description?: string;
    description_en?: string;
    description_gu?: string;
    icon?: string;
    accent_color?: string;
    is_enabled: boolean;
    is_public: boolean;
    show_apply_form: boolean;
    type: string;
    sort_order: number;
    count: number;
    active_count: number;
}

/** Lucide icon options available for selection in the admin panel */
const ICON_OPTIONS: {
    value: string;
    label: string;
    Component: React.ComponentType<{ size?: number; color?: string }>;
}[] = [
    { value: 'award', label: 'Award / Scheme', Component: Award },
    { value: 'graduation-cap', label: 'Scholarship', Component: GraduationCap },
    { value: 'briefcase', label: 'Jobs', Component: Briefcase },
    { value: 'heart-pulse', label: 'Health', Component: HeartPulse },
    { value: 'shield', label: 'Shield / Rights', Component: Shield },
    { value: 'book-open', label: 'Education', Component: BookOpen },
    { value: 'activity', label: 'Activity / Pulse', Component: Activity },
    { value: 'droplet', label: 'Blood / Water', Component: Droplets },
    { value: 'users', label: 'Mentorship', Component: Users },
    { value: 'leaf', label: 'Forest / Agri', Component: Leaf },
    { value: 'home', label: 'Home', Component: Home },
    { value: 'sun', label: 'Solar', Component: Sun },
    { value: 'zap', label: 'Skill / Energy', Component: Zap },
    { value: 'wrench', label: 'Technical', Component: Wrench },
    { value: 'package', label: 'Forest Produce', Component: Package },
    { value: 'sparkles', label: 'Sakhi / Women', Component: Sparkles },
    { value: 'smile', label: 'Welfare', Component: Smile },
    { value: 'coins', label: 'Financial', Component: Coins },
    { value: 'bike', label: 'Bicycle', Component: Bike },
    { value: 'ambulance', label: 'Emergency', Component: Ambulance },
    { value: 'navigation', label: 'Navigation', Component: Navigation },
    { value: 'lightbulb', label: 'Infrastructure', Component: Lightbulb },
    { value: 'map-pin', label: 'Village Reports', Component: MapPin },
    { value: 'help-circle', label: 'Help / Guidance', Component: HelpCircle },
    { value: 'scale', label: 'Legal / Justice', Component: Scale },
    {
        value: 'clipboard-list',
        label: 'Mock Tests / List',
        Component: ClipboardList,
    },
    { value: 'hand-heart', label: 'Volunteer', Component: HandHeart },
    { value: 'landmark', label: 'Government', Component: Landmark },
    { value: 'sprout', label: 'Entrepreneur', Component: Sprout },
    {
        value: 'shopping-bag',
        label: 'Market / Artisan',
        Component: ShoppingBag,
    },
    { value: 'building-2', label: 'Infrastructure', Component: Building2 },
    { value: 'star', label: 'Star / Featured', Component: Star },
    { value: 'search', label: 'Search / Track', Component: Search },
    { value: 'layers', label: 'General', Component: Layers },
];

export default function ContentIndex({ modules }: { modules: ModuleItem[] }) {
    const { loc } = useTranslation();
    const [editing, setEditing] = useState<ModuleItem | null>(null);
    const [creating, setCreating] = useState(false);
    const [editorLocale, setEditorLocale] = useState<EditorLocale>('en');

    const form = useForm({
        title_en: '',
        title_gu: '',
        description_en: '',
        description_gu: '',
        slug: '',
        icon: 'layers',
        accent_color: '#B45309',
        is_enabled: true,
        is_public: true,
        show_apply_form: true,
        type: 'cms',
        sort_order: 100,
    });

    const openCreate = () => {
        form.reset();
        setEditing(null);
        setEditorLocale('en');
        setCreating(true);
    };

    const openEdit = (module: ModuleItem) => {
        setEditorLocale('en');
        form.setData({
            title_en: module.title_en,
            title_gu: module.title_gu,
            description_en: module.description_en ?? '',
            description_gu: module.description_gu ?? '',
            slug: module.slug,
            icon: module.icon ?? 'layers',
            accent_color: module.accent_color ?? '#B45309',
            is_enabled: module.is_enabled,
            is_public: module.is_public,
            show_apply_form: module.show_apply_form,
            type: module.type,
            sort_order: module.sort_order,
        });
        setEditing(module);
        setCreating(true);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (editing) {
            form.put(`/admin/content/modules/${editing.id}`, {
                onSuccess: () => setCreating(false),
            });
            return;
        }
        form.post('/admin/content/modules', {
            onSuccess: () => setCreating(false),
        });
    };

    return (
        <AdminLayout title="Content & Community Modules">
            <div className="space-y-6">
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <h2 className="text-thh-text flex items-center gap-2 text-2xl font-semibold">
                            <Layers className="text-thh-primary h-6 w-6" />
                            Content modules
                        </h2>
                        <p className="text-thh-text-muted mt-1 text-base">
                            Add, hide or reorder public modules. New public
                            modules appear on web and mobile automatically.
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={openCreate}
                        className="bg-thh-primary inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white"
                    >
                        <Plus className="h-4 w-4" />
                        Add module
                    </button>
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {modules.map((module) => {
                        const iconOption = ICON_OPTIONS.find(
                            (o) => o.value === module.icon,
                        );
                        const IconComp = iconOption?.Component;
                        const accentColor = module.accent_color ?? '#B45309';
                        return (
                            <div
                                key={module.id}
                                className="bg-thh-surface border-thh-border rounded-2xl border p-5"
                            >
                                <div className="mb-3 flex items-start justify-between">
                                    <div className="flex items-center gap-3">
                                        {/* Dynamic icon preview */}
                                        <div
                                            className="flex h-10 w-10 items-center justify-center rounded-xl"
                                            style={{
                                                backgroundColor: `${accentColor}22`,
                                            }}
                                        >
                                            {IconComp ? (
                                                <IconComp
                                                    size={20}
                                                    color={accentColor}
                                                />
                                            ) : (
                                                <span
                                                    className="font-mono text-sm"
                                                    style={{
                                                        color: accentColor,
                                                    }}
                                                >
                                                    {module.icon?.slice(0, 3)}
                                                </span>
                                            )}
                                        </div>
                                        <div>
                                            <p className="text-thh-text text-base leading-tight font-semibold">
                                                {loc(
                                                    module.title_en,
                                                    module.title_gu,
                                                )}
                                            </p>
                                            <p className="text-thh-text-muted text-xs">
                                                {module.icon} · {module.slug}
                                            </p>
                                        </div>
                                    </div>
                                    <span
                                        className="h-5 w-5 rounded-full border border-white shadow"
                                        style={{ backgroundColor: accentColor }}
                                    />
                                </div>
                                <p className="text-thh-text-muted mb-4 line-clamp-2 text-sm">
                                    {loc(
                                        module.description_en,
                                        module.description_gu,
                                    )}
                                </p>
                                <div className="text-thh-text-muted mb-4 flex gap-3 text-sm">
                                    <span>{module.active_count} published</span>
                                    <span>
                                        {module.is_public
                                            ? 'Public'
                                            : 'Internal'}
                                    </span>
                                    <span>
                                        {module.is_enabled ? 'On' : 'Off'}
                                    </span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Link
                                        href={`/admin/content/${module.slug}`}
                                        className="bg-thh-bg text-thh-text inline-flex flex-1 items-center justify-center gap-1 rounded-xl px-3 py-2 text-sm font-semibold"
                                    >
                                        Manage items
                                        <ArrowRight className="h-4 w-4" />
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() => openEdit(module)}
                                        className="border-thh-border rounded-xl border p-2"
                                    >
                                        <Pencil className="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            router.delete(
                                                `/admin/content/modules/${module.id}`,
                                            )
                                        }
                                        className="rounded-xl border border-rose-200 p-2 text-rose-600"
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>

            {creating && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <form
                        onSubmit={submit}
                        className="bg-thh-surface max-h-[90vh] w-full max-w-xl space-y-4 overflow-y-auto rounded-2xl p-6"
                    >
                        <h3 className="text-thh-text text-lg font-semibold">
                            {editing ? 'Edit module' : 'New module'}
                        </h3>
                        <p className="text-thh-text-muted text-sm">
                            Public web and mobile show the language the citizen
                            selected. Fill both English and Gujarati.
                        </p>
                        <LocaleTabs
                            value={editorLocale}
                            onChange={setEditorLocale}
                        />
                        {editorLocale === 'en' ? (
                            <>
                                <label className="text-thh-text block text-sm font-semibold">
                                    Title (English)
                                </label>
                                <input
                                    className="border-thh-border w-full rounded-xl border px-3 py-2 text-base"
                                    placeholder="Sakhi Circles"
                                    value={form.data.title_en}
                                    onChange={(e) =>
                                        form.setData('title_en', e.target.value)
                                    }
                                    required
                                />
                                <label className="text-thh-text block text-sm font-semibold">
                                    Description (English)
                                </label>
                                <textarea
                                    className="border-thh-border w-full rounded-xl border px-3 py-2 text-base"
                                    placeholder="Self-help groups and micro-enterprise"
                                    value={form.data.description_en}
                                    onChange={(e) =>
                                        form.setData(
                                            'description_en',
                                            e.target.value,
                                        )
                                    }
                                />
                            </>
                        ) : (
                            <>
                                <label className="text-thh-text block text-sm font-semibold">
                                    શીર્ષક (ગુજરાતી)
                                </label>
                                <input
                                    className="border-thh-border w-full rounded-xl border px-3 py-2 text-base"
                                    placeholder="સખી મંડળ"
                                    value={form.data.title_gu}
                                    onChange={(e) =>
                                        form.setData('title_gu', e.target.value)
                                    }
                                    required
                                />
                                <label className="text-thh-text block text-sm font-semibold">
                                    વર્ણન (ગુજરાતી)
                                </label>
                                <textarea
                                    className="border-thh-border w-full rounded-xl border px-3 py-2 text-base"
                                    placeholder="મહિલા સશક્તિકરણ અને ગૃહઉદ્યોગ"
                                    value={form.data.description_gu}
                                    onChange={(e) =>
                                        form.setData(
                                            'description_gu',
                                            e.target.value,
                                        )
                                    }
                                />
                            </>
                        )}

                        {/* ── Icon Picker ── */}
                        <div>
                            <label className="text-thh-text mb-2 block text-sm font-semibold">
                                Sector Icon
                                <span className="text-thh-text-muted ml-2 font-normal">
                                    (selected:{' '}
                                    <code className="bg-thh-bg rounded px-1 text-xs">
                                        {form.data.icon}
                                    </code>
                                    )
                                </span>
                            </label>
                            <div className="border-thh-border grid max-h-52 grid-cols-5 gap-1.5 overflow-y-auto rounded-xl border p-3 sm:grid-cols-7">
                                {ICON_OPTIONS.map(
                                    ({ value, label, Component }) => {
                                        const isSelected =
                                            form.data.icon === value;
                                        return (
                                            <button
                                                key={value}
                                                type="button"
                                                title={label}
                                                onClick={() =>
                                                    form.setData('icon', value)
                                                }
                                                className={[
                                                    'flex flex-col items-center justify-center rounded-xl p-2 transition-all',
                                                    isSelected
                                                        ? 'shadow-sm'
                                                        : 'hover:bg-thh-bg',
                                                ].join(' ')}
                                                style={
                                                    isSelected
                                                        ? {
                                                              backgroundColor: `${form.data.accent_color}20`,
                                                              outline: `2px solid ${form.data.accent_color}`,
                                                          }
                                                        : {}
                                                }
                                            >
                                                <Component
                                                    size={20}
                                                    color={
                                                        isSelected
                                                            ? form.data
                                                                  .accent_color
                                                            : '#6B7280'
                                                    }
                                                />
                                                <span className="mt-1 hidden text-center text-[9px] leading-tight text-gray-400 sm:block">
                                                    {label
                                                        .split('/')[0]
                                                        .trim()
                                                        .slice(0, 9)}
                                                </span>
                                            </button>
                                        );
                                    },
                                )}
                            </div>
                        </div>

                        {/* ── Color Picker ── */}
                        <div>
                            <label className="text-thh-text mb-2 block text-sm font-semibold">
                                Accent Color
                            </label>
                            <div className="flex items-center gap-3">
                                <input
                                    type="color"
                                    value={form.data.accent_color}
                                    onChange={(e) =>
                                        form.setData(
                                            'accent_color',
                                            e.target.value,
                                        )
                                    }
                                    className="h-10 w-14 cursor-pointer rounded-lg border-0 p-0.5"
                                />
                                <input
                                    className="border-thh-border flex-1 rounded-xl border px-3 py-2 font-mono text-sm"
                                    placeholder="#B45309"
                                    value={form.data.accent_color}
                                    onChange={(e) =>
                                        form.setData(
                                            'accent_color',
                                            e.target.value,
                                        )
                                    }
                                />
                                {/* Live preview */}
                                <div
                                    className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                                    style={{
                                        backgroundColor: `${form.data.accent_color}22`,
                                    }}
                                >
                                    {(() => {
                                        const opt = ICON_OPTIONS.find(
                                            (o) => o.value === form.data.icon,
                                        );
                                        if (!opt) return null;
                                        return (
                                            <opt.Component
                                                size={20}
                                                color={form.data.accent_color}
                                            />
                                        );
                                    })()}
                                </div>
                            </div>
                        </div>

                        {/* ── Slug ── */}
                        <div>
                            <label className="text-thh-text mb-1 block text-sm font-semibold">
                                Slug{' '}
                                <span className="text-thh-text-muted font-normal">
                                    (URL identifier)
                                </span>
                            </label>
                            <input
                                className="border-thh-border w-full rounded-xl border px-3 py-2 font-mono text-sm"
                                placeholder="sakhi_circles"
                                value={form.data.slug}
                                onChange={(e) =>
                                    form.setData('slug', e.target.value)
                                }
                            />
                        </div>

                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={form.data.is_public}
                                onChange={(e) =>
                                    form.setData('is_public', e.target.checked)
                                }
                            />
                            Public on web and mobile
                        </label>
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={form.data.show_apply_form}
                                onChange={(e) =>
                                    form.setData(
                                        'show_apply_form',
                                        e.target.checked,
                                    )
                                }
                            />
                            Show apply form
                        </label>

                        <div className="flex justify-end gap-2 pt-2">
                            <button
                                type="button"
                                onClick={() => setCreating(false)}
                                className="rounded-xl px-4 py-2 text-sm"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={form.processing}
                                className="bg-thh-primary rounded-xl px-4 py-2 text-sm font-semibold text-white"
                            >
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            )}
        </AdminLayout>
    );
}
